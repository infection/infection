<?php





class ReflectionEnumUnitCase extends ReflectionClassConstant
{
public function __construct(object|string $class, string $constant) {}

#[Pure]
public function getValue(): UnitEnum {}




#[Pure]
public function getEnum(): ReflectionEnum {}
}
