<?php

declare(strict_types=1);

namespace Infection\Tests\Mutator\Removal;

use Generator;
use Infection\Mutator\Boolean\TrueValueConfig;
use Infection\Mutator\Removal\ArrayItemRemovalConfig;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use const PHP_INT_MAX;

final class ArrayItemRemovalConfigTest extends TestCase
{
    /**
     * @dataProvider settingsProvider
     */
    public function test_it_can_create_a_config(
        array $settings,
        string $expectedRemove,
        int $expectedLimit
    ): void
    {
        $config = new ArrayItemRemovalConfig($settings);

        $this->assertSame($expectedRemove, $config->getRemove());
        $this->assertSame($expectedLimit, $config->getLimit());
    }

    public function test_the_remove_value_must_be_a_known_value(): void
    {
        try {
            new ArrayItemRemovalConfig(['remove' => 'unknown']);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected one of: "first", "last", "all". Got: "unknown"',
                $exception->getMessage()
            );
        }
    }

    public function test_the_limit_must_be_an_integer(): void
    {
        try {
            new ArrayItemRemovalConfig(['limit' => 'foo']);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected the limit to be an integer. Got "string" instead',
                $exception->getMessage()
            );
        }
    }

    public function test_the_limit_must_be_equal_or_greater_than_1(): void
    {
        try {
            new ArrayItemRemovalConfig(['limit' => 0]);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected the limit to be greater or equal than 1. Got "0" instead',
                $exception->getMessage()
            );
        }
    }

    public function settingsProvider(): Generator
    {
        yield 'default' => [
            [],
            'first',
            PHP_INT_MAX,
        ];

        yield 'setting the remove at the default value' => [
            ['remove' => 'first'],
            'first',
            PHP_INT_MAX,
        ];

        yield 'setting the remove at a different value' => [
            ['remove' => 'last'],
            'last',
            PHP_INT_MAX,
        ];

        yield 'setting the limit at the default value' => [
            ['limit' => PHP_INT_MAX],
            'first',
            PHP_INT_MAX,
        ];

        yield 'setting the limit at a different value' => [
            ['limit' => 1],
            'first',
            1,
        ];

        yield 'setting both values' => [
            [
                'remove' => 'last',
                'limit' => 1,
            ],
            'last',
            1,
        ];
    }
}
