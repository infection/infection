<?php

namespace _HumbugBox9658796bb9f0;

if (!\function_exists('_HumbugBox9658796bb9f0\\trigger_deprecation')) {
    function trigger_deprecation(string $package, string $version, string $message, ...$args) : void
    {
        @\trigger_error(($package || $version ? "Since {$package} {$version}: " : '') . ($args ? \vsprintf($message, $args) : $message), \E_USER_DEPRECATED);
    }
}
