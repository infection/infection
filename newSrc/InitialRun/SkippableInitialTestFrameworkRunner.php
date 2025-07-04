<?php

declare(strict_types=1);

namespace newSrc\InitialRun;

use newSrc\Configuration;
use newSrc\Logger\Logger;
use newSrc\TestFramework\TestFramework;

final class SkippableInitialTestFrameworkRunner implements InitialTestFrameworkRunner
{
    public function __construct(
        private TestFramework $testFramework,
        private Configuration $configuration,
        private Logger $logger,
    ) {
    }

    public function run(): void
    {
        $frameworkName = $this->testFramework->getName();

        if ($this->testFramework->isSkippable() && $this->configuration->shouldSkipInitialTests($frameworkName)) {
            $this->logger->logSkippingInitialTests($frameworkName);

            // The test framework adapter does the coverage check – as it may differ from a test framework to another
            $this->testFramework->checkRequiredArtefacts();
        } else {
            // The test framework adapter does the initial run, rather than knowing about the details
            // We shouldn't need to get the output:
            // - logging can be handled within the test framework
            // - setting the memory limit will be test framework specific, e.g. PHPUnit may need 20MB, Behat 50.
            //   As such, it could simply set internal state as any process started by this test framework is handled by this test framework.
            $this->testFramework->executeInitialRun();
        }
    }
}