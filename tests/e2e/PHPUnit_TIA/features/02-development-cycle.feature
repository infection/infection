@draft @priority_2
Feature: Keep test selection correct as the project evolves
  An old impact map must not hide new coverage or improved assertions.
  Each comparison uses a separate copy of the same updated project with TIA disabled.

  Scenario Outline: Strengthening a test kills a previously escaped mutant
    Given the project has <configuration>
    And CalculatorTest has an assertion that allows the Plus mutant to escape
    And an Infection run has recorded both tests and reported that escaped mutant
    And I strengthen CalculatorTest to distinguish addition from subtraction
    When I run Infection for "src/Calculator.php" again
    Then the initial run executes the strengthened CalculatorTest
    And CalculatorTest kills the previously escaped mutant
    And the mutation results match a run with TIA disabled

    Examples:
      | configuration                       |
      | no explicit PHPUnit TIA settings    |
      | PHPUnit TIA enabled in its XML      |

  Scenario Outline: Changed test inputs establish a previously unknown dependency
    Given a successful initial run has recorded both tests
    And UnrelatedTest did not previously execute Calculator
    And I change <input> so that UnrelatedTest also executes Calculator
    When I run Infection for "src/Calculator.php"
    Then the initial run executes UnrelatedTest with the changed inputs
    And the refreshed impact data records its dependency on Calculator
    And Calculator has the same line-to-test coverage as a run with TIA disabled
    And the mutation results match a run with TIA disabled

    Examples:
      | input                                  |
      | the body of UnrelatedTest              |
      | a data provider used by UnrelatedTest  |

  Scenario: A new test contributes coverage immediately
    Given a successful initial run has recorded both tests
    And I add a new test that executes Calculator
    When I run Infection for "src/Calculator.php"
    Then the initial run executes the new test and CalculatorTest
    And the new test appears in Calculator's line-to-test coverage
    And the mutation results match a run with TIA disabled

  Scenario: A new source file and its tests are not omitted
    Given a successful initial run has recorded both tests
    And I add a new source file with a Plus mutation and a test that kills it
    When I run Infection for the new source file
    Then the initial run executes the new test
    And the new Plus mutant is generated and killed

  Scenario: Changing existing source refreshes coverage
    Given a successful initial run has recorded both tests
    And I extend Calculator with a branch exercised by CalculatorTest
    When I run Infection for "src/Calculator.php"
    Then the initial run executes CalculatorTest
    And coverage includes the new branch
    And the mutation results match a run with TIA disabled

  Scenario: A partial run retains dependencies for other source files
    Given a successful initial run has recorded both tests
    And I run Infection for "src/Calculator.php"
    When I subsequently run Infection for "src/Unrelated.php"
    Then the initial run executes UnrelatedTest
    And Unrelated has the same line-to-test coverage as a run with TIA disabled
