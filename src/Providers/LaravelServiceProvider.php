<?php

namespace Deletedb\Laravel\Providers;

use Deletedb\Laravel\GridCaptcha;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {
        $this->app->singleton('GridCaptcha', GridCaptcha::class);
    }

    public function boot()
    {
        $this->publishes([
            //发布配置文件
            dirname(__DIR__) . '/config' => config_path(),
            //发布语言包
            dirname(__DIR__) . '/resources' => resource_path(),
            //发布资源文件
            dirname(__DIR__) . '/storage' => storage_path(),
        ]);
    }
}
