<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\String\Inflector;

interface InflectorInterface
{
    public function singularize(string $plural) : array;
    public function pluralize(string $singular) : array;
}
