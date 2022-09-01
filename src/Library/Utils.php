<?php
namespace LaravelNemo\Library;

use Illuminate\Support\Str;

class Utils
{
    /**
     * 是否标量
     * @param string $type
     * @return bool
     */
    public static function isScalar(string $type): bool
    {
        return in_array($type, ['int', 'bool', 'string', 'float'], true);
    }

    /**
     * @param string $type
     * @return bool
     */
    public static function isClass(string $type): bool
    {
        return !in_array($type, ['int', 'bool', 'string', 'float','array'], true);
    }

    /**
     * @param mixed $val
     * @return bool
     */
    public static function isTrueFloat(mixed $val):bool
    {
        $pattern = '/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/';

        return (!is_bool($val) && (is_float($val) || preg_match($pattern, trim($val))));
    }

    /**
     * 下划线转驼峰
     * step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
     * step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
     *
     * @param string $uncamelized_words
     * @param string $separator
     * @return string
     */
    public static function camelize(string $uncamelized_words, string $separator = '_'): string
    {
        return str_replace(' ', '', ucwords(str_replace($separator, ' ', $uncamelized_words)));
    }

    /**
     * 驼峰命名转下划线命名
     * 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
     *
     * @param string $camelCaps
     * @param string $separator
     * @return string
     */
    public static function uncamelize(string $camelCaps, string $separator = '_'): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }


    /**
     * @return mixed|null
     */
    public static function uniqueId()
    {
        return Container::get('NEMO_UNIQUE_ID', function () {
            return  sprintf("%s%s",(new \DateTime())->format('YmdHisu'),strtoupper(Str::random(6)));
        });
    }


    /**
     * @return void
     */
    public static function resetUniqueId(): void
    {
        Container::unset('NEMO_UNIQUE_ID');
    }

}
