<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Tests\Command;

use Infection\Command\InfectionCommand;
use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Console\OutputFormatter\ProgressFormatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfectionCommandTest extends TestCase
{
    /** @var InfectionCommand|\PHPUnit_Framework_MockObject_MockObject */
    private $testSubject = null;

    /** @var InputInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $mockInput = null;

    public function setUp()
    {
        parent::setUp();

        $this->testSubject = $this->getMockBuilder(InfectionCommand::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockInput = $this->getMockBuilder(InputInterface::class)
            ->setMethods(['getOption'])
            ->getMockForAbstractClass();

        $mockOuput = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();

        $reflectionProperty = new \ReflectionProperty(InfectionCommand::class, 'input');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->testSubject, $this->mockInput);

        $reflectionProperty = new \ReflectionProperty(InfectionCommand::class, 'output');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->testSubject, $mockOuput);
    }

    public function test_valid_get_output_formatter()
    {
        $reflectionMethod = new \ReflectionMethod(InfectionCommand::class, 'getOutputFormatter');
        $reflectionMethod->setAccessible(true);

        $this->mockInput->method('getOption')
            ->with('formatter')
            ->willReturnOnConsecutiveCalls(
                'progress',
                'dot',
                DotFormatter::class
            );

        $this->assertInstanceOf(ProgressFormatter::class, $reflectionMethod->invoke($this->testSubject));
        $this->assertInstanceOf(DotFormatter::class, $reflectionMethod->invoke($this->testSubject));
        $this->assertInstanceOf(DotFormatter::class, $reflectionMethod->invoke($this->testSubject));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Formatter must implement Infection\Console\OutputFormatter\OutputFormatter
     */
    public function test_class_output_formatter_throws_exception()
    {
        $reflectionMethod = new \ReflectionMethod(InfectionCommand::class, 'getOutputFormatter');
        $reflectionMethod->setAccessible(true);

        $this->mockInput->method('getOption')
                        ->with('formatter')
                        ->willReturn('\stdClass');

        $reflectionMethod->invoke($this->testSubject);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Incorrect formatter. Possible values: "dot", "progress", or an instance of Infection\Console\OutputFormatter\OutputFormatter
     */
    public function test_invalid_output_formatter_throws_exception()
    {
        $reflectionMethod = new \ReflectionMethod(InfectionCommand::class, 'getOutputFormatter');
        $reflectionMethod->setAccessible(true);

        $this->mockInput->method('getOption')
                        ->with('formatter')
                        ->willReturn('invalid');

        $reflectionMethod->invoke($this->testSubject);
    }
}
