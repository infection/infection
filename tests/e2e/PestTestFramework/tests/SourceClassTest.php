<?php

use PestTestFramework\SourceClass;

test('Source Class from SourceTest.php', function () {
    $sourceClass = new SourceClass();

    expect($sourceClass->hello())->toBe('hello');
});

test('Another test from SourceTest.php', function () {
    $sourceClass = new SourceClass();

    expect($sourceClass->hello())->toBe('hello');
});

test('Third test from SourceTest.php', function () {
    $sourceClass = new SourceClass();

    expect($sourceClass->add(5, 3))->toBeGreaterThan(0);
});
