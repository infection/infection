<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection;

use function count;
use _HumbugBox9658796bb9f0\Infection\Console\OutputFormatter\AbstractOutputFormatter;
use function is_array;
use function iterator_to_array;
final class IterableCounter
{
    use CannotBeInstantiated;
    public static function bufferAndCountIfNeeded(iterable &$subjects, bool $runConcurrently) : int
    {
        if ($runConcurrently) {
            return AbstractOutputFormatter::UNKNOWN_COUNT;
        }
        if (is_array($subjects)) {
            return count($subjects);
        }
        $subjects = iterator_to_array($subjects, \false);
        return count($subjects);
    }
}
