<?php


use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;





class stdClass {}




interface iterable {}

/**
@template
@template
@template-implements






*/
interface Traversable extends iterable {}

/**
@template
@template
@template-implements


*/
interface IteratorAggregate extends Traversable
{







#[TentativeType]
public function getIterator(): Traversable;
}

/**
@template
@template
@template-implements



*/
interface Iterator extends Traversable
{





#[TentativeType]
public function current(): mixed;






#[TentativeType]
public function next(): void;






#[TentativeType]
public function key(): mixed;







#[TentativeType]
public function valid(): bool;






#[TentativeType]
public function rewind(): void;
}

/**
@template
@template


*/
interface ArrayAccess
{











#[TentativeType]
public function offsetExists(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $offset): bool;









#[TentativeType]
public function offsetGet(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $offset): mixed;












#[TentativeType]
public function offsetSet(
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $offset,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value
): void;









#[TentativeType]
public function offsetUnset(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $offset): void;
}







interface Serializable
{






public function serialize();







public function unserialize(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data);
}







interface Throwable extends Stringable
{






public function getMessage(): string;












public function getCode();







public function getFile(): string;







public function getLine(): int;










public function getTrace(): array;







public function getTraceAsString(): string;







#[LanguageLevelTypeAware(['8.0' => 'Throwable|null'], default: '')]
public function getPrevious();







public function __toString();
}






class Exception implements Throwable
{

protected $message;


protected $code;


#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
protected $file;


#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
protected $line;







#[PhpStormStubsElementAvailable(from: "5.4", to: "8.0")]
final private function __clone(): void {}







#[PhpStormStubsElementAvailable("8.1")]
private function __clone(): void {}








#[Pure]
public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $message = "",
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $code = 0,
#[LanguageLevelTypeAware(['8.0' => 'Throwable|null'], default: 'Throwable')] $previous = null
) {}






#[Pure]
final public function getMessage(): string {}









#[Pure]
final public function getCode() {}






#[Pure]
final public function getFile(): string {}






#[Pure]
final public function getLine(): int {}






#[Pure]
final public function getTrace(): array {}







#[Pure]
final public function getPrevious(): ?Throwable {}






#[Pure]
final public function getTraceAsString(): string {}






#[TentativeType]
public function __toString(): string {}

#[TentativeType]
public function __wakeup(): void {}
}






class Error implements Throwable
{

protected $message;


protected $code;


#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
protected $file;


#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
protected $line;








#[Pure]
public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $message = "",
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $code = 0,
#[LanguageLevelTypeAware(['8.0' => 'Throwable|null'], default: 'Throwable')] $previous = null
) {}







final public function getMessage(): string {}












final public function getCode() {}







final public function getFile(): string {}







final public function getLine(): int {}










final public function getTrace(): array {}







final public function getTraceAsString(): string {}







final public function getPrevious(): ?Throwable {}







public function __toString(): string {}







#[PhpStormStubsElementAvailable(from: "7.0", to: "8.0")]
final private function __clone(): void {}







#[PhpStormStubsElementAvailable('8.1')]
private function __clone(): void {}

#[TentativeType]
public function __wakeup(): void {}
}

class ValueError extends Error {}









class TypeError extends Error {}






class ParseError extends CompileError {}








class ArgumentCountError extends TypeError {}








class ArithmeticError extends Error {}






class CompileError extends Error {}






class DivisionByZeroError extends ArithmeticError {}




class UnhandledMatchError extends Error {}





class ErrorException extends Exception
{
#[LanguageLevelTypeAware(['8.1' => 'int'], default: '')]
protected $severity;











#[Pure]
public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $message = "",
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $code = 0,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $severity = 1,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $filename = __FILE__,
#[LanguageLevelTypeAware(['8.0' => 'int|null'], default: '')] $line = __LINE__,
#[LanguageLevelTypeAware(['8.0' => 'Throwable|null'], default: 'Throwable')] $previous = null
) {}






final public function getSeverity(): int {}
}










final class Closure
{





private function __construct() {}








public function __invoke(...$_) {}










public function bindTo(?object $newThis, object|string|null $newScope = 'static'): ?Closure {}












public static function bind(Closure $closure, ?object $newThis, object|string|null $newScope = 'static'): ?Closure {}









public function call(object $newThis, mixed ...$args): mixed {}






public static function fromCallable(callable $callback): Closure {}
}






interface Countable
{








#[TentativeType]
public function count(): int;
}








final class WeakReference
{





public function __construct() {}







public static function create(object $object): WeakReference {}








public function get(): ?object {}
}

/**
@template
@template
@template-implements







*/
final class WeakMap implements ArrayAccess, Countable, IteratorAggregate
{







public function offsetExists($object): bool {}







public function offsetGet($object): mixed {}








public function offsetSet($object, mixed $value): void {}







public function offsetUnset($object): void {}






public function getIterator(): Iterator {}






public function count(): int {}
}






interface Stringable
{







public function __toString(): string;
}




#[Attribute(Attribute::TARGET_CLASS)]
final class Attribute
{
public int $flags;




public const TARGET_CLASS = 1;




public const TARGET_FUNCTION = 2;




public const TARGET_METHOD = 4;




public const TARGET_PROPERTY = 8;




public const TARGET_CLASS_CONSTANT = 16;




public const TARGET_PARAMETER = 32;




public const TARGET_ALL = 63;





public const IS_REPEATABLE = 64;





public function __construct(#[ExpectedValues(flagsFromClass: Attribute::class)] int $flags = self::TARGET_ALL) {}
}




final class InternalIterator implements Iterator
{
private function __construct() {}

public function current(): mixed {}

public function next(): void {}

public function key(): mixed {}

public function valid(): bool {}

public function rewind(): void {}
}




interface UnitEnum
{
public readonly string $name;




#[Pure]
public static function cases(): array;
}




interface BackedEnum extends UnitEnum
{
public readonly int|string $value;





#[Pure]
public static function from(int|string $value): static;





#[Pure]
public static function tryFrom(int|string $value): ?static;
}







interface IntBackedEnum extends BackedEnum
{
public readonly int $value;





#[Pure]
public static function from(int $value): static;





#[Pure]
public static function tryFrom(int $value): ?static;
}







interface StringBackedEnum extends BackedEnum
{
public readonly string $value;

#[Pure]
public static function from(string $value): static;

#[Pure]
public static function tryFrom(string $value): ?static;
}

/**
@template
@template
@template
@template


*/
final class Fiber
{



public function __construct(callable $callback) {}











public function start(mixed ...$args): mixed {}












public function resume(mixed $value = null): mixed {}












public function throw(Throwable $exception): mixed {}




public function isStarted(): bool {}




public function isSuspended(): bool {}




public function isRunning(): bool {}




public function isTerminated(): bool {}






public function getReturn(): mixed {}




public static function getCurrent(): ?Fiber {}













public static function suspend(mixed $value = null): mixed {}
}




final class FiberError extends Error
{
public function __construct() {}
}




#[Attribute(Attribute::TARGET_METHOD)]
final class ReturnTypeWillChange
{
public function __construct() {}
}




#[Attribute(Attribute::TARGET_CLASS)]
final class AllowDynamicProperties
{
public function __construct() {}
}




#[Attribute(Attribute::TARGET_PARAMETER)]
final class SensitiveParameter
{
public function __construct() {}
}




final class SensitiveParameterValue
{
private readonly mixed $value;

public function __construct(mixed $value) {}

public function getValue(): mixed {}

public function __debugInfo(): array {}
}
