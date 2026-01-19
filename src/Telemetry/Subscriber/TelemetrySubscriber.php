<?php

declare(strict_types=1);

namespace Infection\Telemetry\Subscriber;

use Infection\Event\ApplicationExecutionWasFinished;
use Infection\Event\ApplicationExecutionWasStarted;
use Infection\Event\Events\AstCollection\SourceAstCollectionFinished;
use Infection\Event\Events\AstCollection\SourceAstCollectionFinishedSubscriber;
use Infection\Event\Events\AstCollection\SourceAstCollectionStarted;
use Infection\Event\Events\AstCollection\SourceAstCollectionStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisFinished;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisStarted;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\SourceMutationEvaluationFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\SourceMutationEvaluationFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\SourceMutationEvaluationStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\SourceMutationEvaluationStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\SourceMutationGenerationFinished;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\SourceMutationGenerationFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\SourceMutationGenerationStarted;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\SourceMutationGenerationStartedSubscriber;
use Infection\Event\Events\SourceCollection\SourceCollectionFinished;
use Infection\Event\Events\SourceCollection\SourceCollectionFinishedSubscriber;
use Infection\Event\Events\SourceCollection\SourceCollectionStarted;
use Infection\Event\Events\SourceCollection\SourceCollectionStartedSubscriber;
use Infection\Event\Events\Tracing\SourceTracingFinished;
use Infection\Event\Events\Tracing\SourceTracingFinishedSubscriber;
use Infection\Event\Events\Tracing\SourceTracingStarted;
use Infection\Event\Events\Tracing\SourceTracingStartedSubscriber;
use Infection\Event\InitialStaticAnalysisRunWasFinished;
use Infection\Event\InitialStaticAnalysisRunWasStarted;
use Infection\Event\InitialStaticAnalysisSubStepWasCompleted;
use Infection\Event\InitialTestCaseWasCompleted;
use Infection\Event\InitialTestSuiteWasFinished;
use Infection\Event\InitialTestSuiteWasStarted;
use Infection\Event\MutableFileWasProcessed;
use Infection\Event\MutantProcessWasFinished;
use Infection\Event\MutationGenerationWasFinished;
use Infection\Event\MutationGenerationWasStarted;
use Infection\Event\MutationTestingWasFinished;
use Infection\Event\MutationTestingWasStarted;
use Infection\Event\Subscriber\EventSubscriber;
use function file_put_contents;
use const FILE_APPEND;

final class TelemetrySubscriber implements EventSubscriber,
    SourceCollectionStartedSubscriber,
    SourceCollectionFinishedSubscriber,
    SourceAstCollectionStartedSubscriber,
    SourceAstCollectionFinishedSubscriber,
    MutationAnalysisStartedSubscriber,
    MutationAnalysisFinishedSubscriber,
    SourceMutationGenerationFinishedSubscriber,
    SourceMutationGenerationStartedSubscriber,
    SourceMutationEvaluationStartedSubscriber,
    SourceMutationEvaluationFinishedSubscriber,
    SourceTracingStartedSubscriber,
    SourceTracingFinishedSubscriber
{
    private const LOG_FILE = __DIR__.'/../../../var/telemetry.log';

    public function __construct()
    {
        file_put_contents(self::LOG_FILE, '');
    }

    // Actually having a `::notify()` method à la PHPUnit would be sweet here...
    public function onApplicationExecutionWasStarted(ApplicationExecutionWasStarted $event): void
    {
        $this->log($event);
    }

    public function onApplicationExecutionWasFinished(ApplicationExecutionWasFinished $event): void
    {
        $this->log($event);
    }

    public function onInitialStaticAnalysisRunWasStarted(InitialStaticAnalysisRunWasStarted $event): void
    {
        $this->log($event);
    }

    public function onInitialStaticAnalysisRunWasFinished(InitialStaticAnalysisRunWasFinished $event): void
    {
        $this->log($event);
    }

    public function onInitialStaticAnalysisSubStepWasCompleted(InitialStaticAnalysisSubStepWasCompleted $event): void
    {
        $this->log($event);
    }

    public function onInitialTestCaseWasCompleted(InitialTestCaseWasCompleted $event): void
    {
        $this->log($event);
    }

    public function onInitialTestSuiteWasFinished(InitialTestSuiteWasFinished $event): void
    {
        $this->log($event);
    }

    public function onInitialTestSuiteWasStarted(InitialTestSuiteWasStarted $event): void
    {
        $this->log($event);
    }

    public function onMutableFileWasProcessed(MutableFileWasProcessed $event): void
    {
        $this->log($event);
    }

    public function onMutantProcessWasFinished(MutantProcessWasFinished $event): void
    {
        $this->log($event);
    }

    public function onMutationGenerationWasFinished(MutationGenerationWasFinished $event): void
    {
        $this->log($event);
    }

    public function onMutationGenerationWasStarted(MutationGenerationWasStarted $event): void
    {
        $this->log($event);
    }

    public function onMutationTestingWasFinished(MutationTestingWasFinished $event): void
    {
        $this->log($event);
    }

    public function onMutationTestingWasStarted(MutationTestingWasStarted $event): void
    {
        $this->log($event);
    }

    private function log(object $event): void
    {
        file_put_contents(
            self::LOG_FILE,
            $event::class.PHP_EOL,
            FILE_APPEND,
        );
    }

    public function onSourceCollectionStarted(SourceCollectionStarted $event): void
    {
        $this->log($event);
    }

    public function onSourceCollectionFinished(SourceCollectionFinished $event): void
    {
        $this->log($event);
    }

    public function onSourceTracingFinished(SourceTracingFinished $event): void
    {
        $this->log($event);
    }

    public function onSourceAstCollectionFinished(SourceAstCollectionFinished $event): void
    {
        $this->log($event);
    }

    public function onSourceAstCollectionStarted(SourceAstCollectionStarted $event): void
    {
        $this->log($event);
    }

    public function onMutationAnalysisFinished(MutationAnalysisFinished $event): void
    {
        $this->log($event);
    }

    public function onMutationAnalysisStarted(MutationAnalysisStarted $event): void
    {
        $this->log($event);
    }

    public function onSourceMutationGenerationStarted(SourceMutationGenerationStarted $event): void
    {
        $this->log($event);
    }

    public function onSourceMutationGenerationFinished(SourceMutationGenerationFinished $event): void
    {
        $this->log($event);
    }

    public function onSourceMutationEvaluationStarted(SourceMutationEvaluationStarted $event): void
    {
        $this->log($event);
    }

    public function onSourceMutationEvaluationFinished(SourceMutationEvaluationFinished $event): void
    {
        $this->log($event);
    }

    public function onSourceTracingStarted(SourceTracingStarted $event): void
    {
        $this->log($event);
    }
}