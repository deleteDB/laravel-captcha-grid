<?php
return [
    'image' => [
        'path' => env('GRID_CAPTCHA_IMAGE_PATH', storage_path('gridcaptcha\image')),
        'suffix' => env('GRID_CAPTCHA_IMAGE_SUFFIX', 'jpg'),
        'quality' => env('GRID_CAPTCHA_IMAGE_QUALITY', 70),
        'wide' => env('GRID_CAPTCHA_IMAGE_WIDE', 300),
        'high' => env('GRID_CAPTCHA_IMAGE_HIGH', 300),
    ],
    'captcha' => [
        'validity' => env('GRID_CAPTCHA_IMAGE_VALIDITY', 180),
        'cache_key' => env('GRID_CAPTCHA_IMAGE_CACHE_KEY', 'grid_captcha'),
        'key_length' => env('GRID_CAPTCHA_IMAGE_KEY_LENGTH', 64),
        'key_string' => env('GRID_CAPTCHA_IMAGE_KEY_STRING', 'captcha_key'),
        'code_string' => env('GRID_CAPTCHA_IMAGE_CODE_STRING', 'captcha_code'),
    ],
];
