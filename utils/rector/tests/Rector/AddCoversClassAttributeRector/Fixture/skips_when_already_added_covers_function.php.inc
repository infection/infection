<?php

namespace Utils\Rector\Tests\Rector\AddCoversClassAttributeRector\Fixture;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversFunction;

#[CoversFunction('\trim')]
class CalculatorTest extends TestCase {}
class Calculator {}

?>
-----
<?php

namespace Utils\Rector\Tests\Rector\AddCoversClassAttributeRector\Fixture;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversFunction;

#[CoversFunction('\trim')]
class CalculatorTest extends TestCase {}
class Calculator {}

?>
