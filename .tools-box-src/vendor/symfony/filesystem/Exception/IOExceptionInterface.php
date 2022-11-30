<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Exception;

interface IOExceptionInterface extends ExceptionInterface
{
    public function getPath() : ?string;
}
