<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutant;

use _HumbugBox9658796bb9f0\Infection\CannotBeInstantiated;
final class DetectionStatus
{
    use CannotBeInstantiated;
    public const KILLED = 'killed';
    public const ESCAPED = 'escaped';
    public const ERROR = 'error';
    public const TIMED_OUT = 'timed out';
    public const SKIPPED = 'skipped';
    public const SYNTAX_ERROR = 'syntax error';
    public const NOT_COVERED = 'not covered';
    public const IGNORED = 'ignored';
    public const ALL = [self::KILLED, self::ESCAPED, self::ERROR, self::TIMED_OUT, self::SKIPPED, self::SYNTAX_ERROR, self::NOT_COVERED, self::IGNORED];
}
