# turbocov coverage driver

Runs Infection with [turbocov](https://github.com/shipmonk-rnd/turbocov) as the
coverage driver, through its PHPUnit 13.3+ driver
`ShipMonk\Turbocov\TurbocovDriver` configured in `phpunit.xml`.

turbocov only instruments the paths listed in the `turbocov.collect_paths` INI
setting, which cannot be changed at runtime. It is not set here: this checks
that Infection detects turbocov and provides the setting from its source
directories (`src` and `lib`) for the initial test run.

`Calculator::subtract()` is not covered by any test, so no mutant is generated
for it: 2 mutants are expected (`Calculator::add()` in `src` and `Counter::next()`
in `lib`), both killed. Missing coverage would give 0 mutants, and coverage that
does not tell lines apart would give 3.

The scenario is skipped unless the `turbocov` extension is loaded. Run it with:

```shell
docker compose run --rm php84-turbocov ./tests/e2e_tests bin/infection Turbocov
```
