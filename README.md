# laravel-gridCaptcha
Laravel quickly creates a verification code tool similar to Google verification code

laravel 快速创建一个类似于 Google 点图验证码的本地验证码工具

## 介绍

`laravel-gridCaptcha` 生成类似于谷歌点图验证码的小工具，因为现在PHP大部分生成的验证码，对于恶意者来说很容易识别，而这套小工具很简单但是对于机器人来说需要进行机器学习，恶意者攻击的成本也就增加了，但是这套小工具不同于谷歌验证码需要机器学习，只需要在本地配置好相应的文件即可。因为生成的验证码图片都是读取文件进行生成，所以建议使用Redis进行缓存，代码默认缓存有目录以及目录下的图片 ps:因为是第一次开源，有很多代码写的不是很好，欢迎大佬提出修改意见。

## 预览
![Preview](https://lingshulian.com/s/t/b74e9cf548e1e03c)

## 安装

支持 Laravel 8 以上版本：

```shell
 composer require deletedb/laravel-captcha-grid
```
### 配置项说明

- 发布配置文件

```shell
php artisan vendor:publish --provider="Deletedb\Laravel\Providers\LaravelServiceProvider"

```

```
config/gridcaptcha.php
```

```php
return [
    //生成验证码图片配置
    'image' => [
        //验证码图片路径
        'path' => env('GRID_CAPTCHA_IMAGE_PATH', storage_path('gridcaptcha\image')),
        //从验证码图片路径中获取的文件后缀名
        'suffix' => env('GRID_CAPTCHA_IMAGE_SUFFIX', 'jpg'),
        //生成验证码质量
        'quality' => env('GRID_CAPTCHA_IMAGE_QUALITY', 70),
        //生产验证码宽
        'wide' => env('GRID_CAPTCHA_IMAGE_WIDE', 300),
        //生产验证码高
        'high' => env('GRID_CAPTCHA_IMAGE_HIGH', 300),
    ],
    //验证码配置
    'captcha' => [
        //生成的验证码过期时间 单位秒
        'validity' => env('GRID_CAPTCHA_IMAGE_VALIDITY', 180),
        //验证码缓存的key
        'cache_key' => env('GRID_CAPTCHA_IMAGE_CACHE_KEY', 'grid_captcha'),
        //验证码生成的key长度
        'key_length' => env('GRID_CAPTCHA_IMAGE_KEY_LENGTH', 64),
        //自定义效验验证码key字段
        'key_string' => env('GRID_CAPTCHA_IMAGE_KEY_STRING', 'captcha_key'),
        //自定义效验验证码code字段
        'code_string' => env('GRID_CAPTCHA_IMAGE_CODE_STRING', 'captcha_code'),
    ],
];

```


## 使用

- 生成验证码
```php
<?php

class TestController
{
    public function index(Request $request)
    {
        $captcha = new GridCaptcha();
        //可以设置存储在验证码中的信息 如果验证码成功这段信息将返回
        $captcha_data = [
           'mobile' => '100xxxxx121'
        ];
        return $captcha->get($captcha_data);
    }
}
```

- 生成结果
```json5
{
  "hint": "猴子",//提示文本
  "captcha_key": "Qh8kHYF4C....",//验证码key
  "image": "data:image/jpeg;base64,/9j/...."//base64验证码图片 -- 前端渲染显示
}
```

- 效验验证码

```php
<?php

// http请求示例:
// POST http://xxx.com/api/auth/captcha -> 自己定义路由
// Body: -> 请求体
// {
//     "captcha_key":"Qh8kHYF4C...."//验证码key
//     "captcha_code":"0543"//用户输入的验证码
// }

class TestController
{
    /**
     * Request 的方式进行效验
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse
     */
    public function requestCheck(Request $request)
    {
        //注意: $request 里面需要传递 配置文件中的 code_string 以及 key_string 看上方 http 请求示例
        $captcha = new GridCaptcha();
        //注意一定要使用 === 还需要判断返回的数据类型
        if ($captcha_data = $captcha->checkRequest($request) === false) {
            return response()->json([
                'message' => '验证码错误',
                'code' => 401,
            ]);
        }
        //此处您可以进行业务逻辑处理返回只是方便查看，比如可以获取到上方设置在验证码中的数据 如：上方设置的是手机号 ， 您这里可以获取验证码中的手机号，当效验成功发送短信验证码等...
        return $captcha_data;
    }


    /**
     * 传值 的方式进行效验
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse
     */
    public function check(Request $request)
    {
        $captcha = new GridCaptcha();
        //注意一定要使用 === 还需要判断返回的数据类型
        if ($captcha_data = $captcha->check('Qh8kHYF4C...', '1574') === false) {
            return response()->json([
                'message' => '验证码错误',
                'code' => 401,
            ]);
        }
        return $captcha_data;
    }
}

```

- 效验成功返回结果
```json
{
  "mobile" : "100xxxxx121"
}
```


- 本地化提示

 ```
resources/lang/grid-captcha.php
```
```php
<?php
//一个图片目录对应一个提示
return [
    'banmaxian' => '斑马线',
    'gongjiaoche' => '公交车',
    'heiban' => '黑板',
    'honglvdeng' => '红绿灯',
    'hongzao' => '红枣',
    'houzi' => '猴子',
    'qianbi' => '铅笔',
    'shutiao' => '薯条',
    'xiaofangshuan' => '消防栓',
    'zhenglong' => '蒸笼',
];
```

- 新增验证码图片

  例：新增一个类型为 `pingguo` 验证码类型的图片，需要在配置文件中的 `image.path` 目录下创建名为 `pingguo` 的目录并且把相关类型的图片文件存放在 `pingguo` 目录，新增一个类型至少要有四张相关类型的图片，不限制文件名，只要文件后缀名是配置文件中指定的即可如下:
```
─storage
    └─gridcaptcha
        └─image
            ├─pingguo
            │       1.jpg
            │       10.jpg
            │       11.jpg
            │       12.jpg
            │       13.jpg
            │
            ├─gongjiaoche
            │       1.jpg
            │       10.jpg
            │       11.jpg
            │       12.jpg
```

## 特别说明
因为读取文件是缓存消耗I/O的操作所以我推荐使用Redis进行缓存，此工具默认使用了缓存了的只需要在 `.env` 文件修改 `CACHE_DRIVER=redis` 即可

## License

MIT