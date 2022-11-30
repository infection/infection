<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Sync;

use _HumbugBoxb47773b41c19\Amp\Serialization\SerializationException as SerializerException;
\class_alias(SerializerException::class, SerializationException::class);
function flattenThrowableBacktrace(\Throwable $exception) : array
{
    $trace = $exception->getTrace();
    foreach ($trace as &$call) {
        unset($call['object']);
        $call['args'] = \array_map(__NAMESPACE__ . '\\flattenArgument', $call['args'] ?? []);
    }
    return $trace;
}
function formatFlattenedBacktrace(array $trace) : string
{
    $output = [];
    foreach ($trace as $index => $call) {
        if (isset($call['class'])) {
            $name = $call['class'] . $call['type'] . $call['function'];
        } else {
            $name = $call['function'];
        }
        $output[] = \sprintf('#%d %s(%d): %s(%s)', $index, $call['file'] ?? '[internal function]', $call['line'] ?? 0, $name, \implode(', ', $call['args'] ?? ['...']));
    }
    return \implode("\n", $output);
}
function flattenArgument($value) : string
{
    if ($value instanceof \Closure) {
        $closureReflection = new \ReflectionFunction($value);
        return \sprintf('Closure(%s:%s)', $closureReflection->getFileName(), $closureReflection->getStartLine());
    }
    if (\is_object($value)) {
        return \sprintf('Object(%s)', \get_class($value));
    }
    if (\is_array($value)) {
        return 'Array([' . \implode(', ', \array_map(__FUNCTION__, $value)) . '])';
    }
    if (\is_resource($value)) {
        return \sprintf('Resource(%s)', \get_resource_type($value));
    }
    if (\is_string($value)) {
        return '"' . $value . '"';
    }
    if (\is_null($value)) {
        return 'null';
    }
    if (\is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    return (string) $value;
}
