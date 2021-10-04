<?php
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
