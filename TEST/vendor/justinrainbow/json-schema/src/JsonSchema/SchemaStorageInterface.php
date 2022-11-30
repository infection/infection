<?php

namespace _HumbugBox9658796bb9f0\JsonSchema;

interface SchemaStorageInterface
{
    public function addSchema($id, $schema = null);
    public function getSchema($id);
    public function resolveRef($ref);
    public function resolveRefSchema($refSchema);
}
