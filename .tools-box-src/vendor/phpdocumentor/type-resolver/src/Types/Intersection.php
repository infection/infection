<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
/**
@psalm-immutable
*/
final class Intersection extends AggregatedType
{
    public function __construct(array $types)
    {
        parent::__construct($types, '&');
    }
}
