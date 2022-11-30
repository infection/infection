<?php

namespace _HumbugBox9658796bb9f0\JsonSchema;

use _HumbugBox9658796bb9f0\JsonSchema\Constraints\BaseConstraint;
use _HumbugBox9658796bb9f0\JsonSchema\Entity\JsonPointer;
use _HumbugBox9658796bb9f0\JsonSchema\Exception\UnresolvableJsonPointerException;
use _HumbugBox9658796bb9f0\JsonSchema\Uri\UriResolver;
use _HumbugBox9658796bb9f0\JsonSchema\Uri\UriRetriever;
class SchemaStorage implements SchemaStorageInterface
{
    const INTERNAL_PROVIDED_SCHEMA_URI = 'internal://provided-schema/';
    protected $uriRetriever;
    protected $uriResolver;
    protected $schemas = array();
    public function __construct(UriRetrieverInterface $uriRetriever = null, UriResolverInterface $uriResolver = null)
    {
        $this->uriRetriever = $uriRetriever ?: new UriRetriever();
        $this->uriResolver = $uriResolver ?: new UriResolver();
    }
    public function getUriRetriever()
    {
        return $this->uriRetriever;
    }
    public function getUriResolver()
    {
        return $this->uriResolver;
    }
    public function addSchema($id, $schema = null)
    {
        if (\is_null($schema) && $id !== self::INTERNAL_PROVIDED_SCHEMA_URI) {
            $schema = $this->uriRetriever->retrieve($id);
        }
        if (\is_array($schema)) {
            $schema = BaseConstraint::arrayToObjectRecursive($schema);
        }
        if (\is_object($schema) && \property_exists($schema, 'id')) {
            if ($schema->id == 'http://json-schema.org/draft-04/schema#') {
                $schema->properties->id->format = 'uri-reference';
            } elseif ($schema->id == 'http://json-schema.org/draft-03/schema#') {
                $schema->properties->id->format = 'uri-reference';
                $schema->properties->{'$ref'}->format = 'uri-reference';
            }
        }
        $this->expandRefs($schema, $id);
        $this->schemas[$id] = $schema;
    }
    private function expandRefs(&$schema, $base = null)
    {
        if (!\is_object($schema)) {
            if (\is_array($schema)) {
                foreach ($schema as &$member) {
                    $this->expandRefs($member, $base);
                }
            }
            return;
        }
        if (\property_exists($schema, 'id') && \is_string($schema->id) && $base != $schema->id) {
            $base = $this->uriResolver->resolve($schema->id, $base);
        }
        if (\property_exists($schema, '$ref') && \is_string($schema->{'$ref'})) {
            $refPointer = new JsonPointer($this->uriResolver->resolve($schema->{'$ref'}, $base));
            $schema->{'$ref'} = (string) $refPointer;
        }
        foreach ($schema as &$member) {
            $this->expandRefs($member, $base);
        }
    }
    public function getSchema($id)
    {
        if (!\array_key_exists($id, $this->schemas)) {
            $this->addSchema($id);
        }
        return $this->schemas[$id];
    }
    public function resolveRef($ref)
    {
        $jsonPointer = new JsonPointer($ref);
        $fileName = $jsonPointer->getFilename();
        if (!\strlen($fileName)) {
            throw new UnresolvableJsonPointerException(\sprintf("Could not resolve fragment '%s': no file is defined", $jsonPointer->getPropertyPathAsString()));
        }
        $refSchema = $this->getSchema($fileName);
        foreach ($jsonPointer->getPropertyPaths() as $path) {
            if (\is_object($refSchema) && \property_exists($refSchema, $path)) {
                $refSchema = $this->resolveRefSchema($refSchema->{$path});
            } elseif (\is_array($refSchema) && \array_key_exists($path, $refSchema)) {
                $refSchema = $this->resolveRefSchema($refSchema[$path]);
            } else {
                throw new UnresolvableJsonPointerException(\sprintf('File: %s is found, but could not resolve fragment: %s', $jsonPointer->getFilename(), $jsonPointer->getPropertyPathAsString()));
            }
        }
        return $refSchema;
    }
    public function resolveRefSchema($refSchema)
    {
        if (\is_object($refSchema) && \property_exists($refSchema, '$ref') && \is_string($refSchema->{'$ref'})) {
            $newSchema = $this->resolveRef($refSchema->{'$ref'});
            $refSchema = (object) (\get_object_vars($refSchema) + \get_object_vars($newSchema));
            unset($refSchema->{'$ref'});
        }
        return $refSchema;
    }
}
