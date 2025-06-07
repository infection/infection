<?php

namespace IdenticalEqualNonNumericNonEmptyClassConst;

class Foo {
    private const BAR = 'baz';
    public function doFoo($x) {
        return $x === self::BAR;
    }
}
