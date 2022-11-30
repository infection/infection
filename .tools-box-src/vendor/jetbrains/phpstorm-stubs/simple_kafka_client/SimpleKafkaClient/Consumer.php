<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\SimpleKafkaClient;

use SimpleKafkaClient;
class Consumer extends SimpleKafkaClient
{
    public function __construct(Configuration $configuration)
    {
    }
    public function assign(?array $topicPartitions) : void
    {
    }
    public function getAssignment() : array
    {
    }
    public function subscribe(array $topics) : void
    {
    }
    public function getSubscription() : array
    {
    }
    public function unsubscribe() : void
    {
    }
    public function consume(int $timeoutMs) : Message
    {
    }
    public function commit($messageOrOffsets) : void
    {
    }
    public function commitAsync($messageOrOffsets) : void
    {
    }
    public function close() : void
    {
    }
    public function getCommittedOffsets(array $topicPartitions, int $timeoutMs) : array
    {
    }
    public function getOffsetPositions(array $topicPartitions) : array
    {
    }
}
