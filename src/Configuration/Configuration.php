<?php

declare(strict_types=1);

namespace Infection\Configuration;

use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\Mutator\Mutators;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Webmozart\Assert\Assert;

final class Configuration
{
    private const TEST_FRAMEWORKS = [
        'phpunit',
        'phpspec',
    ];

    private $timeout;
    private $source;
    private $logs;
    private $tmpDir;
    private $phpUnit;
    private $mutators;
    private $testFramework;
    private $bootstrap;
    private $initialTestsPhpOptions;
    private $testFrameworkOptions;

    public function __construct(
        ?int $timeout,
        Source $source,
        Logs $logs,
        ?string $tmpDir,
        PhpUnit $phpUnit,
        Mutators $mutators,
        ?string $testFramework,
        ?string $bootstrap,
        ?string $initialTestsPhpOptions,
        ?string $testFrameworkOptions
    ) {
        Assert::nullOrGreaterThanEq($timeout, 1);
        Assert::nullOrOneOf($testFramework, self::TEST_FRAMEWORKS);

        $this->timeout = $timeout;
        $this->source = $source;
        $this->logs = $logs;
        $this->tmpDir = $tmpDir;
        $this->phpUnit = $phpUnit;
        $this->mutators = $mutators;
        $this->testFramework = $testFramework;
        $this->bootstrap = $bootstrap;
        $this->initialTestsPhpOptions = $initialTestsPhpOptions;
        $this->testFrameworkOptions = $testFrameworkOptions;
    }

    public function withInput(
        ?Source $source,
        ?Logs $logs,
        ?string $tmpDir,
        ?PhpUnit $phpUnit,
        ?Mutators $mutators,
        ?string $testFramework,
        ?string $bootstrap,
        ?string $initialTestsPhpOptions,
        ?string $testFrameworkOptions
    ): self
    {
        $self = $this;

        return $self;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function getSource(): Source
    {
        return $this->source;
    }

    public function getLogs(): Logs
    {
        return $this->logs;
    }

    public function getTmpDir(): ?string
    {
        return $this->tmpDir;
    }

    public function getPhpUnit(): PhpUnit
    {
        return $this->phpUnit;
    }

    public function getMutators(): Mutators
    {
        return $this->mutators;
    }

    public function getTestFramework(): ?string
    {
        return $this->testFramework;
    }

    public function getBootstrap(): ?string
    {
        return $this->bootstrap;
    }

    public function getInitialTestsPhpOptions(): ?string
    {
        return $this->initialTestsPhpOptions;
    }

    public function getTestFrameworkOptions(): ?string
    {
        return $this->testFrameworkOptions;
    }
}