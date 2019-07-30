<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration;

use Generator;
use Infection\Configuration\Configuration;
use Infection\Configuration\ConfigurationFactory;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\Mutator\Mutators;
use Infection\Configuration\Entry\Mutator\TrueValue;
use Infection\Configuration\Entry\Mutator\TrueValueSettings;
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
        yield 'minimal' => [
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

        yield '[source] nominal' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src", "lib"]
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(
                    ['src', 'lib'],
                    []
                ),
            ]),
        ];

        yield '[source] excludes nominal' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"],
        "excludes": ["fixtures", "tests"]
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(
                    ['src'],
                    ['fixtures', 'tests']
                ),
            ]),
        ];

        yield '[source] empty strings' => [
            <<<'JSON'
{
    "source": {
        "directories": [""],
        "excludes": [""]
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source([], []),
            ]),
        ];

        yield '[source] empty & untrimmed strings' => [
            <<<'JSON'
{
    "source": {
        "directories": [" src ", ""],
        "excludes": [" fixtures ", ""]
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], ['fixtures']),
            ]),
        ];

        yield '[timeout] nominal' => [
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

        yield '[logs][text] nominal' => [
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

        yield '[logs][summary] nominal' => [
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

        yield '[logs][debug] nominal' => [
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

        yield '[logs][perMutator] nominal' => [
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

        yield '[logs][badge] nominal' => [
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

        yield '[logs] nominal' => [
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

        yield '[logs] empty strings' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "logs": {
        "text": "",
        "summary": "",
        "debug": "",
        "perMutator": "",
        "badge": {
            "branch": ""
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
                    null
                )
            ]),
        ];

        yield '[logs] empty & untrimmed strings' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "logs": {
        "text": " text.log ",
        "summary": " summary.log ",
        "debug": " debug.log ",
        "perMutator": " perMutator.log ",
        "badge": {
            "branch": " master "
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

        yield '[tmpDir] nominal' => [
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

        yield '[tmpDir] empty string' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "tmpDir": ""
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'tmpDir' => null,
            ]),
        ];

        yield '[tmpDir] untrimmed string' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "tmpDir": " custom-tmp "
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'tmpDir' => 'custom-tmp',
            ]),
        ];

        yield '[phpUnit] no property' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "phpUnit": {}
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'phpunit' => new PhpUnit(null, null),
            ]),
        ];

        yield '[phpUnit][configDir] nominal' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "phpUnit": {
        "configDir": "phpunit.xml"
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'phpunit' => new PhpUnit('phpunit.xml', null),
            ]),
        ];

        yield '[phpUnit][customPath] nominal' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "phpUnit": {
        "customPath": "bin/phpunit"
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'phpunit' => new PhpUnit(null, 'bin/phpunit'),
            ]),
        ];

        yield '[phpUnit] nominal' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "phpUnit": {
        "configDir": "phpunit.xml",
        "customPath": "bin/phpunit"
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'phpunit' => new PhpUnit('phpunit.xml', 'bin/phpunit'),
            ]),
        ];

        yield '[phpUnit] empty & untrimmed strings' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "phpUnit": {
        "configDir": "",
        "customPath": " bin/phpunit "
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'phpunit' => new PhpUnit(null, 'bin/phpunit'),
            ]),
        ];

        yield '[testFramework] nominal' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "testFramework": "phpunit"
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'testFramework' => 'phpunit',
            ]),
        ];

        yield '[bootstrap] nominal' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "bootstrap": "src/bootstrap.php"
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'bootstrap' => 'src/bootstrap.php',
            ]),
        ];

        yield '[bootstrap] empty string' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "bootstrap": ""
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'bootstrap' => null,
            ]),
        ];

        yield '[bootstrap] untrimmed string' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "bootstrap": " src/bootstrap.php "
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'bootstrap' => 'src/bootstrap.php',
            ]),
        ];

        yield '[initialTestsPhpOptions] nominal' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "initialTestsPhpOptions": "-d zend_extension=xdebug.so"
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'initialTestsPhpOptions' => '-d zend_extension=xdebug.so',
            ]),
        ];

        yield '[initialTestsPhpOptions] empty string' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "initialTestsPhpOptions": ""
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'initialTestsPhpOptions' => null,
            ]),
        ];

        yield '[initialTestsPhpOptions] untrimmed string' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "initialTestsPhpOptions": " -d zend_extension=xdebug.so "
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'initialTestsPhpOptions' => '-d zend_extension=xdebug.so',
            ]),
        ];

        yield '[testFrameworkOptions] nominal' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "testFrameworkOptions": "--debug"
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'testFrameworkOptions' => '--debug',
            ]),
        ];

        yield '[testFrameworkOptions] empty string' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "testFrameworkOptions": ""
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'testFrameworkOptions' => null,
            ]),
        ];

        yield '[testFrameworkOptions] untrimmed string' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "testFrameworkOptions": "--debug"
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'testFrameworkOptions' => '--debug',
            ]),
        ];

        yield '[mutators][TrueValue] true' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "TrueValue": true
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [],
                    new TrueValue(
                        true,
                        [],
                        new TrueValueSettings(
                            true,
                            true
                        )

                    ),
                    null,
                    null,
                    null
                ),
            ]),
        ];

        yield '[mutators][TrueValue] false' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "TrueValue": false
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [],
                    new TrueValue(
                        false,
                        [],
                        new TrueValueSettings(
                            false,
                            false
                        )

                    ),
                    null,
                    null,
                    null
                ),
            ]),
        ];

        yield '[mutators][TrueValue] ignore' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "TrueValue": {
            "ignore": ["fileA", "fileB"]
        }
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [],
                    new TrueValue(
                        true,
                        ['fileA', 'fileB'],
                        new TrueValueSettings(
                            true,
                            true
                        )

                    ),
                    null,
                    null,
                    null
                ),
            ]),
        ];

        yield '[mutators][TrueValue] empty & untrimmed ignore' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "TrueValue": {
            "ignore": [" file ", ""]
        }
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [],
                    new TrueValue(
                        true,
                        ['file'],
                        new TrueValueSettings(
                            true,
                            true
                        )

                    ),
                    null,
                    null,
                    null
                ),
            ]),
        ];

        yield '[mutators][TrueValue] in_array' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "TrueValue": {
            "settings": {
                "in_array": false
            }
        }
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [],
                    new TrueValue(
                        true,
                        [],
                        new TrueValueSettings(
                            false,
                            true
                        )

                    ),
                    null,
                    null,
                    null
                ),
            ]),
        ];

        yield '[mutators][TrueValue] array_search' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "TrueValue": {
            "settings": {
                "array_search": false
            }
        }
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [],
                    new TrueValue(
                        true,
                        [],
                        new TrueValueSettings(
                            true,
                            false
                        )

                    ),
                    null,
                    null,
                    null
                ),
            ]),
        ];

        yield '[mutators][TrueValue] nominal' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "TrueValue": {
            "ignore": ["fileA"],
            "settings": {
                "in_array": false,
                "array_search": false
            }
        }
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [],
                    new TrueValue(
                        true,
                        ['fileA'],
                        new TrueValueSettings(
                            false,
                            false
                        )

                    ),
                    null,
                    null,
                    null
                ),
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
