<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection;

interface ProjectFactory
{
    public function create(string $name, array $files) : Project;
}
