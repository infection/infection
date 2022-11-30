<?php

namespace _HumbugBox9658796bb9f0\JsonSchema\Constraints;

use _HumbugBox9658796bb9f0\JsonSchema\Constraints\TypeCheck\LooseTypeCheck;
use _HumbugBox9658796bb9f0\JsonSchema\Entity\JsonPointer;
use _HumbugBox9658796bb9f0\JsonSchema\Exception\ValidationException;
use _HumbugBox9658796bb9f0\JsonSchema\Uri\UriResolver;
class UndefinedConstraint extends Constraint
{
    protected $appliedDefaults = array();
    public function check(&$value, $schema = null, JsonPointer $path = null, $i = null, $fromDefault = \false)
    {
        if (\is_null($schema) || !\is_object($schema)) {
            return;
        }
        $path = $this->incrementPath($path ?: new JsonPointer(''), $i);
        if ($fromDefault) {
            $path->setFromDefault();
        }
        $this->validateCommonProperties($value, $schema, $path, $i);
        $this->validateOfProperties($value, $schema, $path, '');
        $this->validateTypes($value, $schema, $path, $i);
    }
    public function validateTypes(&$value, $schema, JsonPointer $path, $i = null)
    {
        if ($this->getTypeCheck()->isArray($value)) {
            $this->checkArray($value, $schema, $path, $i);
        }
        if (LooseTypeCheck::isObject($value)) {
            $this->checkObject($value, $schema, $path, isset($schema->properties) ? $schema->properties : null, isset($schema->additionalProperties) ? $schema->additionalProperties : null, isset($schema->patternProperties) ? $schema->patternProperties : null, $this->appliedDefaults);
        }
        if (\is_string($value)) {
            $this->checkString($value, $schema, $path, $i);
        }
        if (\is_numeric($value)) {
            $this->checkNumber($value, $schema, $path, $i);
        }
        if (isset($schema->enum)) {
            $this->checkEnum($value, $schema, $path, $i);
        }
    }
    protected function validateCommonProperties(&$value, $schema, JsonPointer $path, $i = '')
    {
        if (isset($schema->extends)) {
            if (\is_string($schema->extends)) {
                $schema->extends = $this->validateUri($schema, $schema->extends);
            }
            if (\is_array($schema->extends)) {
                foreach ($schema->extends as $extends) {
                    $this->checkUndefined($value, $extends, $path, $i);
                }
            } else {
                $this->checkUndefined($value, $schema->extends, $path, $i);
            }
        }
        if (!$path->fromDefault()) {
            $this->applyDefaultValues($value, $schema, $path);
        }
        if ($this->getTypeCheck()->isObject($value)) {
            if (!$value instanceof self && isset($schema->required) && \is_array($schema->required)) {
                foreach ($schema->required as $required) {
                    if (!$this->getTypeCheck()->propertyExists($value, $required)) {
                        $this->addError($this->incrementPath($path ?: new JsonPointer(''), $required), 'The property ' . $required . ' is required', 'required');
                    }
                }
            } elseif (isset($schema->required) && !\is_array($schema->required)) {
                if ($schema->required && $value instanceof self) {
                    $propertyPaths = $path->getPropertyPaths();
                    $propertyName = \end($propertyPaths);
                    $this->addError($path, 'The property ' . $propertyName . ' is required', 'required');
                }
            } else {
                if ($value instanceof self) {
                    return;
                }
            }
        }
        if (!$value instanceof self) {
            $this->checkType($value, $schema, $path, $i);
        }
        if (isset($schema->disallow)) {
            $initErrors = $this->getErrors();
            $typeSchema = new \stdClass();
            $typeSchema->type = $schema->disallow;
            $this->checkType($value, $typeSchema, $path);
            if (\count($this->getErrors()) == \count($initErrors)) {
                $this->addError($path, 'Disallowed value was matched', 'disallow');
            } else {
                $this->errors = $initErrors;
            }
        }
        if (isset($schema->not)) {
            $initErrors = $this->getErrors();
            $this->checkUndefined($value, $schema->not, $path, $i);
            if (\count($this->getErrors()) == \count($initErrors)) {
                $this->addError($path, 'Matched a schema which it should not', 'not');
            } else {
                $this->errors = $initErrors;
            }
        }
        if (isset($schema->dependencies) && $this->getTypeCheck()->isObject($value)) {
            $this->validateDependencies($value, $schema->dependencies, $path);
        }
    }
    private function shouldApplyDefaultValue($requiredOnly, $schema, $name = null, $parentSchema = null)
    {
        if (!$requiredOnly) {
            return \true;
        }
        if ($name !== null && isset($parentSchema->required) && \is_array($parentSchema->required) && \in_array($name, $parentSchema->required)) {
            return \true;
        }
        if (isset($schema->required) && !\is_array($schema->required) && $schema->required) {
            return \true;
        }
        return \false;
    }
    protected function applyDefaultValues(&$value, $schema, $path)
    {
        if (!$this->factory->getConfig(self::CHECK_MODE_APPLY_DEFAULTS)) {
            return;
        }
        $requiredOnly = $this->factory->getConfig(self::CHECK_MODE_ONLY_REQUIRED_DEFAULTS);
        if (isset($schema->properties) && LooseTypeCheck::isObject($value)) {
            foreach ($schema->properties as $currentProperty => $propertyDefinition) {
                $propertyDefinition = $this->factory->getSchemaStorage()->resolveRefSchema($propertyDefinition);
                if (!LooseTypeCheck::propertyExists($value, $currentProperty) && \property_exists($propertyDefinition, 'default') && $this->shouldApplyDefaultValue($requiredOnly, $propertyDefinition, $currentProperty, $schema)) {
                    if (\is_object($propertyDefinition->default)) {
                        LooseTypeCheck::propertySet($value, $currentProperty, clone $propertyDefinition->default);
                    } else {
                        LooseTypeCheck::propertySet($value, $currentProperty, $propertyDefinition->default);
                    }
                    $this->appliedDefaults[] = $currentProperty;
                }
            }
        } elseif (isset($schema->items) && LooseTypeCheck::isArray($value)) {
            $items = array();
            if (LooseTypeCheck::isArray($schema->items)) {
                $items = $schema->items;
            } elseif (isset($schema->minItems) && \count($value) < $schema->minItems) {
                $items = \array_fill(\count($value), $schema->minItems - \count($value), $schema->items);
            }
            foreach ($items as $currentItem => $itemDefinition) {
                $itemDefinition = $this->factory->getSchemaStorage()->resolveRefSchema($itemDefinition);
                if (!\array_key_exists($currentItem, $value) && \property_exists($itemDefinition, 'default') && $this->shouldApplyDefaultValue($requiredOnly, $itemDefinition)) {
                    if (\is_object($itemDefinition->default)) {
                        $value[$currentItem] = clone $itemDefinition->default;
                    } else {
                        $value[$currentItem] = $itemDefinition->default;
                    }
                }
                $path->setFromDefault();
            }
        } elseif ($value instanceof self && \property_exists($schema, 'default') && $this->shouldApplyDefaultValue($requiredOnly, $schema)) {
            $value = \is_object($schema->default) ? clone $schema->default : $schema->default;
            $path->setFromDefault();
        }
    }
    protected function validateOfProperties(&$value, $schema, JsonPointer $path, $i = '')
    {
        if ($value instanceof self) {
            return;
        }
        if (isset($schema->allOf)) {
            $isValid = \true;
            foreach ($schema->allOf as $allOf) {
                $initErrors = $this->getErrors();
                $this->checkUndefined($value, $allOf, $path, $i);
                $isValid = $isValid && \count($this->getErrors()) == \count($initErrors);
            }
            if (!$isValid) {
                $this->addError($path, 'Failed to match all schemas', 'allOf');
            }
        }
        if (isset($schema->anyOf)) {
            $isValid = \false;
            $startErrors = $this->getErrors();
            $caughtException = null;
            foreach ($schema->anyOf as $anyOf) {
                $initErrors = $this->getErrors();
                try {
                    $this->checkUndefined($value, $anyOf, $path, $i);
                    if ($isValid = \count($this->getErrors()) == \count($initErrors)) {
                        break;
                    }
                } catch (ValidationException $e) {
                    $isValid = \false;
                }
            }
            if (!$isValid) {
                $this->addError($path, 'Failed to match at least one schema', 'anyOf');
            } else {
                $this->errors = $startErrors;
            }
        }
        if (isset($schema->oneOf)) {
            $allErrors = array();
            $matchedSchemas = 0;
            $startErrors = $this->getErrors();
            foreach ($schema->oneOf as $oneOf) {
                try {
                    $this->errors = array();
                    $this->checkUndefined($value, $oneOf, $path, $i);
                    if (\count($this->getErrors()) == 0) {
                        $matchedSchemas++;
                    }
                    $allErrors = \array_merge($allErrors, \array_values($this->getErrors()));
                } catch (ValidationException $e) {
                }
            }
            if ($matchedSchemas !== 1) {
                $this->addErrors(\array_merge($allErrors, $startErrors));
                $this->addError($path, 'Failed to match exactly one schema', 'oneOf');
            } else {
                $this->errors = $startErrors;
            }
        }
    }
    protected function validateDependencies($value, $dependencies, JsonPointer $path, $i = '')
    {
        foreach ($dependencies as $key => $dependency) {
            if ($this->getTypeCheck()->propertyExists($value, $key)) {
                if (\is_string($dependency)) {
                    if (!$this->getTypeCheck()->propertyExists($value, $dependency)) {
                        $this->addError($path, "{$key} depends on {$dependency} and {$dependency} is missing", 'dependencies');
                    }
                } elseif (\is_array($dependency)) {
                    foreach ($dependency as $d) {
                        if (!$this->getTypeCheck()->propertyExists($value, $d)) {
                            $this->addError($path, "{$key} depends on {$d} and {$d} is missing", 'dependencies');
                        }
                    }
                } elseif (\is_object($dependency)) {
                    $this->checkUndefined($value, $dependency, $path, $i);
                }
            }
        }
    }
    protected function validateUri($schema, $schemaUri = null)
    {
        $resolver = new UriResolver();
        $retriever = $this->factory->getUriRetriever();
        $jsonSchema = null;
        if ($resolver->isValid($schemaUri)) {
            $schemaId = \property_exists($schema, 'id') ? $schema->id : null;
            $jsonSchema = $retriever->retrieve($schemaId, $schemaUri);
        }
        return $jsonSchema;
    }
}
