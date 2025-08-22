<?php

declare(strict_types=1);

namespace newSrc\Trace\Symbol;

use Stringable;

/**
 * Represents a PHP Symbol: a class, a method or a function.
 *
 * Currently, in the context of a traditional test framework, only those matter. Maybe for a static analyser there are
 * more advanced symbols.
 */
interface Symbol
{
    public function toString(): string;
}
