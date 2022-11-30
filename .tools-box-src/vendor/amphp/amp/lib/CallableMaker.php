<?php

namespace _HumbugBoxb47773b41c19\Amp;

if (\PHP_VERSION_ID < 70100) {
    /**
    @psalm-suppress */
    trait CallableMaker
    {
        private static $__reflectionClass;
        private static $__reflectionMethods = [];
        /**
        @psalm-suppress
        */
        private function callableFromInstanceMethod(string $method) : callable
        {
            if (!isset(self::$__reflectionMethods[$method])) {
                if (self::$__reflectionClass === null) {
                    self::$__reflectionClass = new \ReflectionClass(self::class);
                }
                self::$__reflectionMethods[$method] = self::$__reflectionClass->getMethod($method);
            }
            return self::$__reflectionMethods[$method]->getClosure($this);
        }
        /**
        @psalm-suppress
        */
        private static function callableFromStaticMethod(string $method) : callable
        {
            if (!isset(self::$__reflectionMethods[$method])) {
                if (self::$__reflectionClass === null) {
                    self::$__reflectionClass = new \ReflectionClass(self::class);
                }
                self::$__reflectionMethods[$method] = self::$__reflectionClass->getMethod($method);
            }
            return self::$__reflectionMethods[$method]->getClosure();
        }
    }
} else {
    /**
    @psalm-suppress */
    trait CallableMaker
    {
        private function callableFromInstanceMethod(string $method) : callable
        {
            return \Closure::fromCallable([$this, $method]);
        }
        private static function callableFromStaticMethod(string $method) : callable
        {
            return \Closure::fromCallable([self::class, $method]);
        }
    }
}
