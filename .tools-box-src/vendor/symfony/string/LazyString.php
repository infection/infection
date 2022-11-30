<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\String;

class LazyString implements \Stringable, \JsonSerializable
{
    private \Closure|string $value;
    public static function fromCallable(callable|array $callback, mixed ...$arguments) : static
    {
        if (\is_array($callback) && !\is_callable($callback) && !(($callback[0] ?? null) instanceof \Closure || 2 < \count($callback))) {
            throw new \TypeError(\sprintf('Argument 1 passed to "%s()" must be a callable or a [Closure, method] lazy-callable, "%s" given.', __METHOD__, '[' . \implode(', ', \array_map('get_debug_type', $callback)) . ']'));
        }
        $lazyString = new static();
        $lazyString->value = static function () use(&$callback, &$arguments, &$value) : string {
            if (null !== $arguments) {
                if (!\is_callable($callback)) {
                    $callback[0] = $callback[0]();
                    $callback[1] = $callback[1] ?? '__invoke';
                }
                $value = $callback(...$arguments);
                $callback = self::getPrettyName($callback);
                $arguments = null;
            }
            return $value ?? '';
        };
        return $lazyString;
    }
    public static function fromStringable(string|int|float|bool|\Stringable $value) : static
    {
        if (\is_object($value)) {
            return static::fromCallable([$value, '__toString']);
        }
        $lazyString = new static();
        $lazyString->value = (string) $value;
        return $lazyString;
    }
    public static final function isStringable(mixed $value) : bool
    {
        return \is_string($value) || $value instanceof \Stringable || \is_scalar($value);
    }
    public static final function resolve(\Stringable|string|int|float|bool $value) : string
    {
        return $value;
    }
    public function __toString() : string
    {
        if (\is_string($this->value)) {
            return $this->value;
        }
        try {
            return $this->value = ($this->value)();
        } catch (\Throwable $e) {
            if (\TypeError::class === \get_class($e) && __FILE__ === $e->getFile()) {
                $type = \explode(', ', $e->getMessage());
                $type = \substr(\array_pop($type), 0, -\strlen(' returned'));
                $r = new \ReflectionFunction($this->value);
                $callback = $r->getStaticVariables()['callback'];
                $e = new \TypeError(\sprintf('Return value of %s() passed to %s::fromCallable() must be of the type string, %s returned.', $callback, static::class, $type));
            }
            throw $e;
        }
    }
    public function __sleep() : array
    {
        $this->__toString();
        return ['value'];
    }
    public function jsonSerialize() : string
    {
        return $this->__toString();
    }
    private function __construct()
    {
    }
    private static function getPrettyName(callable $callback) : string
    {
        if (\is_string($callback)) {
            return $callback;
        }
        if (\is_array($callback)) {
            $class = \is_object($callback[0]) ? \get_debug_type($callback[0]) : $callback[0];
            $method = $callback[1];
        } elseif ($callback instanceof \Closure) {
            $r = new \ReflectionFunction($callback);
            if (\str_contains($r->name, '{closure}') || !($class = $r->getClosureScopeClass())) {
                return $r->name;
            }
            $class = $class->name;
            $method = $r->name;
        } else {
            $class = \get_debug_type($callback);
            $method = '__invoke';
        }
        return $class . '::' . $method;
    }
}
