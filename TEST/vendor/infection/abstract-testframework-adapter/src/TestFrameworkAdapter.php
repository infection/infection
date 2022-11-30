<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\AbstractTestFramework;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
interface TestFrameworkAdapter
{
    public function getName() : string;
    public function testsPass(string $output) : bool;
    public function hasJUnitReport() : bool;
    public function getInitialTestRunCommandLine(string $extraOptions, array $phpExtraArgs, bool $skipCoverage) : array;
    public function getMutantCommandLine(array $coverageTests, string $mutatedFilePath, string $mutationHash, string $mutationOriginalFilePath, string $extraOptions) : array;
    public function getVersion() : string;
    public function getInitialTestsFailRecommendations(string $commandLine) : string;
}
