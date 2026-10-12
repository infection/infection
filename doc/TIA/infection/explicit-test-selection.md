# Deciding how TIA interacts with explicit test selection

A developer can restrict mutation targets and tests independently. For example,
`infection src/Calculator.php tests/CalculatorTest.php` requests mutations in Calculator
and execution of CalculatorTest.

Infection currently honours explicit tests and suppresses automatic TIA. The `--filter`,
`--group`, and `--testsuite` examples in the [explicit-selector outline][selectors] all
pass and are tagged `@current_behavior`.

Whether TIA should further narrow an explicit test selection remains open. Earlier notes
described precedence as agreed and a candidate for an ADR; later notes reopened the
question of intersection. No ADR was written. Neither policy may widen the requested tests.

The current examples do not distinguish the policies: none selects both impacted and
unrelated tests. There is also no scenario for positional test paths. Explicitly requested
test paths are not interchangeable with changed-test paths added to keep recordings current.

[selectors]: ../../../tests/e2e/PHPUnit_TIA/features/05-configuration-and-boundaries.feature#L114
