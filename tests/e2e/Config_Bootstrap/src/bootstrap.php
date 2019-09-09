<?php

if (count(get_defined_vars()) > 1) {
    throw new \Exception('Bootstrap file should be loaded in its separate scope, only $infectionBootstrapFile variable is allowed');
}

file_put_contents(__DIR__ . './../infection-file.txt', 'Hello World!');
