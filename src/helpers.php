<?php

use Deletedb\Laravel\Facades\GridCaptcha;

if (!function_exists('grid_captcha')) {

    /**
     * 当传递时快速创建验证码
     * @param array|null $captchaData
     * @return array|GridCaptcha
     */
    function grid_captcha(array $captchaData = null)
    {
        if ($captchaData !== null) {
            return GridCaptcha::get($captchaData);
        }
        return GridCaptcha::class;
    }
}
