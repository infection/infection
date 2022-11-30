<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\FileSystem\Locator;

interface Locator
{
    public function locate(string $fileName) : string;
    public function locateOneOf(array $fileNames) : string;
}
