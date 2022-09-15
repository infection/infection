<?php

namespace Stream_Wrapper_Execution\Test;

use Stream_Wrapper_Execution\FinalClass;
use Stream_Wrapper_Execution\SourceClass;
use PHPUnit\Framework\TestCase;
use DG\BypassFinals;

class SourceClassTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        BypassFinals::enable();
    }


    public function test_returns_one()
    {
        $sourceClass = new SourceClass(new FinalClass());

        $this->assertSame(1, $sourceClass->getOne());
    }

    public function test_final_class_can_be_mocked()
    {
        $mock = $this->createMock(FinalClass::class);

        $mock->expects(self::once())->method('get')->willReturn(1);

        $sourceClass = new SourceClass($mock);

        self::assertSame(1, $sourceClass->getOne());
    }
}
