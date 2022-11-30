<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\TentativeType;






class Reflection
{







#[TentativeType]
public static function getModifierNames(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $modifiers): array {}

/**
@removed








*/
#[Deprecated(since: '7.4')]
public static function export(Reflector $reflector, $return = false) {}
}
