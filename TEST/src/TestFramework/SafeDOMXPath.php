<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class SafeDOMXPath
{
    private DOMXPath $xPath;
    public function __construct(private DOMDocument $document)
    {
        $this->xPath = new DOMXPath($document);
    }
    public function __get(string $property) : DOMDocument
    {
        return $this->{$property};
    }
    public static function fromString(string $content) : self
    {
        $document = new DOMDocument();
        $success = @$document->loadXML($content);
        Assert::true($success);
        return new self($document);
    }
    public function query(string $query) : DOMNodeList
    {
        $nodes = @$this->xPath->query($query);
        Assert::isInstanceOf($nodes, DOMNodeList::class);
        return $nodes;
    }
}
