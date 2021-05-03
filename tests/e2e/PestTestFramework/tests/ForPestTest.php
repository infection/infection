<?php

use PestTestFramework\ForPest;

test('Source Class from SourceTest.php', function () {
    $sourceClass = new ForPest();

    expect($sourceClass->hello())->toBe('hello');
});

test('Another test from SourceTest.php', function () {
    $sourceClass = new ForPest();

    expect($sourceClass->hello())->toBe('hello');
});

test('Third test from SourceTest.php', function () {
    $sourceClass = new ForPest();

    expect($sourceClass->add(5, 3))->toBeGreaterThan(0);
});
