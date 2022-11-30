<?php





class ReflectionEnumBackedCase extends ReflectionEnumUnitCase
{
public function __construct(object|string $class, string $constant) {}

#[Pure]
public function getBackingValue(): int|string {}
}
