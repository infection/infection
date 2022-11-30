<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;







class ReflectionObject extends ReflectionClass
{






public function __construct(#[LanguageLevelTypeAware(['8.0' => 'object'], default: '')] $object) {}

/**
@removed









*/
#[Deprecated(since: '7.4')]
public static function export($argument, $return = false) {}
}
