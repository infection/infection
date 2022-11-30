<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\String;

class LazyString implements \Stringable, \JsonSerializable
{
    private $value;
    public static function fromCallable($callback, ...$arguments) : self
    {
        if (!\is_callable($callback) && !(\is_array($callback) && isset($callback[0]) && $callback[0] instanceof \Closure && 2 >= \count($callback))) {
            throw new \TypeError(\sprintf('Argument 1 passed to "%s()" must be a callable or a [Closure, method] lazy-callable, "%s" given.', __METHOD__, \get_debug_type($callback)));
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
    public static function fromStringable($value) : self
    {
        if (!self::isStringable($value)) {
            throw new \TypeError(\sprintf('Argument 1 passed to "%s()" must be a scalar or a stringable object, "%s" given.', __METHOD__, \get_debug_type($value)));
        }
        if (\is_object($value)) {
            return static::fromCallable([$value, '__toString']);
        }
        $lazyString = new static();
        $lazyString->value = (string) $value;
        return $lazyString;
    }
    public static final function isStringable($value) : bool
    {
        return \is_string($value) || $value instanceof self || (\is_object($value) ? \method_exists($value, '__toString') : \is_scalar($value));
    }
    public static final function resolve($value) : string
    {
        return $value;
    }
    public function __toString()
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
            if (\PHP_VERSION_ID < 70400) {
                return \trigger_error($e, \E_USER_ERROR);
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
            if (\false !== \strpos($r->name, '{closure}') || !($class = $r->getClosureScopeClass())) {
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
