<?php

namespace _HumbugBox9658796bb9f0\JsonSchema\Uri\Retrievers;

abstract class AbstractRetriever implements UriRetrieverInterface
{
    protected $contentType;
    public function getContentType()
    {
        return $this->contentType;
    }
}
