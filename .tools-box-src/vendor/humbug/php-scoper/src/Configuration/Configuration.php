<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Patcher\Patcher;
use InvalidArgumentException;
use function _HumbugBoxb47773b41c19\Safe\preg_match;
use function sprintf;
final class Configuration
{
    private const PREFIX_PATTERN = '/^[\\p{L}\\d_\\\\]+$/u';
    private readonly string $prefix;
    public function __construct(private ?string $path, private ?string $outputDir, string $prefix, private array $filesWithContents, private array $excludedFilesWithContents, private Patcher $patcher, private SymbolsConfiguration $symbolsConfiguration)
    {
        self::validatePrefix($prefix);
        $this->prefix = $prefix;
    }
    public function getPath() : ?string
    {
        return $this->path;
    }
    public function getOutputDir() : ?string
    {
        return $this->outputDir;
    }
    public function withPrefix(string $prefix) : self
    {
        return new self($this->path, $this->outputDir, $prefix, $this->filesWithContents, $this->excludedFilesWithContents, $this->patcher, $this->symbolsConfiguration);
    }
    public function getPrefix() : string
    {
        return $this->prefix;
    }
    public function withFilesWithContents(array $filesWithContents) : self
    {
        return new self($this->path, $this->outputDir, $this->prefix, $filesWithContents, $this->excludedFilesWithContents, $this->patcher, $this->symbolsConfiguration);
    }
    public function getFilesWithContents() : array
    {
        return $this->filesWithContents;
    }
    public function getExcludedFilesWithContents() : array
    {
        return $this->excludedFilesWithContents;
    }
    public function withPatcher(Patcher $patcher) : self
    {
        return new self($this->path, $this->outputDir, $this->prefix, $this->filesWithContents, $this->excludedFilesWithContents, $patcher, $this->symbolsConfiguration);
    }
    public function getPatcher() : Patcher
    {
        return $this->patcher;
    }
    public function getSymbolsConfiguration() : SymbolsConfiguration
    {
        return $this->symbolsConfiguration;
    }
    private static function validatePrefix(string $prefix) : void
    {
        if (1 !== preg_match(self::PREFIX_PATTERN, $prefix)) {
            throw new InvalidArgumentException(sprintf('The prefix needs to be composed solely of letters, digits and backslashes (as namespace separators). Got "%s"', $prefix));
        }
        if (preg_match('/\\\\{2,}/', $prefix)) {
            throw new InvalidArgumentException(sprintf('Invalid namespace separator sequence. Got "%s"', $prefix));
        }
    }
}
