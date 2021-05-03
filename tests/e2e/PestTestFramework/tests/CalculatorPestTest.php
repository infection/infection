<?php

use PestTestFramework\Calculator;

test('it can subtract one number from another', function () {
    $sourceClass = new Calculator();

    expect($sourceClass->sub(3, 1))->toBe(2);
});

