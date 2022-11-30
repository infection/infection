<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;







abstract class ReflectionType implements Stringable
{







#[TentativeType]
public function allowsNull(): bool {}

/**
@removed






*/
#[Pure]
public function isBuiltin() {}









#[Deprecated(since: "7.1")]
public function __toString(): string {}






#[PhpStormStubsElementAvailable(from: "5.4", to: "8.0")]
final private function __clone(): void {}






#[PhpStormStubsElementAvailable(from: "8.1")]
private function __clone(): void {}
}
