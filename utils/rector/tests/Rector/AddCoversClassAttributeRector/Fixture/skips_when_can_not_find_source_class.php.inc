<?php

namespace Utils\Rector\Tests\Rector\AddCoversClassAttributeRector\Fixture\Abc {
    use PHPUnit\Framework\TestCase;

    class CalculatorTest extends TestCase {}
}

namespace Utils\Rector\Tests\Rector\AddCoversClassAttributeRector\Fixture\Xyz {
    class Calculator {}
}

?>
-----
<?php

namespace Utils\Rector\Tests\Rector\AddCoversClassAttributeRector\Fixture\Abc {
    use PHPUnit\Framework\TestCase;

    class CalculatorTest extends TestCase {}
}

namespace Utils\Rector\Tests\Rector\AddCoversClassAttributeRector\Fixture\Xyz {
    class Calculator {}
}

?>
