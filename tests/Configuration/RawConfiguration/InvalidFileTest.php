<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration\RawConfiguration;

use Error;
use Generator;
use Infection\Configuration\RawConfiguration\InvalidFile;
use Infection\Configuration\RawConfiguration\RawConfiguration;
use PHPUnit\Framework\TestCase;
use Throwable;

class InvalidFileTest extends TestCase
{
    public function test_it_can_be_created_for_file_not_found(): void
    {
        $config = new RawConfiguration('/path/to/config');

        $exception = InvalidFile::createForFileNotFound($config);

        $this->assertSame(
            'The file "/path/to/config" could not be found or is not a file.',
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function test_it_can_be_created_for_file_not_readable(): void
    {
        $config = new RawConfiguration('/path/to/config');

        $exception = InvalidFile::createForFileNotReadable($config);

        $this->assertSame(
            'The file "/path/to/config" is not readable.',
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function test_it_can_be_created_for_file_content_could_not_be_retrieved(): void
    {
        $config = new RawConfiguration('/path/to/config');

        $exception = InvalidFile::createForCouldNotRetrieveFileContents($config);

        $this->assertSame(
            'Could not retrieve the contents of the file "/path/to/config".',
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * @dataProvider jsonErrorProvider
     */
    public function test_it_can_be_created_for_file_with_invalid_JSON_content(
        RawConfiguration $config,
        string $error,
        Throwable $previous,
        string $expectedErrorMessage
    ): void
    {
        $exception = InvalidFile::createForInvalidJson($config, $error, $previous);

        $this->assertSame($expectedErrorMessage, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function jsonErrorProvider(): Generator
    {
        yield [
            new RawConfiguration('/path/to/config'),
            'Error message',
            new Error(),
            'Could not parse the JSON file "/path/to/config": Error message',
        ];

        yield 'empty error message' => [
            new RawConfiguration('/path/to/config'),
            '',
            new Error(),
            'Could not parse the JSON file "/path/to/config": ',
        ];
    }
}
