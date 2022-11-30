<?php
declare(strict_types=1);

use SimpleKafkaClient\Exception;
use SimpleKafkaClient\Metadata;
use SimpleKafkaClient\Topic;
use SimpleKafkaClient\TopicPartition;

abstract class SimpleKafkaClient
{







public function getMetadata(bool $allTopics, int $timeoutMs, Topic $topic): Metadata {}




public function getOutQLen(): int {}





public function poll(int $timeoutMs): int {}









public function queryWatermarkOffsets(string $topic, int $partition, int &$low, int &$high, int $timeoutMs): void {}







public function offsetsForTimes(array $topicPartitions, int $timeoutMs): array {}




public function setOAuthBearerTokenFailure(string $errorString): void {}







public function setOAuthBearerToken(string $token, int $lifetimeMs, string $principalName, ?array $extensions = null): void {}
}
