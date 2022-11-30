<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\FileSystem;

use const DIRECTORY_SEPARATOR;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_replace;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Path;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class TmpDirProvider
{
    private const BASE_DIR_NAME = 'infection';
    public function providePath(string $baseTmpDir) : string
    {
        Assert::true(Path::isAbsolute($baseTmpDir), sprintf('Expected the temporary directory passed to be an absolute path. Got "%s"', $baseTmpDir));
        return str_replace([DIRECTORY_SEPARATOR, '//'], ['/', '/'], sprintf('%s/%s', $baseTmpDir, self::BASE_DIR_NAME));
    }
}
