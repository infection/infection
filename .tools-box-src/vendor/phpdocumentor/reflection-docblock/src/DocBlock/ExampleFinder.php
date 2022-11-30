<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Example;
use function array_slice;
use function file;
use function getcwd;
use function implode;
use function is_readable;
use function rtrim;
use function sprintf;
use function trim;
use const DIRECTORY_SEPARATOR;
class ExampleFinder
{
    private $sourceDirectory = '';
    private $exampleDirectories = [];
    public function find(Example $example) : string
    {
        $filename = $example->getFilePath();
        $file = $this->getExampleFileContents($filename);
        if (!$file) {
            return sprintf('** File not found : %s **', $filename);
        }
        return implode('', array_slice($file, $example->getStartingLine() - 1, $example->getLineCount()));
    }
    public function setSourceDirectory(string $directory = '') : void
    {
        $this->sourceDirectory = $directory;
    }
    public function getSourceDirectory() : string
    {
        return $this->sourceDirectory;
    }
    public function setExampleDirectories(array $directories) : void
    {
        $this->exampleDirectories = $directories;
    }
    public function getExampleDirectories() : array
    {
        return $this->exampleDirectories;
    }
    private function getExampleFileContents(string $filename) : ?array
    {
        $normalizedPath = null;
        foreach ($this->exampleDirectories as $directory) {
            $exampleFileFromConfig = $this->constructExamplePath($directory, $filename);
            if (is_readable($exampleFileFromConfig)) {
                $normalizedPath = $exampleFileFromConfig;
                break;
            }
        }
        if (!$normalizedPath) {
            if (is_readable($this->getExamplePathFromSource($filename))) {
                $normalizedPath = $this->getExamplePathFromSource($filename);
            } elseif (is_readable($this->getExamplePathFromExampleDirectory($filename))) {
                $normalizedPath = $this->getExamplePathFromExampleDirectory($filename);
            } elseif (is_readable($filename)) {
                $normalizedPath = $filename;
            }
        }
        $lines = $normalizedPath && is_readable($normalizedPath) ? file($normalizedPath) : \false;
        return $lines !== \false ? $lines : null;
    }
    private function getExamplePathFromExampleDirectory(string $file) : string
    {
        return getcwd() . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . $file;
    }
    private function constructExamplePath(string $directory, string $file) : string
    {
        return rtrim($directory, '\\/') . DIRECTORY_SEPARATOR . $file;
    }
    private function getExamplePathFromSource(string $file) : string
    {
        return sprintf('%s%s%s', trim($this->getSourceDirectory(), '\\/'), DIRECTORY_SEPARATOR, trim($file, '"'));
    }
}
