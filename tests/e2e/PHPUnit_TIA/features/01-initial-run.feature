Feature: Reuse project-configured PHPUnit test impact data
    Infection uses PHPUnit test impact data to reduce initial test execution
    while preserving coverage and mutation results.

    Background:
        Given PHPUnit is configured to record test impact data from executed code without deriving it from coverage targets

  # Blocker: ../../../../doc/TIA/infection/project-cache.md
    @skip
    Scenario: Infection reuses impact data from a previous PHPUnit run
        Given a successful PHPUnit run has executed all tests and recorded their impact data
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        And the mutants that were evaluated are:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |
        When I run Infection again with the same options and unchanged source, tests, and configuration
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        And line-to-test coverage for "src/Calculator.php" is unchanged from the first Infection run
        And the generated mutations and their detection statuses are unchanged from the first Infection run
        And the reported MSI is unchanged from the first Infection run

    Scenario: Infection establishes reusable impact data on a cold start
        Given PHPUnit has not been run and there is no recorded impact data
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        And the mutants that were evaluated are:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |
        When I run Infection again with the same options and unchanged source, tests, and configuration
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        And line-to-test coverage for "src/Calculator.php" is unchanged from the first Infection run
        And the generated mutations and their detection statuses are unchanged from the first Infection run
        And the reported MSI is unchanged from the first Infection run
