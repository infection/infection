<?php

declare(strict_types=1);

namespace Infection\Tests\Mutator\Extensions;

use Generator;
use Infection\Mutator\Extensions\MBStringConfig;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MBStringConfigTest extends TestCase
{
    /**
     * @dataProvider settingsProvider
     */
    public function test_it_can_create_a_config(array $settings, array $expected): void
    {
        $config = new MBStringConfig($settings);

        $this->assertSame($expected, $config->getAllowedFunctions());
    }

    public function test_its_settings_must_be_boolean_values(): void
    {
        try {
            new MBStringConfig(['foo' => 'bar']);

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
            new MBStringConfig(['foo' => true]);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected one of: "mb_chr", "mb_ord", "mb_parse_str", "mb_send_mail", "mb_strcut", "mb_stripos", "mb_stristr", "mb_strlen", "mb_strpos", "mb_strrchr", "mb_strripos", "mb_strrpos", "mb_strstr", "mb_strtolower", "mb_strtoupper", "mb_substr_count", "mb_substr", "mb_convert_case". Got: "foo"',
                $exception->getMessage()
            );
        }
    }

    public function settingsProvider(): Generator
    {
        yield 'default' => [
            [],
            [
                'mb_chr',
                'mb_ord',
                'mb_parse_str',
                'mb_send_mail',
                'mb_strcut',
                'mb_stripos',
                'mb_stristr',
                'mb_strlen',
                'mb_strpos',
                'mb_strrchr',
                'mb_strripos',
                'mb_strrpos',
                'mb_strstr',
                'mb_strtolower',
                'mb_strtoupper',
                'mb_substr_count',
                'mb_substr',
                'mb_convert_case',
            ],
        ];

        yield 'one function enabled' => [
            ['mb_chr' => true],
            [
                'mb_chr',
                'mb_ord',
                'mb_parse_str',
                'mb_send_mail',
                'mb_strcut',
                'mb_stripos',
                'mb_stristr',
                'mb_strlen',
                'mb_strpos',
                'mb_strrchr',
                'mb_strripos',
                'mb_strrpos',
                'mb_strstr',
                'mb_strtolower',
                'mb_strtoupper',
                'mb_substr_count',
                'mb_substr',
                'mb_convert_case',
            ],
        ];

        yield 'one function disabled' => [
            ['mb_chr' => false],
            [
                'mb_ord',
                'mb_parse_str',
                'mb_send_mail',
                'mb_strcut',
                'mb_stripos',
                'mb_stristr',
                'mb_strlen',
                'mb_strpos',
                'mb_strrchr',
                'mb_strripos',
                'mb_strrpos',
                'mb_strstr',
                'mb_strtolower',
                'mb_strtoupper',
                'mb_substr_count',
                'mb_substr',
                'mb_convert_case',
            ],
        ];
    }
}
