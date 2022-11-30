<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tag;
interface DocBlockFactoryInterface
{
    public static function createInstance(array $additionalTags = []) : DocBlockFactory;
    public function create($docblock, ?Types\Context $context = null, ?Location $location = null) : DocBlock;
}
