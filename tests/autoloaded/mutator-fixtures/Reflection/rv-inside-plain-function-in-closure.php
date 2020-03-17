<?php

namespace InfectionReflectionPlainFunctionInClosure;

$a = function (int $b) {
    function bar() {
        count([]);
    }
};
