<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Configuration\Schema;

use _HumbugBox9658796bb9f0\Infection\Configuration\Entry\Logs;
use _HumbugBox9658796bb9f0\Infection\Configuration\Entry\PhpUnit;
use _HumbugBox9658796bb9f0\Infection\Configuration\Entry\Source;
use _HumbugBox9658796bb9f0\Infection\TestFramework\TestFrameworkTypes;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class SchemaConfiguration
{
    private string $file;
    private ?float $timeout;
    private Source $source;
    private Logs $logs;
    private ?string $tmpDir;
    private PhpUnit $phpUnit;
    private ?bool $ignoreMsiWithNoMutations;
    private ?float $minMsi;
    private ?float $minCoveredMsi;
    private array $mutators;
    private ?string $testFramework;
    private ?string $bootstrap;
    private ?string $initialTestsPhpOptions;
    private ?string $testFrameworkExtraOptions;
    public function __construct(string $file, ?float $timeout, Source $source, Logs $logs, ?string $tmpDir, PhpUnit $phpUnit, ?bool $ignoreMsiWithNoMutations, ?float $minMsi, ?float $minCoveredMsi, array $mutators, ?string $testFramework, ?string $bootstrap, ?string $initialTestsPhpOptions, ?string $testFrameworkExtraOptions)
    {
        Assert::nullOrGreaterThanEq($timeout, 0);
        Assert::nullOrOneOf($testFramework, TestFrameworkTypes::TYPES);
        $this->file = $file;
        $this->timeout = $timeout;
        $this->source = $source;
        $this->logs = $logs;
        $this->tmpDir = $tmpDir;
        $this->phpUnit = $phpUnit;
        $this->ignoreMsiWithNoMutations = $ignoreMsiWithNoMutations;
        $this->minMsi = $minMsi;
        $this->minCoveredMsi = $minCoveredMsi;
        $this->mutators = $mutators;
        $this->testFramework = $testFramework;
        $this->bootstrap = $bootstrap;
        $this->initialTestsPhpOptions = $initialTestsPhpOptions;
        $this->testFrameworkExtraOptions = $testFrameworkExtraOptions;
    }
    public function getFile() : string
    {
        return $this->file;
    }
    public function getTimeout() : ?float
    {
        return $this->timeout;
    }
    public function getSource() : Source
    {
        return $this->source;
    }
    public function getLogs() : Logs
    {
        return $this->logs;
    }
    public function getTmpDir() : ?string
    {
        return $this->tmpDir;
    }
    public function getPhpUnit() : PhpUnit
    {
        return $this->phpUnit;
    }
    public function getIgnoreMsiWithNoMutations() : ?bool
    {
        return $this->ignoreMsiWithNoMutations;
    }
    public function getMinMsi() : ?float
    {
        return $this->minMsi;
    }
    public function getMinCoveredMsi() : ?float
    {
        return $this->minCoveredMsi;
    }
    public function getMutators() : array
    {
        return $this->mutators;
    }
    public function getTestFramework() : ?string
    {
        return $this->testFramework;
    }
    public function getBootstrap() : ?string
    {
        return $this->bootstrap;
    }
    public function getInitialTestsPhpOptions() : ?string
    {
        return $this->initialTestsPhpOptions;
    }
    public function getTestFrameworkExtraOptions() : ?string
    {
        return $this->testFrameworkExtraOptions;
    }
}
