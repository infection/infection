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

namespace Infection\Tests\Configuration\Schema;

use function array_diff_key;
use function array_fill_keys;
use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use Generator;
use function implode;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Configuration\Schema\SchemaConfigurationFactory;
use Infection\Mutagen\Mutator\ProfileList;
use Infection\TestFramework\TestFrameworkTypes;
use JsonSchema\Validator;
use const PHP_EOL;
use PHPUnit\Framework\TestCase;
use function Safe\json_decode;
use function sprintf;
use stdClass;
use function var_export;

/**g
 * @covers \Infection\Configuration\Schema\SchemaConfigurationFactory
 * @covers \Infection\Configuration\Entry\Badge
 * @covers \Infection\Configuration\Entry\Logs
 * @covers \Infection\Configuration\Entry\PhpUnit
 * @covers \Infection\Configuration\Entry\Source
 */
final class SchemaConfigurationFactoryTest extends TestCase
{
    private const SCHEMA_FILE = 'file://' . __DIR__ . '/../../../../resources/schema.json';

    private const PROFILES = [
        '@arithmetic',
        '@boolean',
        '@cast',
        '@conditional_boundary',
        '@conditional_negotiation',
        '@function_signature',
        '@number',
        '@operator',
        '@regex',
        '@removal',
        '@return_value',
        '@sort',
        '@zero_iteration',
        '@default',
    ];

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

