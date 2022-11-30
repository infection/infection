<?php

namespace _HumbugBox9658796bb9f0\JsonSchema\Constraints;

use _HumbugBox9658796bb9f0\JsonSchema\Entity\JsonPointer;
class CollectionConstraint extends Constraint
{
    public function check(&$value, $schema = null, JsonPointer $path = null, $i = null)
    {
        if (isset($schema->minItems) && \count($value) < $schema->minItems) {
            $this->addError($path, 'There must be a minimum of ' . $schema->minItems . ' items in the array', 'minItems', array('minItems' => $schema->minItems));
        }
        if (isset($schema->maxItems) && \count($value) > $schema->maxItems) {
            $this->addError($path, 'There must be a maximum of ' . $schema->maxItems . ' items in the array', 'maxItems', array('maxItems' => $schema->maxItems));
        }
        if (isset($schema->uniqueItems) && $schema->uniqueItems) {
            $unique = $value;
            if (\is_array($value) && \count($value)) {
                $unique = \array_map(function ($e) {
                    return \var_export($e, \true);
                }, $value);
            }
            if (\count(\array_unique($unique)) != \count($value)) {
                $this->addError($path, 'There are no duplicates allowed in the array', 'uniqueItems');
            }
        }
        if (isset($schema->items)) {
            $this->validateItems($value, $schema, $path, $i);
        }
    }
    protected function validateItems(&$value, $schema = null, JsonPointer $path = null, $i = null)
    {
        if (\is_object($schema->items)) {
            foreach ($value as $k => &$v) {
                $initErrors = $this->getErrors();
                $this->checkUndefined($v, $schema->items, $path, $k);
                if (\count($initErrors) < \count($this->getErrors()) && (isset($schema->additionalItems) && $schema->additionalItems !== \false)) {
                    $secondErrors = $this->getErrors();
                    $this->checkUndefined($v, $schema->additionalItems, $path, $k);
                }
                if (isset($secondErrors) && \count($secondErrors) < \count($this->getErrors())) {
                    $this->errors = $secondErrors;
                } elseif (isset($secondErrors) && \count($secondErrors) === \count($this->getErrors())) {
                    $this->errors = $initErrors;
                }
            }
            unset($v);
        } else {
            foreach ($value as $k => &$v) {
                if (\array_key_exists($k, $schema->items)) {
                    $this->checkUndefined($v, $schema->items[$k], $path, $k);
                } else {
                    if (\property_exists($schema, 'additionalItems')) {
                        if ($schema->additionalItems !== \false) {
                            $this->checkUndefined($v, $schema->additionalItems, $path, $k);
                        } else {
                            $this->addError($path, 'The item ' . $i . '[' . $k . '] is not defined and the definition does not allow additional items', 'additionalItems', array('additionalItems' => $schema->additionalItems));
                        }
                    } else {
                        $this->checkUndefined($v, new \stdClass(), $path, $k);
                    }
                }
            }
            unset($v);
            if (\count($value) > 0) {
                for ($k = \count($value); $k < \count($schema->items); $k++) {
                    $undefinedInstance = $this->factory->createInstanceFor('undefined');
                    $this->checkUndefined($undefinedInstance, $schema->items[$k], $path, $k);
                }
            }
        }
    }
}
