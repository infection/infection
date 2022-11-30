<?php

namespace Parle;

use JetBrains\PhpStorm\Immutable;

class Stack
{




#[Immutable]
public $empty = true;




#[Immutable]
public $size = 0;




public $top;








public function pop(): void {}








public function push($item) {}
}
