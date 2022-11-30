<?php

namespace _HumbugBox9658796bb9f0\JsonSchema\Uri;

use _HumbugBox9658796bb9f0\JsonSchema\Exception\InvalidSchemaMediaTypeException;
use _HumbugBox9658796bb9f0\JsonSchema\Exception\JsonDecodingException;
use _HumbugBox9658796bb9f0\JsonSchema\Exception\ResourceNotFoundException;
use _HumbugBox9658796bb9f0\JsonSchema\Uri\Retrievers\FileGetContents;
use _HumbugBox9658796bb9f0\JsonSchema\Uri\Retrievers\UriRetrieverInterface;
use _HumbugBox9658796bb9f0\JsonSchema\UriRetrieverInterface as BaseUriRetrieverInterface;
use _HumbugBox9658796bb9f0\JsonSchema\Validator;
class UriRetriever implements BaseUriRetrieverInterface
{
    protected $translationMap = array('|^https?://json-schema.org/draft-(0[34])/schema#?|' => 'package://dist/schema/json-schema-draft-$1.json');
    protected $allowedInvalidContentTypeEndpoints = array('http://json-schema.org/', 'https://json-schema.org/');
    protected $uriRetriever = null;
    private $schemaCache = array();
    public function addInvalidContentTypeEndpoint($endpoint)
    {
        $this->allowedInvalidContentTypeEndpoints[] = $endpoint;
    }
    public function confirmMediaType($uriRetriever, $uri)
    {
        $contentType = $uriRetriever->getContentType();
        if (\is_null($contentType)) {
            return;
        }
        if (\in_array($contentType, array(Validator::SCHEMA_MEDIA_TYPE, 'application/json'))) {
            return;
        }
        foreach ($this->allowedInvalidContentTypeEndpoints as $endpoint) {
            if (\strpos($uri, $endpoint) === 0) {
                return \true;
            }
        }
        throw new InvalidSchemaMediaTypeException(\sprintf('Media type %s expected', Validator::SCHEMA_MEDIA_TYPE));
    }
    public function getUriRetriever()
    {
        if (\is_null($this->uriRetriever)) {
            $this->setUriRetriever(new FileGetContents());
        }
        return $this->uriRetriever;
    }
    public function resolvePointer($jsonSchema, $uri)
    {
        $resolver = new UriResolver();
        $parsed = $resolver->parse($uri);
        if (empty($parsed['fragment'])) {
            return $jsonSchema;
        }
        $path = \explode('/', $parsed['fragment']);
        while ($path) {
            $pathElement = \array_shift($path);
            if (!empty($pathElement)) {
                $pathElement = \str_replace('~1', '/', $pathElement);
                $pathElement = \str_replace('~0', '~', $pathElement);
                if (!empty($jsonSchema->{$pathElement})) {
                    $jsonSchema = $jsonSchema->{$pathElement};
                } else {
                    throw new ResourceNotFoundException('Fragment "' . $parsed['fragment'] . '" not found' . ' in ' . $uri);
                }
                if (!\is_object($jsonSchema)) {
                    throw new ResourceNotFoundException('Fragment part "' . $pathElement . '" is no object ' . ' in ' . $uri);
                }
            }
        }
        return $jsonSchema;
    }
    public function retrieve($uri, $baseUri = null, $translate = \true)
    {
        $resolver = new UriResolver();
        $resolvedUri = $fetchUri = $resolver->resolve($uri, $baseUri);
        $arParts = $resolver->parse($resolvedUri);
        if (isset($arParts['fragment'])) {
            unset($arParts['fragment']);
            $fetchUri = $resolver->generate($arParts);
        }
        if ($translate) {
            $fetchUri = $this->translate($fetchUri);
        }
        $jsonSchema = $this->loadSchema($fetchUri);
        $jsonSchema = $this->resolvePointer($jsonSchema, $resolvedUri);
        if ($jsonSchema instanceof \stdClass) {
            $jsonSchema->id = $resolvedUri;
        }
        return $jsonSchema;
    }
    protected function loadSchema($fetchUri)
    {
        if (isset($this->schemaCache[$fetchUri])) {
            return $this->schemaCache[$fetchUri];
        }
        $uriRetriever = $this->getUriRetriever();
        $contents = $this->uriRetriever->retrieve($fetchUri);
        $this->confirmMediaType($uriRetriever, $fetchUri);
        $jsonSchema = \json_decode($contents);
        if (\JSON_ERROR_NONE < ($error = \json_last_error())) {
            throw new JsonDecodingException($error);
        }
        $this->schemaCache[$fetchUri] = $jsonSchema;
        return $jsonSchema;
    }
    public function setUriRetriever(UriRetrieverInterface $uriRetriever)
    {
        $this->uriRetriever = $uriRetriever;
        return $this;
    }
    public function parse($uri)
    {
        \preg_match('|^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\\?([^#]*))?(#(.*))?|', $uri, $match);
        $components = array();
        if (5 < \count($match)) {
            $components = array('scheme' => $match[2], 'authority' => $match[4], 'path' => $match[5]);
        }
        if (7 < \count($match)) {
            $components['query'] = $match[7];
        }
        if (9 < \count($match)) {
            $components['fragment'] = $match[9];
        }
        return $components;
    }
    public function generate(array $components)
    {
        $uri = $components['scheme'] . '://' . $components['authority'] . $components['path'];
        if (\array_key_exists('query', $components)) {
            $uri .= $components['query'];
        }
        if (\array_key_exists('fragment', $components)) {
            $uri .= $components['fragment'];
        }
        return $uri;
    }
    public function resolve($uri, $baseUri = null)
    {
        $components = $this->parse($uri);
        $path = $components['path'];
        if (\array_key_exists('scheme', $components) && 'http' === $components['scheme']) {
            return $uri;
        }
        $baseComponents = $this->parse($baseUri);
        $basePath = $baseComponents['path'];
        $baseComponents['path'] = UriResolver::combineRelativePathWithBasePath($path, $basePath);
        return $this->generate($baseComponents);
    }
    public function isValid($uri)
    {
        $components = $this->parse($uri);
        return !empty($components);
    }
    public function setTranslation($from, $to)
    {
        $this->translationMap[$from] = $to;
    }
    public function translate($uri)
    {
        foreach ($this->translationMap as $from => $to) {
            $uri = \preg_replace($from, $to, $uri);
        }
        $uri = \preg_replace('|^package://|', \sprintf('file://%s/', \realpath(__DIR__ . '/../../..')), $uri);
        return $uri;
    }
}
