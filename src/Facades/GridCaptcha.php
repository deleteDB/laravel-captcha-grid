<?php

namespace Deletedb\Laravel\Facades;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array get(array $captchaData = [])
 * @method static false|array check(string $captchaKey, string $captchaCode, bool $checkAndDelete = true)
 * @method static false|array checkRequest(Request $request, bool $checkAndDelete = true)
 * @method static string getCaptchaCode()
 * @see Deletedb\Laravel\GridCaptcha
 */
class GridCaptcha extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'GridCaptcha';
    }
}
