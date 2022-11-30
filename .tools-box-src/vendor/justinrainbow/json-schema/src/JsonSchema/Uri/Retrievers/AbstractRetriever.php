<?php

namespace _HumbugBoxb47773b41c19\JsonSchema\Uri\Retrievers;

abstract class AbstractRetriever implements UriRetrieverInterface
{
    protected $contentType;
    public function getContentType()
    {
        return $this->contentType;
    }
}
