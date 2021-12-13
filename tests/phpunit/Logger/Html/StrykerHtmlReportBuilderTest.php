<?php

declare(strict_types=1);


namespace Infection\Tests\Logger\Html;


use Infection\Logger\Html\StrykerHtmlReportBuilder;
use Infection\Logger\JsonLogger;
use Infection\Metrics\Collector;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutator\Loop\For_;
use Infection\Tests\Mutator\MutatorName;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use function array_map;
use function implode;
use function Infection\Tests\normalize_trailing_spaces;
use function Later\now;
use function Safe\json_decode;
use function Safe\json_encode;
use function Safe\sprintf;

final class StrykerHtmlReportBuilderTest extends TestCase
{
    private const SCHEMA_FILE = 'file://' . __DIR__ . '/../../../../resources/mutation-testing-report-schema.json';

    /**
     * @dataProvider metricsProvider
     */
    public function test_it_logs_correctly_with_mutations(
        MetricsCalculator $metricsCalculator,
        ResultsCollector $resultsCollector,
        array $expectedReport
    ): void
    {
        $this->markTestSkipped();
        $report = (new StrykerHtmlReportBuilder($metricsCalculator, $resultsCollector))->build();

        $this->assertSame($expectedReport, json_decode(json_encode($report), true));
        $this->assertJsonDocumentMatchesSchema($report);
    }

    public function metricsProvider()
    {
        yield 'no mutations' => [
            new MetricsCalculator(2),
            new ResultsCollector(),
            [
                'schemaVersion' => '1',
                'thresholds' => [
                    'high' => 90,
                    'low' => 50,
                ],
                'files' => [],
                'testFiles' => [],
                'framework' => [
                    'name' => 'Infection',
                    'branding' => [
                        'homepageUrl' => 'https://infection.github.io/',
                        'imageUrl' => 'https://infection.github.io/images/logo.png'
                    ]
                ]
            ],
        ];

        yield 'one mutation' => [
            $this->createIgnoredMetricsCalculator(),
            $this->createIgnoredResultsCollector(),
            [
                'schemaVersion' => '1',
                'thresholds' => [
                    'high' => 90,
                    'low' => 50,
                ],
                'files' => [],
                'testFiles' => [],
                'framework' => [
                    'name' => 'Infection',
                    'branding' => [
                        'homepageUrl' => 'https://infection.github.io/',
                        'imageUrl' => 'https://infection.github.io/images/logo.png'
                    ]
                ]
            ],
        ];
    }

    private function createIgnoredMetricsCalculator(): MetricsCalculator
    {
        $collector = new MetricsCalculator(2);

        $this->initIgnoredCollector($collector);

        return $collector;
    }

    private function createIgnoredResultsCollector(): ResultsCollector
    {
        $collector = new ResultsCollector();

        $this->initIgnoredCollector($collector);

        return $collector;
    }

    private function assertJsonDocumentMatchesSchema($report): void
    {
        $resultReport = json_decode(json_encode($report));

        $validator = new Validator();

        $validator->validate($resultReport, (object)['$ref' => self::SCHEMA_FILE]);

        $normalizedErrors = array_map(
            static function (array $error): string {
                return sprintf('[%s] %s%s', $error['property'], $error['message'], PHP_EOL);
            },
            $validator->getErrors()
        );

        $this->assertTrue(
            $validator->isValid(),
            sprintf(
                'Expected the given JSON to be valid but is violating the following rules of'
                . ' the schema: %s- %s',
                PHP_EOL,
                implode('- ', $normalizedErrors)
            )
        );
    }

    private function createCollectorWithOneMutant(): ResultsCollector
    {
        $collector = new ResultsCollector();

        $this->initIgnoredCollector($collector);

        return $collector;
    }

    private function initIgnoredCollector(Collector $collector): void
    {
        $collector->collect(
            $this->createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::IGNORED,
                'ignored#0'
            ),
        );
    }

    private function createMutantExecutionResult(
        int $i,
        string $mutatorClassName,
        string $detectionStatus,
        string $echoMutatedMessage
    ): MutantExecutionResult {
        return new MutantExecutionResult(
            'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"',
            'process output',
            $detectionStatus,
            now(normalize_trailing_spaces(
                <<<DIFF
--- Original
+++ New
@@ @@

- echo 'original';
+ echo '$echoMutatedMessage';

DIFF
            )),
            'a1b2c3',
            MutatorName::getName($mutatorClassName),
            realpath(__DIR__ . '/../../Fixtures/EmptyClass.php'),
            10 - $i,
            20 - $i,
            1,
            1 + $i,
            now('<?php $a = 1;'),
            now('<?php $a = 2;'),
            []
        );
    }
}
