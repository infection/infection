<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;







class ReflectionFunction extends ReflectionFunctionAbstract
{



#[Immutable]
public $name;






public const IS_DEPRECATED = 2048;








public function __construct(#[LanguageLevelTypeAware(['8.0' => 'Closure|string'], default: '')] $function) {}






#[TentativeType]
public function __toString(): string {}

/**
@removed









*/
#[Deprecated(since: '7.4')]
public static function export($name, $return = false) {}







#[Deprecated(since: '8.0')]
#[Pure]
#[TentativeType]
public function isDisabled(): bool {}










#[TentativeType]
public function invoke(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] ...$args): mixed {}









#[TentativeType]
public function invokeArgs(array $args): mixed {}







#[Pure]
#[TentativeType]
public function getClosure(): Closure {}

#[PhpStormStubsElementAvailable(from: '8.2')]
public function isAnonymous(): bool {}
}
