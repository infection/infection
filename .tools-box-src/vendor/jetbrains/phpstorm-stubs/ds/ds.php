<?php









namespace Ds;

use Countable;
use IteratorAggregate;
use JsonSerializable;
use OutOfBoundsException;
use OutOfRangeException;
use Traversable;
use UnderflowException;








interface Collection extends Countable, IteratorAggregate, JsonSerializable
{




public function clear(): void;






public function copy();






public function isEmpty(): bool;








public function toArray(): array;
}


















interface Hashable
{


















public function equals($obj): bool;


























public function hash();
}


















interface Sequence extends Collection
{









public function allocate(int $capacity): void;










public function apply(callable $callback): void;






public function capacity(): int;








public function contains(...$values): bool;














public function filter(?callable $callback = null);







public function find($value);







public function first();








public function get(int $index);










public function insert(int $index, ...$values): void;









public function join(string $glue = ''): string;







public function last();














public function map(callable $callback);









public function merge($values);







public function pop();





public function push(...$values): void;














public function reduce(callable $callback, $initial = null);







public function remove(int $index);





public function reverse(): void;






public function reversed();










public function rotate(int $rotations): void;








public function set(int $index, $value): void;







public function shift();
















public function slice(int $index, int $length = null);
















public function sort(?callable $comparator = null): void;

















public function sorted(?callable $comparator = null);








public function sum(): float|int;








public function unshift($values): void;
}


























class Vector implements Sequence
{
public const MIN_CAPACITY = 10;






public function __construct($values = []) {}










public function allocate(int $capacity): void {}










public function apply(callable $callback): void {}






public function capacity(): int {}





public function clear(): void {}








public function contains(...$values): bool {}





public function copy(): Vector {}















public function filter(?callable $callback = null): Vector {}








public function find($value) {}







public function first() {}







public function get(int $index) {}

public function getIterator(): Traversable {}










public function insert(int $index, ...$values): void {}








public function join(?string $glue = null): string {}







public function last() {}










public function map(callable $callback): Vector {}











public function merge($values): Vector {}







public function pop() {}






public function push(...$values): void {}













public function reduce(callable $callback, $initial = null) {}







public function remove(int $index) {}





public function reverse(): void {}







public function reversed(): Vector {}











public function rotate(int $rotations): void {}











public function set(int $index, $value): void {}









public function shift() {}

















public function slice(int $index, int $length = null): Vector {}
















public function sort(?callable $comparator = null): void {}














public function sorted(?callable $comparator = null): Vector {}








public function sum(): float {}









public function unshift($values): void {}










public function count(): int {}






public function isEmpty(): bool {}








public function toArray(): array {}








public function jsonSerialize() {}
}

class Deque implements Sequence
{






public function __construct(...$values) {}










public function count(): int {}





public function clear(): void {}






public function copy(): Collection {}

public function getIterator(): Traversable {}






public function isEmpty(): bool {}








public function toArray(): array {}











public function allocate(int $capacity): void {}











public function apply(callable $callback): void {}






public function capacity(): int {}








public function contains(...$values): bool {}















public function filter(?callable $callback = null): Deque {}







public function find($value) {}







public function first() {}








public function get(int $index) {}










public function insert(int $index, ...$values): void {}









public function join(string $glue = ''): string {}







public function last() {}

















public function map(callable $callback): Deque {}









public function merge($values): Deque {}







public function pop() {}





public function push(...$values): void {}













public function reduce(callable $callback, $initial = null) {}







public function remove(int $index) {}





public function reverse(): void {}






public function reversed(): Deque {}










public function rotate(int $rotations): void {}








public function set(int $index, $value): void {}







public function shift() {}
















public function slice(int $index, int $length = null): Deque {}
















public function sort(?callable $comparator = null): void {}

















public function sorted(?callable $comparator = null): Deque {}








public function sum(): float|int {}








public function unshift($values): void {}








public function jsonSerialize() {}
}

