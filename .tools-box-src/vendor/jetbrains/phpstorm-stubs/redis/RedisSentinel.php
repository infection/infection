<?php

































class RedisSentinel
{














public function __construct(
string $host,
int $port,
float $timeout = 0,
?string $persistent = null,
int $retryInterval = 0,
float $readTimeout = 0
) {}














public function ckquorum(string $master): bool {}














public function failover(string $master): bool {}

















public function flushconfig(): bool {}













public function getMasterAddrByName(string $master) {}












public function master(string $master) {}










public function masters() {}










public function ping(): bool {}














public function reset(string $pattern): bool {}












public function sentinels(string $master) {}












public function slaves(string $master) {}
}