        $actual = (new SchemaConfigurationFactory())->create(
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

        foreach (TestFrameworkTypes::TYPES as $testFrameworkType) {
            yield '[testFramework] ' . $testFrameworkType => (static function () use (
                $testFrameworkType
            ): array {
                return [
                    <<<"JSON"
{
    "source": {
        "directories": ["src"]
    },
    "testFramework": "{$testFrameworkType}"
}
JSON
                    ,
                    self::createConfig([
                        'source' => new Source(['src'], []),
                        'testFramework' => $testFrameworkType,
                    ]),
                ];
            })();
        }

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
                'mutators' => [
                    'TrueValue' => true,
                ],
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
                'mutators' => [
                    'TrueValue' => false,
                ],
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
                'mutators' => [
                    'TrueValue' => (object) [
                        'ignore' => [
                            'fileA',
                            'fileB',
                        ],
                    ],
                ],
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
                'mutators' => [
                    'TrueValue' => (object) [
                        'ignore' => [
                            ' file ',
                            '',
                        ],
                    ],
                ],
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
                'mutators' => [
                    'TrueValue' => (object) [
                        'settings' => (object) [
                            'in_array' => false,
                        ],
                    ],
                ],
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
                'mutators' => [
                    'TrueValue' => (object) [
                        'settings' => (object) [
                            'array_search' => false,
                        ],
                    ],
                ],
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
                'mutators' => [
                    'TrueValue' => (object) [
                        'ignore' => ['fileA'],
                        'settings' => (object) [
                            'in_array' => false,
                            'array_search' => false,
                        ],
                    ],
                ],
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
                'mutators' => [
                    'ArrayItemRemoval' => true,
                ],
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
                'mutators' => [
                    'ArrayItemRemoval' => false,
                ],
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
                'mutators' => [
                    'ArrayItemRemoval' => (object) [
                        'ignore' => [
                            'fileA',
                            'fileB',
                        ],
                    ],
                ],
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
                'mutators' => [
                    'ArrayItemRemoval' => (object) [
                        'ignore' => [
                            ' file ',
                            '',
                        ],
                    ],
                ],
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
                'mutators' => [
                    'ArrayItemRemoval' => (object) [
                        'settings' => (object) [
                            'remove' => 'first',
                        ],
                    ],
                ],
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
                'mutators' => [
                    'ArrayItemRemoval' => (object) [
                        'settings' => (object) [
                            'limit' => 10,
                        ],
                    ],
                ],
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
                'mutators' => [
                    'ArrayItemRemoval' => (object) [
                        'ignore' => ['file'],
                        'settings' => (object) [
                            'remove' => 'first',
                            'limit' => 10,
                        ],
                    ],
                ],
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
                'mutators' => [
                    'BCMath' => true,
                ],
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
                'mutators' => [
                    'BCMath' => false,
                ],
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
                'mutators' => [
                    'BCMath' => (object) [
                        'ignore' => [
                            'fileA',
                            'fileB',
                        ],
                    ],
                ],
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
                'mutators' => [
                    'BCMath' => (object) [
                        'ignore' => [
                            ' file ',
                            '',
                        ],
                    ],
                ],
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

        foreach ($orderedBcMathSettings as $bcMathSetting) {
            yield '[mutators][BCMath] setting ' . $bcMathSetting => (static function () use ($bcMathSetting): array {
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
                        'mutators' => [
                            'BCMath' => (object) [
                                'settings' => (object) [
                                    $bcMathSetting => false,
                                ],
                            ],
                        ],
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
                'mutators' => [
                    'BCMath' => (object) [
                        'ignore' => ['file'],
                        'settings' => (object) [
                            'bcadd' => false,
                            'bccomp' => false,
                            'bcdiv' => false,
                            'bcmod' => false,
                            'bcmul' => false,
                            'bcpow' => false,
                            'bcsub' => false,
                            'bcsqrt' => false,
                            'bcpowmod' => false,
                        ],
                    ],
                ],
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
                'mutators' => [
                    'MBString' => true,
                ],
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
                'mutators' => [
                    'MBString' => false,
                ],
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
                'mutators' => [
                    'MBString' => (object) [
                        'ignore' => [
                            'fileA',
                            'fileB',
                        ],
                    ],
                ],
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
                'mutators' => [
                    'MBString' => (object) [
                        'ignore' => [
                            ' file ',
                            '',
                        ],
                    ],
                ],
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

        foreach ($orderedMBStringSettings as $mbStringSetting) {
            yield '[mutators][MBString] setting ' . $mbStringSetting => (static function () use ($mbStringSetting): array {
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
                        'mutators' => [
                            'MBString' => (object) [
                                'settings' => (object) [
                                    $mbStringSetting => false,
                                ],
                            ],
                        ],
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
                'mutators' => [
                    'MBString' => (object) [
                        'ignore' => ['file'],
                        'settings' => (object) [
                            'mb_chr' => false,
                            'mb_ord' => false,
                            'mb_parse_str' => false,
                            'mb_send_mail' => false,
                            'mb_strcut' => false,
                            'mb_stripos' => false,
                            'mb_stristr' => false,
                            'mb_strlen' => false,
                            'mb_strpos' => false,
                            'mb_strrchr' => false,
                            'mb_strripos' => false,
                            'mb_strrpos' => false,
                            'mb_strstr' => false,
                            'mb_strtolower' => false,
                            'mb_strtoupper' => false,
                            'mb_substr_count' => false,
                            'mb_substr' => false,
                            'mb_convert_case' => false,
                        ],
                    ],
                ],
            ]),
        ];

        $genericMutatorNamesList = array_keys(array_diff_key(
            ProfileList::ALL_MUTATORS,
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
                    'mutators' => [
                        $mutator => true,
                    ],
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
                    'mutators' => [
                        $mutator => false,
                    ],
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
                    'mutators' => [
                        $mutator => (object) [
                            'ignore' => ['fileA', 'fileB'],
                        ],
                    ],
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
                    'mutators' => [
                        $mutator => (object) [
                            'ignore' => [
                                ' file ',
                                '',
                            ],
                        ],
                    ],
                ]),
            ];
        }

        foreach (self::PROFILES as $index => $profile) {
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
                        'mutators' => [
                            $profile => false,
                        ],
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
                        'mutators' => [
                            $profile => true,
                        ],
                    ]),
                ];
            })();
        }

        yield '[mutators][profile] ' . $profile . ' ignore' => (static function () use (
            $profile
        ): array {
            return [
                <<<JSON
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "$profile": {
            "ignore": ["fileA", "fileB"]
        }
    }
}
JSON
                ,
                self::createConfig([
                    'source' => new Source(['src'], []),
                    'mutators' => [
                        $profile => (object) [
                            'ignore' => ['fileA', 'fileB'],
                        ],
                    ],
                ]),
            ];
        })();

        yield '[mutators][profile] ' . $profile . ' ignore empty & untrimmed' => (static function () use (
            $profile
        ): array {
            return [
                <<<JSON
{
    "source": {
        "directories": ["src"]
    },
    "mutators": {
        "$profile": {
            "ignore": [" file ", ""]
        }
    }
}
JSON
                ,
                self::createConfig([
                    'source' => new Source(['src'], []),
                    'mutators' => [
                        $profile => (object) [
                            'ignore' => [
                                ' file ',
                                '',
                            ],
                        ],
                    ],
                ]),
            ];
        })();

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
                'mutators' => [
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
                'mutators' => [
                    'TrueValue' => (object) [
                        'ignore' => ['fileA'],
                        'settings' => (object) [
                            'in_array' => false,
                            'array_search' => false,
                        ],
                    ],
                    'ArrayItemRemoval' => (object) [
                        'ignore' => ['file'],
                        'settings' => (object) [
                            'remove' => 'first',
                            'limit' => 10,
                        ],
                    ],
                    'MBString' => (object) [
                        'ignore' => ['file'],
                        'settings' => (object) [
                            'mb_chr' => false,
                            'mb_ord' => false,
                            'mb_parse_str' => false,
                            'mb_send_mail' => false,
                            'mb_strcut' => false,
                            'mb_stripos' => false,
                            'mb_stristr' => false,
                            'mb_strlen' => false,
                            'mb_strpos' => false,
                            'mb_strrchr' => false,
                            'mb_strripos' => false,
                            'mb_strrpos' => false,
                            'mb_strstr' => false,
                            'mb_strtolower' => false,
                            'mb_strtoupper' => false,
                            'mb_substr_count' => false,
                            'mb_substr' => false,
                            'mb_convert_case' => false,
                        ],
                    ],
                    'BCMath' => (object) [
                        'ignore' => ['file'],
                        'settings' => (object) [
                            'bcadd' => false,
                            'bccomp' => false,
                            'bcdiv' => false,
                            'bcmod' => false,
                            'bcmul' => false,
                            'bcpow' => false,
                            'bcsub' => false,
                            'bcsqrt' => false,
                            'bcpowmod' => false,
                        ],
                    ],
                    'Assignment' => true,
                    'AssignmentEqual' => true,
                    'BitwiseAnd' => true,
                    'BitwiseNot' => true,
                    'BitwiseOr' => true,
                    'BitwiseXor' => true,
                    'Decrement' => true,
                    'DivEqual' => true,
                    'Division' => true,
                    'Exponentiation' => true,
                    'Increment' => true,
                    'Minus' => true,
                    'MinusEqual' => true,
                    'ModEqual' => true,
                    'Modulus' => true,
                    'MulEqual' => true,
                    'Multiplication' => true,
                    'Plus' => true,
                    'PlusEqual' => true,
                    'PowEqual' => true,
                    'ShiftLeft' => true,
                    'ShiftRight' => true,
                    'RoundingFamily' => true,
                    'ArrayItem' => true,
                    'EqualIdentical' => true,
                    'FalseValue' => true,
                    'IdenticalEqual' => true,
                    'LogicalAnd' => true,
                    'LogicalLowerAnd' => true,
                    'LogicalLowerOr' => true,
                    'LogicalNot' => true,
                    'LogicalOr' => true,
                    'NotEqualNotIdentical' => true,
                    'NotIdenticalNotEqual' => true,
                    'Yield_' => true,
                    'GreaterThan' => true,
                    'GreaterThanOrEqualTo' => true,
                    'LessThan' => true,
                    'LessThanOrEqualTo' => true,
                    'Equal' => true,
                    'GreaterThanNegotiation' => true,
                    'GreaterThanOrEqualToNegotiation' => true,
                    'Identical' => true,
                    'LessThanNegotiation' => true,
                    'LessThanOrEqualToNegotiation' => true,
                    'NotEqual' => true,
                    'NotIdentical' => true,
                    'PublicVisibility' => true,
                    'ProtectedVisibility' => true,
                    'DecrementInteger' => true,
                    'IncrementInteger' => true,
                    'OneZeroInteger' => true,
                    'OneZeroFloat' => true,
                    'AssignCoalesce' => true,
                    'Break_' => true,
                    'Continue_' => true,
                    'Throw_' => true,
                    'Finally_' => true,
                    'Coalesce' => true,
                    'PregQuote' => true,
                    'PregMatchMatches' => true,
                    'FunctionCallRemoval' => true,
                    'MethodCallRemoval' => true,
                    'ArrayOneItem' => true,
                    'FloatNegation' => true,
                    'FunctionCall' => true,
                    'IntegerNegation' => true,
                    'NewObject' => true,
                    'This' => true,
                    'Spaceship' => true,
                    'Foreach_' => true,
                    'For_' => true,
                    'CastArray' => true,
                    'CastBool' => true,
                    'CastFloat' => true,
                    'CastInt' => true,
                    'CastObject' => true,
                    'CastString' => true,
                    'UnwrapArrayChangeKeyCase' => true,
                    'UnwrapArrayChunk' => true,
                    'UnwrapArrayColumn' => true,
                    'UnwrapArrayCombine' => true,
                    'UnwrapArrayDiff' => true,
                    'UnwrapArrayDiffAssoc' => true,
                    'UnwrapArrayDiffKey' => true,
                    'UnwrapArrayDiffUassoc' => true,
                    'UnwrapArrayDiffUkey' => true,
                    'UnwrapArrayFilter' => true,
                    'UnwrapArrayFlip' => true,
                    'UnwrapArrayIntersect' => true,
                    'UnwrapArrayIntersectAssoc' => true,
                    'UnwrapArrayIntersectKey' => true,
                    'UnwrapArrayIntersectUassoc' => true,
                    'UnwrapArrayIntersectUkey' => true,
                    'UnwrapArrayKeys' => true,
                    'UnwrapArrayMap' => true,
                    'UnwrapArrayMerge' => true,
                    'UnwrapArrayMergeRecursive' => true,
                    'UnwrapArrayPad' => true,
                    'UnwrapArrayReduce' => true,
                    'UnwrapArrayReplace' => true,
                    'UnwrapArrayReplaceRecursive' => true,
                    'UnwrapArrayReverse' => true,
                    'UnwrapArraySlice' => true,
                    'UnwrapArraySplice' => true,
                    'UnwrapArrayUdiff' => true,
                    'UnwrapArrayUdiffAssoc' => true,
                    'UnwrapArrayUdiffUassoc' => true,
                    'UnwrapArrayUintersect' => true,
                    'UnwrapArrayUintersectAssoc' => true,
                    'UnwrapArrayUintersectUassoc' => true,
                    'UnwrapArrayUnique' => true,
                    'UnwrapArrayValues' => true,
                    'UnwrapLcFirst' => true,
                    'UnwrapStrRepeat' => true,
                    'UnwrapStrToLower' => true,
                    'UnwrapStrToUpper' => true,
                    'UnwrapTrim' => true,
                    'UnwrapUcFirst' => true,
                    'UnwrapUcWords' => true,
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
            'mutators' => [],
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
