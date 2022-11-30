<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\FileSystem\Locator;

use function implode;
use RuntimeException;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class FileOrDirectoryNotFound extends RuntimeException
{
    public static function fromFileName(string $file, array $roots) : self
    {
        Assert::allString($roots);
        return new self(sprintf('Could not locate the file/directory "%s"%s.', $file, $roots === [] ? '' : sprintf(' in "%s"', implode('", "', $roots))));
    }
    public static function multipleFilesDoNotExist(string $path, array $files) : self
    {
        return new self(sprintf('The path "%s" does not contain any of the requested files: "%s"', $path, implode('", "', $files)));
    }
    public static function fromFiles(array $files, array $roots) : self
    {
        Assert::allString($files);
        Assert::allString($roots);
        return new self($files === [] ? 'Could not locate any files (no file provided).' : sprintf('Could not locate the files "%s"%s', implode('", "', $files), $roots === [] ? '' : sprintf(' in "%s"', implode('", "', $roots))));
    }
}
