<?php

namespace _HumbugBoxb47773b41c19\JsonSchema;

interface SchemaStorageInterface
{
    public function addSchema($id, $schema = null);
    public function getSchema($id);
    public function resolveRef($ref);
    public function resolveRefSchema($refSchema);
}
