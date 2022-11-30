<?php

namespace _HumbugBoxb47773b41c19\JsonSchema\Constraints;

use _HumbugBoxb47773b41c19\JsonSchema\Entity\JsonPointer;
use _HumbugBoxb47773b41c19\JsonSchema\Exception\InvalidArgumentException;
use UnexpectedValueException as StandardUnexpectedValueException;
class TypeConstraint extends Constraint
{
    public static $wording = array('integer' => 'an integer', 'number' => 'a number', 'boolean' => 'a boolean', 'object' => 'an object', 'array' => 'an array', 'string' => 'a string', 'null' => 'a null', 'any' => null, 0 => null);
    public function check(&$value = null, $schema = null, JsonPointer $path = null, $i = null)
    {
        $type = isset($schema->type) ? $schema->type : null;
        $isValid = \false;
        $wording = array();
        if (\is_array($type)) {
            $this->validateTypesArray($value, $type, $wording, $isValid, $path);
        } elseif (\is_object($type)) {
            $this->checkUndefined($value, $type, $path);
            return;
        } else {
            $isValid = $this->validateType($value, $type);
        }
        if ($isValid === \false) {
            if (!\is_array($type)) {
                $this->validateTypeNameWording($type);
                $wording[] = self::$wording[$type];
            }
            $this->addError($path, \ucwords(\gettype($value)) . ' value found, but ' . $this->implodeWith($wording, ', ', 'or') . ' is required', 'type');
        }
    }
    protected function validateTypesArray(&$value, array $type, &$validTypesWording, &$isValid, $path)
    {
        foreach ($type as $tp) {
            if (\is_object($tp)) {
                if (!$isValid) {
                    $validator = $this->factory->createInstanceFor('type');
                    $subSchema = new \stdClass();
                    $subSchema->type = $tp;
                    $validator->check($value, $subSchema, $path, null);
                    $error = $validator->getErrors();
                    $isValid = !(bool) $error;
                    $validTypesWording[] = self::$wording['object'];
                }
            } else {
                $this->validateTypeNameWording($tp);
                $validTypesWording[] = self::$wording[$tp];
                if (!$isValid) {
                    $isValid = $this->validateType($value, $tp);
                }
            }
        }
    }
    protected function implodeWith(array $elements, $delimiter = ', ', $listEnd = \false)
    {
        if ($listEnd === \false || !isset($elements[1])) {
            return \implode($delimiter, $elements);
        }
        $lastElement = \array_slice($elements, -1);
        $firsElements = \join($delimiter, \array_slice($elements, 0, -1));
        $implodedElements = \array_merge(array($firsElements), $lastElement);
        return \join(" {$listEnd} ", $implodedElements);
    }
    protected function validateTypeNameWording($type)
    {
        if (!\array_key_exists($type, self::$wording)) {
            throw new StandardUnexpectedValueException(\sprintf('No wording for %s available, expected wordings are: [%s]', \var_export($type, \true), \implode(', ', \array_filter(self::$wording))));
        }
    }
    protected function validateType(&$value, $type)
    {
        if (!$type) {
            return \true;
        }
        if ('any' === $type) {
            return \true;
        }
        if ('object' === $type) {
            return $this->getTypeCheck()->isObject($value);
        }
        if ('array' === $type) {
            return $this->getTypeCheck()->isArray($value);
        }
        $coerce = $this->factory->getConfig(Constraint::CHECK_MODE_COERCE_TYPES);
        if ('integer' === $type) {
            if ($coerce) {
                $value = $this->toInteger($value);
            }
            return \is_int($value);
        }
        if ('number' === $type) {
            if ($coerce) {
                $value = $this->toNumber($value);
            }
            return \is_numeric($value) && !\is_string($value);
        }
        if ('boolean' === $type) {
            if ($coerce) {
                $value = $this->toBoolean($value);
            }
            return \is_bool($value);
        }
        if ('string' === $type) {
            return \is_string($value);
        }
        if ('email' === $type) {
            return \is_string($value);
        }
        if ('null' === $type) {
            return \is_null($value);
        }
        throw new InvalidArgumentException((\is_object($value) ? 'object' : $value) . ' is an invalid type for ' . $type);
    }
    protected function toBoolean($value)
    {
        if ($value === 'true') {
            return \true;
        }
        if ($value === 'false') {
            return \false;
        }
        return $value;
    }
    protected function toNumber($value)
    {
        if (\is_numeric($value)) {
            return $value + 0;
        }
        return $value;
    }
    protected function toInteger($value)
    {
        if (\is_numeric($value) && (int) $value == $value) {
            return (int) $value;
        }
        return $value;
    }
}
