<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger\GitHub;

use Exception;
final class NoFilesInDiffToMutate extends Exception
{
    public static function create() : self
    {
        return new self('No files in diff found, skipping mutation analysis.');
    }
}
