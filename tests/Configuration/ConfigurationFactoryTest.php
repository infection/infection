<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration;

use Generator;
use Infection\Configuration\Configuration;
use Infection\Configuration\ConfigurationFactory;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\Mutator\Mutators;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use InvalidArgumentException;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use stdClass;
use function array_map;
use function array_merge;
use function array_values;
use function implode;
use function Safe\json_decode;
use function sprintf;
use const PHP_EOL;

class ConfigurationFactoryTest extends TestCase
{
    private const SCHEMA_FILE = 'file://'.__DIR__.'/../../resources/schema.json';

    /**
     * @dataProvider provideRawConfig
     */
    public function test_it_can_create_a_config(
        string $json,
        Configuration $expected
    ): void
    {
        $rawConfig = json_decode($json);

        // Validate the schema here to ensure we are not testing against invalid
        // schemas
        // This statement should be more seen as a safeguard against use testing
        // improbable cases rather than a test in itself
        $this->assertJsonIsSchemaValid($rawConfig);

        $actual = (new ConfigurationFactory())->create($rawConfig);

        $this->assertEquals($expected, $actual);
    }

    public function provideRawConfig(): Generator
    {
        // The schema is given as a JSON here to be closer to how the user configure the schema
        yield 'empty' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
            ]),
        ];

        yield 'with timeout' => [
            <<<'JSON'
{
    "timeout": 100,
    "source": {
        "directories": ["src"]
    }
}
JSON
            ,
            self::createConfig([
                'timeout' => 100,
                'source' => new Source(['src'], []),
            ]),
        ];

        yield 'logs - text' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "logs": {
        "text": "text.log"
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'logs' => new Logs(
                    'text.log',
                    null,
                    null,
                    null,
                    null
                )
            ]),
        ];

        yield 'logs - summary' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "logs": {
        "summary": "summary.log"
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'logs' => new Logs(
                    null,
                    'summary.log',
                    null,
                    null,
                    null
                )
            ]),
        ];

        yield 'logs - debug' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "logs": {
        "debug": "debug.log"
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'logs' => new Logs(
                    null,
                    null,
                    'debug.log',
                    null,
                    null
                )
            ]),
        ];

        yield 'logs - perMutator' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "logs": {
        "perMutator": "perMutator.log"
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'logs' => new Logs(
                    null,
                    null,
                    null,
                    'perMutator.log',
                    null
                )
            ]),
        ];

        yield 'logs - badge' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "logs": {
        "badge": {
            "branch": "master"
        }
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'logs' => new Logs(
                    null,
                    null,
                    null,
                    null,
                    new Badge('master')
                )
            ]),
        ];

        yield 'logs - all' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "logs": {
        "text": "text.log",
        "summary": "summary.log",
        "debug": "debug.log",
        "perMutator": "perMutator.log",
        "badge": {
            "branch": "master"
        }
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'logs' => new Logs(
                    'text.log',
                    'summary.log',
                    'debug.log',
                    'perMutator.log',
                    new Badge('master')
                )
            ]),
        ];

        yield 'tmp dir' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "tmpDir": "custom-tmp"
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'tmpDir' => 'custom-tmp',
            ]),
        ];
    }

    private static function createConfig(array $args): Configuration
    {
        $defaultArgs = [
            'timeout' => null,
            'source' => new Source([], []),
            'logs' => new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            'tmpDir' => null,
            'phpunit' => new PhpUnit(null, null),
            'mutators' => new Mutators(
                [],
                null,
                null,
                null,
                null
            ),
            'testFramework' => null,
            'bootstrap' => null,
            'initialTestsPhpOptions' => null,
            'testFrameworkOptions' => null
        ];

        $args = array_values(array_merge($defaultArgs, $args));

        return new Configuration(...$args);
    }

    private function assertJsonIsSchemaValid(stdClass $decodedJson): void
    {
        $validator = new Validator();

        $validator->validate($decodedJson, (object) ['$ref' => self::SCHEMA_FILE]);

        $normalizedErrors = array_map(
            static function (array $error): string {
                return sprintf('[%s] %s%s', $error['property'], $error['message'], PHP_EOL);
            },
            $validator->getErrors()
        );

        $this->assertTrue(
            $validator->isValid(),
            sprintf(
                'Expected the given JSON to be valid but is violating the following rules of'
                .' the schema: %s- %s',
                PHP_EOL,
                implode('- ', $normalizedErrors)
            )
        );
    }
}
