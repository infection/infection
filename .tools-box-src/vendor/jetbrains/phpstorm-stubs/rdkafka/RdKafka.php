<?php

use RdKafka\Exception;
use RdKafka\Metadata;
use RdKafka\Topic;
use RdKafka\TopicConf;
use RdKafka\TopicPartition;

abstract class RdKafka
{





public function addBrokers($broker_list) {}









public function getMetadata($all_topics, $only_topic = null, $timeout_ms = 0) {}




public function getOutQLen() {}







public function newTopic($topic_name, $topic_conf = null) {}






public function poll($timeout_ms) {}






public function setLogLevel($level) {}







public function offsetsForTimes($topic_partitions, $timeout_ms) {}










public function queryWatermarkOffsets($topic, $partition = 0, &$low = 0, &$high = 0, $timeout_ms = 0) {}






public function purge($purge_flags) {}






public function flush($timeout_ms) {}

public function metadata($all_topics, $only_topic = false, $timeout_ms = 0) {}

public function setLogger($logger) {}

public function outqLen() {}
}
