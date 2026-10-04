@priority_1
Feature: Reuse project-configured PHPUnit test impact data
    The project enables TIA with a cache directory configured in its PHPUnit XML.
    Infection runs without supplied coverage or skipped initial tests.
    Dependencies are recorded from executed code, not derived from coverage targets.
    CalculatorTest executes Calculator; UnrelatedTest executes only Unrelated.

  # Blocker: ../../../../doc/TIA-notes.md#project-configured-cache-is-not-reused
    @skip
    Scenario: Infection reuses impact data from a previous PHPUnit run
        Given PHPUnit is configured to record test impact data from executed code without deriving it from coverage targets
        And a successful PHPUnit run has executed all tests and recorded their impact data
        When I run Infection for "src/Calculator.php"
        Then the initial run executes the following tests:
            | CalculatorTest::test_calculate |
        And the following mutations are generated and evaluated:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |
        When I repeat the same Infection command without changing any project files
        Then the initial run executes the following tests:
            | CalculatorTest::test_calculate |
        And Calculator's covering tests are unchanged from the first Infection run
        And the generated and evaluated mutations are identical to the first Infection run
        And the reported MSI is identical to the first Infection run

    Scenario: Infection establishes reusable impact data on a cold start
        Given PHPUnit is configured to record test impact data from executed code without deriving it from coverage targets
        And PHPUnit has not been run and there is no recorded impact data
        When I run Infection for "src/Calculator.php"
        Then the initial run executes all tests
        And the following mutations are generated and evaluated:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |
        When I repeat the same Infection command without changing any project files
        Then the initial run executes the following tests:
            | CalculatorTest::test_calculate |
        And Calculator's covering tests are unchanged from the first Infection run
        And the generated and evaluated mutations are identical to the first Infection run
        And the reported MSI is identical to the first Infection run
