Feature: Keep TIA out of mutant execution
    Infection supplies the covering tests for each mutant.
    Mutants must leave the initial run's persistent impact data and test history intact.

    Background:
        Given PHPUnit is configured to record test impact data from executed code without deriving it from coverage targets

    Scenario Outline: Mutant isolation with <strategy> dependencies
        Given I apply this diff to "src/Calculator.php":
            """diff
            -        return $a + $b;
            +        return $a + $b + 0;
            """
        When I run Infection on "src/Calculator.php" with the following options:
            | --test-framework-options=<option> |
        Then the initial test run executes all tests
        And the effective PHPUnit configuration includes:
            | recordTestImpactData                    | true      |
            | deriveTestImpactDataFromCoverageTargets | <derived> |
        And each mutant is tested using exactly these tests:
            | CalculatorTest::test_calculate |
        And TIA selection and recording are disabled for mutant test runs
        And mutants leave the initial impact data and test-run history unchanged
        When I run Infection again with the same options and unchanged source, tests, and configuration
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        And the mutants that were evaluated are:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |
            | src/Calculator.php | Plus    | escaped         |
        And each mutant is tested using exactly these tests:
            | CalculatorTest::test_calculate |
        And TIA selection and recording are disabled for mutant test runs
        And mutants leave the initial impact data and test-run history unchanged
        And line-to-test coverage for "src/Calculator.php" is unchanged from the first Infection run
        And the generated mutations and their detection statuses are unchanged from the first Infection run
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

        Examples:
            | strategy                  | option                                                 | derived |
            | observed execution        | --do-not-derive-test-impact-data-from-coverage-targets | false   |
            | declared coverage targets | --derive-test-impact-data-from-coverage-targets        | true    |
