<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Reference;

use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class Url implements Reference
{
    private $uri;
    public function __construct(string $uri)
    {
        Assert::stringNotEmpty($uri);
        $this->uri = $uri;
    }
    public function __toString() : string
    {
        return $this->uri;
    }
}
