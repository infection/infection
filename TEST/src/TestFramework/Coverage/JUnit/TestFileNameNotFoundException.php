<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\JUnit;

use Exception;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
final class TestFileNameNotFoundException extends Exception
{
    public static function notFoundFromFQN(string $fqn, string $jUnitFilePath) : self
    {
        return new self(sprintf('For FQCN: %s. Junit report: %s', $fqn, $jUnitFilePath));
    }
}
