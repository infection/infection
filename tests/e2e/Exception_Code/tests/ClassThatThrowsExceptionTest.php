<?php

namespace ExceptionCode\Test;

use ExceptionCode\ClassThatThrowsException;
use PHPUnit\Framework\TestCase;

class ClassThatThrowsExceptionTest extends TestCase
{
    public function test_exception_code_is_correct()
    {
        $this->expectExceptionMessage('some message');
        $this->expectExceptionCode(1337);

        ClassThatThrowsException::someMethod();
    }
}
