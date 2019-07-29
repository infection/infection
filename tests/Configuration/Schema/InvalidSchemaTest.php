<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration\Schema;

use Generator;
use Infection\Configuration\RawConfiguration\RawConfiguration;
use Infection\Configuration\Schema\InvalidSchema;
use PHPUnit\Framework\TestCase;

class InvalidSchemaTest extends TestCase
{
    /**
     * @dataProvider configWithErrorsProvider
     */
    public function test_it_can_be_instantiated(
        RawConfiguration $config,
        array $errors,
        string $expectedErrorMessage
    ): void
    {
        $exception = InvalidSchema::create($config, $errors);

        $this->assertSame($expectedErrorMessage, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function configWithErrorsProvider(): Generator
    {
        $path = '/path/to/config';

        yield 'no error' => [
            new RawConfiguration($path),
            [],
            '"/path/to/config" does not match the expected JSON schema.',
        ];

        yield 'pseudo empty error' => [
            new RawConfiguration($path),
            ['', ''],
            '"/path/to/config" does not match the expected JSON schema.',
        ];

        yield 'one error' => [
            new RawConfiguration($path),
            ['Error message'],
            <<<'ERROR'
"/path/to/config" does not match the expected JSON schema:
 - Error message
ERROR
            ,
        ];

        yield 'multiple errors' => [
            new RawConfiguration($path),
            [
                'First error message',
                'Second error message',
            ],
            <<<'ERROR'
"/path/to/config" does not match the expected JSON schema:
 - First error message
 - Second error message
ERROR
            ,
        ];

        yield 'worst case' => [
            new RawConfiguration($path),
            [
                ' First error message ',
                '',
                'Second error message'."\n",
            ],
            <<<'ERROR'
"/path/to/config" does not match the expected JSON schema:
 - First error message
 - Second error message
ERROR
            ,
        ];
    }
}
