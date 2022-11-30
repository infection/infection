<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\String\Inflector;

interface InflectorInterface
{
    public function singularize(string $plural) : array;
    public function pluralize(string $singular) : array;
}
