Scenario:
    Given a configured infection project
    When infection is executed with a filter
    And no source file is found as a result
    Then infection exists with a successful error code
    And the initial tests were not executed
