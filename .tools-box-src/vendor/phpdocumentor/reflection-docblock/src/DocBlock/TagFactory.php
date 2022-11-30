<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock;

use InvalidArgumentException;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Context as TypeContext;
interface TagFactory
{
    public function addParameter(string $name, $value) : void;
    public function create(string $tagLine, ?TypeContext $context = null) : Tag;
    public function addService(object $service) : void;
    public function registerTagHandler(string $tagName, string $handler) : void;
}
