<?php

namespace _HumbugBoxb47773b41c19\JsonSchema;

use _HumbugBoxb47773b41c19\JsonSchema\Constraints\BaseConstraint;
use _HumbugBoxb47773b41c19\JsonSchema\Constraints\Constraint;
class Validator extends BaseConstraint
{
    const SCHEMA_MEDIA_TYPE = 'application/schema+json';
    const ERROR_NONE = 0x0;
    const ERROR_ALL = 0xffffffff;
    const ERROR_DOCUMENT_VALIDATION = 0x1;
    const ERROR_SCHEMA_VALIDATION = 0x2;
    public function validate(&$value, $schema = null, $checkMode = null)
    {
        if (\is_array($schema)) {
            $schema = self::arrayToObjectRecursive($schema);
        }
        $initialCheckMode = $this->factory->getConfig();
        if ($checkMode !== null) {
            $this->factory->setConfig($checkMode);
        }
        if (\is_object($schema) && \property_exists($schema, 'id')) {
            $schemaURI = $schema->id;
        } else {
            $schemaURI = SchemaStorage::INTERNAL_PROVIDED_SCHEMA_URI;
        }
        $this->factory->getSchemaStorage()->addSchema($schemaURI, $schema);
        $validator = $this->factory->createInstanceFor('schema');
        $validator->check($value, $this->factory->getSchemaStorage()->getSchema($schemaURI));
        $this->factory->setConfig($initialCheckMode);
        $this->addErrors(\array_unique($validator->getErrors(), \SORT_REGULAR));
        return $validator->getErrorMask();
    }
    public function check($value, $schema)
    {
        return $this->validate($value, $schema);
    }
    public function coerce(&$value, $schema)
    {
        return $this->validate($value, $schema, Constraint::CHECK_MODE_COERCE_TYPES);
    }
}
