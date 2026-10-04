@draft @priority_1
Feature: Keep TIA out of mutant execution
  Infection already selects and orders covering tests for each mutant.
  Mutant execution must leave the initial run's impact recording intact.

  Scenario Outline: Mutants neither select through TIA nor change its recording
    Given the project records dependencies from <strategy>
    And a successful initial run has recorded both tests
    When I run Infection for "src/Calculator.php"
    Then the initial run executes the following tests:
      | CalculatorTest::test_calculate |
    And the Calculator mutant executes the covering tests in Infection's chosen order
    And no mutant command or generated configuration enables TIA selection or recording
    And no mutant process emits TIA selection output
    And the impact data and test-run history are unchanged between the end of the initial run and the end of mutant execution
    And CalculatorTest kills the Calculator Plus mutant

    Examples:
      | strategy          |
      | observed execution |
      | declared coverage targets |

  Scenario: A mutant does not poison the next warm run
    Given a successful initial run has recorded both tests
    And an Infection run for Calculator has evaluated its Plus mutant
    When I run Infection for "src/Calculator.php" again without changing the project
    Then the initial run executes the following tests:
      | CalculatorTest::test_calculate |
    And no impact dependency refers to a temporary mutant file
    And the mutation results match a run with TIA disabled
