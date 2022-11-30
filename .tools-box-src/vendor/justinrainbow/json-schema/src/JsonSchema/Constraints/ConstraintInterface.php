<?php

namespace _HumbugBoxb47773b41c19\JsonSchema\Constraints;

use _HumbugBoxb47773b41c19\JsonSchema\Entity\JsonPointer;
interface ConstraintInterface
{
    public function getErrors();
    public function addErrors(array $errors);
    public function addError(JsonPointer $path = null, $message, $constraint = '', array $more = null);
    public function isValid();
    public function check(&$value, $schema = null, JsonPointer $path = null, $i = null);
}
