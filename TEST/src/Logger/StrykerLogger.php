<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger;

use function getenv;
use _HumbugBox9658796bb9f0\Infection\Configuration\Entry\StrykerConfig;
use _HumbugBox9658796bb9f0\Infection\Environment\BuildContextResolver;
use _HumbugBox9658796bb9f0\Infection\Environment\CouldNotResolveBuildContext;
use _HumbugBox9658796bb9f0\Infection\Environment\CouldNotResolveStrykerApiKey;
use _HumbugBox9658796bb9f0\Infection\Environment\StrykerApiKeyResolver;
use _HumbugBox9658796bb9f0\Infection\Logger\Html\StrykerHtmlReportBuilder;
use _HumbugBox9658796bb9f0\Infection\Logger\Http\StrykerDashboardClient;
use _HumbugBox9658796bb9f0\Infection\Metrics\MetricsCalculator;
use _HumbugBox9658796bb9f0\Psr\Log\LoggerInterface;
use function _HumbugBox9658796bb9f0\Safe\json_encode;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
final class StrykerLogger implements MutationTestingResultsLogger
{
    public function __construct(private BuildContextResolver $buildContextResolver, private StrykerApiKeyResolver $strykerApiKeyResolver, private StrykerDashboardClient $strykerDashboardClient, private MetricsCalculator $metricsCalculator, private StrykerHtmlReportBuilder $strykerHtmlReportBuilder, private StrykerConfig $strykerConfig, private LoggerInterface $logger)
    {
    }
    public function log() : void
    {
        try {
            $buildContext = $this->buildContextResolver->resolve();
        } catch (CouldNotResolveBuildContext $exception) {
            $this->logReportWasNotSent($exception->getMessage());
            return;
        }
        $branch = $buildContext->branch();
        if (!$this->strykerConfig->applicableForBranch($branch)) {
            $this->logReportWasNotSent(sprintf('Branch "%s" does not match expected Stryker configuration', $branch));
            return;
        }
        try {
            $apiKey = $this->strykerApiKeyResolver->resolve(getenv());
        } catch (CouldNotResolveStrykerApiKey $exception) {
            $this->logReportWasNotSent($exception->getMessage());
            return;
        }
        $this->logger->warning('Sending dashboard report...');
        $report = $this->strykerConfig->isForFullReport() ? $this->strykerHtmlReportBuilder->build() : ['mutationScore' => $this->metricsCalculator->getMutationScoreIndicator()];
        $this->strykerDashboardClient->sendReport('github.com/' . $buildContext->repositorySlug(), $branch, $apiKey, json_encode($report));
    }
    private function logReportWasNotSent(string $message) : void
    {
        $this->logger->warning(sprintf('Dashboard report has not been sent: %s', $message));
    }
}
