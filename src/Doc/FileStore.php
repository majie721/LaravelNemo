<?php

namespace LaravelNemo\Doc;

class FileStore
{
    /**
     * @param string $content 文件内容
     * @param string $ext 文件后缀名,md.text
     */
    public function __construct(protected string $content,protected string $ext)
    {

    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * 存储文件
     *
     * @param string $path      写入路径
     * @param bool $overwrite   是否覆盖
     * @return $this
     * @throws \RuntimeException
     */
    public function store(string $path, bool $overwrite = false):self
    {
        $this->ext && $path .= ".$this->ext";
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        if (file_exists($path) && !$overwrite) {
            throw new \RuntimeException(" 文件{$path}已存在");
        }

        file_put_contents($path, $this->content);

        return $this;
    }
}
