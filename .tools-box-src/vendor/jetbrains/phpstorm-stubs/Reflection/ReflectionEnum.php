<?php

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;





class ReflectionEnum extends ReflectionClass
{
public function __construct(object|string $objectOrClass) {}





public function hasCase(string $name): bool {}




public function getCases(): array {}





public function getCase(string $name): ReflectionEnumUnitCase {}




public function isBacked(): bool {}




#[LanguageLevelTypeAware(['8.2' => 'null|ReflectionNamedType'], default: 'null|ReflectionType')]
public function getBackingType() {}
}
