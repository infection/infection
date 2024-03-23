<?php

namespace Utils\Rector\Tests\Rector\AddCoversClassAttributeRector\Fixture;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class CalculatorTest extends TestCase {}
class Calculator {}

?>
-----
<?php

namespace Utils\Rector\Tests\Rector\AddCoversClassAttributeRector\Fixture;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class CalculatorTest extends TestCase {}
class Calculator {}

?>
