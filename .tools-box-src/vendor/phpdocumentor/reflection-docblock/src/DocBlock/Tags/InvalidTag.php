<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags;

use Closure;
use Exception;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tag;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use Throwable;
use function array_map;
use function get_class;
use function get_resource_type;
use function is_array;
use function is_object;
use function is_resource;
use function sprintf;
final class InvalidTag implements Tag
{
    private $name;
    private $body;
    private $throwable;
    private function __construct(string $name, string $body)
    {
        $this->name = $name;
        $this->body = $body;
    }
    public function getException() : ?Throwable
    {
        return $this->throwable;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public static function create(string $body, string $name = '') : self
    {
        return new self($name, $body);
    }
    public function withError(Throwable $exception) : self
    {
        $this->flattenExceptionBacktrace($exception);
        $tag = new self($this->name, $this->body);
        $tag->throwable = $exception;
        return $tag;
    }
    private function flattenExceptionBacktrace(Throwable $exception) : void
    {
        $traceProperty = (new ReflectionClass(Exception::class))->getProperty('trace');
        $traceProperty->setAccessible(\true);
        do {
            $trace = $exception->getTrace();
            if (isset($trace[0]['args'])) {
                $trace = array_map(function (array $call) : array {
                    $call['args'] = array_map([$this, 'flattenArguments'], $call['args'] ?? []);
                    return $call;
                }, $trace);
            }
            $traceProperty->setValue($exception, $trace);
            $exception = $exception->getPrevious();
        } while ($exception !== null);
        $traceProperty->setAccessible(\false);
    }
    private function flattenArguments($value)
    {
        if ($value instanceof Closure) {
            $closureReflection = new ReflectionFunction($value);
            $value = sprintf('(Closure at %s:%s)', $closureReflection->getFileName(), $closureReflection->getStartLine());
        } elseif (is_object($value)) {
            $value = sprintf('object(%s)', get_class($value));
        } elseif (is_resource($value)) {
            $value = sprintf('resource(%s)', get_resource_type($value));
        } elseif (is_array($value)) {
            $value = array_map([$this, 'flattenArguments'], $value);
        }
        return $value;
    }
    public function render(?Formatter $formatter = null) : string
    {
        if ($formatter === null) {
            $formatter = new Formatter\PassthroughFormatter();
        }
        return $formatter->format($this);
    }
    public function __toString() : string
    {
        return $this->body;
    }
}
