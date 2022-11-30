<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Annotation;

use function array_map;
use function explode;
use function implode;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tag;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Generic;
final class CompactedFormatter implements Formatter
{
    public function format(Tag $tag) : string
    {
        if (!$tag instanceof Generic) {
            return \trim('@' . $tag->getName());
        }
        $description = (string) $tag;
        if (!\str_starts_with($description, '(')) {
            return \trim('@' . $tag->getName());
        }
        $description = implode('', array_map('trim', explode("\n", (string) $tag)));
        return \trim('@' . $tag->getName() . $description);
    }
}
