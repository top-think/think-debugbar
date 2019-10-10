<?php

namespace think\debugbar;

use Closure;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\JavascriptRenderer;
use think\debugbar\collector\FilesCollector;
use think\debugbar\collector\RequestDataCollector;
use think\debugbar\collector\SessionCollector;
use think\debugbar\collector\ThinkCollector;
use think\event\LogWrite;
use think\Response;
use think\response\Redirect;
use think\Session;
use think\App;

class DebugBar extends \DebugBar\DebugBar
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function getJavascriptRenderer($baseUrl = '/debugbar', $basePath = null)
    {
        if ($this->jsRenderer === null) {
            $this->jsRenderer = new JavascriptRenderer($this, $baseUrl, $basePath);
        }
        return $this->jsRenderer;
    }

    /**
     * Starts a measure
     *
     * @param string $name  Internal name, used to stop the measure
     * @param string $label Public name
     */
    public function startMeasure($name, $label = null)
    {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->startMeasure($name, $label);
        }
    }

    /**
     * Stops a measure
     *
     * @param string $name
     */
    public function stopMeasure($name)
    {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            try {
                $collector->stopMeasure($name);
            } catch (\Exception $e) {
                //  $this->addThrowable($e);
            }
        }
    }

    /**
     * Adds a measure
     *
     * @param string $label
     * @param float  $start
     * @param float  $end
     */
    public function addMeasure($label, $start, $end)
    {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->addMeasure($label, $start, $end);
        }
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * @param string  $label
     * @param Closure $closure
     */
    public function measure($label, Closure $closure)
    {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->measure($label, $closure);
        } else {
            $closure();
        }
    }

    public function addMessage($message, $label = 'info')
    {
        if ($this->hasCollector('messages')) {
            /** @var MessagesCollector $collector */
            $collector = $this->getCollector('messages');
            $collector->addMessage($message, $label);
        }
    }

    public function init()
    {
        $this->addCollector(new ThinkCollector($this->app));
        $this->addCollector(new PhpInfoCollector());
        $this->addCollector(new MessagesCollector());

        $logger = new MessagesCollector('log');
        $this['messages']->aggregate($logger);

        $this->app->log->listen(function (LogWrite $event) use ($logger) {
            foreach ($event->log as $channel => $logs) {
                foreach ($logs as $log) {
                    $logger->addMessage(
                        '[' . date('H:i:s') . '] ' . $log,
                        $channel,
                        false
                    );
                }
            }
        });

        $this->addCollector(new RequestDataCollector($this->app->request));
        $this->addCollector(new TimeDataCollector($this->app->request->time()));
        $this->addCollector(new MemoryCollector());

        //配置
        $configCollector = new ConfigCollector();
        $configCollector->setData($this->app->config->get());
        $this->addCollector($configCollector);

        //文件
        $this->addCollector(new FilesCollector($this->app));
    }

    public function addCollector(DataCollectorInterface $collector)
    {
        parent::addCollector($collector);

        if (method_exists($collector, 'useHtmlVarDumper')) {
            $collector->useHtmlVarDumper();
        }

        return $this;
    }

    public function inject(Response $response)
    {
        if ($response instanceof Redirect) {
            return;
        }

        if ($this->app->exists(Session::class)) {
            $this->addCollector(new  SessionCollector($this->app->make(Session::class)));
        }

        $content = $response->getContent();

        //把缓冲区的日志写入
        $this->app->log->save();

        $renderer = $this->getJavascriptRenderer();

        $renderedContent = $renderer->renderHead() . $renderer->render();

        // trace调试信息注入
        $pos = strripos($content, '</body>');
        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $renderedContent . substr($content, $pos);
        } else {
            $content = $content . $renderedContent;
        }
        $response->content($content);
    }
}
