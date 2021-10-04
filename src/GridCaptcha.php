<?php


namespace Deletedb\Laravel;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;
use Illuminate\Support\Str;

class GridCaptcha
{

    /**
     * 验证码图片路径
     * @var string
     */
    protected $captchaImagePath = '';

    /**
     * 验证码缓存key
     * @var string
     */
    protected $captchaCacheKey = 'grid_captcha';

    /**
     * 验证码效验从请求中获取的 Key
     * @var string
     */
    protected $captchaKeyString = 'captcha_key';

    /**
     * 验证码效验从请求中获取的Code key
     * @var string
     */
    protected $captchaKeyCodeString = 'captcha_code';

    /**
     * 验证码key长度
     * @var int
     */
    protected $captchaKeyLength = 64;

    /**
     * 验证码图片后缀
     * @var string
     */
    protected $imageSuffix = 'jpg';

    /**
     * 输出图片质量
     * @var int
     */
    protected $imageQuality = 70;

    /**
     * 生成验证码图片宽
     * @var int
     */
    protected $captchaImageWide = 300;

    /**
     * 生成验证码图片高
     * @var int
     */
    protected $captchaImageHigh = 300;

    /**
     * 验证码有效期(单位秒)
     * @var int
     */
    protected $captchaValidity = 180;

    /**
     * 存储验证码数据效验成功后返回
     * @var array
     */
    protected $captchaData = [];

    /**
     * 生成的随机验证码
     * @var string
     */
    protected $captchaCode = '';

    /**
     * 验证码key
     * @var string
     */
    protected $captchaKey = '';

    /**
     * 存储验证码图片路径
     * @var array
     */
    protected $imageFile = [];


    public function __construct()
    {
        //初始化配置
        $config = config('gridcaptcha');
        $this->captchaImagePath = $config['image']['path'];
        $this->imageSuffix = $config['image']['suffix'];
        $this->imageQuality = $config['image']['quality'];
        $this->captchaImageWide = $config['image']['wide'];
        $this->captchaImageHigh = $config['image']['high'];

        $this->captchaValidity = $config['captcha']['validity'];
        $this->captchaCacheKey = $config['captcha']['cache_key'];
        $this->captchaKeyLength = $config['captcha']['key_length'];
        $this->captchaKeyString = $config['captcha']['key_string'];
        $this->captchaKeyCodeString = $config['captcha']['code_string'];
    }

    /**
     * 获取验证码
     * @param array $captchaData
     * @return array
     * @throws InvalidArgumentException
     */
    public function get(array $captchaData = [])
    {
        $this->captchaData = $captchaData;
        $this->captchaCode = substr(str_shuffle('012345678'), 0, 4);
        $this->captchaKey = Str::random($this->captchaKeyLength);
        $this->imageFile = Cache::remember("$this->captchaCacheKey:path", 604800, function () {
            return $this->getImageFile();
        });
        Cache::set("$this->captchaCacheKey:data:$this->captchaKey", [
            'captcha_key' => $this->captchaKey,
            'captcha_code' => $this->captchaCode,
            'captcha_data' => $captchaData,
        ], $this->captchaValidity);
        return $this->generateIntCodeImg();
    }

    /**
     * 效验验证码是否正确
     * @param Request $request
     * @param bool $checkAndDelete 效验之后是否删除
     * @return false|array
     */
    public function check(Request $request, bool $checkAndDelete = true)
    {
        $input = $request->validate([
            $this->captchaKeyString => "required|string|size:$this->captchaKeyLength",
            $this->captchaKeyCodeString => 'required|integer|digits_between:1,4',
        ]);
        //判断是否获取到
        $captcha_data = $checkAndDelete
            ? Cache::pull("$this->captchaCacheKey:data:" . $input[$this->captchaKeyString], false)
            : Cache::get("$this->captchaCacheKey:data:" . $input[$this->captchaKeyString], false);
        if ($captcha_data === false) {
            return false;
        }
        //判断验证码是正确
        if (!empty(array_diff(
            str_split($captcha_data['captcha_code']),
            str_split($input[$this->captchaKeyCodeString])
        ))) {
            return false;
        }
        return $captcha_data['captcha_data'];
    }

