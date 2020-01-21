<?php

declare(strict_types=1);

namespace Infection\Tests\Mutator\Extensions;

use Generator;
use Infection\Mutator\Boolean\TrueValueConfig;
use Infection\Mutator\Extensions\BCMathConfig;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class BCMathConfigTest extends TestCase
{
    /**
     * @dataProvider settingsProvider
     */
    public function test_it_can_create_a_config(array $settings, array $expected): void
    {
        $config = new BCMathConfig($settings);

        $this->assertSame($expected, $config->getAllowedFunctions());
    }

    public function test_its_settings_must_be_boolean_values(): void
    {
        try {
            new BCMathConfig(['foo' => 'bar']);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected the value for "foo" to be a boolean. Got "string" instead',
                $exception->getMessage()
            );
        }
    }

    public function test_it_must_be_a_known_function(): void
    {
        try {
            new BCMathConfig(['foo' => true]);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected one of: "bcadd", "bccomp", "bcdiv", "bcmod", "bcmul", "bcpow", "bcsub", "bcsqrt", "bcpowmod". Got: "foo"',
                $exception->getMessage()
            );
        }
    }

    public function settingsProvider(): Generator
    {
        yield 'default' => [
            [],
            [
                'bcadd',
                'bccomp',
                'bcdiv',
                'bcmod',
                'bcmul',
                'bcpow',
                'bcsub',
                'bcsqrt',
                'bcpowmod',
            ],
        ];

        yield 'one function enabled' => [
            ['bcadd' => true],
            [
                'bcadd',
                'bccomp',
                'bcdiv',
                'bcmod',
                'bcmul',
                'bcpow',
                'bcsub',
                'bcsqrt',
                'bcpowmod',
            ],
        ];

        yield 'one function disabled' => [
            ['bcadd' => false],
            [
                'bccomp',
                'bcdiv',
                'bcmod',
                'bcmul',
                'bcpow',
                'bcsub',
                'bcsqrt',
                'bcpowmod',
            ],
        ];
    }
}
