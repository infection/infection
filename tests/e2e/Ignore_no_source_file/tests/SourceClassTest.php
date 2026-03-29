<?php

namespace Infection\E2ETests\Ignore_no_source_file\Tests;

use Exception;
use Infection\E2ETests\Ignore_no_source_file\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        throw new Exception('Executing the tests should fail!');
    }
}
