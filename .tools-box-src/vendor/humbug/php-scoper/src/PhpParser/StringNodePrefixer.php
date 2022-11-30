<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\PhpScoper;
use _HumbugBoxb47773b41c19\PhpParser\Error as PhpParserError;
use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar\String_;
use function substr;
final class StringNodePrefixer
{
    public function __construct(private readonly PhpScoper $scoper)
    {
    }
    public function prefixStringValue(String_ $node) : void
    {
        try {
            $lastChar = substr($node->value, -1);
            $newValue = $this->scoper->scopePhp($node->value);
            if ("\n" !== $lastChar) {
                $newValue = substr($newValue, 0, -1);
            }
            $node->value = $newValue;
        } catch (PhpParserError $error) {
        }
    }
}
