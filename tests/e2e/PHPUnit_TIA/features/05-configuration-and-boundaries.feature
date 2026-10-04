Feature: Respect TIA configuration and selection boundaries
    Infection can enable observed-execution TIA for projects without explicit settings.
    Explicit opt-outs and test selections must remain effective.

    Background:
        Given PHPUnit is configured to record test impact data from executed code without deriving it from coverage targets

    Scenario: Infection enables reusable TIA without explicit project settings
        Given PHPUnit has no explicit TIA configuration
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        And the effective PHPUnit configuration includes:
            | recordTestImpactData                    | true  |
            | recordTestRunHistory                    | true  |
            | deriveTestImpactDataFromCoverageTargets | false |
        When I run Infection again with the same options and unchanged source, tests, and configuration
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

    Scenario Outline: The CLI opt-out <option> disables automatic impact selection
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        When I run Infection on "src/Calculator.php" with the following options:
            | --test-framework-options=<option> |
        Then the effective PHPUnit configuration includes:
            | recordTestImpactData | false |
            | impactedByFile       | null  |
            | impactedBy           | []    |
            | onlyImpacted         | false |
        And the initial test run executes all tests
        And the mutants that were evaluated are:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |

        Examples:
            | option                           |
            | --do-not-record-test-impact-data |
            | --do-not-record-test-run-history |

    # Blocker: ../../../../doc/phpunit-tia-problems.md#3-infection-integration-gaps
    @skip
    Scenario Outline: The XML opt-out <attribute> disables automatic impact selection
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        Given the PHPUnit configuration has these attributes:
            | <attribute> | false |
        When I run Infection on "src/Calculator.php"
        Then the effective PHPUnit configuration includes:
            | <attribute>          | false |
            | recordTestImpactData | false |
            | impactedByFile       | null  |
        And the initial test run executes all tests
        And the mutants that were evaluated are:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |

        Examples:
            | attribute            |
            | recordTestImpactData |
            | recordTestRunHistory |

    Scenario: Declared targets do not turn unexecuted code into mutation coverage
        Given I apply this diff to "src/Calculator.php":
            """
            +        if ($a === 99) {
            +            return $a + $b;
            +        }
            +
                     return $a + $b;
            """
        When I run Infection on "src/Calculator.php" with the following options:
            | --test-framework-options=--derive-test-impact-data-from-coverage-targets |
        Then the initial test run executes all tests
        When I run Infection again with the same options and unchanged source, tests, and configuration
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        And the effective PHPUnit configuration includes:
            | deriveTestImpactDataFromCoverageTargets | true |
        And coverage for "src/Calculator.php" contains exactly these lines:
            | 11 |
            | 15 |
        And the mutants that were evaluated are:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

    Scenario: Declared-target recording requires complete coverage metadata
        Given the PHPUnit configuration has these attributes:
            | requireCoverageMetadata | false |
        When I run Infection on "src/Calculator.php" with the following options:
            | --test-framework-options=--derive-test-impact-data-from-coverage-targets |
        Then Infection output contains 'Deriving dependencies from coverage targets requires requireCoverageMetadata="true"'
        And the effective PHPUnit configuration includes:
            | recordTestImpactData                    | false |
            | deriveTestImpactDataFromCoverageTargets | false |
            | impactedByFile                          | null  |
        And the initial test run executes all tests
        And the mutants that were evaluated are:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |

    # Characterizes the current implementation. Whether TIA should narrow this
    # selection further remains an open question in doc/phpunit-tia-problems.md.
    @current_behavior
    Scenario Outline: The explicit selector <selector> currently suppresses automatic TIA
        Given I apply this diff to "tests/CalculatorTest.php":
            """
             #[CoversClass(Calculator::class)]
            +#[\PHPUnit\Framework\Attributes\Group('calculator')]
            """
        And I apply this diff to "phpunit.xml":
            """
            -        <testsuite name="tests">
            -            <directory>tests</directory>
            +        <testsuite name="calculator">
            +            <file>tests/CalculatorTest.php</file>
            +        </testsuite>
            +        <testsuite name="unrelated">
            +            <file>tests/UnrelatedTest.php</file>
                     </testsuite>
            """
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        When I run Infection on "src/Calculator.php" with the following options:
            | --test-framework-options=<selector> |
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        And the effective PHPUnit configuration includes:
            | impactedByFile | null  |
            | impactedBy     | []    |
            | onlyImpacted   | false |
        And line-to-test coverage for "src/Calculator.php" is unchanged from the first Infection run
        And the generated mutations and their detection statuses are unchanged from the first Infection run
        And the mutants that were evaluated are:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |

        Examples:
            | selector                |
            | --filter=CalculatorTest |
            | --group=calculator      |
            | --testsuite=calculator  |

    # Blocker: ../../../../doc/phpunit-tia-problems.md#open-decisions-and-missing-evidence
    # A successful-empty outcome remains a proposal, including its exit status and reports.
    @skip @decision_pending
    Scenario: An empty explicit impact intersection is distinguished from missing data
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        When I run Infection on "src/Calculator.php" with the following options:
            | --test-framework-options=--impacted-by=src/Calculator.php --filter=UnrelatedTest |
        Then PHPUnit output contains "No tests executed!"
        And the initial test run executes no tests
        And no mutations are generated or evaluated
