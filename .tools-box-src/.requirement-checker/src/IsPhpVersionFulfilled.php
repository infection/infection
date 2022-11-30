<?php

namespace HumbugBox420\KevinGH\RequirementChecker;

use HumbugBox420\Composer\Semver\Semver;
use function sprintf;
final class IsPhpVersionFulfilled implements IsFulfilled
{
    private $requiredPhpVersion;
    public function __construct(string $requiredPhpVersion)
    {
        $this->requiredPhpVersion = $requiredPhpVersion;
    }
    public function __invoke() : bool
    {
        return Semver::satisfies(sprintf('%d.%d.%d', \PHP_MAJOR_VERSION, \PHP_MINOR_VERSION, \PHP_RELEASE_VERSION), $this->requiredPhpVersion);
    }
}