class Map implements Collection
{






public function __construct(...$values) {}










public function allocate(int $capacity) {}









public function apply(callable $callback) {}








public function capacity(): int {}










public function count(): int {}





public function clear(): void {}






public function copy(): Collection {}












public function diff(Map $map): Map {}











public function filter(?callable $callback = null): Map {}










public function first(): Pair {}
























public function get($key, $default = null) {}

public function getIterator(): Traversable {}










public function hasKey($key): bool {}










public function hasValue($value): bool {}

















public function intersect(Map $map): Map {}










public function isEmpty(): bool {}
















public function toArray(): array {}








public function jsonSerialize() {}






public function keys(): Set {}
















public function ksort(?callable $comparator = null) {}

















public function ksorted(?callable $comparator = null): Map {}







public function last(): Pair {}















public function map(callable $callback): Map {}














public function merge($values): Map {}








public function pairs(): Sequence {}






















public function put($key, $value) {}














public function putAll($pairs) {}
















public function reduce(callable $callback, $initial) {}































public function remove($key, $default = null) {}






public function reverse() {}










public function reversed(): Map {}












public function skip(int $position): Pair {}





















public function slice(int $index, ?int $length = null): Map {}





















public function sort(?callable $comparator = null) {}






















public function sorted(?callable $comparator = null): Map {}












public function sum(): float|int {}

















public function union(Map $map): Map {}









public function values(): Sequence {}














public function xor(Map $map): Map {}
}





class Pair implements JsonSerializable
{



public $key;




public $value;









public function __construct($key = null, $value = null) {}






public function clear() {}








public function copy(): Pair {}








public function isEmpty(): bool {}











public function toArray(): array {}







public function jsonSerialize() {}
}










class Set implements Collection
{









public function __construct(iterable $values = []) {}















public function add(...$values) {}














public function allocate(int $capacity) {}

















public function contains(...$values): bool {}







public function capacity(): int {}





public function clear(): void {}










public function count(): int {}






public function copy(): Set {}













public function diff(Set $set): Set {}
















public function filter(?callable $callback = null): Set {}








public function first() {}










public function get(int $index) {}

public function getIterator(): Traversable {}














public function intersect(Set $set): Set {}







public function isEmpty(): bool {}











public function join(?string $glue = null): string {}













public function map(callable $callback): Set {}














public function merge($values): Set {}

















public function reduce(callable $callback, $initial = null) {}









public function remove(...$values) {}






public function reverse() {}










public function reversed(): Set {}


















public function slice(int $index, ?int $length = null): Set {}










public function last() {}


















public function sort(?callable $comparator = null) {}






















public function sorted(?callable $comparator = null): Set {}












public function sum(): float|int {}














public function union(Set $set): Set {}














public function xor(Set $set): Set {}








public function toArray(): array {}








public function jsonSerialize() {}
}










class Stack implements Collection
{









public function __construct($values = []) {}













public function allocate(int $capacity) {}








public function capacity(): int {}





public function clear(): void {}










public function count(): int {}






public function copy(): Stack {}

public function getIterator(): Traversable {}






public function isEmpty(): bool {}








public function toArray(): array {}








public function jsonSerialize() {}










public function peek() {}










public function pop() {}








public function push(...$values) {}
}










class Queue implements Collection
{









public function __construct($values = []) {}













public function allocate(int $capacity) {}








public function capacity(): int {}





public function clear(): void {}










public function count(): int {}






public function copy(): Stack {}

public function getIterator(): Traversable {}






public function isEmpty(): bool {}








public function toArray(): array {}








public function jsonSerialize() {}










public function peek() {}










public function pop() {}








public function push(...$values) {}
}












class PriorityQueue implements Collection
{
public const MIN_CAPACITY = 8;










public function count(): int {}





public function clear(): void {}






public function copy() {}

public function getIterator(): Traversable {}






public function isEmpty(): bool {}








public function peek() {}







public function push($value, int $priority) {}








public function toArray(): array {}








public function jsonSerialize() {}
}
