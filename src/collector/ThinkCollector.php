<?php

namespace think\debugbar\collector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use think\App;

class ThinkCollector extends DataCollector implements Renderable
{
    protected $app;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    function collect()
    {
        return [
            "version" => $this->app->version(),
        ];
    }

    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    function getName()
    {
        return 'think';
    }

    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see DebugBar\JavascriptRenderer::addControl()}
     *
     * @return array
     */
    function getWidgets()
    {
        return [
            "version" => [
                "icon"    => "github",
                "tooltip" => "Version",
                "map"     => "think.version",
                "default" => "",
            ],
        ];
    }
}
