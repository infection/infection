<?php

use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;






class ReflectionGenerator
{







public function __construct(Generator $generator) {}









#[Pure]
#[TentativeType]
public function getExecutingLine(): int {}









#[Pure]
#[TentativeType]
public function getExecutingFile(): string {}

















#[Pure]
#[TentativeType]
public function getTrace(int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT): array {}










#[Pure]
#[TentativeType]
public function getFunction(): ReflectionFunctionAbstract {}









#[Pure]
#[TentativeType]
public function getThis(): ?object {}








#[Pure]
#[TentativeType]
public function getExecutingGenerator(): Generator {}
}
