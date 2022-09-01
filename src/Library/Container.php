<?php

namespace LaravelNemo\Library;

class Container
{
    private static $container = [];

    /**
     * 获取容器
     *
     * @param string $key
     * @param \Closure|mixed $value
     * @return mixed
     */
    public static function get(string $key, $value = null)
    {
        if ($value !== null && ! self::has($key)) {
            self::set($key, $value);
        }
        return self::$container[$key] ?? null;
    }

    /**
     * 设置容器
     *
     * @param string $key
     * @param $value
     * @return mixed
     */
    public static function set(string $key, $value)
    {
        if ($value instanceof \Closure) {
            $value = $value();
        }
        self::$container[$key] = $value;
        return $value;
    }


    /**
     * 销毁容器指定key
     *
     * @param string$key
     * @return mixed
     */
    public static function unset(string $key): void
    {
        if (self::has($key)) {
            unset(self::$container[$key]);
        }
    }


    /**
     * 清空容器
     *
     * @return mixed
     */
    public static function clear(): void
    {
        self::$container = null;
    }

    public static function has(string $key): bool
    {
        if (array_key_exists($key, self::$container)) {
            return true;
        }
        return false;
    }

    public static function all()
    {
        return self::$container;
    }
}
