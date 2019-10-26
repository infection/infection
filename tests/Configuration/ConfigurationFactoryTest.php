<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Configuration;

use function array_diff_key;
use function array_fill;
use function array_fill_keys;
use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use Generator;
use function implode;
use Infection\Configuration\ConfigurationFactory;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\Mutator\ArrayItemRemoval;
use Infection\Configuration\Entry\Mutator\ArrayItemRemovalSettings;
use Infection\Configuration\Entry\Mutator\BCMath;
use Infection\Configuration\Entry\Mutator\BCMathSettings;
use Infection\Configuration\Entry\Mutator\GenericMutator;
use Infection\Configuration\Entry\Mutator\MBString;
use Infection\Configuration\Entry\Mutator\MBStringSettings;
use Infection\Configuration\Entry\Mutator\Mutators;
use Infection\Configuration\Entry\Mutator\TrueValue;
use Infection\Configuration\Entry\Mutator\TrueValueSettings;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\SchemaConfiguration;
use Infection\Mutator\Util\MutatorProfile;
use JsonSchema\Validator;
use const PHP_EOL;
use PHPUnit\Framework\TestCase;
use function Safe\json_decode;
use function sprintf;
use stdClass;
use function var_export;

/**
 * @covers \Infection\Configuration\ConfigurationFactory
 * @covers \Infection\Configuration\Entry\Mutator\ArrayItemRemoval
 * @covers \Infection\Configuration\Entry\Mutator\ArrayItemRemovalSettings
 * @covers \Infection\Configuration\Entry\Mutator\BCMath
 * @covers \Infection\Configuration\Entry\Mutator\BCMathSettings
 * @covers \Infection\Configuration\Entry\Mutator\MBString
 * @covers \Infection\Configuration\Entry\Mutator\MBStringSettings
 * @covers \Infection\Configuration\Entry\Mutator\Mutators
 * @covers \Infection\Configuration\Entry\Mutator\TrueValue
 * @covers \Infection\Configuration\Entry\Mutator\TrueValueSettings
 * @covers \Infection\Configuration\Entry\Badge
 * @covers \Infection\Configuration\Entry\Logs
 * @covers \Infection\Configuration\Entry\PhpUnit
 * @covers \Infection\Configuration\Entry\Source
 */
final class ConfigurationFactoryTest extends TestCase
{
    private const SCHEMA_FILE = 'file://' . __DIR__ . '/../../resources/schema.json';

