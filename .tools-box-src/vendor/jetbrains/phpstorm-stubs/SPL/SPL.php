<?php


use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;






class LogicException extends Exception {}






class BadFunctionCallException extends LogicException {}






class BadMethodCallException extends BadFunctionCallException {}





class DomainException extends LogicException {}





class InvalidArgumentException extends LogicException {}





class LengthException extends LogicException {}






class OutOfRangeException extends LogicException {}





class RuntimeException extends Exception {}






class OutOfBoundsException extends RuntimeException {}





class OverflowException extends RuntimeException {}








class RangeException extends RuntimeException {}





class UnderflowException extends RuntimeException {}








class UnexpectedValueException extends RuntimeException {}





class EmptyIterator implements Iterator
{





#[TentativeType]
public function current(): never {}






#[TentativeType]
public function next(): void {}






#[TentativeType]
public function key(): never {}







#[TentativeType]
#[LanguageLevelTypeAware(['8.2' => 'false'], default: 'bool')]
public function valid() {}






#[TentativeType]
public function rewind(): void {}
}






class CallbackFilterIterator extends FilterIterator
{









public function __construct(Iterator $iterator, callable $callback) {}







#[TentativeType]
public function accept(): bool {}
}







class RecursiveCallbackFilterIterator extends CallbackFilterIterator implements RecursiveIterator
{







public function __construct(
RecursiveIterator $iterator,
#[LanguageLevelTypeAware(['8.0' => 'callable'], default: '')] $callback
) {}






#[TentativeType]
public function hasChildren(): bool {}






#[TentativeType]
public function getChildren(): RecursiveCallbackFilterIterator {}
}






interface RecursiveIterator extends Iterator
{





#[TentativeType]
public function hasChildren(): bool;






#[TentativeType]
public function getChildren(): ?RecursiveIterator;
}





