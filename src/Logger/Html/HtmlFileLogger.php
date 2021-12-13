<?php

declare(strict_types=1);


namespace Infection\Logger\Html;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Logger\LineMutationTestingResultsLogger;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutator\FunctionSignature\ProtectedVisibility;
use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\ProfileList;
use Infection\Mutator\Removal\MethodCallRemoval;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function array_reduce;
use function array_unique;
use function in_array;
use function json_encode;
use function md5;
use function preg_match;
use function strlen;

/**
 * @internal
 */
final class HtmlFileLogger implements LineMutationTestingResultsLogger
{
    private StrykerHtmlReportBuilder $strykerHtmlReportBuilder;

    public function __construct(
        StrykerHtmlReportBuilder $strykerHtmlReportBuilder
    )
    {
        $this->strykerHtmlReportBuilder = $strykerHtmlReportBuilder;
    }

    public function getLogLines(): array
    {
        return [
            <<<"HTML"
            <!DOCTYPE html>
            <html>
                <body>
                    <a href="/">Back</a>
                    <mutation-test-report-app title-postfix="Infection"></mutation-test-report-app>
                    <script defer src="https://www.unpkg.com/mutation-testing-elements"></script>
                    <script>
                        const app = document.getElementsByTagName('mutation-test-report-app').item(0);
                        function updateTheme() {
                            document.body.style.backgroundColor = app.themeBackgroundColor;
                        }
                        app.addEventListener('theme-changed', updateTheme);
                        updateTheme();

                        document.getElementsByTagName('mutation-test-report-app').item(0).report = {$this->getMutationTestingReport()}
                        ;
                    </script>
                </body>
            </html>
            HTML
        ];
    }

    public function getMutationTestingReport(): string
    {
        return json_encode($this->strykerHtmlReportBuilder->build());
    }
}
