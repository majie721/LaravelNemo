<?php

namespace LaravelNemo\Library;

class App
{
    /**
     * 是否是调试模式
     *
     * @return bool
     */
    public static function isDebug(): bool
    {
        return (bool)config('app.debug', false);
    }

    public static function debugger():array{
        if (app()->bound('debugbar') && app('debugbar')->isEnabled()) {
            $info = app('debugbar')->getData();
            return [
                'time'=>$info['time'],
                'queries'=>$info['queries'],
                'memory'=>$info['memory'],
                'exceptions'=>$info['exceptions'],
            ];
        }
        return [];
    }
}
