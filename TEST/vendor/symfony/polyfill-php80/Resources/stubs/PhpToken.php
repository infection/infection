<?php

namespace _HumbugBox9658796bb9f0;

if (\PHP_VERSION_ID < 80000 && \extension_loaded('tokenizer')) {
    class PhpToken extends Symfony\Polyfill\Php80\PhpToken
    {
    }
}
