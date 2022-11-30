<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Composer;

use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class ComposerFile
{
    private $path;
    private $contents;
    public static function createEmpty() : self
    {
        return new self(null, []);
    }
    public function __construct(?string $path, array $contents)
    {
        Assert::nullOrNotEmpty($path);
        if (null === $path) {
            Assert::same([], $contents);
        }
        $this->path = $path;
        $this->contents = $contents;
    }
    public function getPath() : ?string
    {
        return $this->path;
    }
    public function getDecodedContents() : array
    {
        return $this->contents;
    }
}
