<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\XmlReport;

use UnexpectedValueException;
final class NoLineExecutedInDiffLinesMode extends UnexpectedValueException
{
    public static function create() : self
    {
        return new self('No covered lines in diff found, skipping mutation analysis.');
    }
}
