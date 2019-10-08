<?php

namespace think\debugbar\middleware;

use think\debugbar\DebugBar;
use think\helper\Str;
use think\Log;
use think\Request;

class InjectDebugbar
{
    protected $debugbar;
    protected $log;
    protected $app;

    public function __construct(DebugBar $debugbar, Log $log)
    {
        $this->debugbar = $debugbar;
        $this->log      = $log;
    }

    public function handle(Request $request, $next)
    {
        if (Str::startsWith($request->pathinfo(), "debugbar/")) {
            return $next($request);
        }

        $this->debugbar->init();

        $response = $next($request);

        $this->debugbar->inject($response);

        return $response;
    }

}
