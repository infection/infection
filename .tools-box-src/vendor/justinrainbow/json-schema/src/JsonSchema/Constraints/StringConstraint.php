<?php

namespace _HumbugBoxb47773b41c19\JsonSchema\Constraints;

use _HumbugBoxb47773b41c19\JsonSchema\Entity\JsonPointer;
class StringConstraint extends Constraint
{
    public function check(&$element, $schema = null, JsonPointer $path = null, $i = null)
    {
        if (isset($schema->maxLength) && $this->strlen($element) > $schema->maxLength) {
            $this->addError($path, 'Must be at most ' . $schema->maxLength . ' characters long', 'maxLength', array('maxLength' => $schema->maxLength));
        }
        if (isset($schema->minLength) && $this->strlen($element) < $schema->minLength) {
            $this->addError($path, 'Must be at least ' . $schema->minLength . ' characters long', 'minLength', array('minLength' => $schema->minLength));
        }
        if (isset($schema->pattern) && !\preg_match('#' . \str_replace('#', '\\#', $schema->pattern) . '#u', $element)) {
            $this->addError($path, 'Does not match the regex pattern ' . $schema->pattern, 'pattern', array('pattern' => $schema->pattern));
        }
        $this->checkFormat($element, $schema, $path, $i);
    }
    private function strlen($string)
    {
        if (\extension_loaded('mbstring')) {
            return \mb_strlen($string, \mb_detect_encoding($string));
        }
        return \strlen($string);
    }
}
