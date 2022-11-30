<?php

namespace _HumbugBoxb47773b41c19;

if (!\function_exists('_HumbugBoxb47773b41c19\\trigger_deprecation')) {
    function trigger_deprecation(string $package, string $version, string $message, mixed ...$args) : void
    {
        @\trigger_error(($package || $version ? "Since {$package} {$version}: " : '') . ($args ? \vsprintf($message, $args) : $message), \E_USER_DEPRECATED);
    }
}
