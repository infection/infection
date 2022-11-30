<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tag;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
use function array_key_exists;
use function preg_match;
use function rawurlencode;
use function str_replace;
use function strpos;
use function trim;
final class Example implements Tag, Factory\StaticMethod
{
    private $filePath;
    private $isURI;
    private $startingLine;
    private $lineCount;
    private $content;
    public function __construct(string $filePath, bool $isURI, int $startingLine, int $lineCount, ?string $content)
    {
        Assert::stringNotEmpty($filePath);
        Assert::greaterThanEq($startingLine, 1);
        Assert::greaterThanEq($lineCount, 0);
        $this->filePath = $filePath;
        $this->startingLine = $startingLine;
        $this->lineCount = $lineCount;
        if ($content !== null) {
            $this->content = trim($content);
        }
        $this->isURI = $isURI;
    }
    public function getContent() : string
    {
        if ($this->content === null || $this->content === '') {
            $filePath = $this->filePath;
            if ($this->isURI) {
                $filePath = $this->isUriRelative($this->filePath) ? str_replace('%2F', '/', rawurlencode($this->filePath)) : $this->filePath;
            }
            return trim($filePath);
        }
        return $this->content;
    }
    public function getDescription() : ?string
    {
        return $this->content;
    }
    public static function create(string $body) : ?Tag
    {
        if (!preg_match('/^\\s*(?:(\\"[^\\"]+\\")|(\\S+))(?:\\s+(.*))?$/sux', $body, $matches)) {
            return null;
        }
        $filePath = null;
        $fileUri = null;
        if ($matches[1] !== '') {
            $filePath = $matches[1];
        } else {
            $fileUri = $matches[2];
        }
        $startingLine = 1;
        $lineCount = 0;
        $description = null;
        if (array_key_exists(3, $matches)) {
            $description = $matches[3];
            if (preg_match('/^([1-9]\\d*)(?:\\s+((?1))\\s*)?(.*)$/sux', $matches[3], $contentMatches)) {
                $startingLine = (int) $contentMatches[1];
                if (isset($contentMatches[2])) {
                    $lineCount = (int) $contentMatches[2];
                }
                if (array_key_exists(3, $contentMatches)) {
                    $description = $contentMatches[3];
                }
            }
        }
        return new static($filePath ?? $fileUri ?? '', $fileUri !== null, $startingLine, $lineCount, $description);
    }
    public function getFilePath() : string
    {
        return trim($this->filePath, '"');
    }
    public function __toString() : string
    {
        $filePath = $this->filePath;
        $isDefaultLine = $this->startingLine === 1 && $this->lineCount === 0;
        $startingLine = !$isDefaultLine ? (string) $this->startingLine : '';
        $lineCount = !$isDefaultLine ? (string) $this->lineCount : '';
        $content = (string) $this->content;
        return $filePath . ($startingLine !== '' ? ($filePath !== '' ? ' ' : '') . $startingLine : '') . ($lineCount !== '' ? ($filePath !== '' || $startingLine !== '' ? ' ' : '') . $lineCount : '') . ($content !== '' ? ($filePath !== '' || $startingLine !== '' || $lineCount !== '' ? ' ' : '') . $content : '');
    }
    private function isUriRelative(string $uri) : bool
    {
        return strpos($uri, ':') === \false;
    }
    public function getStartingLine() : int
    {
        return $this->startingLine;
    }
    public function getLineCount() : int
    {
        return $this->lineCount;
    }
    public function getName() : string
    {
        return 'example';
    }
    public function render(?Formatter $formatter = null) : string
    {
        if ($formatter === null) {
            $formatter = new Formatter\PassthroughFormatter();
        }
        return $formatter->format($this);
    }
}
