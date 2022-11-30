<?php

namespace _HumbugBoxb47773b41c19\RdKafka;

class KafkaConsumer
{
    public function __construct($conf)
    {
    }
    public function assign($topic_partitions = null)
    {
    }
    public function commit($message_or_offsets = null)
    {
    }
    public function commitAsync($message_or_offsets = null)
    {
    }
    public function consume($timeout_ms)
    {
    }
    public function getAssignment()
    {
    }
    public function getMetadata($all_topics, $only_topic = null, $timeout_ms)
    {
    }
    public function getSubscription()
    {
    }
    public function subscribe($topics)
    {
    }
    public function unsubscribe()
    {
    }
    public function getCommittedOffsets($topic_partitions, $timeout_ms)
    {
    }
    public function offsetsForTimes($topic_partitions, $timeout_ms)
    {
    }
    public function queryWatermarkOffsets($topic, $partition = 0, &$low = 0, &$high = 0, $timeout_ms = 0)
    {
    }
    public function getOffsetPositions($topic_partitions)
    {
    }
    public function newTopic($topic_name, $topic_conf = null)
    {
    }
    public function close()
    {
    }
}
