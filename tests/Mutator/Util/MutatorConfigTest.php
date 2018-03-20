<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Util;

use Infection\Mutator\Util\MutatorConfig;
use PHPUnit\Framework\TestCase;

class MutatorConfigTest extends TestCase
{
    /**
     * @dataProvider providesIgnoredValues
     *
     * @param array $ignored
     * @param string $class
     * @param string $method
     */
    public function test_is_ignored_returns_true_if_there_is_a_match(array $ignored, string $class, string $method)
    {
        $config = new MutatorConfig(['ignore' => $ignored]);

        $this->assertTrue($config->isIgnored($class, $method));
    }

    public function providesIgnoredValues(): \Generator
    {
        yield 'It ignores a full class' => [
            ['Foo\Bar\Test'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It ignores a full class with method' => [
            ['Foo\Bar\Test::method'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It ignores a pattern of a class' => [
            ['Foo\*\Test'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It ignores a pattern of a class with method' => [
            ['Foo\*::method'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It ignores a pattern of a method' => [
            ['Foo\Bar\Test::m?th?d'],
            'Foo\Bar\Test',
            'method',
        ];
    }

    /**
     * @dataProvider providesNotIgnoredValues
     *
     * @param array $ignored
     * @param string $class
     * @param string $method
     */
    public function test_is_ignored_returns_false_if_there_is_no_match(array $ignored, string $class, string $method)
    {
        $config = new MutatorConfig(['ignore' => $ignored]);

        $this->assertFalse($config->isIgnored($class, $method));
    }

    public function providesNotIgnoredValues(): \Generator
    {
        yield 'It does not ignores a full class when the methods dont match' => [
            ['Foo\Bar\Test::otherMethod'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It does not ignore a class if casing doesnt match' => [
            ['FoO\BAr\tEst'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It does not ignore a pattern of a class if the method does not match' => [
            ['Foo\*\Test::other'],
            'Foo\Bar\Test',
            'method',
        ];

        yield 'It does ignores a pattern of a class with method if the class doesnt match' => [
            ['Foo\*::method'],
            'Bar\Foo\Test',
            'method',
        ];
    }
}
