<?php

namespace HumbugBox420\KevinGH\RequirementChecker;

require __DIR__ . '/../vendor/autoload.php';
if (\false === \in_array(\PHP_SAPI, array('cli', 'phpdbg', 'embed'), \true)) {
    echo \PHP_EOL . 'The application may only be invoked from a command line, got "' . \PHP_SAPI . '"' . \PHP_EOL;
    exit(1);
}
if ((\false === isset($_SERVER['BOX_REQUIREMENT_CHECKER']) || \true === (bool) $_SERVER['BOX_REQUIREMENT_CHECKER']) && \false === Checker::checkRequirements()) {
    exit(1);
}
