<?php

use PestTestFramework\ForPestWithDataProvider;

test('tests division with inline dataset', function (float $a, float $b, float $expectedResult) {
    $sourceClass = new ForPestWithDataProvider();

    expect($sourceClass->div($a, $b))->toBe($expectedResult);
})->with([
    [2.0, 4.0, 0.5]
]);

test('tests division with shared dataset', function (float $a, float $b, float $expectedResult) {
    $sourceClass = new ForPestWithDataProvider();

    expect($sourceClass->div($a, $b))->toBe($expectedResult);
})->with('floats');
