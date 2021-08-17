<?php

use Syntax_Error_Pest\Test;

use Syntax_Error_Pest\ForPest;

test('Syntax Error for Pest', function () {
    $sourceClass = new ForPest();

    expect($sourceClass->hello())->toBe('hello');
});
