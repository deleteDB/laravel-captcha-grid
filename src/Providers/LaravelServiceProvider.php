<?php

namespace Deletedb\Laravel\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{

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
