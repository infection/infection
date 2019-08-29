<?php

namespace PhpUnit_Depends_Order\Test;

use PhpUnit_Depends_Order\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    private static $counter = 0;

    /**
     * If one of the tests with `@depends` annotation is reordered, and this reorder is caused by
     * incorrectly used `executionOrder="random"` flag (or `--order=random` option), then PHPUnit automatically
     * skips such test case.
     *
     * When one of the test is skipped, we get self::$counter < 4, which will trigger an error.
     *
     * Expected result: random order of these tests should respect `@depends` annotation and do not
     * produce skipped tests, thanks to `resolveDependencies="true"`flag (or `--resolve-dependencies` option)
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        if (self::$counter < 4) {
            throw new \Exception('One of the tests were skipped. Something wrong with the order of executed tests.');
        }
    }

    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());

        self::$counter++;
    }

    public function testEmpty()
    {
        $stack = [];
        $this->assertEmpty($stack);

        self::$counter++;

        return $stack;
    }

    /**
     * @depends testEmpty
     */
    public function testPush(array $stack)
    {
        array_push($stack, 'foo');
        $this->assertSame('foo', $stack[count($stack)-1]);
        $this->assertNotEmpty($stack);

        self::$counter++;

        return $stack;
    }

    /**
     * @depends testPush
     */
    public function testPop(array $stack)
    {
        $this->assertSame('foo', array_pop($stack));
        $this->assertEmpty($stack);

        self::$counter++;
    }
}
