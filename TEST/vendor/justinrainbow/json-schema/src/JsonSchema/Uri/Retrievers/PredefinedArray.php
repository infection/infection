<?php

namespace _HumbugBox9658796bb9f0\JsonSchema\Uri\Retrievers;

use _HumbugBox9658796bb9f0\JsonSchema\Validator;
class PredefinedArray extends AbstractRetriever
{
    private $schemas;
    public function __construct(array $schemas, $contentType = Validator::SCHEMA_MEDIA_TYPE)
    {
        $this->schemas = $schemas;
        $this->contentType = $contentType;
    }
    public function retrieve($uri)
    {
        if (!\array_key_exists($uri, $this->schemas)) {
            throw new \_HumbugBox9658796bb9f0\JsonSchema\Exception\ResourceNotFoundException(\sprintf('The JSON schema "%s" was not found.', $uri));
        }
        return $this->schemas[$uri];
    }
}
