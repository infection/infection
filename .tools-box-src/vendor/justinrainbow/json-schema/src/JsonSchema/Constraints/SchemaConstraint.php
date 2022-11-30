<?php

namespace _HumbugBoxb47773b41c19\JsonSchema\Constraints;

use _HumbugBoxb47773b41c19\JsonSchema\Entity\JsonPointer;
use _HumbugBoxb47773b41c19\JsonSchema\Exception\InvalidArgumentException;
use _HumbugBoxb47773b41c19\JsonSchema\Exception\InvalidSchemaException;
use _HumbugBoxb47773b41c19\JsonSchema\Exception\RuntimeException;
use _HumbugBoxb47773b41c19\JsonSchema\Validator;
class SchemaConstraint extends Constraint
{
    const DEFAULT_SCHEMA_SPEC = 'http://json-schema.org/draft-04/schema#';
    public function check(&$element, $schema = null, JsonPointer $path = null, $i = null)
    {
        if ($schema !== null) {
            $validationSchema = $schema;
        } elseif ($this->getTypeCheck()->propertyExists($element, $this->inlineSchemaProperty)) {
            $validationSchema = $this->getTypeCheck()->propertyGet($element, $this->inlineSchemaProperty);
        } else {
            throw new InvalidArgumentException('no schema found to verify against');
        }
        if (\is_array($validationSchema)) {
            $validationSchema = BaseConstraint::arrayToObjectRecursive($validationSchema);
        }
        if ($this->factory->getConfig(self::CHECK_MODE_VALIDATE_SCHEMA)) {
            if (!$this->getTypeCheck()->isObject($validationSchema)) {
                throw new RuntimeException('Cannot validate the schema of a non-object');
            }
            if ($this->getTypeCheck()->propertyExists($validationSchema, '$schema')) {
                $schemaSpec = $this->getTypeCheck()->propertyGet($validationSchema, '$schema');
            } else {
                $schemaSpec = self::DEFAULT_SCHEMA_SPEC;
            }
            $schemaStorage = $this->factory->getSchemaStorage();
            if (!$this->getTypeCheck()->isObject($schemaSpec)) {
                $schemaSpec = $schemaStorage->getSchema($schemaSpec);
            }
            $initialErrorCount = $this->numErrors();
            $initialConfig = $this->factory->getConfig();
            $initialContext = $this->factory->getErrorContext();
            $this->factory->removeConfig(self::CHECK_MODE_VALIDATE_SCHEMA | self::CHECK_MODE_APPLY_DEFAULTS);
            $this->factory->addConfig(self::CHECK_MODE_TYPE_CAST);
            $this->factory->setErrorContext(Validator::ERROR_SCHEMA_VALIDATION);
            try {
                $this->check($validationSchema, $schemaSpec);
            } catch (\Exception $e) {
                if ($this->factory->getConfig(self::CHECK_MODE_EXCEPTIONS)) {
                    throw new InvalidSchemaException('Schema did not pass validation', 0, $e);
                }
            }
            if ($this->numErrors() > $initialErrorCount) {
                $this->addError($path, 'Schema is not valid', 'schema');
            }
            $this->factory->setConfig($initialConfig);
            $this->factory->setErrorContext($initialContext);
        }
        $this->checkUndefined($element, $validationSchema, $path, $i);
    }
}
