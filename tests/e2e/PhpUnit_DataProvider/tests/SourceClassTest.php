<?php

namespace PhpUnit_DataProvider\Test;

use PhpUnit_DataProvider\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    /**
     * @dataProvider instancesProvider
     */
    public function testAlwaysTrueFromFactoryMethod(SourceClass $class)
    {
        // $class = SourceClass::factoryMethod();
        self::assertTrue($class->getValue());
    }

    public function instancesProvider(): array
    {
        return [
            [SourceClass::factoryMethod()],
        ];
    }
}
