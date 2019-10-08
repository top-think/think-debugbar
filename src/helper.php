<?php
if (!function_exists('debugbar')) {
    /**
     * Get the Debugbar instance
     *
     * @return \think\debugbar\DebugBar
     */
    function debugbar()
    {
        return app(\think\debugbar\DebugBar::class);
    }
}

if (!function_exists('trace')) {
    /**
     * 记录日志信息
     * @param mixed  $log   log信息 支持字符串和数组
     * @param string $level 日志级别
     * @return array|void
     */
    function trace($log, string $level = 'debug')
    {
        debugbar()->addMessage($log, $level);
    }
}

if (!function_exists('start_measure')) {
    /**
     * Starts a measure
     *
     * @param string $name  Internal name, used to stop the measure
     * @param string $label Public name
     */
    function start_measure($name, $label = null)
    {
        debugbar()->startMeasure($name, $label);
    }
}

if (!function_exists('stop_measure')) {
    /**
     * Stop a measure
     *
     * @param string $name Internal name, used to stop the measure
     */
    function stop_measure($name)
    {
        debugbar()->stopMeasure($name);
    }
}

if (!function_exists('add_measure')) {
    /**
     * Adds a measure
     *
     * @param string $label
     * @param float  $start
     * @param float  $end
     */
    function add_measure($label, $start, $end)
    {
        debugbar()->addMeasure($label, $start, $end);
    }
}

if (!function_exists('measure')) {
    /**
     * Utility function to measure the execution of a Closure
     *
     * @param string   $label
     * @param \Closure $closure
     */
    function measure($label, \Closure $closure)
    {
        debugbar()->measure($label, $closure);
    }
}
