<?php

namespace _HumbugBoxb47773b41c19\JsonSchema\Constraints;

use _HumbugBoxb47773b41c19\JsonSchema\Entity\JsonPointer;
abstract class Constraint extends BaseConstraint implements ConstraintInterface
{
    protected $inlineSchemaProperty = '$schema';
    const CHECK_MODE_NONE = 0x0;
    const CHECK_MODE_NORMAL = 0x1;
    const CHECK_MODE_TYPE_CAST = 0x2;
    const CHECK_MODE_COERCE_TYPES = 0x4;
    const CHECK_MODE_APPLY_DEFAULTS = 0x8;
    const CHECK_MODE_EXCEPTIONS = 0x10;
    const CHECK_MODE_DISABLE_FORMAT = 0x20;
    const CHECK_MODE_ONLY_REQUIRED_DEFAULTS = 0x80;
    const CHECK_MODE_VALIDATE_SCHEMA = 0x100;
    protected function incrementPath(JsonPointer $path = null, $i)
    {
        $path = $path ?: new JsonPointer('');
        if ($i === null || $i === '') {
            return $path;
        }
        $path = $path->withPropertyPaths(\array_merge($path->getPropertyPaths(), array($i)));
        return $path;
    }
    protected function checkArray(&$value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('collection');
        $validator->check($value, $schema, $path, $i);
        $this->addErrors($validator->getErrors());
    }
    protected function checkObject(&$value, $schema = null, JsonPointer $path = null, $properties = null, $additionalProperties = null, $patternProperties = null, $appliedDefaults = array())
    {
        $validator = $this->factory->createInstanceFor('object');
        $validator->check($value, $schema, $path, $properties, $additionalProperties, $patternProperties, $appliedDefaults);
        $this->addErrors($validator->getErrors());
    }
    protected function checkType(&$value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('type');
        $validator->check($value, $schema, $path, $i);
        $this->addErrors($validator->getErrors());
    }
    protected function checkUndefined(&$value, $schema = null, JsonPointer $path = null, $i = null, $fromDefault = \false)
    {
        $validator = $this->factory->createInstanceFor('undefined');
        $validator->check($value, $this->factory->getSchemaStorage()->resolveRefSchema($schema), $path, $i, $fromDefault);
        $this->addErrors($validator->getErrors());
    }
    protected function checkString($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('string');
        $validator->check($value, $schema, $path, $i);
        $this->addErrors($validator->getErrors());
    }
    protected function checkNumber($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('number');
        $validator->check($value, $schema, $path, $i);
        $this->addErrors($validator->getErrors());
    }
    protected function checkEnum($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('enum');
        $validator->check($value, $schema, $path, $i);
        $this->addErrors($validator->getErrors());
    }
    protected function checkFormat($value, $schema = null, JsonPointer $path = null, $i = null)
    {
        $validator = $this->factory->createInstanceFor('format');
        $validator->check($value, $schema, $path, $i);
        $this->addErrors($validator->getErrors());
    }
    protected function getTypeCheck()
    {
        return $this->factory->getTypeCheck();
    }
    protected function convertJsonPointerIntoPropertyPath(JsonPointer $pointer)
    {
        $result = \array_map(function ($path) {
            return \sprintf(\is_numeric($path) ? '[%d]' : '.%s', $path);
        }, $pointer->getPropertyPaths());
        return \trim(\implode('', $result), '.');
    }
}
