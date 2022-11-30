<?php

namespace _HumbugBoxb47773b41c19\iter;

function makeRewindable(callable $function)
{
    return function (...$args) use($function) {
        return new rewindable\_RewindableGenerator($function, $args);
    };
}
function callRewindable(callable $function, ...$args)
{
    return new rewindable\_RewindableGenerator($function, $args);
}
namespace _HumbugBoxb47773b41c19\iter\rewindable;

use ReturnTypeWillChange;
function range()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\range', \func_get_args());
}
function map()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\map', \func_get_args());
}
function mapKeys()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\mapKeys', \func_get_args());
}
function mapWithKeys()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\mapWithKeys', \func_get_args());
}
function flatMap()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\flatMap', \func_get_args());
}
function reindex()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\reindex', \func_get_args());
}
function filter()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\filter', \func_get_args());
}
function enumerate()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\enumerate', \func_get_args());
}
function toPairs()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\toPairs', \func_get_args());
}
function fromPairs()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\fromPairs', \func_get_args());
}
function reductions()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\reductions', \func_get_args());
}
function zip()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\zip', \func_get_args());
}
function zipKeyValue()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\zipKeyValue', \func_get_args());
}
function chain()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\chain', \func_get_args());
}
function product()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\product', \func_get_args());
}
function slice()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\slice', \func_get_args());
}
function take()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\take', \func_get_args());
}
function drop()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\drop', \func_get_args());
}
function repeat()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\repeat', \func_get_args());
}
function takeWhile()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\takeWhile', \func_get_args());
}
function dropWhile()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\dropWhile', \func_get_args());
}
function keys()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\keys', \func_get_args());
}
function values()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\values', \func_get_args());
}
function flatten()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\flatten', \func_get_args());
}
function flip()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\flip', \func_get_args());
}
function chunk()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\chunk', \func_get_args());
}
function chunkWithKeys()
{
    return new _RewindableGenerator('_HumbugBoxb47773b41c19\\iter\\chunkWithKeys', \func_get_args());
}
class _RewindableGenerator implements \Iterator
{
    protected $function;
    protected $args;
    protected $generator;
    public function __construct(callable $function, array $args)
    {
        $this->function = $function;
        $this->args = $args;
        $this->generator = null;
    }
    public function rewind() : void
    {
        $function = $this->function;
        $this->generator = $function(...$this->args);
    }
    public function next() : void
    {
        if (!$this->generator) {
            $this->rewind();
        }
        $this->generator->next();
    }
    public function valid() : bool
    {
        if (!$this->generator) {
            $this->rewind();
        }
        return $this->generator->valid();
    }
    #[ReturnTypeWillChange]
    public function key()
    {
        if (!$this->generator) {
            $this->rewind();
        }
        return $this->generator->key();
    }
    #[ReturnTypeWillChange]
    public function current()
    {
        if (!$this->generator) {
            $this->rewind();
        }
        return $this->generator->current();
    }
    public function send($value = null)
    {
        if (!$this->generator) {
            $this->rewind();
        }
        return $this->generator->send($value);
    }
    public function throw($exception)
    {
        if (!$this->generator) {
            $this->rewind();
        }
        return $this->generator->throw($exception);
    }
}
