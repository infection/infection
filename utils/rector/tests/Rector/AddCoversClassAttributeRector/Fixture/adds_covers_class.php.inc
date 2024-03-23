<?php

namespace Utils\Rector\Tests\Rector\AddCoversClassAttributeRector\Fixture;

use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase {}
class Calculator {}

?>
-----
<?php

namespace Utils\Rector\Tests\Rector\AddCoversClassAttributeRector\Fixture;

use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Utils\Rector\Tests\Rector\AddCoversClassAttributeRector\Fixture\Calculator::class)]
class CalculatorTest extends TestCase {}
class Calculator {}

?>