class RecursiveIteratorIterator implements OuterIterator
{



public const LEAVES_ONLY = 0;




public const SELF_FIRST = 1;




public const CHILD_FIRST = 2;




public const CATCH_GET_CHILD = 16;









public function __construct(
Traversable $iterator,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $mode = self::LEAVES_ONLY,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = 0
) {}






#[TentativeType]
public function rewind(): void {}






#[TentativeType]
public function valid(): bool {}






#[TentativeType]
public function key(): mixed {}






#[TentativeType]
public function current(): mixed {}






#[TentativeType]
public function next(): void {}






#[TentativeType]
public function getDepth(): int {}







#[TentativeType]
public function getSubIterator(#[LanguageLevelTypeAware(['8.0' => 'int|null'], default: '')] $level): ?RecursiveIterator {}






#[TentativeType]
public function getInnerIterator(): RecursiveIterator {}






#[TentativeType]
public function beginIteration(): void {}






#[TentativeType]
public function endIteration(): void {}






#[TentativeType]
public function callHasChildren(): bool {}






#[TentativeType]
public function callGetChildren(): ?RecursiveIterator {}






#[TentativeType]
public function beginChildren(): void {}






#[TentativeType]
public function endChildren(): void {}






#[TentativeType]
public function nextElement(): void {}










#[TentativeType]
public function setMaxDepth(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $maxDepth = -1): void {}






#[TentativeType]
public function getMaxDepth(): int|false {}
}






interface OuterIterator extends Iterator
{





#[TentativeType]
public function getInnerIterator(): ?Iterator;
}










class IteratorIterator implements OuterIterator
{






public function __construct(Traversable $iterator, #[PhpStormStubsElementAvailable(from: '8.0')] ?string $class = '') {}






#[TentativeType]
public function getInnerIterator(): ?Iterator {}






#[TentativeType]
public function rewind(): void {}






#[TentativeType]
public function valid(): bool {}






#[TentativeType]
public function key(): mixed {}






#[TentativeType]
public function current(): mixed {}






#[TentativeType]
public function next(): void {}
}







abstract class FilterIterator extends IteratorIterator
{





#[TentativeType]
abstract public function accept(): bool;






public function __construct(Iterator $iterator) {}






#[TentativeType]
public function rewind(): void {}






public function valid(): bool {}






public function key(): mixed {}






public function current(): mixed {}






#[TentativeType]
public function next(): void {}






public function getInnerIterator(): Iterator {}
}







abstract class RecursiveFilterIterator extends FilterIterator implements RecursiveIterator
{





public function __construct(RecursiveIterator $iterator) {}






#[TentativeType]
public function hasChildren(): bool {}






#[TentativeType]
public function getChildren(): ?RecursiveFilterIterator {}
}





class ParentIterator extends RecursiveFilterIterator
{





#[TentativeType]
public function accept(): bool {}






public function __construct(RecursiveIterator $iterator) {}






public function hasChildren() {}






public function getChildren() {}
}





interface SeekableIterator extends Iterator
{








#[TentativeType]
public function seek(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $offset): void;
}






class LimitIterator extends IteratorIterator
{







public function __construct(
Iterator $iterator,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $offset = 0,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $limit = -1
) {}






#[TentativeType]
public function rewind(): void {}






#[TentativeType]
public function valid(): bool {}






public function key(): mixed {}






public function current(): mixed {}






#[TentativeType]
public function next(): void {}









#[TentativeType]
public function seek(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $offset): int {}






#[TentativeType]
public function getPosition(): int {}






public function getInnerIterator(): Iterator {}
}





class CachingIterator extends IteratorIterator implements ArrayAccess, Countable, Stringable
{




public const CALL_TOSTRING = 1;




public const TOSTRING_USE_KEY = 2;





public const TOSTRING_USE_CURRENT = 4;





public const TOSTRING_USE_INNER = 8;




public const CATCH_GET_CHILD = 16;




public const FULL_CACHE = 256;







public function __construct(Iterator $iterator, #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = self::CALL_TOSTRING) {}






#[TentativeType]
public function rewind(): void {}






#[TentativeType]
public function valid(): bool {}






public function key(): mixed {}






public function current(): mixed {}






#[TentativeType]
public function next(): void {}






#[TentativeType]
public function hasNext(): bool {}






#[TentativeType]
public function __toString(): string {}






public function getInnerIterator(): Iterator {}






#[TentativeType]
public function getFlags(): int {}







#[TentativeType]
public function setFlags(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags): void {}








#[TentativeType]
public function offsetGet($key): mixed {}









#[TentativeType]
public function offsetSet($key, #[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value): void {}








#[TentativeType]
public function offsetUnset($key): void {}








#[TentativeType]
public function offsetExists($key): bool {}







#[TentativeType]
public function getCache(): array {}








#[TentativeType]
public function count(): int {}
}





class RecursiveCachingIterator extends CachingIterator implements RecursiveIterator
{






public function __construct(Iterator $iterator, #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = self::CALL_TOSTRING) {}






#[TentativeType]
public function hasChildren(): bool {}






#[TentativeType]
public function getChildren(): ?RecursiveCachingIterator {}
}





class NoRewindIterator extends IteratorIterator
{





public function __construct(Iterator $iterator) {}






#[TentativeType]
public function rewind(): void {}






#[TentativeType]
public function valid(): bool {}






#[TentativeType]
public function key(): mixed {}






#[TentativeType]
public function current(): mixed {}






#[TentativeType]
public function next(): void {}






public function getInnerIterator(): Iterator {}
}





class AppendIterator extends IteratorIterator
{




public function __construct() {}









#[TentativeType]
public function append(Iterator $iterator): void {}






#[TentativeType]
public function rewind(): void {}






#[TentativeType]
public function valid(): bool {}






public function key(): mixed {}






#[TentativeType]
public function current(): mixed {}






#[TentativeType]
public function next(): void {}






public function getInnerIterator(): Iterator {}






#[TentativeType]
public function getIteratorIndex(): ?int {}






#[TentativeType]
public function getArrayIterator(): ArrayIterator {}
}







class InfiniteIterator extends IteratorIterator
{





public function __construct(Iterator $iterator) {}






#[TentativeType]
public function next(): void {}
}





class RegexIterator extends FilterIterator
{



public const ALL_MATCHES = 2;




public const GET_MATCH = 1;




public const MATCH = 0;




public const REPLACE = 4;




public const SPLIT = 3;




public const USE_KEY = 1;
public const INVERT_MATCH = 2;

#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $replacement;










public function __construct(
Iterator $iterator,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $pattern,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $mode = self::MATCH,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = 0,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $pregFlags = 0
) {}






#[TentativeType]
public function accept(): bool {}






#[TentativeType]
public function getMode(): int {}



















































#[TentativeType]
public function setMode(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $mode): void {}






#[TentativeType]
public function getFlags(): int {}



























#[TentativeType]
public function setFlags(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags): void {}







#[TentativeType]
public function getRegex(): string {}






#[TentativeType]
public function getPregFlags(): int {}










#[TentativeType]
public function setPregFlags(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $pregFlags): void {}
}





class RecursiveRegexIterator extends RegexIterator implements RecursiveIterator
{









public function __construct(
RecursiveIterator $iterator,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $pattern,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $mode = self::MATCH,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = 0,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $pregFlags = 0
) {}






#[TentativeType]
public function hasChildren(): bool {}






#[TentativeType]
public function getChildren(): RecursiveRegexIterator {}
}





class RecursiveTreeIterator extends RecursiveIteratorIterator
{
public const BYPASS_CURRENT = 4;
public const BYPASS_KEY = 8;
public const PREFIX_LEFT = 0;
public const PREFIX_MID_HAS_NEXT = 1;
public const PREFIX_MID_LAST = 2;
public const PREFIX_END_HAS_NEXT = 3;
public const PREFIX_END_LAST = 4;
public const PREFIX_RIGHT = 5;









public function __construct(
$iterator,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = self::BYPASS_KEY,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $cachingIteratorFlags = CachingIterator::CATCH_GET_CHILD,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $mode = self::SELF_FIRST
) {}






public function rewind(): void {}






public function valid(): bool {}






#[TentativeType]
public function key(): mixed {}






#[TentativeType]
public function current(): mixed {}






public function next(): void {}






public function beginIteration() {}






public function endIteration() {}






public function callHasChildren() {}






public function callGetChildren() {}






public function beginChildren() {}






public function endChildren() {}






public function nextElement() {}






#[TentativeType]
public function getPrefix(): string {}




#[TentativeType]
public function setPostfix(#[PhpStormStubsElementAvailable(from: '7.3')] string $postfix): void {}












#[TentativeType]
public function setPrefixPart(
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $part,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $value
): void {}






#[TentativeType]
public function getEntry(): string {}






#[TentativeType]
public function getPostfix(): string {}
}





class ArrayObject implements IteratorAggregate, ArrayAccess, Serializable, Countable
{



public const STD_PROP_LIST = 1;




public const ARRAY_AS_PROPS = 2;








public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'object|array'], default: '')] $array = [],
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = 0,
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $iteratorClass = "ArrayIterator"
) {}









#[TentativeType]
public function offsetExists(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $key): bool {}









#[TentativeType]
public function offsetGet(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $key): mixed {}












#[TentativeType]
public function offsetSet(
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $key,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value
): void {}









#[TentativeType]
public function offsetUnset(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $key): void {}









#[TentativeType]
public function append(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value): void {}







#[TentativeType]
public function getArrayCopy(): array {}







#[TentativeType]
public function count(): int {}






#[TentativeType]
public function getFlags(): int {}




































#[TentativeType]
public function setFlags(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags): void {}







#[TentativeType]
public function asort(#[PhpStormStubsElementAvailable(from: '8.0')] int $flags = SORT_REGULAR): bool {}







#[TentativeType]
public function ksort(#[PhpStormStubsElementAvailable(from: '8.0')] int $flags = SORT_REGULAR): bool {}














#[TentativeType]
public function uasort(#[LanguageLevelTypeAware(['8.0' => 'callable'], default: '')] $callback): bool {}

















#[TentativeType]
public function uksort(#[LanguageLevelTypeAware(['8.0' => 'callable'], default: '')] $callback): bool {}






#[TentativeType]
public function natsort(): bool {}






#[TentativeType]
public function natcasesort(): bool {}









#[TentativeType]
public function unserialize(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data): void {}






#[TentativeType]
public function serialize(): string {}





#[TentativeType]
public function __debugInfo(): array {}





#[TentativeType]
public function __serialize(): array {}





#[TentativeType]
public function __unserialize(array $data): void {}






#[TentativeType]
public function getIterator(): Iterator {}









#[TentativeType]
public function exchangeArray(#[LanguageLevelTypeAware(['8.0' => 'object|array'], default: '')] $array): array {}









#[TentativeType]
public function setIteratorClass(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $iteratorClass): void {}






#[TentativeType]
public function getIteratorClass(): string {}
}






class ArrayIterator implements SeekableIterator, ArrayAccess, Serializable, Countable
{
public const STD_PROP_LIST = 1;
public const ARRAY_AS_PROPS = 2;








public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'object|array'], default: '')] $array = [],
#[PhpStormStubsElementAvailable(from: '7.0')] #[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags = 0,
#[PhpStormStubsElementAvailable(from: '7.0', to: '7.1')] $iterator_class = null
) {}









#[TentativeType]
public function offsetExists(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $key): bool {}









#[TentativeType]
public function offsetGet(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $key): mixed {}












#[TentativeType]
public function offsetSet(
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $key,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value
): void {}









#[TentativeType]
public function offsetUnset(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $key): void {}









#[TentativeType]
public function append(#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $value): void {}







#[TentativeType]
public function getArrayCopy(): array {}







#[TentativeType]
public function count(): int {}






#[TentativeType]
public function getFlags(): int {}












#[TentativeType]
public function setFlags(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $flags): void {}







#[TentativeType]
public function asort(#[PhpStormStubsElementAvailable(from: '8.0')] int $flags = SORT_REGULAR): bool {}







#[TentativeType]
public function ksort(#[PhpStormStubsElementAvailable(from: '8.0')] int $flags = SORT_REGULAR): bool {}









#[TentativeType]
public function uasort(#[LanguageLevelTypeAware(['8.0' => 'callable'], default: '')] $callback): bool {}









#[TentativeType]
public function uksort(#[LanguageLevelTypeAware(['8.0' => 'callable'], default: '')] $callback): bool {}






#[TentativeType]
public function natsort(): bool {}






#[TentativeType]
public function natcasesort(): bool {}









#[TentativeType]
public function unserialize(#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $data): void {}






#[TentativeType]
public function serialize(): string {}






#[TentativeType]
public function rewind(): void {}






#[TentativeType]
public function current(): mixed {}






#[TentativeType]
public function key(): string|int|null {}






#[TentativeType]
public function next(): void {}






#[TentativeType]
public function valid(): bool {}









#[TentativeType]
public function seek(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $offset): void {}





#[TentativeType]
public function __debugInfo(): array {}





#[TentativeType]
public function __serialize(): array {}





#[TentativeType]
public function __unserialize(array $data): void {}
}







class RecursiveArrayIterator extends ArrayIterator implements RecursiveIterator
{
public const CHILD_ARRAYS_ONLY = 4;







#[TentativeType]
public function hasChildren(): bool {}






#[TentativeType]
public function getChildren(): ?RecursiveArrayIterator {}
}
