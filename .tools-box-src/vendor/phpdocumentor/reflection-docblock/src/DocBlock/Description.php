<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter;
use function vsprintf;
class Description
{
    private $bodyTemplate;
    private $tags;
    public function __construct(string $bodyTemplate, array $tags = [])
    {
        $this->bodyTemplate = $bodyTemplate;
        $this->tags = $tags;
    }
    public function getBodyTemplate() : string
    {
        return $this->bodyTemplate;
    }
    public function getTags() : array
    {
        return $this->tags;
    }
    public function render(?Formatter $formatter = null) : string
    {
        if ($formatter === null) {
            $formatter = new PassthroughFormatter();
        }
        $tags = [];
        foreach ($this->tags as $tag) {
            $tags[] = '{' . $formatter->format($tag) . '}';
        }
        return vsprintf($this->bodyTemplate, $tags);
    }
    public function __toString() : string
    {
        return $this->render();
    }
}
