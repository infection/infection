<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutation;

use _HumbugBox9658796bb9f0\Infection\Event\EventDispatcher\EventDispatcher;
use _HumbugBox9658796bb9f0\Infection\Event\MutableFileWasProcessed;
use _HumbugBox9658796bb9f0\Infection\Event\MutationGenerationWasFinished;
use _HumbugBox9658796bb9f0\Infection\Event\MutationGenerationWasStarted;
use _HumbugBox9658796bb9f0\Infection\IterableCounter;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\PhpParser\UnparsableFile;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\IgnoreNode\NodeIgnorer;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\Trace;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\TraceProvider;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class MutationGenerator
{
    private array $mutators;
    public function __construct(private TraceProvider $traceProvider, array $mutators, private EventDispatcher $eventDispatcher, private FileMutationGenerator $fileMutationGenerator, private bool $runConcurrently)
    {
        Assert::allIsInstanceOf($mutators, Mutator::class);
        $this->mutators = $mutators;
    }
    public function generate(bool $onlyCovered, array $nodeIgnorers) : iterable
    {
        $traces = $this->traceProvider->provideTraces();
        $numberOfFiles = IterableCounter::bufferAndCountIfNeeded($traces, $this->runConcurrently);
        $this->eventDispatcher->dispatch(new MutationGenerationWasStarted($numberOfFiles));
        foreach ($traces as $trace) {
            yield from $this->fileMutationGenerator->generate($trace, $onlyCovered, $this->mutators, $nodeIgnorers);
            $this->eventDispatcher->dispatch(new MutableFileWasProcessed());
        }
        $this->eventDispatcher->dispatch(new MutationGenerationWasFinished());
    }
}
