<?php

namespace Infection\Tests\Mutant\Exception;

use Infection\Mutant\Exception\MsiCalculationException;

class MsiCalculationExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function test_it_is_instance_of_logic_exception()
    {
        $exception = MsiCalculationException::create('');

        $this->assertInstanceOf(
            \LogicException::class,
            $exception
        );
    }

    public function test_it_has_correct_error_message()
    {
        $exception = MsiCalculationException::create('min-msi');
        $this->assertSame(
            'Seems like something is wrong with calculations and min-msi options.',
            $exception->getMessage(),
            'The error message was incorrectly parsed.'
        );
    }
}
