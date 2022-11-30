<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

use _HumbugBoxb47773b41c19\Amp\Coroutine;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\Channel;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\SerializationException;
use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\call;
final class TaskRunner
{
    private $channel;
    private $environment;
    public function __construct(Channel $channel, Environment $environment)
    {
        $this->channel = $channel;
        $this->environment = $environment;
    }
    public function run() : Promise
    {
        return new Coroutine($this->execute());
    }
    /**
    @coroutine
    */
    private function execute() : \Generator
    {
        $job = (yield $this->channel->receive());
        while ($job instanceof Internal\Job) {
            try {
                $result = (yield call([$job->getTask(), "run"], $this->environment));
                $result = new Internal\TaskSuccess($job->getId(), $result);
            } catch (\Throwable $exception) {
                $result = new Internal\TaskFailure($job->getId(), $exception);
            }
            $job = null;
            try {
                (yield $this->channel->send($result));
            } catch (SerializationException $exception) {
                (yield $this->channel->send(new Internal\TaskFailure($result->getId(), $exception)));
            }
            $result = null;
            $job = (yield $this->channel->receive());
        }
        return $job;
    }
}
