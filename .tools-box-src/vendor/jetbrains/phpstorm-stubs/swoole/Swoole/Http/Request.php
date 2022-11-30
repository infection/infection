<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole\Http;

class Request
{
    public $fd = 0;
    public $streamId = 0;
    public $header;
    public $server;
    public $cookie;
    public $get;
    public $files;
    public $post;
    public $tmpfiles;
    public function __destruct()
    {
    }
    public function getContent()
    {
    }
    public function rawContent()
    {
    }
    public function getData()
    {
    }
    public static function create($options = null)
    {
    }
    public function parse($data)
    {
    }
    public function isCompleted()
    {
    }
    public function getMethod()
    {
    }
}
