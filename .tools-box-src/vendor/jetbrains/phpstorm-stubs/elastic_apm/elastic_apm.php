<?php

namespace Elastic\Apm;




final class ElasticApm
{
public const VERSION = '1.3.1';

private function __construct() {}
















public static function beginCurrentTransaction(
string $name,
string $type,
?float $timestamp = null,
?string $serializedDistTracingData = null
): TransactionInterface {}


















public static function captureCurrentTransaction(
string $name,
string $type,
\Closure $callback,
?float $timestamp = null,
?string $serializedDistTracingData = null
) {}






public static function getCurrentTransaction(): TransactionInterface {}








public static function getCurrentExecutionSegment(): ExecutionSegmentInterface {}
















public static function beginTransaction(
string $name,
string $type,
?float $timestamp = null,
?string $serializedDistTracingData = null
): TransactionInterface {}


















public static function captureTransaction(
string $name,
string $type,
\Closure $callback,
?float $timestamp = null,
?string $serializedDistTracingData = null
) {}












public static function newTransaction(string $name, string $type): TransactionBuilderInterface {}












public static function createErrorFromThrowable(\Throwable $throwable): ?string {}












public static function createCustomError(CustomErrorData $customErrorData): ?string {}




public static function pauseRecording(): void {}




public static function resumeRecording(): void {}







public static function getSerializedCurrentDistributedTracingData(): string {}
}







interface TransactionBuilderInterface
{





public function asCurrent(): self;








public function timestamp(float $timestamp): self;






public function distributedTracingHeaderExtractor(\Closure $headerExtractor): self;






public function begin(): TransactionInterface;









public function capture(\Closure $callback);
}

interface TransactionInterface extends ExecutionSegmentInterface
{






public function isSampled(): bool;







public function getParentId(): ?string;




















public function beginCurrentSpan(
string $name,
string $type,
?string $subtype = null,
?string $action = null,
?float $timestamp = null
): SpanInterface;





















public function captureCurrentSpan(
string $name,
string $type,
\Closure $callback,
?string $subtype = null,
?string $action = null,
?float $timestamp = null
);






public function getCurrentSpan(): SpanInterface;




public function context(): TransactionContextInterface;











public function setResult(?string $result): void;




public function getResult(): ?string;








public function ensureParentId(): string;
}

interface SpanInterface extends ExecutionSegmentInterface
{





public function getTransactionId(): string;








public function getParentId(): string;













public function setAction(?string $action): void;











public function setSubtype(?string $subtype): void;




public function context(): SpanContextInterface;









public function endSpanEx(int $numberOfStackFramesToSkip, ?float $duration = null): void;
}

interface SpanContextInterface extends ExecutionSegmentContextInterface
{





public function db(): SpanContextDbInterface;






public function http(): SpanContextHttpInterface;






public function destination(): SpanContextDestinationInterface;
}

interface SpanContextDbInterface
{









public function setStatement(?string $statement): void;
}

interface SpanContextHttpInterface
{









public function setUrl(?string $url): void;










public function setStatusCode(?int $statusCode): void;












public function setMethod(?string $method): void;
}






interface SpanContextDestinationInterface
{









public function setService(string $name, string $resource, string $type): void;
}




interface ExecutionSegmentInterface
{






public function getId(): string;







public function getTraceId(): string;










public function getTimestamp(): float;


















public function beginChildSpan(
string $name,
string $type,
?string $subtype = null,
?string $action = null,
?float $timestamp = null
): SpanInterface;




















public function captureChildSpan(
string $name,
string $type,
\Closure $callback,
?string $subtype = null,
?string $action = null,
?float $timestamp = null
);
















public function setName(string $name): void;














public function setType(string $type): void;







public function getDistributedTracingData(): ?DistributedTracingData;










public function injectDistributedTracingHeaders(\Closure $headerInjector): void;










public function end(?float $duration = null): void;




public function hasEnded(): bool;











public function createErrorFromThrowable(\Throwable $throwable): ?string;











public function createCustomError(CustomErrorData $customErrorData): ?string;















public function setOutcome(?string $outcome): void;




public function getOutcome(): ?string;




public function isNoop(): bool;




public function discard(): void;
}

final class DistributedTracingData
{

public $traceId;


public $parentId;


public $isSampled;







public function serializeToString(): string {}










public function injectHeaders(\Closure $headerInjector): void {}
}










class CustomErrorData
{









public $code = null;








public $message = null;










public $module = null;








public $type = null;
}

interface TransactionContextInterface extends ExecutionSegmentContextInterface
{






public function request(): TransactionContextRequestInterface;
}




interface ExecutionSegmentContextInterface
{















public function setLabel(string $key, $value): void;
}

interface TransactionContextRequestInterface
{











public function setMethod(string $method): void;






public function url(): TransactionContextRequestUrlInterface;
}

interface TransactionContextRequestUrlInterface
{











public function setDomain(?string $domain): void;












public function setFull(?string $full): void;














public function setOriginal(?string $original): void;












public function setPath(?string $path): void;










public function setPort(?int $port): void;












public function setProtocol(?string $protocol): void;













public function setQuery(?string $query): void;
}
