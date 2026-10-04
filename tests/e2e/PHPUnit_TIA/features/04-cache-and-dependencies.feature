@draft @priority_3
Feature: Reuse valid impact data and fall back when it cannot be used
  Falling back runs the full suite within any explicit user test selection.

  Scenario Outline: Missing or unusable recordings do not omit tests
    Given the impact recording is <state>
    When I run Infection for "src/Calculator.php"
    Then the initial run executes all tests
    And the output explains why impact selection was unavailable
    And CalculatorTest kills the Calculator Plus mutant

    Examples:
      | state                           |
      | missing                         |
      | empty                           |
      | incompatible with this PHPUnit  |

  Scenario: An unknown queried path triggers a safe fallback
    Given a successful initial run has recorded both tests
    And Calculator's path is absent from the recorded impact dependencies
    When I run Infection for "src/Calculator.php"
    Then the initial run executes all tests
    And Calculator has the same line-to-test coverage as a run with TIA disabled

  Scenario Outline: Shared execution dependencies invalidate old recordings
    Given a successful initial run has recorded both tests
    And I change <dependency>
    When I run Infection for "src/Calculator.php"
    Then the old recording is not used to omit tests
    And the initial run executes all tests
    And the output explains why the recording was invalidated

    Examples:
      | dependency                                  |
      | an execution setting in the project XML     |
      | the project bootstrap script                |
      | the project's composer.lock                 |
      | a PHP execution setting passed to PHPUnit   |

  Scenario: Generated XML outside the project still finds its dependency lock
    Given a successful initial run has recorded both tests
    And Infection writes its generated PHPUnit XML outside the project directory
    And I change the project's composer.lock
    When I run Infection for "src/Calculator.php"
    Then the old recording is invalidated by the dependency-lock change
    And the initial run executes all tests

  Scenario Outline: Plain PHPUnit and Infection can share a compatible recording
    Given <producer> has recorded both tests in the cache used by both tools
    When <consumer> selects tests impacted by "src/Calculator.php"
    Then CalculatorTest is executed and UnrelatedTest is omitted
    And the existing recording is accepted despite generated report paths and presentation settings

    Examples:
      | producer       | consumer       |
      | plain PHPUnit  | Infection      |
      | Infection      | plain PHPUnit  |

  Scenario: Changed external fixtures remain relevant to explicit impact queries
    Given UnrelatedTest declares a dependency on a JSON fixture
    And a successful initial run has recorded that dependency
    And I change the JSON fixture
    When I run Infection for "src/Calculator.php"
    Then the initial run also executes UnrelatedTest
    And the changed fixture is accounted for despite Infection querying Calculator explicitly

  Scenario Outline: An unwritable cache does not prevent mutation testing
    Given the impact cache <failure>
    When I run Infection for "src/Calculator.php"
    Then Infection reports that TIA is unavailable because the cache cannot be written
    And the initial run executes CalculatorTest and UnrelatedTest without TIA
    And CalculatorTest kills the Calculator Plus mutant

    Examples:
      | failure                         |
      | cannot be created               |
      | exists but cannot be updated    |
