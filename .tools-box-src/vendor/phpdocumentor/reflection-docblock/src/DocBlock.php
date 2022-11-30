<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tag;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\TagWithType;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class DocBlock
{
    private $summary;
    private $description;
    private $tags = [];
    private $context;
    private $location;
    private $isTemplateStart;
    private $isTemplateEnd;
    public function __construct(string $summary = '', ?DocBlock\Description $description = null, array $tags = [], ?Types\Context $context = null, ?Location $location = null, bool $isTemplateStart = \false, bool $isTemplateEnd = \false)
    {
        Assert::allIsInstanceOf($tags, Tag::class);
        $this->summary = $summary;
        $this->description = $description ?: new DocBlock\Description('');
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
        $this->context = $context;
        $this->location = $location;
        $this->isTemplateEnd = $isTemplateEnd;
        $this->isTemplateStart = $isTemplateStart;
    }
    public function getSummary() : string
    {
        return $this->summary;
    }
    public function getDescription() : DocBlock\Description
    {
        return $this->description;
    }
    public function getContext() : ?Types\Context
    {
        return $this->context;
    }
    public function getLocation() : ?Location
    {
        return $this->location;
    }
    public function isTemplateStart() : bool
    {
        return $this->isTemplateStart;
    }
    public function isTemplateEnd() : bool
    {
        return $this->isTemplateEnd;
    }
    public function getTags() : array
    {
        return $this->tags;
    }
    public function getTagsByName(string $name) : array
    {
        $result = [];
        foreach ($this->getTags() as $tag) {
            if ($tag->getName() !== $name) {
                continue;
            }
            $result[] = $tag;
        }
        return $result;
    }
    public function getTagsWithTypeByName(string $name) : array
    {
        $result = [];
        foreach ($this->getTagsByName($name) as $tag) {
            if (!$tag instanceof TagWithType) {
                continue;
            }
            $result[] = $tag;
        }
        return $result;
    }
    public function hasTag(string $name) : bool
    {
        foreach ($this->getTags() as $tag) {
            if ($tag->getName() === $name) {
                return \true;
            }
        }
        return \false;
    }
    public function removeTag(Tag $tagToRemove) : void
    {
        foreach ($this->tags as $key => $tag) {
            if ($tag === $tagToRemove) {
                unset($this->tags[$key]);
                break;
            }
        }
    }
    private function addTag(Tag $tag) : void
    {
        $this->tags[] = $tag;
    }
}
