<?php
namespace LaravelNemo\Interface;

use LaravelNemo\Doc\FileStore;

interface IDocGenerator
{
    public function generate(): FileStore;
}
