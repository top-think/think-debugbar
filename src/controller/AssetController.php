<?php

namespace think\debugbar\controller;

use think\debugbar\DebugBar;
use think\helper\Arr;

class AssetController
{
    public function index(DebugBar $debugbar, $path)
    {
        $renderer = $debugbar->getJavascriptRenderer();

        $basePath = $renderer->getBasePath();

        $filename = $basePath . '/' . $path;

        $ext = strtolower(Arr::last(explode('.', $filename)));

        $types = [
            'css' => 'text/css',
            'js'  => 'application/javascript',
        ];

        if (array_key_exists($ext, $types)) {
            $mime = $types[$ext];
        } else {
            $mime = mime_content_type($filename);
        }

        return response(file_get_contents($filename))->contentType($mime);
    }
}