    /**
     * @dataProvider provideRawConfig
     */
    public function test_it_can_create_a_config(
        string $json,
        SchemaConfiguration $expected
    ): void {
        $rawConfig = json_decode($json);

        // Validate the schema here to ensure we are not testing against invalid
        // schemas
        // This statement should be more seen as a safeguard against use testing
        // improbable cases rather than a test in itself
        $this->assertJsonIsSchemaValid($rawConfig);

        $actual = (new ConfigurationFactory())->create(
            '/path/to/config',
            $rawConfig
        );

        $this->assertSame(
            var_export($expected, true),
            var_export($actual, true)
        );
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
                ),
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
                ),
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
                ),
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
                ),
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
                ),
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
                ),
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
                ),
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
                ),
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
                'phpunit' => new PhpUnit(
                    'phpunit.xml',
                    null
                ),
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
                'phpunit' => new PhpUnit(
                    null,
                    'bin/phpunit'
                ),
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
                'phpunit' => new PhpUnit(
                    'phpunit.xml',
                    'bin/phpunit'
                ),
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
                'phpunit' => new PhpUnit(
                    null,
                    'bin/phpunit'
                ),
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

        yield '[mutators][ArrayItemRemoval] true' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "ArrayItemRemoval": true
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [],
                    null,
                    new ArrayItemRemoval(
                        true,
                        [],
                        new ArrayItemRemovalSettings(
                            'all',
                            null
                        )
                    ),
                    null,
                    null
                ),
            ]),
        ];

        yield '[mutators][ArrayItemRemoval] false' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "ArrayItemRemoval": false
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [],
                    null,
                    new ArrayItemRemoval(
                        false,
                        [],
                        new ArrayItemRemovalSettings(
                            'all',
                            null
                        )
                    ),
                    null,
                    null
                ),
            ]),
        ];

        yield '[mutators][ArrayItemRemoval] ignore' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "ArrayItemRemoval": {
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
                    null,
                    new ArrayItemRemoval(
                        true,
                        ['fileA', 'fileB'],
                        new ArrayItemRemovalSettings(
                            'all',
                            null
                        )
                    ),
                    null,
                    null
                ),
            ]),
        ];

        yield '[mutators][ArrayItemRemoval] empty & untrimmed ignore' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "ArrayItemRemoval": {
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
                    null,
                    new ArrayItemRemoval(
                        true,
                        ['file'],
                        new ArrayItemRemovalSettings(
                            'all',
                            null
                        )
                    ),
                    null,
                    null
                ),
            ]),
        ];

        yield '[mutators][ArrayItemRemoval] remove' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "ArrayItemRemoval": {
            "settings": {
                "remove": "first"
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
                    null,
                    new ArrayItemRemoval(
                        true,
                        [],
                        new ArrayItemRemovalSettings(
                            'first',
                            null
                        )
                    ),
                    null,
                    null
                ),
            ]),
        ];

        yield '[mutators][ArrayItemRemoval] limit' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "ArrayItemRemoval": {
            "settings": {
                "limit": 10
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
                    null,
                    new ArrayItemRemoval(
                        true,
                        [],
                        new ArrayItemRemovalSettings(
                            'all',
                            10
                        )
                    ),
                    null,
                    null
                ),
            ]),
        ];

        yield '[mutators][ArrayItemRemoval] nominal' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "ArrayItemRemoval": {
            "ignore": ["file"],
            "settings": {
                "remove": "first",
                "limit": 10
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
                    null,
                    new ArrayItemRemoval(
                        true,
                        ['file'],
                        new ArrayItemRemovalSettings(
                            'first',
                            10
                        )
                    ),
                    null,
                    null
                ),
            ]),
        ];

        yield '[mutators][BCMath] true' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "BCMath": true
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [],
                    null,
                    null,
                    new BCMath(
                        true,
                        [],
                        new BCMathSettings(
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true
                        )
                    ),
                    null
                ),
            ]),
        ];

        yield '[mutators][BCMath] false' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "BCMath": false
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [],
                    null,
                    null,
                    new BCMath(
                        false,
                        [],
                        new BCMathSettings(
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false
                        )
                    ),
                    null
                ),
            ]),
        ];

        yield '[mutators][BCMath] ignore' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "BCMath": {
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
                    null,
                    null,
                    new BCMath(
                        true,
                        ['fileA', 'fileB'],
                        new BCMathSettings(
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true
                        )
                    ),
                    null
                ),
            ]),
        ];

        yield '[mutators][BCMath] empty & untrimmed ignore' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "BCMath": {
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
                    null,
                    null,
                    new BCMath(
                        true,
                        ['file'],
                        new BCMathSettings(
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true
                        )
                    ),
                    null
                ),
            ]),
        ];

        $orderedBcMathSettings = [
            'bcadd',
            'bccomp',
            'bcdiv',
            'bcmod',
            'bcmul',
            'bcpow',
            'bcsub',
            'bcsqrt',
            'bcpowmod',
        ];

        foreach ($orderedBcMathSettings as $index => $bcMathSetting) {
            yield '[mutators][BCMath] setting ' . $bcMathSetting => (static function () use (
                $index,
                $bcMathSetting
            ): array {
                $settingsArguments = array_fill(0, 9, true);
                $settingsArguments[$index] = false;

                return [
                    <<<JSON
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "BCMath": {
            "settings": {
                "$bcMathSetting": false
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
                            null,
                            null,
                            new BCMath(
                                true,
                                [],
                                new BCMathSettings(...$settingsArguments)
                            ),
                            null
                        ),
                    ]),
                ];
            })();
        }

        yield '[mutators][BCMath] nominal' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "BCMath": {
            "ignore": ["file"],
            "settings": {
                "bcadd": false,
                "bccomp": false,
                "bcdiv": false,
                "bcmod": false,
                "bcmul": false,
                "bcpow": false,
                "bcsub": false,
                "bcsqrt": false,
                "bcpowmod": false
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
                    null,
                    null,
                    new BCMath(
                        true,
                        ['file'],
                        new BCMathSettings(
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false
                        )
                    ),
                    null
                ),
            ]),
        ];

        yield '[mutators][MBString] true' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "MBString": true
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [],
                    null,
                    null,
                    null,
                    new MBString(
                        true,
                        [],
                        new MBStringSettings(
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true
                        )
                    )
                ),
            ]),
        ];

        yield '[mutators][MBString] false' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "MBString": false
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [],
                    null,
                    null,
                    null,
                    new MBString(
                        false,
                        [],
                        new MBStringSettings(
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false
                        )
                    )
                ),
            ]),
        ];

        yield '[mutators][MBString] ignore' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "MBString": {
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
                    null,
                    null,
                    null,
                    new MBString(
                        true,
                        ['fileA', 'fileB'],
                        new MBStringSettings(
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true
                        )
                    )
                ),
            ]),
        ];

        yield '[mutators][MBString] empty & untrimmed ignore' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "MBString": {
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
                    null,
                    null,
                    null,
                    new MBString(
                        true,
                        ['file'],
                        new MBStringSettings(
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true,
                            true
                        )
                    )
                ),
            ]),
        ];

        $orderedMBStringSettings = [
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
        ];

        foreach ($orderedMBStringSettings as $index => $mbStringSetting) {
            yield '[mutators][MBString] setting ' . $mbStringSetting => (static function () use (
                $index,
                $mbStringSetting
            ): array {
                $settingsArguments = array_fill(0, 18, true);
                $settingsArguments[$index] = false;

                return [
                    <<<JSON
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "MBString": {
            "settings": {
                "$mbStringSetting": false
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
                            null,
                            null,
                            null,
                            new MBString(
                                true,
                                [],
                                new MBStringSettings(...$settingsArguments)
                            )
                        ),
                    ]),
                ];
            })();
        }

        yield '[mutators][MBString] nominal' => [
            <<<'JSON'
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "MBString": {
            "ignore": ["file"],
            "settings": {
                "mb_chr": false,
                "mb_ord": false,
                "mb_parse_str": false,
                "mb_send_mail": false,
                "mb_strcut": false,
                "mb_stripos": false,
                "mb_stristr": false,
                "mb_strlen": false,
                "mb_strpos": false,
                "mb_strrchr": false,
                "mb_strripos": false,
                "mb_strrpos": false,
                "mb_strstr": false,
                "mb_strtolower": false,
                "mb_strtoupper": false,
                "mb_substr_count": false,
                "mb_substr": false,
                "mb_convert_case": false
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
                    null,
                    null,
                    null,
                    new MBString(
                        true,
                        ['file'],
                        new MBStringSettings(
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false
                        )
                    )
                ),
            ]),
        ];

        $genericMutatorNamesList = array_keys(array_diff_key(
            MutatorProfile::FULL_MUTATOR_LIST,
            array_fill_keys(
                [
                    'TrueValue',
                    'ArrayItemRemoval',
                    'BCMath',
                    'MBString',
                ],
                null
            )
        ));

        foreach ($genericMutatorNamesList as $mutator) {
            yield '[mutators][generic][' . $mutator . '] enabled' => [
                <<<JSON
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "$mutator": true
    }
}
JSON
                ,
                self::createConfig([
                    'source' => new Source(['src'], []),
                    'mutators' => new Mutators(
                        [],
                        null,
                        null,
                        null,
                        null,
                        new GenericMutator($mutator, true, [])
                    ),
                ]),
            ];

            yield '[mutators][generic][' . $mutator . '] disabled' => [
                <<<JSON
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "$mutator": false
    }
}
JSON
                ,
                self::createConfig([
                    'source' => new Source(['src'], []),
                    'mutators' => new Mutators(
                        [],
                        null,
                        null,
                        null,
                        null,
                        new GenericMutator($mutator, false, [])
                    ),
                ]),
            ];

            yield '[mutators][generic][' . $mutator . '] ignore' => [
                <<<JSON
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "$mutator": {
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
                        null,
                        null,
                        null,
                        null,
                        new GenericMutator(
                            $mutator,
                            true,
                            ['fileA', 'fileB']
                        )
                    ),
                ]),
            ];

            yield '[mutators][generic][' . $mutator . '] ignore empty & untrimmed' => [
                <<<JSON
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "$mutator": {
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
                        null,
                        null,
                        null,
                        null,
                        new GenericMutator($mutator, true, ['file'])
                    ),
                ]),
            ];
        }

        foreach (Mutators::PROFILES as $index => $profile) {
            yield '[mutators][profile] ' . $profile . ' false' => (static function () use (
                $profile
            ): array {
                return [
                    <<<JSON
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "$profile": false
    }
}
JSON
                    ,
                    self::createConfig([
                        'source' => new Source(['src'], []),
                        'mutators' => new Mutators(
                            [$profile => false],
                            null,
                            null,
                            null,
                            null
                        ),
                    ]),
                ];
            })();

            yield '[mutators][profile] ' . $profile . ' true' => (static function () use (
                $profile
            ): array {
                return [
                    <<<JSON
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "$profile": true
    }
}
JSON
                    ,
                    self::createConfig([
                        'source' => new Source(['src'], []),
                        'mutators' => new Mutators(
                            [$profile => true],
                            null,
                            null,
                            null,
                            null
                        ),
                    ]),
                ];
            })();
        }

        yield '[mutators][profile] nominal' => [
            <<<JSON
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "@arithmetic": true,
        "@boolean": true,
        "@cast": true,
        "@conditional_boundary": true,
        "@conditional_negotiation": true,
        "@function_signature": true,
        "@number": true,
        "@operator": true,
        "@regex": true,
        "@removal": true,
        "@return_value": true,
        "@sort": true,
        "@zero_iteration": true,
        "@default": true
    }
}
JSON
            ,
            self::createConfig([
                'source' => new Source(['src'], []),
                'mutators' => new Mutators(
                    [
                        '@arithmetic' => true,
                        '@boolean' => true,
                        '@cast' => true,
                        '@conditional_boundary' => true,
                        '@conditional_negotiation' => true,
                        '@function_signature' => true,
                        '@number' => true,
                        '@operator' => true,
                        '@regex' => true,
                        '@removal' => true,
                        '@return_value' => true,
                        '@sort' => true,
                        '@zero_iteration' => true,
                        '@default' => true,
                    ],
                    null,
                    null,
                    null,
                    null
                ),
            ]),
        ];

        yield 'nominal' => [
            <<<'JSON'
{
    "timeout": 5,
    "source": {
        "directories": ["src", "lib"],
        "excludes": ["fixtures", "tests"]
    },
    "logs": {
        "text": "text.log",
        "summary": "summary.log",
        "debug": "debug.log",
        "perMutator": "perMutator.log",
        "badge": {
            "branch": "master"
        }
    },
    "tmpDir": "custom-tmp",
    "phpUnit": {
        "configDir": "phpunit.xml",
        "customPath": "bin/phpunit"
    },
    "testFramework": "phpunit",
    "bootstrap": "src/bootstrap.php",
    "initialTestsPhpOptions": "-d zend_extension=xdebug.so",
    "testFrameworkOptions": "--debug",
    "mutators": {
        "TrueValue": {
            "ignore": ["fileA"],
            "settings": {
                "in_array": false,
                "array_search": false
            }
        },
        "ArrayItemRemoval": {
            "ignore": ["file"],
            "settings": {
                "remove": "first",
                "limit": 10
            }
        },
        "MBString": {
            "ignore": ["file"],
            "settings": {
                "mb_chr": false,
                "mb_ord": false,
                "mb_parse_str": false,
                "mb_send_mail": false,
                "mb_strcut": false,
                "mb_stripos": false,
                "mb_stristr": false,
                "mb_strlen": false,
                "mb_strpos": false,
                "mb_strrchr": false,
                "mb_strripos": false,
                "mb_strrpos": false,
                "mb_strstr": false,
                "mb_strtolower": false,
                "mb_strtoupper": false,
                "mb_substr_count": false,
                "mb_substr": false,
                "mb_convert_case": false
            }
        },
        "BCMath": {
            "ignore": ["file"],
            "settings": {
                "bcadd": false,
                "bccomp": false,
                "bcdiv": false,
                "bcmod": false,
                "bcmul": false,
                "bcpow": false,
                "bcsub": false,
                "bcsqrt": false,
                "bcpowmod": false
            }
        },
        "MBString": {
            "ignore": ["file"],
            "settings": {
                "mb_chr": false,
                "mb_ord": false,
                "mb_parse_str": false,
                "mb_send_mail": false,
                "mb_strcut": false,
                "mb_stripos": false,
                "mb_stristr": false,
                "mb_strlen": false,
                "mb_strpos": false,
                "mb_strrchr": false,
                "mb_strripos": false,
                "mb_strrpos": false,
                "mb_strstr": false,
                "mb_strtolower": false,
                "mb_strtoupper": false,
                "mb_substr_count": false,
                "mb_substr": false,
                "mb_convert_case": false
            }
        },
        "Assignment": true,
        "AssignmentEqual": true,
        "BitwiseAnd": true,
        "BitwiseNot": true,
        "BitwiseOr": true,
        "BitwiseXor": true,
        "Decrement": true,
        "DivEqual": true,
        "Division": true,
        "Exponentiation": true,
        "Increment": true,
        "Minus": true,
        "MinusEqual": true,
        "ModEqual": true,
        "Modulus": true,
        "MulEqual": true,
        "Multiplication": true,
        "Plus": true,
        "PlusEqual": true,
        "PowEqual": true,
        "ShiftLeft": true,
        "ShiftRight": true,
        "RoundingFamily": true,
        "ArrayItem": true,
        "EqualIdentical": true,
        "FalseValue": true,
        "IdenticalEqual": true,
        "LogicalAnd": true,
        "LogicalLowerAnd": true,
        "LogicalLowerOr": true,
        "LogicalNot": true,
        "LogicalOr": true,
        "NotEqualNotIdentical": true,
        "NotIdenticalNotEqual": true,
        "Yield_": true,
        "GreaterThan": true,
        "GreaterThanOrEqualTo": true,
        "LessThan": true,
        "LessThanOrEqualTo": true,
        "Equal": true,
        "GreaterThanNegotiation": true,
        "GreaterThanOrEqualToNegotiation": true,
        "Identical": true,
        "LessThanNegotiation": true,
        "LessThanOrEqualToNegotiation": true,
        "NotEqual": true,
        "NotIdentical": true,
        "PublicVisibility": true,
        "ProtectedVisibility": true,
        "DecrementInteger": true,
        "IncrementInteger": true,
        "OneZeroInteger": true,
        "OneZeroFloat": true,
        "AssignCoalesce": true,
        "Break_": true,
        "Continue_": true,
        "Throw_": true,
        "Finally_": true,
        "Coalesce": true,
        "PregQuote": true,
        "PregMatchMatches": true,
        "FunctionCallRemoval": true,
        "MethodCallRemoval": true,
        "ArrayOneItem": true,
        "FloatNegation": true,
        "FunctionCall": true,
        "IntegerNegation": true,
        "NewObject": true,
        "This": true,
        "Spaceship": true,
        "Foreach_": true,
        "For_": true,
        "CastArray": true,
        "CastBool": true,
        "CastFloat": true,
        "CastInt": true,
        "CastObject": true,
        "CastString": true,
        "UnwrapArrayChangeKeyCase": true,
        "UnwrapArrayChunk": true,
        "UnwrapArrayColumn": true,
        "UnwrapArrayCombine": true,
        "UnwrapArrayDiff": true,
        "UnwrapArrayDiffAssoc": true,
        "UnwrapArrayDiffKey": true,
        "UnwrapArrayDiffUassoc": true,
        "UnwrapArrayDiffUkey": true,
        "UnwrapArrayFilter": true,
        "UnwrapArrayFlip": true,
        "UnwrapArrayIntersect": true,
        "UnwrapArrayIntersectAssoc": true,
        "UnwrapArrayIntersectKey": true,
        "UnwrapArrayIntersectUassoc": true,
        "UnwrapArrayIntersectUkey": true,
        "UnwrapArrayKeys": true,
        "UnwrapArrayMap": true,
        "UnwrapArrayMerge": true,
        "UnwrapArrayMergeRecursive": true,
        "UnwrapArrayPad": true,
        "UnwrapArrayReduce": true,
        "UnwrapArrayReplace": true,
        "UnwrapArrayReplaceRecursive": true,
        "UnwrapArrayReverse": true,
        "UnwrapArraySlice": true,
        "UnwrapArraySplice": true,
        "UnwrapArrayUdiff": true,
        "UnwrapArrayUdiffAssoc": true,
        "UnwrapArrayUdiffUassoc": true,
        "UnwrapArrayUintersect": true,
        "UnwrapArrayUintersectAssoc": true,
        "UnwrapArrayUintersectUassoc": true,
        "UnwrapArrayUnique": true,
        "UnwrapArrayValues": true,
        "UnwrapLcFirst": true,
        "UnwrapStrRepeat": true,
        "UnwrapStrToLower": true,
        "UnwrapStrToUpper": true,
        "UnwrapTrim": true,
        "UnwrapUcFirst": true,
        "UnwrapUcWords": true,
        "@arithmetic": true,
        "@boolean": true,
        "@cast": true,
        "@conditional_boundary": true,
        "@conditional_negotiation": true,
        "@function_signature": true,
        "@number": true,
        "@operator": true,
        "@regex": true,
        "@removal": true,
        "@return_value": true,
        "@sort": true,
        "@zero_iteration": true,
        "@default": true
    }
}
JSON
            ,
            self::createConfig([
                'timeout' => 5,
                'source' => new Source(
                    ['src', 'lib'],
                    ['fixtures', 'tests']
                ),
                'logs' => new Logs(
                    'text.log',
                    'summary.log',
                    'debug.log',
                    'perMutator.log',
                    new Badge('master')
                ),
                'tmpDir' => 'custom-tmp',
                'phpunit' => new PhpUnit(
                    'phpunit.xml',
                    'bin/phpunit'
                ),
                'testFramework' => 'phpunit',
                'bootstrap' => 'src/bootstrap.php',
                'initialTestsPhpOptions' => '-d zend_extension=xdebug.so',
                'testFrameworkOptions' => '--debug',
                'mutators' => new Mutators(
                    [
                        '@arithmetic' => true,
                        '@boolean' => true,
                        '@cast' => true,
                        '@conditional_boundary' => true,
                        '@conditional_negotiation' => true,
                        '@function_signature' => true,
                        '@number' => true,
                        '@operator' => true,
                        '@regex' => true,
                        '@removal' => true,
                        '@return_value' => true,
                        '@sort' => true,
                        '@zero_iteration' => true,
                        '@default' => true,
                    ],
                    new TrueValue(
                        true,
                        ['fileA'],
                        new TrueValueSettings(
                            false,
                            false
                        )
                    ),
                    new ArrayItemRemoval(
                        true,
                        ['file'],
                        new ArrayItemRemovalSettings(
                            'first',
                            10
                        )
                    ),
                    new BCMath(
                        true,
                        ['file'],
                        new BCMathSettings(
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false
                        )
                    ),
                    new MBString(
                        true,
                        ['file'],
                        new MBStringSettings(
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false,
                            false
                        )
                    ),
                    new GenericMutator('Assignment', true, []),
                    new GenericMutator('AssignmentEqual', true, []),
                    new GenericMutator('BitwiseAnd', true, []),
                    new GenericMutator('BitwiseNot', true, []),
                    new GenericMutator('BitwiseOr', true, []),
                    new GenericMutator('BitwiseXor', true, []),
                    new GenericMutator('Decrement', true, []),
                    new GenericMutator('DivEqual', true, []),
                    new GenericMutator('Division', true, []),
                    new GenericMutator('Exponentiation', true, []),
                    new GenericMutator('Increment', true, []),
                    new GenericMutator('Minus', true, []),
                    new GenericMutator('MinusEqual', true, []),
                    new GenericMutator('ModEqual', true, []),
                    new GenericMutator('Modulus', true, []),
                    new GenericMutator('MulEqual', true, []),
                    new GenericMutator('Multiplication', true, []),
                    new GenericMutator('Plus', true, []),
                    new GenericMutator('PlusEqual', true, []),
                    new GenericMutator('PowEqual', true, []),
                    new GenericMutator('ShiftLeft', true, []),
                    new GenericMutator('ShiftRight', true, []),
                    new GenericMutator('RoundingFamily', true, []),
                    new GenericMutator('ArrayItem', true, []),
                    new GenericMutator('EqualIdentical', true, []),
                    new GenericMutator('FalseValue', true, []),
                    new GenericMutator('IdenticalEqual', true, []),
                    new GenericMutator('LogicalAnd', true, []),
                    new GenericMutator('LogicalLowerAnd', true, []),
                    new GenericMutator('LogicalLowerOr', true, []),
                    new GenericMutator('LogicalNot', true, []),
                    new GenericMutator('LogicalOr', true, []),
                    new GenericMutator('NotEqualNotIdentical', true, []),
                    new GenericMutator('NotIdenticalNotEqual', true, []),
                    new GenericMutator('Yield_', true, []),
                    new GenericMutator('GreaterThan', true, []),
                    new GenericMutator('GreaterThanOrEqualTo', true, []),
                    new GenericMutator('LessThan', true, []),
                    new GenericMutator('LessThanOrEqualTo', true, []),
                    new GenericMutator('Equal', true, []),
                    new GenericMutator('GreaterThanNegotiation', true, []),
                    new GenericMutator('GreaterThanOrEqualToNegotiation', true, []),
                    new GenericMutator('Identical', true, []),
                    new GenericMutator('LessThanNegotiation', true, []),
                    new GenericMutator('LessThanOrEqualToNegotiation', true, []),
                    new GenericMutator('NotEqual', true, []),
                    new GenericMutator('NotIdentical', true, []),
                    new GenericMutator('PublicVisibility', true, []),
                    new GenericMutator('ProtectedVisibility', true, []),
                    new GenericMutator('DecrementInteger', true, []),
                    new GenericMutator('IncrementInteger', true, []),
                    new GenericMutator('OneZeroInteger', true, []),
                    new GenericMutator('OneZeroFloat', true, []),
                    new GenericMutator('AssignCoalesce', true, []),
                    new GenericMutator('Break_', true, []),
                    new GenericMutator('Continue_', true, []),
                    new GenericMutator('Throw_', true, []),
                    new GenericMutator('Finally_', true, []),
                    new GenericMutator('Coalesce', true, []),
                    new GenericMutator('PregQuote', true, []),
                    new GenericMutator('PregMatchMatches', true, []),
                    new GenericMutator('FunctionCallRemoval', true, []),
                    new GenericMutator('MethodCallRemoval', true, []),
                    new GenericMutator('ArrayOneItem', true, []),
                    new GenericMutator('FloatNegation', true, []),
                    new GenericMutator('FunctionCall', true, []),
                    new GenericMutator('IntegerNegation', true, []),
                    new GenericMutator('NewObject', true, []),
                    new GenericMutator('This', true, []),
                    new GenericMutator('Spaceship', true, []),
                    new GenericMutator('Foreach_', true, []),
                    new GenericMutator('For_', true, []),
                    new GenericMutator('CastArray', true, []),
                    new GenericMutator('CastBool', true, []),
                    new GenericMutator('CastFloat', true, []),
                    new GenericMutator('CastInt', true, []),
                    new GenericMutator('CastObject', true, []),
                    new GenericMutator('CastString', true, []),
                    new GenericMutator('UnwrapArrayChangeKeyCase', true, []),
                    new GenericMutator('UnwrapArrayChunk', true, []),
                    new GenericMutator('UnwrapArrayColumn', true, []),
                    new GenericMutator('UnwrapArrayCombine', true, []),
                    new GenericMutator('UnwrapArrayDiff', true, []),
                    new GenericMutator('UnwrapArrayDiffAssoc', true, []),
                    new GenericMutator('UnwrapArrayDiffKey', true, []),
                    new GenericMutator('UnwrapArrayDiffUassoc', true, []),
                    new GenericMutator('UnwrapArrayDiffUkey', true, []),
                    new GenericMutator('UnwrapArrayFilter', true, []),
                    new GenericMutator('UnwrapArrayFlip', true, []),
                    new GenericMutator('UnwrapArrayIntersect', true, []),
                    new GenericMutator('UnwrapArrayIntersectAssoc', true, []),
                    new GenericMutator('UnwrapArrayIntersectKey', true, []),
                    new GenericMutator('UnwrapArrayIntersectUassoc', true, []),
                    new GenericMutator('UnwrapArrayIntersectUkey', true, []),
                    new GenericMutator('UnwrapArrayKeys', true, []),
                    new GenericMutator('UnwrapArrayMap', true, []),
                    new GenericMutator('UnwrapArrayMerge', true, []),
                    new GenericMutator('UnwrapArrayMergeRecursive', true, []),
                    new GenericMutator('UnwrapArrayPad', true, []),
                    new GenericMutator('UnwrapArrayReduce', true, []),
                    new GenericMutator('UnwrapArrayReplace', true, []),
                    new GenericMutator('UnwrapArrayReplaceRecursive', true, []),
                    new GenericMutator('UnwrapArrayReverse', true, []),
                    new GenericMutator('UnwrapArraySlice', true, []),
                    new GenericMutator('UnwrapArraySplice', true, []),
                    new GenericMutator('UnwrapArrayUdiff', true, []),
                    new GenericMutator('UnwrapArrayUdiffAssoc', true, []),
                    new GenericMutator('UnwrapArrayUdiffUassoc', true, []),
                    new GenericMutator('UnwrapArrayUintersect', true, []),
                    new GenericMutator('UnwrapArrayUintersectAssoc', true, []),
                    new GenericMutator('UnwrapArrayUintersectUassoc', true, []),
                    new GenericMutator('UnwrapArrayUnique', true, []),
                    new GenericMutator('UnwrapArrayValues', true, []),
                    new GenericMutator('UnwrapLcFirst', true, []),
                    new GenericMutator('UnwrapStrRepeat', true, []),
                    new GenericMutator('UnwrapStrToLower', true, []),
                    new GenericMutator('UnwrapStrToUpper', true, []),
                    new GenericMutator('UnwrapTrim', true, []),
                    new GenericMutator('UnwrapUcFirst', true, []),
                    new GenericMutator('UnwrapUcWords', true, [])
                ),
            ]),
        ];
    }

    private static function createConfig(array $args): SchemaConfiguration
    {
        $defaultArgs = [
            'path' => '/path/to/config',
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
            'testFrameworkOptions' => null,
        ];

        $args = array_values(array_merge($defaultArgs, $args));

        return new SchemaConfiguration(...$args);
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
                . ' the schema: %s- %s',
                PHP_EOL,
                implode('- ', $normalizedErrors)
            )
        );
    }
}
