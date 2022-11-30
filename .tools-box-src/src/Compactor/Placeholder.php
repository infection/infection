<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Compactor;

use function array_keys;
use function str_replace;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class Placeholder implements Compactor
{
    private array $placeholders;
    public function __construct(array $placeholders)
    {
        Assert::allScalar($placeholders);
        $this->placeholders = $placeholders;
    }
    public function compact(string $file, string $contents) : string
    {
        return str_replace(array_keys($this->placeholders), $this->placeholders, $contents);
    }
}
