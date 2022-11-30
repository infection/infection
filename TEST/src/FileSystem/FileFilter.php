<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\FileSystem;

use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\Trace;
use SplFileInfo;
interface FileFilter
{
    public function filter(iterable $input) : iterable;
}
