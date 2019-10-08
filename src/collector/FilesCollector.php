<?php

namespace think\debugbar\collector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use think\App;
use think\helper\Str;

class FilesCollector extends DataCollector implements Renderable
{
    protected $app;

    protected $ignored = [
        'vendor/maximebf/debugbar',
        'vendor/topthink/think-debugbar',
    ];

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $files = $this->getIncludedFiles();

        $included = [];
        foreach ($files as $file) {

            if (Str::contains($file, $this->ignored)) {
                continue;
            }

            $included[] = [
                'message'   => "'" . $this->stripBasePath($file) . "',",
                // Use PHP syntax so we can copy-paste to compile config file.
                'is_string' => true,
            ];
        }

        return [
            'messages' => $included,
            'count'    => count($included),
        ];
    }

    /**
     * Get the files included on load.
     *
     * @return array
     */
    protected function getIncludedFiles()
    {
        return get_included_files();
    }

    /**
     * Remove the basePath from the paths, so they are relative to the base
     *
     * @param $path
     * @return string
     */
    protected function stripBasePath($path)
    {
        return ltrim(str_replace($this->app->getRootPath(), '', $path), '/');
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        $name = $this->getName();
        return [
            "$name"       => [
                "icon"    => "files-o",
                "widget"  => "PhpDebugBar.Widgets.MessagesWidget",
                "map"     => "$name.messages",
                "default" => "{}",
            ],
            "$name:badge" => [
                "map"     => "$name.count",
                "default" => "null",
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'files';
    }
}
