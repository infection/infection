<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration\Schema;

use Generator;
use Infection\Configuration\RawConfiguration\RawConfiguration;
use Infection\Configuration\Schema\InvalidSchema;
use Infection\Configuration\Schema\SchemaValidator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use stdClass;
use Webmozart\Assert\Assert;
use function json_decode;
use function Safe\json_last_error_msg;

class SchemaValidatorTest extends TestCase
{
    /**
     * @dataProvider configProvider
     */
    public function test_it_validates_the_given_raw_config(
        RawConfiguration $config,
        ?string $expectedErrorMessage
    ): void
    {
        try {
            (new SchemaValidator())->validate($config);

            if (null !== $expectedErrorMessage) {
                $this->fail('Expected the config to be invalid.');
            } else {
                $this->addToAssertionCount(1);
            }
        } catch (InvalidSchema $exception) {
            if (null === $expectedErrorMessage) {
                $this->fail('Did not expect the config to be invalid.');
            } else {
                $this->assertSame(
                    $expectedErrorMessage,
                    $exception->getMessage()
                );
            }
        }
    }

    public function configProvider(): Generator
    {
        $path = '/path/to/config';

        yield 'empty JSON' => [
            self::createConfigWithContents(
                $path,
               '{}'
            ),
            <<<'ERROR'
"/path/to/config" does not match the expected JSON schema:
 - [source] The property source is required
ERROR
            ,
        ];

        yield 'invalid timeout' => [
            self::createConfigWithContents(
                $path,
                '{"timeout": "10"}'
            ),
            <<<'ERROR'
"/path/to/config" does not match the expected JSON schema:
 - [source] The property source is required
 - [timeout] String value found, but an integer is required
ERROR
            ,
        ];

        yield 'valid schema' => [
            self::createConfigWithContents(
                $path,
                <<<'JSON'
{
    "source": {
        "directories": ["src"]
    }
}
JSON
            ),
            null,
        ];
    }

    private static function createConfigWithContents(
        string $path,
        string $contents
    ): RawConfiguration
    {
        $config = new RawConfiguration($path);

        $decodedContents = json_decode($contents);

        Assert::notNull($decodedContents, json_last_error_msg());

        $configReflection = new ReflectionClass(RawConfiguration::class);

        $decodedContentsReflection = $configReflection->getProperty('decodedContents');
        $decodedContentsReflection->setAccessible(true);
        $decodedContentsReflection->setValue($config, $decodedContents);

        return $config;
    }
}
