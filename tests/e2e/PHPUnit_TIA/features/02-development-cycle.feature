Feature: Keep test selection correct as the project evolves
    Infection reuses impact data while developers change source code and tests.
    Updated projects retain the coverage and mutation results of a full-suite run.

    Background:
        Given PHPUnit is configured to record test impact data from executed code without deriving it from coverage targets
        And PHPUnit has not been run and there is no recorded impact data

    Scenario: Strengthening an assertion kills a previously escaped mutant
        Given I apply this diff to "tests/CalculatorTest.php":
            """
            -        $this->assertSame(3, (new Calculator())->calculate(1, 2));
            +        $this->assertSame(3, (new Calculator())->calculate(3, 0));
            """
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        And the mutants that were evaluated are:
            | file               | mutator | outcome |
            | src/Calculator.php | Plus    | escaped |
        When I apply this diff to "tests/CalculatorTest.php":
            """
            -        $this->assertSame(3, (new Calculator())->calculate(3, 0));
            +        $this->assertSame(3, (new Calculator())->calculate(1, 2));
            """
        And I run Infection on "src/Calculator.php"
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        And the mutants that were evaluated are:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

    # Blocker: ../../../../doc/TIA-notes.md#explicit-impact-queries-miss-changed-tests
    @skip
    Scenario: An existing test starts covering another source file
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        When I apply this diff to "tests/UnrelatedTest.php":
            """
             #[CoversClass(Unrelated::class)]
            +#[CoversClass(\Infection\E2ETests\PHPUnitTIA\Calculator::class)]
            """
        And I apply this diff to "tests/UnrelatedTest.php":
            """
            -        $this->assertSame(2, (new Unrelated())->calculate(1, 2));
            +        $this->assertSame(2, (new Unrelated())->calculate(1, 2), 'Unrelated must multiply both operands.');
            +        $this->assertSame(3, (new \Infection\E2ETests\PHPUnitTIA\Calculator())->calculate(1, 2), 'Calculator must add both operands.');
            """
        And I run Infection on "src/Calculator.php"
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
            | UnrelatedTest::test_calculate  |
        When I run Infection again with the same options and unchanged source, tests, and configuration
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
            | UnrelatedTest::test_calculate  |
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

    # Blocker: ../../../../doc/TIA-notes.md#explicit-impact-queries-miss-changed-tests
    @skip
    Scenario: Changed data-provider inputs establish a previously unknown dependency
        Given the project file "tests/UnrelatedTest.php" contains:
            """
            <?php

            declare(strict_types=1);

            namespace Infection\E2ETests\PHPUnitTIA\Tests;

            use Infection\E2ETests\PHPUnitTIA\Calculator;
            use Infection\E2ETests\PHPUnitTIA\Unrelated;
            use PHPUnit\Framework\Attributes\CoversClass;
            use PHPUnit\Framework\Attributes\DataProvider;
            use PHPUnit\Framework\TestCase;

            #[CoversClass(Unrelated::class)]
            #[CoversClass(Calculator::class)]
            final class UnrelatedTest extends TestCase
            {
                #[DataProvider('calculators')]
                public function test_calculate(string $class, int $expected): void
                {
                    $this->assertSame($expected, (new $class())->calculate(1, 2), 'The calculator returned the wrong result.');
                }

                public static function calculators(): iterable
                {
                    yield 'selected calculator' => [Unrelated::class, 2];
                }
            }
            """
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        When I apply this diff to "tests/UnrelatedTest.php":
            """
            -        yield 'selected calculator' => [Unrelated::class, 2];
            +        yield 'selected calculator' => [Calculator::class, 3];
            """
        And I run Infection on "src/Calculator.php"
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate                    |
            | UnrelatedTest::test_calculate#selected calculator |
        When I run Infection again with the same options and unchanged source, tests, and configuration
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate                    |
            | UnrelatedTest::test_calculate#selected calculator |
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

    Scenario: A new test contributes coverage immediately
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        Given the project file "tests/AdditionalCalculatorTest.php" contains:
            """
            <?php

            declare(strict_types=1);

            namespace Infection\E2ETests\PHPUnitTIA\Tests;

            use Infection\E2ETests\PHPUnitTIA\Calculator;
            use PHPUnit\Framework\Attributes\CoversClass;
            use PHPUnit\Framework\TestCase;

            #[CoversClass(Calculator::class)]
            final class AdditionalCalculatorTest extends TestCase
            {
                public function test_calculate(): void
                {
                    $this->assertSame(7, (new Calculator())->calculate(3, 4), 'Calculator must add both operands.');
                }
            }
            """
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes only the following tests:
            | AdditionalCalculatorTest::test_calculate |
            | CalculatorTest::test_calculate           |
        When I run Infection again with the same options and unchanged source, tests, and configuration
        Then the initial test run executes only the following tests:
            | AdditionalCalculatorTest::test_calculate |
            | CalculatorTest::test_calculate           |
        And the mutants that were evaluated are:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

    Scenario: A new source file and its tests are not omitted
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        Given the project file "src/AdditionalCalculator.php" contains:
            """
            <?php

            declare(strict_types=1);

            namespace Infection\E2ETests\PHPUnitTIA;

            final class AdditionalCalculator
            {
                public function calculate(int $a, int $b): int
                {
                    return $a + $b;
                }
            }
            """
        And the project file "tests/AdditionalCalculatorTest.php" contains:
            """
            <?php

            declare(strict_types=1);

            namespace Infection\E2ETests\PHPUnitTIA\Tests;

            use Infection\E2ETests\PHPUnitTIA\AdditionalCalculator;
            use PHPUnit\Framework\Attributes\CoversClass;
            use PHPUnit\Framework\TestCase;

            #[CoversClass(AdditionalCalculator::class)]
            final class AdditionalCalculatorTest extends TestCase
            {
                public function test_calculate(): void
                {
                    $this->assertSame(7, (new AdditionalCalculator())->calculate(3, 4), 'AdditionalCalculator must add both operands.');
                }
            }
            """
        When I run Infection on "src/AdditionalCalculator.php"
        Then the initial test run executes all tests
        And the mutants that were evaluated are:
            | file                         | mutator | outcome         |
            | src/AdditionalCalculator.php | Plus    | killed by tests |
        When I run Infection again with the same options and unchanged source, tests, and configuration
        Then the initial test run executes only the following tests:
            | AdditionalCalculatorTest::test_calculate |
        And the mutants that were evaluated are:
            | file                         | mutator | outcome         |
            | src/AdditionalCalculator.php | Plus    | killed by tests |
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

    Scenario: Changing existing source refreshes coverage
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        When I apply this diff to "src/Calculator.php":
            """
            +        if ($a === 1) {
            +            return 1 + $b;
            +        }
            +
                     return $a + $b;
            """
        And I run Infection on "src/Calculator.php"
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        And the mutants that were evaluated are:
            | file               | mutator | outcome         |
            | src/Calculator.php | Plus    | killed by tests |
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |

    Scenario: A partial run retains dependencies for other source files
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes all tests
        When I run Infection again with the same options and unchanged source, tests, and configuration
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        When I run Infection on "src/Unrelated.php"
        Then the initial test run executes only the following tests:
            | UnrelatedTest::test_calculate |
        And the results match a run with TIA disabled:
            | coverage            |
            | generated mutations |
            | detection statuses  |
            | MSI                 |
        When I run Infection on "src/Calculator.php"
        Then the initial test run executes only the following tests:
            | CalculatorTest::test_calculate |
        And the generated mutations and their detection statuses are unchanged from the first Infection run
