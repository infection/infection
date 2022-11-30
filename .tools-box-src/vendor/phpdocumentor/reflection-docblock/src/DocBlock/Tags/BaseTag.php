<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Description;
abstract class BaseTag implements DocBlock\Tag
{
    protected $name = '';
    protected $description;
    public function getName() : string
    {
        return $this->name;
    }
    public function getDescription() : ?Description
    {
        return $this->description;
    }
    public function render(?Formatter $formatter = null) : string
    {
        if ($formatter === null) {
            $formatter = new Formatter\PassthroughFormatter();
        }
        return $formatter->format($this);
    }
}
