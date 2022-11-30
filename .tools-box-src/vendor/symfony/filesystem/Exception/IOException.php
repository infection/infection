<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Exception;

class IOException extends \RuntimeException implements IOExceptionInterface
{
    private ?string $path;
    public function __construct(string $message, int $code = 0, \Throwable $previous = null, string $path = null)
    {
        $this->path = $path;
        parent::__construct($message, $code, $previous);
    }
    public function getPath() : ?string
    {
        return $this->path;
    }
}
