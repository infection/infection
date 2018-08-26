<?php

namespace Namespace_\Test;

use Namespace_\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $outHandle = fopen('php://stderr', 'w');
        fwrite($outHandle, 'Start Of Error');

        sleep(5);

        fwrite($outHandle, 'End Of Error');
        fclose($outHandle);

        $this->assertFalse(true);
    }
}
