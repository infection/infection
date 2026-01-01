Scenario:
    Given a configured infection project
    When infection is executed with the minimum passing MSI to 100% and ignore MSI check when no mutation is generated
    And no mutation is generated
    Then infection exists with a successful error code
    And the summary log file is generated
