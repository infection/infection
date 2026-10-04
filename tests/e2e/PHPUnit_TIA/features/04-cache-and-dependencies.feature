Feature: Reuse valid impact data and fall back when it cannot be used
    An unusable recording must not prevent collecting complete mutation coverage.
    PHPUnit explains why it falls back and records data for subsequent warm runs.

    Background:
        Given PHPUnit is configured to record test impact data from executed code without deriving it from coverage targets

    Scenario Outline: A <state> recording is replaced with reusable data
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        Given the recorded impact data becomes "<state>"
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        And PHPUnit output contains "<reason>"
        And line-to-test coverage for "src/Calculator.php" is unchanged from the first Infection run
        And the generated mutations and their detection statuses are unchanged from the first Infection run
        When I run Infection again with the same options and unchanged source, tests, and configuration
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

        Examples:
            | state        | reason                                                            |
            | missing      | no test impact data has been recorded                             |
            | empty        | the test impact data that was recorded cannot be read             |
            | incompatible | the test impact data was recorded with another version of PHPUnit |

    Scenario: An unrecorded queried source triggers a full-suite fallback
        Given the PHPUnit configuration has these attributes:
            | cacheDirectory | .infection/phpunit |
        When I run PHPUnit with options:
            | --filter=UnrelatedTest |
        Then the initial test run executes only the following tests:
            | UnrelatedTest::test_calculate |
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        And PHPUnit output contains "is not among the files that were recorded"
        And the mutants that were evaluated are:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

    Scenario Outline: Changing <dependency> invalidates a warm recording
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        When I run Infection again with the same options and unchanged source, tests, and configuration
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        Given I change the shared execution dependency "<dependency>"
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        And PHPUnit output contains "<reason>"
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

        Examples:
            | dependency          | reason                                                             |
            | PHPUnit XML setting | the configuration changed since the test impact data was recorded  |
            | bootstrap script    | a bootstrap script changed since the test impact data was recorded |
            | composer.lock       | composer.lock changed since the test impact data was recorded      |

    # Blocker: ../../../../doc/phpunit-tia-problems.md#2-configuration-and-recording-identity
    @skip
    Scenario: A changed PHP runtime setting invalidates a warm recording
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        When I run Infection on "src/Calculator.php" with the following options:
            | --initial-tests-php-options=-d precision=15 |
        Then the initial test run executes all tests
        And PHPUnit output contains "the configuration changed since the test impact data was recorded"
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

    # Blocker: ../../../../doc/phpunit-tia-problems.md#2-configuration-and-recording-identity
    @skip
    Scenario: Generated XML outside the project still tracks the project's dependency lock
        Given Infection writes generated PHPUnit XML outside the scenario project
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        Given I change the shared execution dependency "composer.lock"
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        And PHPUnit output contains "composer.lock changed since the test impact data was recorded"
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

    Scenario: Infection reuses a PHPUnit recording when both tools use the same cache
        Given the PHPUnit configuration has these attributes:
            | cacheDirectory | .infection/phpunit |
        And a successful PHPUnit run has executed all tests and recorded their impact data
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

    Scenario: PHPUnit reuses an Infection recording when both tools use the same cache
        Given the PHPUnit configuration has these attributes:
            | cacheDirectory | .infection/phpunit |
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        When I run PHPUnit with options:
            | --impacted-by=src/Calculator.php |
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |

    # Blocker: ../../../../doc/phpunit-tia-problems.md#1-explicit-queries-can-omit-newly-relevant-tests
    @skip
    Scenario: A changed external fixture remains relevant to an explicit impact query
        Given the project file "tests/input.json" contains:
            """
            {"expected": 2}
            """
        And I apply this diff to "tests/UnrelatedTest.php":
            """
             #[CoversClass(Unrelated::class)]
            +#[\PHPUnit\Framework\Attributes\UsesFixture('input.json')]
            """
        And I apply this diff to "tests/UnrelatedTest.php":
            """
            -        $this->assertSame(2, (new Unrelated())->calculate(1, 2));
            +        $input = json_decode(file_get_contents(__DIR__ . '/input.json'), true, flags: JSON_THROW_ON_ERROR);
            +        $this->assertSame($input['expected'], (new Unrelated())->calculate(1, 2), 'The fixture must describe the multiplication result.');
            """
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        Given the project file "tests/input.json" contains:
            """
            {"expected": 2, "revision": 2}
            """
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
            | UnrelatedTest::test_calculate  |

    # Blocker: ../../../../doc/phpunit-tia-problems.md#3-infection-integration-gaps
    @skip
    Scenario Outline: Cache obstruction by <obstruction> permits mutation testing without TIA
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        Given the impact cache is obstructed by "<obstruction>"
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        And the effective PHPUnit configuration includes:
            | recordTestImpactData | false |
            | impactedByFile       | null  |
        And the mutants that were evaluated are:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |

        Examples:
            | obstruction                            |
            | a file at the cache-directory path     |
            | a directory at the recording-file path |
