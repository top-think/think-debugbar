<?php

namespace think\debugbar;

use think\debugbar\controller\AssetController;
use think\debugbar\middleware\InjectDebugbar;
use think\Route;

class Service extends \think\Service
{
    public function boot()
    {
        $this->app->middleware->add(InjectDebugbar::class);
        $this->registerRoutes(function (Route $route) {
            $route->get("debugbar/:path", AssetController::class . "@index")->pattern(['path' => '[\w\.\/\-_]+']);
        });
    }
}
