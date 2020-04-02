<?php

namespace Namespace_\Test;

use Namespace_\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $stderrHandle = fopen('php://stderr', 'w');
        fwrite($stderrHandle, 'Start Of Error');

        sleep(5);

        fwrite($stderrHandle, 'End Of Error');
        fclose($stderrHandle);

        $this->assertFalse(true);
    }
}
