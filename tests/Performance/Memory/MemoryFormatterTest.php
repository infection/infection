<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Performance\Memory;

use Infection\Performance\Memory\MemoryFormatter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MemoryFormatterTest extends TestCase
{
    /**
     * @var MemoryFormatter
     */
    private $memoryFormatter;

    protected function setUp()
    {
        $this->memoryFormatter = new MemoryFormatter();
    }

    /**
     * @dataProvider bytesProvider
     *
     * @param float $bytes
     * @param string $expectedString
     */
    public function test_it_converts_bytes_to_string(float $bytes, string $expectedString)
    {
        $timeString = $this->memoryFormatter->toHumanReadableString($bytes);

        $this->assertSame($expectedString, $timeString);
    }

    public function bytesProvider()
    {
        yield [1048576, '1.00MB'];
        yield [1572864, '1.50MB'];
        yield [116737966, '111.33MB'];
    }
}
