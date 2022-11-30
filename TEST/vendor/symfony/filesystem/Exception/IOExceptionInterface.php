<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Exception;

interface IOExceptionInterface extends ExceptionInterface
{
    public function getPath();
}
