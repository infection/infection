<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Pipeline;

use function array_filter;
use function array_map;
use function array_reduce;
use function array_shift;
use function array_slice;
use function array_values;
use ArrayIterator;
use function assert;
use CallbackFilterIterator;
use function count;
use Countable;
use EmptyIterator;
use Generator;
use function is_array;
use function is_iterable;
use function is_string;
use Iterator;
use function iterator_to_array;
use IteratorAggregate;
use Traversable;
class Standard implements IteratorAggregate, Countable
{
    private $pipeline;
    public function __construct(iterable $input = null)
    {
        while ($input instanceof IteratorAggregate) {
            $input = $input->getIterator();
        }
        $this->pipeline = $input;
    }
    /**
    @psalm-suppress
    */
    public function unpack(?callable $func = null) : self
    {
        $func = $func ?? static function (...$args) {
            yield from $args;
        };
        return $this->map(static function (iterable $args = []) use($func) {
            return $func(...$args);
        });
    }
    public function map(?callable $func = null) : self
    {
        if (null === $func) {
            return $this;
        }
        if (is_iterable($this->pipeline)) {
            /**
            @phan-suppress-next-line */
            $this->pipeline = self::apply($this->pipeline, $func);
            return $this;
        }
        $this->pipeline = $func();
        if ($this->pipeline instanceof Generator) {
            return $this;
        }
        $this->pipeline = [$this->pipeline];
        return $this;
    }
    private static function apply(iterable $previous, callable $func) : iterable
    {
        foreach ($previous as $key => $value) {
            $result = $func($value);
            if ($result instanceof Generator) {
                yield from $result;
                continue;
            }
            (yield $key => $result);
        }
    }
    public function cast(callable $func = null) : self
    {
        if (null === $func) {
            return $this;
        }
        if (is_array($this->pipeline)) {
            $this->pipeline = array_map($func, $this->pipeline);
            return $this;
        }
        if (is_iterable($this->pipeline)) {
            /**
            @phan-suppress-next-line */
            $this->pipeline = self::applyOnce($this->pipeline, $func);
            return $this;
        }
        $this->pipeline = [$func()];
        return $this;
    }
    private static function applyOnce(iterable $previous, callable $func) : iterable
    {
        foreach ($previous as $key => $value) {
            (yield $key => $func($value));
        }
    }
    public function filter(?callable $func = null) : self
    {
        if (null === $this->pipeline) {
            return $this;
        }
        if ([] === $this->pipeline) {
            return $this;
        }
        $func = self::resolvePredicate($func);
        if (is_array($this->pipeline)) {
            $this->pipeline = array_filter($this->pipeline, $func);
            return $this;
        }
        $iterator = $this->pipeline;
        /**
        @phan-suppress-next-line */
        $this->pipeline = new CallbackFilterIterator($iterator, $func);
        return $this;
    }
    private static function resolvePredicate(?callable $func) : callable
    {
        if (null === $func) {
            return static function ($value) {
                return $value;
            };
        }
        if (is_string($func)) {
            return static function ($value) use($func) {
                return $func($value);
            };
        }
        return $func;
    }
    public function reduce(?callable $func = null, $initial = null)
    {
        return $this->fold($initial ?? 0, $func);
    }
    public function fold($initial, ?callable $func = null)
    {
        $func = self::resolveReducer($func);
        if (is_array($this->pipeline)) {
            return array_reduce($this->pipeline, $func, $initial);
        }
        foreach ($this as $value) {
            $initial = $func($initial, $value);
        }
        return $initial;
    }
    private static function resolveReducer(?callable $func) : callable
    {
        if (null !== $func) {
            return $func;
        }
        return static function ($carry, $item) {
            $carry += $item;
            return $carry;
        };
    }
    public function getIterator() : Traversable
    {
        if ($this->pipeline instanceof Traversable) {
            return $this->pipeline;
        }
        if (null !== $this->pipeline) {
            return new ArrayIterator($this->pipeline);
        }
        return new EmptyIterator();
    }
    public function toArray(bool $useKeys = \false) : array
    {
        if (null === $this->pipeline) {
            return [];
        }
        if ([] === $this->pipeline) {
            return [];
        }
        if (is_array($this->pipeline)) {
            if ($useKeys) {
                return $this->pipeline;
            }
            return array_values($this->pipeline);
        }
        return iterator_to_array($this, $useKeys);
    }
    public function count() : int
    {
        if (null === $this->pipeline) {
            return 0;
        }
        if ([] === $this->pipeline) {
            return 0;
        }
        if (!is_array($this->pipeline)) {
            $this->pipeline = iterator_to_array($this, \false);
        }
        return count($this->pipeline);
    }
    public function slice(int $offset, ?int $length = null)
    {
        if (null === $this->pipeline) {
            return $this;
        }
        if (0 === $length) {
            $this->pipeline = null;
            return $this;
        }
        if (is_array($this->pipeline)) {
            $this->pipeline = array_slice($this->pipeline, $offset, $length, \true);
            return $this;
        }
        if ($offset < 0) {
            $this->pipeline = self::tail($this->pipeline, -$offset);
        }
        if ($offset > 0) {
            assert($this->pipeline instanceof Iterator);
            $this->pipeline = self::skip($this->pipeline, $offset);
        }
        if ($length < 0) {
            $this->pipeline = self::head($this->pipeline, -$length);
        }
        if ($length > 0) {
            $this->pipeline = self::take($this->pipeline, $length);
        }
        return $this;
    }
    /**
    @psalm-param
    */
    private static function skip(Iterator $input, int $skip) : iterable
    {
        foreach ($input as $_) {
            if (0 === $skip--) {
                break;
            }
        }
        if (!$input->valid()) {
            return;
        }
        yield from $input;
    }
    /**
    @psalm-param
    */
    private static function take(iterable $input, int $take) : iterable
    {
        foreach ($input as $key => $value) {
            (yield $key => $value);
            if (0 === --$take) {
                break;
            }
        }
    }
    private static function tail(iterable $input, int $length) : iterable
    {
        $buffer = [];
        foreach ($input as $key => $value) {
            if (count($buffer) < $length) {
                $buffer[] = [$key, $value];
                continue;
            }
            array_shift($buffer);
            $buffer[] = [$key, $value];
        }
        foreach ($buffer as list($key, $value)) {
            (yield $key => $value);
        }
    }
    private static function head(iterable $input, int $length) : iterable
    {
        $buffer = [];
        foreach ($input as $key => $value) {
            $buffer[] = [$key, $value];
            if (count($buffer) > $length) {
                [$key, $value] = array_shift($buffer);
                (yield $key => $value);
            }
        }
    }
    public function zip(iterable ...$inputs)
    {
        if (null === $this->pipeline) {
            $this->pipeline = array_shift($inputs);
        }
        if ([] === $inputs) {
            return $this;
        }
        $this->map(static function ($item) : array {
            return [$item];
        });
        foreach (self::toIterators(...$inputs) as $iterator) {
            $this->map(static function (array $current) use($iterator) {
                if (!$iterator->valid()) {
                    $current[] = null;
                    return $current;
                }
                $current[] = $iterator->current();
                $iterator->next();
                return $current;
            });
        }
        return $this;
    }
    private static function toIterators(iterable ...$inputs) : array
    {
        return array_map(static function (iterable $input) : Iterator {
            while ($input instanceof IteratorAggregate) {
                $input = $input->getIterator();
            }
            if ($input instanceof Iterator) {
                return $input;
            }
            return new ArrayIterator($input);
        }, $inputs);
    }
}
