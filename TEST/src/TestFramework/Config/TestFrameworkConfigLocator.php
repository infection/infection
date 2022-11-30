<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Config;

use function file_exists;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Locator\FileOrDirectoryNotFound;
use function _HumbugBox9658796bb9f0\Safe\realpath;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
final class TestFrameworkConfigLocator implements TestFrameworkConfigLocatorInterface
{
    private const DEFAULT_EXTENSIONS = ['xml', 'yml', 'xml.dist', 'yml.dist', 'dist.xml', 'dist.yml'];
    public function __construct(private string $configDir)
    {
    }
    public function locate(string $testFrameworkName, ?string $customDir = null) : string
    {
        $dir = $customDir ?: $this->configDir;
        $triedFiles = [];
        foreach (self::DEFAULT_EXTENSIONS as $extension) {
            $conf = sprintf('%s/%s.%s', $dir, $testFrameworkName, $extension);
            if (file_exists($conf)) {
                return realpath($conf);
            }
            $triedFiles[] = sprintf('%s.%s', $testFrameworkName, $extension);
        }
        throw FileOrDirectoryNotFound::multipleFilesDoNotExist($dir, $triedFiles);
    }
}