    /**
     * 生成九宫格验证码图片
     * @return array
     */
    protected function generateIntCodeImg()
    {
        //随机获取正确的验证码
        $correct_str = array_rand($this->imageFile, 1);
        $correct_path = $this->imageFile[$correct_str];
        $correct_key = array_rand($correct_path, 4);
        //移除正确的验证码 [方便后面取错误验证码 , 不会重复取到正确的]
        unset($this->imageFile[$correct_str]);

        //循环获取正确的验证码图片
        $correct_img = [];
        foreach ($correct_key as $key) {
            $correct_img[] = $correct_path[$key];
        }

        //循环获取错误的验证码
        $error_key = array_rand($this->imageFile, 5);
        $error_img = [];
        foreach ($error_key as $path_key) {
            $error_path = $this->imageFile[$path_key];
            $error_img[] = $error_path[array_rand($error_path, 1)];
        }

        //对全部验证码图片打乱排序
        $code_array = str_split($this->captchaCode);
        $results_img = [];
        for ($i = 0; $i < 9; $i++) {
            $results_img[] = in_array($i, $code_array)
                ? array_shift($correct_img)
                : array_shift($error_img);
        }

        //处理提示文本
        $trans_key = "grid-captcha.$correct_str";
        $hint = trans($trans_key);
        if ($trans_key == $hint) {
            $hint = $correct_str;
        }

        //组合返回消息
        return [
            'hint' => $hint,
            'captcha_key' => $this->captchaKey,
            'image' => $this->combinationCaptchaImg($results_img),
        ];
    }


    /**
     * 组合验证码图片
     * @param array $imgPath
     * @return string
     */
    protected function combinationCaptchaImg(array $imgPath)
    {
        //初始化参数
        $space_x = $space_y = $start_x = $start_y = $line_x = 0;
        $pic_w = intval($this->captchaImageWide / 3);
        $pic_h = intval($this->captchaImageHigh / 3);

        //设置背景
        $background = imagecreatetruecolor($this->captchaImageWide, $this->captchaImageHigh);
        $color = imagecolorallocate($background, 255, 255, 255);
        imagefill($background, 0, 0, $color);
        imageColorTransparent($background, $color);

        foreach ($imgPath as $key => $path) {
            $keys = $key + 1;
            //图片换行
            if ($keys == 4 || $keys == 7) {
                $start_x = $line_x;
                $start_y = $start_y + $pic_h + $space_y;
            }
            //缓存中读取文件
            $gd_resource = imagecreatefromstring(Cache::remember("$this->captchaCacheKey:file:$path",
                604800,
                function () use ($path) {
                    return file_get_contents($path);
                })
            );
            imagecopyresized($background, $gd_resource, $start_x, $start_y, 0, 0,
                $pic_w, $pic_h, imagesx($gd_resource), imagesy($gd_resource));
            $start_x = $start_x + $pic_w + $space_x;
        }
        ob_start();
        imagejpeg($background, null, $this->imageQuality);
        //释放图片资源
        imagedestroy($background);
        return "data:image/jpeg;base64," . base64_encode(ob_get_clean());
    }


    /**
     * 获取验证码图片
     * @return array
     * @throws Exception
     */
    protected function getImageFile()
    {
        //获取验证码目录下面的图片
        $image_path = glob($this->captchaImagePath . '\*');
        $image_file = [];
        foreach ($image_path as $file) {
            $image_file[pathinfo($file)['basename'] ?? 'null'] = glob("$file\*.$this->imageSuffix");
        }
        unset($image_file['null']);
        if (empty($image_file)) {
            throw new Exception('找不到验证码图片');
        }
        return $image_file;
    }

    /**
     * 获取验证码
     * @return string
     */
    public function getCaptchaCode()
    {
        return $this->captchaCode;
    }
}
