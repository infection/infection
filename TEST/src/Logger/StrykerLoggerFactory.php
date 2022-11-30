<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger;

use _HumbugBox9658796bb9f0\Infection\Configuration\Entry\Logs;
use _HumbugBox9658796bb9f0\Infection\Environment\BuildContextResolver;
use _HumbugBox9658796bb9f0\Infection\Environment\StrykerApiKeyResolver;
use _HumbugBox9658796bb9f0\Infection\Logger\Html\StrykerHtmlReportBuilder;
use _HumbugBox9658796bb9f0\Infection\Logger\Http\StrykerCurlClient;
use _HumbugBox9658796bb9f0\Infection\Logger\Http\StrykerDashboardClient;
use _HumbugBox9658796bb9f0\Infection\Metrics\MetricsCalculator;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\CiDetector;
use _HumbugBox9658796bb9f0\Psr\Log\LoggerInterface;
class StrykerLoggerFactory
{
    public function __construct(private MetricsCalculator $metricsCalculator, private StrykerHtmlReportBuilder $strykerHtmlReportBuilder, private CiDetector $ciDetector, private LoggerInterface $logger)
    {
    }
    public function createFromLogEntries(Logs $logConfig) : ?MutationTestingResultsLogger
    {
        $strykerConfig = $logConfig->getStrykerConfig();
        if ($strykerConfig === null) {
            return null;
        }
        return new StrykerLogger(new BuildContextResolver($this->ciDetector), new StrykerApiKeyResolver(), new StrykerDashboardClient(new StrykerCurlClient(), $this->logger), $this->metricsCalculator, $this->strykerHtmlReportBuilder, $strykerConfig, $this->logger);
    }
}
