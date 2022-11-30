<?php

namespace _HumbugBox9658796bb9f0\JsonSchema\Constraints;

use _HumbugBox9658796bb9f0\JsonSchema\Entity\JsonPointer;
class ObjectConstraint extends Constraint
{
    protected $appliedDefaults = array();
    public function check(&$element, $schema = null, JsonPointer $path = null, $properties = null, $additionalProp = null, $patternProperties = null, $appliedDefaults = array())
    {
        if ($element instanceof UndefinedConstraint) {
            return;
        }
        $this->appliedDefaults = $appliedDefaults;
        $matches = array();
        if ($patternProperties) {
            $matches = $this->validatePatternProperties($element, $path, $patternProperties);
        }
        if ($properties) {
            $this->validateProperties($element, $properties, $path);
        }
        $this->validateElement($element, $matches, $schema, $path, $properties, $additionalProp);
    }
    public function validatePatternProperties($element, JsonPointer $path = null, $patternProperties)
    {
        $try = array('/', '#', '+', '~', '%');
        $matches = array();
        foreach ($patternProperties as $pregex => $schema) {
            $delimiter = '/';
            foreach ($try as $delimiter) {
                if (\strpos($pregex, $delimiter) === \false) {
                    break;
                }
            }
            if (@\preg_match($delimiter . $pregex . $delimiter . 'u', '') === \false) {
                $this->addError($path, 'The pattern "' . $pregex . '" is invalid', 'pregex', array('pregex' => $pregex));
                continue;
            }
            foreach ($element as $i => $value) {
                if (\preg_match($delimiter . $pregex . $delimiter . 'u', $i)) {
                    $matches[] = $i;
                    $this->checkUndefined($value, $schema ?: new \stdClass(), $path, $i, \in_array($i, $this->appliedDefaults));
                }
            }
        }
        return $matches;
    }
    public function validateElement($element, $matches, $schema = null, JsonPointer $path = null, $properties = null, $additionalProp = null)
    {
        $this->validateMinMaxConstraint($element, $schema, $path);
        foreach ($element as $i => $value) {
            $definition = $this->getProperty($properties, $i);
            if (!\in_array($i, $matches) && $additionalProp === \false && $this->inlineSchemaProperty !== $i && !$definition) {
                $this->addError($path, 'The property ' . $i . ' is not defined and the definition does not allow additional properties', 'additionalProp');
            }
            if (!\in_array($i, $matches) && $additionalProp && !$definition) {
                if ($additionalProp === \true) {
                    $this->checkUndefined($value, null, $path, $i, \in_array($i, $this->appliedDefaults));
                } else {
                    $this->checkUndefined($value, $additionalProp, $path, $i, \in_array($i, $this->appliedDefaults));
                }
            }
            $require = $this->getProperty($definition, 'requires');
            if ($require && !$this->getProperty($element, $require)) {
                $this->addError($path, 'The presence of the property ' . $i . ' requires that ' . $require . ' also be present', 'requires');
            }
            $property = $this->getProperty($element, $i, $this->factory->createInstanceFor('undefined'));
            if (\is_object($property)) {
                $this->validateMinMaxConstraint(!$property instanceof UndefinedConstraint ? $property : $element, $definition, $path);
            }
        }
    }
    public function validateProperties(&$element, $properties = null, JsonPointer $path = null)
    {
        $undefinedConstraint = $this->factory->createInstanceFor('undefined');
        foreach ($properties as $i => $value) {
            $property =& $this->getProperty($element, $i, $undefinedConstraint);
            $definition = $this->getProperty($properties, $i);
            if (\is_object($definition)) {
                $this->checkUndefined($property, $definition, $path, $i, \in_array($i, $this->appliedDefaults));
            }
        }
    }
    protected function &getProperty(&$element, $property, $fallback = null)
    {
        if (\is_array($element) && (isset($element[$property]) || \array_key_exists($property, $element))) {
            return $element[$property];
        } elseif (\is_object($element) && \property_exists($element, $property)) {
            return $element->{$property};
        }
        return $fallback;
    }
    protected function validateMinMaxConstraint($element, $objectDefinition, JsonPointer $path = null)
    {
        if (isset($objectDefinition->minProperties) && !\is_object($objectDefinition->minProperties)) {
            if ($this->getTypeCheck()->propertyCount($element) < $objectDefinition->minProperties) {
                $this->addError($path, 'Must contain a minimum of ' . $objectDefinition->minProperties . ' properties', 'minProperties', array('minProperties' => $objectDefinition->minProperties));
            }
        }
        if (isset($objectDefinition->maxProperties) && !\is_object($objectDefinition->maxProperties)) {
            if ($this->getTypeCheck()->propertyCount($element) > $objectDefinition->maxProperties) {
                $this->addError($path, 'Must contain no more than ' . $objectDefinition->maxProperties . ' properties', 'maxProperties', array('maxProperties' => $objectDefinition->maxProperties));
            }
        }
    }
}
