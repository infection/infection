<?php

namespace ParamCoverage\Test;

use ParamCoverage\ArraySearcher;
use PHPUnit\Framework\TestCase;

class ArraySearcherTest extends TestCase
{
    public function testSearchDefaultsToNonStrictSearch()
    {
        $array = ['0', '1', '2', '3'];
        $meta = new ArraySearcher($array);
        $key = $meta->search(3);
        $this->assertSame(3, $key);
    }
}
