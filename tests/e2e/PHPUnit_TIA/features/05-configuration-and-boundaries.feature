@draft @priority_4
Feature: Respect TIA configuration and explain selection boundaries
  These scenarios cover compatibility and choices that need agreement before implementation.

  Scenario Outline: Explicit opt-outs take precedence over automatic TIA
    Given a successful initial run has recorded both tests
    And <opt_out>
    When I run Infection for "src/Calculator.php"
    Then Infection does not add automatic impact selection or enable impact recording
    And the initial run executes all tests
    And CalculatorTest kills the Calculator Plus mutant

    Examples:
      | opt_out                                             |
      | PHPUnit impact recording is explicitly disabled     |
      | PHPUnit test-run history is explicitly disabled      |
      | TIA is disabled in Infection's phpUnit configuration |

  Scenario: Declared targets cannot replace actual mutation coverage
    Given impact dependencies are derived from declared coverage targets
    And CalculatorTest declares Calculator as a target but executes only part of it
    When I run Infection for "src/Calculator.php"
    Then mutation eligibility uses executed line coverage
    And unexecuted lines are not treated as covered merely because Calculator was declared

  Scenario: Declared-target recording requires complete coverage metadata
    Given impact dependencies are derived from declared coverage targets
    And coverage metadata is not required for every test
    When I run Infection for "src/Calculator.php"
    Then Infection explains why declared-target TIA cannot be enabled
    And the initial run executes CalculatorTest and UnrelatedTest without TIA
    And CalculatorTest kills the Calculator Plus mutant

  # Proposed preference from TIA-notes.md; current integration skips automatic
  # impact selection when a user test selector is present.
  @decision_pending
  Scenario Outline: TIA narrows an explicit selection without widening it
    Given a successful initial run has recorded both tests
    And the user's <selector> includes CalculatorTest and UnrelatedTest
    When I run Infection for "src/Calculator.php"
    Then the initial run executes the following tests:
      | CalculatorTest::test_calculate |
    And no test outside the user's selection is executed
    And the mutation results match a run with TIA disabled and the same selector

    Examples:
      | selector      |
      | --filter      |
      | --group       |
      | --testsuite   |

  # Agree on the exit status and report shape before implementing this case.
  @decision_pending
  Scenario: A legitimate empty intersection is explained
    Given valid impact data selects only CalculatorTest for Calculator
    And the user's group selects only UnrelatedTest
    When I run Infection for "src/Calculator.php"
    Then Infection explains that no tests match both selections
    And the empty selection is not described as missing or unusable impact data
    And no mutation is reported as killed by tests that did not execute

  # No concurrency guarantee is proposed: concurrent cache updates are a known
  # limitation in TIA-notes.md and are outside this integration's scope.
  # The note "security issues" needs a concrete threat model before scenarios
  # can state an observable security requirement.
