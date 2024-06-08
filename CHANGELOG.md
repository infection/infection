# Change Log

## [0.29.5](https://github.com/infection/infection/tree/0.29.5) (2024-06-08)

[Full Changelog](https://github.com/infection/infection/compare/0.29.4...0.29.5)

**Added:**

* Update GitHub actions used by @vincentchalamon in https://github.com/infection/infection/pull/1979
* Introduce GitHub Actions Concurrency used by @vincentchalamon in https://github.com/infection/infection/pull/1979

## [0.29.4](https://github.com/infection/infection/tree/0.29.4) (2024-06-08)

[Full Changelog](https://github.com/infection/infection/compare/0.29.0...0.29.4)

**Added:**

* Introduce `--logger-project-root-directory` by @vincentchalamon in https://github.com/infection/infection/pull/1978

## [0.29.0](https://github.com/infection/infection/tree/0.29.0) (2024-05-28)

[Full Changelog](https://github.com/infection/infection/compare/0.28.1...0.29.0)

**Added:**

* Support custom mutators by @vss414 in https://github.com/infection/infection/pull/1686
* Custom mutator generator by @maks-rafalko in https://github.com/infection/infection/pull/1969

Read about how to create custom mutators: https://infection.github.io/guide/custom-mutators.html

**Changed:**

* Move `Infection\Mutator\Mutator` to a separate package by @maks-rafalko in https://github.com/infection/infection/pull/1963
* Make `Mutator::getDefinition` return type non-nullable by @maks-rafalko in https://github.com/infection/infection/pull/1958
* Enable Rector's `AddCoversClassAttributeRector` rule by @maks-rafalko in https://github.com/infection/infection/pull/1962
* Mention Discord instead of Slack in issue github template by @staabm in https://github.com/infection/infection/pull/1951
* test: Force mutators to include remedies by @theofidry in https://github.com/infection/infection/pull/1954
* Use the latest composer 2 to prevent issue with incompatibility for Box and composer 2.1 by @maks-rafalko in https://github.com/infection/infection/pull/1957
* Use the latest v1 test checker action by @maks-rafalko in https://github.com/infection/infection/pull/1960
* Upgrade Rector and fix new issues by @maks-rafalko in https://github.com/infection/infection/pull/1961
* Use new PHP-CS-Fixer with parallelization by @maks-rafalko in https://github.com/infection/infection/pull/1964
* Remove our own custom FQCN visitor as we already use php-parser's `NameResolver` visitor by @maks-rafalko in https://github.com/infection/infection/pull/1967
* Replace deprecated constant `NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN` with `NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN` by @maks-rafalko in https://github.com/infection/infection/pull/1968
* Remove our own `ParentConnectorVisitor` and use `nikic-phpparser`'s one by @maks-rafalko in https://github.com/infection/infection/pull/1970

## [0.28.0](https://github.com/infection/infection/tree/0.28.0) (2024-03-23)

[Full Changelog](https://github.com/infection/infection/compare/0.27.11...0.28.0)

**Added:**

* Add PHP-Parser 5 support by @sidz in https://github.com/infection/infection/pull/1909

# Change Log

## [0.27.3](https://github.com/infection/infection/tree/0.27.3) (2023-09-28)

[Full Changelog](https://github.com/infection/infection/compare/0.27.2...0.27.3)

**Added:**

* Add GitLab code quality reporting (`--logger-gitlab`) in https://github.com/infection/infection/pull/1878

## [0.27.0](https://github.com/infection/infection/tree/0.27.0) (2023-05-16)

[Full Changelog](https://github.com/infection/infection/compare/0.26.21...0.27.0)

**Added:**

* Add negation mutators by @manhunto in https://github.com/infection/infection/pull/1753
* Calculate results and show metrics if Infection is interrupted with `SIGINT` (ctrl + c) by @maks-rafalko in https://github.com/infection/infection/pull/1857

**Changed:**

* #857 Treat log paths as relative to config directory by @LeoVie  in https://github.com/infection/infection/pull/1851
* Do not mutate `$var instanceof ClassName` inside `assert()` function as it's impossible or hard to kill by @maks-rafalko in https://github.com/infection/infection/pull/1852

**Fixed:**

* During all "unwrap" functions, return the real values instead of values wrapped with `Node\Arg()` class by @maks-rafalko in https://github.com/infection/infection/pull/1853
* Make PHPUnit 10.1 XML coverage report and test cases names with provider compatible with Infection and old format by @maks-rafalko in https://github.com/infection/infection/pull/1854


**Internal:**

* Upgrade PHP-CS-Fixer by @maks-rafalko in https://github.com/infection/infection/pull/1855
* Add GH Action to requrie tests in Pull Requests by @maks-rafalko in https://github.com/infection/infection/pull/1848
* Update `sebastian/differ` to the latest verions by @maks-rafalko in https://github.com/infection/infection/pull/1850
* Allow fidry/cpu-core-counter v0.5 by @Slamdunk in https://github.com/infection/infection/pull/1826
* Remove `xdebug-filter.php` as it's not used and deprecated by @maks-rafalko in https://github.com/infection/infection/pull/1856
* Upgrade PHPStan to the latest version and fix some errors by @maks-rafalko in https://github.com/infection/infection/pull/1859
* Upgrade the codebase up to PHP 8.1 syntax using Rector by @maks-rafalko in https://github.com/infection/infection/pull/1860
* Upgrade psalm to the latest version by @maks-rafalko in https://github.com/infection/infection/pull/1858
* Integrate `sidz/phpstan-rules` to avoid magic numbers in our code base by @maks-rafalko in https://github.com/infection/infection/pull/1861

## New Contributors
* @manhunto made their first contribution in https://github.com/infection/infection/pull/1753
* @LeoVie made their first contribution in https://github.com/infection/infection/pull/1851

**Full Changelog**: https://github.com/infection/infection/compare/0.26.21...0.27.0

## [0.26.20](https://github.com/infection/infection/tree/0.26.20) (2023-04-15)

[Full Changelog](https://github.com/infection/infection/compare/0.26.19...0.26.20)

**Added:**

* Add support for PHPUnit 10.1 and use `<source />` tag for coverage instead of `<coverage />` by @maks-rafalko in https://github.com
* Log thread count as part of performance by @icanhazstring in https://github.com/infection/infection/pull/1836

**Changed:**

* Bump minimum PHP version required to PHP 8.1 by @theofidry in https://github.com/infection/infection/pull/1765
* Declare conflict with uncompatible versions of antecedent/patchwork by @sanmai in https://github.com/infection/infection/pull/1829
* Change CDN URL for mutation-testing-elements.js by @maks-rafalko in https://github.com/infection/infection/pull/1830
* Trying to remove false positive on logical or. by @Neirda24 in https://github.com/infection/infection/pull/1801

## [0.26.19](https://github.com/infection/infection/tree/0.26.19) (2023-02-06)

[Full Changelog](https://github.com/infection/infection/compare/0.26.18...0.25.19)

**Added:**

* PHPUnit 10 support

## [0.26.18](https://github.com/infection/infection/tree/0.26.18) (2023-01-21)

[Full Changelog](https://github.com/infection/infection/compare/0.26.17...0.25.18)

**Added:**

* New logger "summaryJson" - machine-readable file in JSON format. (#1808)

## [0.26.17](https://github.com/infection/infection/tree/0.26.17) (2023-01-19)

[Full Changelog](https://github.com/infection/infection/compare/0.26.16...0.25.17)

**Added:**

- PHP 8.2 support

**Fixed:**

* ArrayItemRemoval mutator should not mutate an array when set as an attributes argument #1797
* PHP Warning on startup when using Infection from PHAR #1770
* PHP 8.2: Deprecated: Use of "static" in callables is deprecated in .../vendor/webmozart/assert/src/Assert.php on line 939 #1802
* False positive: Throw_ mutant not covered by tests #1778
* Line CodeCoverage is not a reliable source of truth #1750

## [0.26.16](https://github.com/infection/infection/tree/0.26.16) (2022-10-22)

[Full Changelog](https://github.com/infection/infection/compare/0.26.15...0.25.16)

**Added:**

* Add new `MatchArmRemoval` mutator https://github.com/infection/infection/pull/1744
* Add new `CatchBlockRemoval` mutator https://github.com/infection/infection/pull/1742
* Add new `Catch`_ mutator https://github.com/infection/infection/pull/1741
* Add new `UnwrapFinally` mutator https://github.com/infection/infection/pull/1740

**Fixed:**

* Fix the issue with anonymous classes inside arrays https://github.com/infection/infection/pull/1745

**Changed:**

* Do not mutate coalesce operator in the Assignment mutator mutator https://github.com/infection/infection/pull/1739
* Make CPU cores count more tolerant towards system command errors  https://github.com/infection/infection/pull/1733

## [0.26.0](https://github.com/infection/infection/tree/0.26.0) (2022-01-10)

[Full Changelog](https://github.com/infection/infection/compare/0.26.0...0.25.4)

**Added:**

* Implement the Stryker HTML report  https://github.com/infection/infection/pull/1625
* Add new `--git-diff-lines` option to generate Mutants only for the changed *lines* https://github.com/infection/infection/pull/1632
* Show ignored mutants on progress and summary https://github.com/infection/infection/pull/1612

**Changed:**

* Replace `badge` logger with more advanced `stryker` logger https://github.com/infection/infection/pull/1629
* Mark Mutant as killed if Test Framework returns non-zero exit code https://github.com/infection/infection/pull/1621
* Set `failOnRisky`, `failOnWarning` to `true` if parameters are not already set for mutants https://github.com/infection/infection/pull/1630
* Automatically set `XDEBUG_MODE=coverage` if needed https://github.com/infection/infection/pull/1518
* Add `dg/bypass-finals` to the conflict packages list https://github.com/infection/infection/pull/1605

## [0.25.4](https://github.com/infection/infection/tree/0.25.4) (2021-12-08)

[Full Changelog](https://github.com/infection/infection/compare/0.25.3...0.25.4)

**Added:**

* PHP 8.1 support https://github.com/infection/infection/pull/1535
* Allow Symfony 6 https://github.com/infection/infection/pull/1606
* Set `XDEBUG_MODE` for processes with coverage https://github.com/infection/infection/pull/1518
* Add `dg/bypass-finals` to the conflict packages list https://github.com/infection/infection/pull/1605

**Changed:**

* Stop Infection execution with `0` exit code when git diff filter returns empty result https://github.com/infection/infection/pull/1600
* feat: Concat does not generate mutant when both operands are the same https://github.com/infection/infection/pull/1602

## [0.25.0](https://github.com/infection/infection/tree/0.25.0) (2021-09-05)
[Full Changelog](https://github.com/infection/infection/compare/0.24.0...0.25.0)

**Added:**

- Detect syntax errors during mutation analysis and differentiate them from all errors #1555 #262
- Add `$schema` to generated `infection.json` config file for autocomplete #1553 #1432

**Changed**:

- [Performance] Add files to coverage whitelist instead of the whole directories when `--filter` or `--git-diff-filter` are used #1543
- [Performance] Speed up Infection runs by remembering which test killed a mutant #1519 #1549
- [internal] Allow Infection test suite to be executed in parallel using Paratest #1544
- Generate `infection.json` (without `.dist` postfix) by default #1554
- Do not mark Mutant as Killed when no tests were executed #1546

**Fixed:**

- Display time and consumed memory even in case of insufficient MSI #1562
- Trim "\n" and "\t" characters when replacing relative paths with absolute ones during XML config creation #1550 #1542
- For Mutant's `phpunit.xml`, set `executionOrder="default"` to prevent random ordering of the tests since we need them to be sorted (fastest - first) #1547

## [0.24.0](https://github.com/infection/infection/tree/0.24.0) (2021-07-25)
[Full Changelog](https://github.com/infection/infection/compare/0.23.0...0.24.0)

**Added:**

- [Mutator] Add Mutator `SpreadAssignment` #1529
- [Mutator] Add Mutator `SpreadRemoval` #1529

**Changed**:

- [Performance] Improve Infection performance executed against slow test suites #1539
- Allow using MSI Badge for multiple branches #1538
- Add Mutator information to GitHub annotation logger #1540
- [BC BREAK] Rename `Spread` mutator to `SpreadOneItem` #1529

## [0.23.0](https://github.com/infection/infection/tree/0.23.0) (2021-05-13)
[Full Changelog](https://github.com/infection/infection/compare/0.22.1...0.23.0)

**Added:**

- Add support for `Pest` test framework  #1516

**Fixed:**

- Multiplication mutator should not mutate when return value is integer #1515

**Changed**:

* [BE BREAK] Remove `CodeCoverageAnnotationIgnorer` #1517
* Upgrade xdebug-handler to v2 #1510

## [0.22.0](https://github.com/infection/infection/tree/0.22.0) (2021-04-24)
[Full Changelog](https://github.com/infection/infection/compare/0.21.5...0.22.0)

**Added:**

- Add `INFECTION` and `TEST_TOKEN` environment variables for each Mutant process #1504

**Fixed:**

- composer install --no-scripts installs 0.13.4 instead of 0.15.0 https://github.com/infection/infection/issues/876
- Invalid mutator config Bug https://github.com/infection/infection/issues/1479
- Error: Expected a value other than null https://github.com/infection/infection/issues/1480
- git-diff-filter option on MacOS doesn't work https://github.com/infection/infection/issues/1492
- TypeError: Argument 1 passed to PhpParser\Node\Scalar\LNumber::__construct() must be of the type int, float given https://github.com/infection/infection/issues/1484
- Do not increment max integer value https://github.com/infection/infection/pull/1486
- Do not decrement min integer value https://github.com/infection/infection/pull/1488
- Fix file not found exception for Codeception Cests

## [0.21.0](https://github.com/infection/infection/tree/0.21.0) (2021-01-27)
[Full Changelog](https://github.com/infection/infection/compare/0.20.2...0.21.0)

**Added:**

- Introduce `--noop` option to run Noop mutators that does not change the source code (AST) https://github.com/infection/infection/pull/1465
- Add support for `@infection-ignore-all` annotation https://github.com/infection/infection/pull/1468
- Introduce `--noop` option to run Noop mutators that do not change the source code (AST) https://github.com/infection/infection/pull/1465
- Add a `describe` command https://github.com/infection/infection/pull/1442
- [MUTATOR] Add `Concat` operator mutator https://github.com/infection/infection/pull/1440
- [MUTATOR] Add `ConcatOperandRemoval` operator mutator https://github.com/infection/infection/pull/1440
- [MUTATOR] Add `While` expression mutator https://github.com/infection/infection/pull/1405
- [MUTATOR] Add `DoWhile` expression mutator https://github.com/infection/infection/pull/1411
- [MUTATOR] Add `PregMatchRemoveFlags` mutator - remove flags one by one https://github.com/infection/infection/pull/1462
- [MUTATOR] Add `PregMatchRemoveCaret` https://github.com/infection/infection/pull/1455
- [MUTATOR] Add `PregMatchRemoveDollar` mutator https://github.com/infection/infection/pull/1455
- [MUTATOR] Add `NullSafe` operator mutator https://github.com/infection/infection/pull/1457

**Changed:**

- [BC BREAK] Removed `OneZeroInteger` mutator in favor of `IncrementInteger`/`DecrementInteger` mutators
- [BC BREAK] Rename `@zero_iteration` profile to the `@loop` #1407

## [0.20.0](https://github.com/infection/infection/tree/0.20.0) (2020-11-01)
[Full Changelog](https://github.com/infection/infection/compare/0.19.2...0.20.0)

**Added:**

- Add github logger to be able to use Annotations on GitHub Actions https://github.com/infection/infection/pull/1368
- Add `--diff-git-filter` & `--git-diff-base` options https://github.com/infection/infection/pull/1368
- [MUTATOR] Implement UnwrapSubstr mutator https://github.com/infection/infection/pull/1400
- [MUTATOR] Implement UnwrapStrRev mutator https://github.com/infection/infection/pull/1399
- [MUTATOR] Implement UnwrapRtrim mutator https://github.com/infection/infection/pull/1396
- [MUTATOR] Implement UnwrapStrIreplace mutator https://github.com/infection/infection/pull/1397
- [MUTATOR] Implement UnwrapStrShuffle mutator https://github.com/infection/infection/pull/1398
- [MUTATOR] Implement UnwrapLtrim mutator https://github.com/infection/infection/pull/1395
- [MUTATOR] Add Ternary operator mutator https://github.com/infection/infection/pull/1390
- [MUTATOR] Add Flip Coalesce operator mutator  https://github.com/infection/infection/pull/1389

**Changed:**

- Remove redundant Coalesce Mutator and rename FlipCoalesce to Coalesce https://github.com/infection/infection/pull/1391

## [0.19.0](https://github.com/infection/infection/tree/0.19.0) (2020-10-28)
[Full Changelog](https://github.com/infection/infection/compare/0.18.2...0.19.0)

**Added:**

- [MUTATOR] Introduce YieldValue mutator [\#1342](https://github.com/infection/infection/pull/1342)

**Changed:**

- Drop support for PHP 7.3 [\#1340](https://github.com/infection/infection/pull/1340)
- Don't mutate `$limit` argument from 0 to -1 and from -1 to 0 in `preg_split` function [\#1347](https://github.com/infection/infection/pull/1347)

**Fixed:**

- PHPUnit 9.3 compatibility issue [\#1283](https://github.com/infection/infection/issues/1283)
- In Assert.php line 2042: Expected a value other than null [\#1357](https://github.com/infection/infection/issues/1357)
- Don't mutant `$limit` 0, -1 in `preg_split` [\#1345](https://github.com/infection/infection/issues/1345)

## [0.18.0](https://github.com/infection/infection/tree/0.18.0) (2020-10-18)
[Full Changelog](https://github.com/infection/infection/compare/0.17.7...0.18.0)

**Added:**

- Exclude mutations matching the source code by Regular Expression [\#1326](https://github.com/infection/infection/pull/1326)
- [MUTATOR] Add mutator to remove shared cases [\#1326](https://github.com/infection/infection/pull/1306)

**Changed:**

- Allow fractional values for timeout  [\#1313](https://github.com/infection/infection/pull/1313)

## [0.17.0](https://github.com/infection/infection/tree/0.17.0) (2020-08-17)
[Full Changelog](https://github.com/infection/infection/compare/0.16.4...0.17.0)

**Added:**

- Set `failOnRisky`, `failOnWarning` to `true` if parameters are not already set [\#1280](https://github.com/infection/infection/pull/1280)
- Add JSON logger, useful for CI and analyzing results of Infection programmatically [\#1278](https://github.com/infection/infection/pull/1278)
- Do not mutate clean up functions [\#1285](https://github.com/infection/infection/pull/1285)
- Restrict installing with faulty versions of PHPUnit's coverage package [\#1295](https://github.com/infection/infection/pull/1295)
- Add `--no-progress` option to opt-out the CI detection [\#1261](https://github.com/infection/infection/pull/1261)
- InstanceOf\_ mutator [\#1232](https://github.com/infection/infection/pull/1232)

**Changed:**

- Exclude mutations that are over specified time limit [\#1171](https://github.com/infection/infection/pull/1171)
- Decrement mutator makes array indexes negative [\#1270](https://github.com/infection/infection/issues/1270)
- Upgrade infection/include-interceptor to ^0.2.4 [\#1288](https://github.com/infection/infection/pull/1288)
- U for uncovered [\#1233](https://github.com/infection/infection/pull/1233)
- Round MSI scores [\#1190](https://github.com/infection/infection/pull/1190)

**Fixed:**

- symbolic link trouble + PHPUnit unknown [\#778](https://github.com/infection/infection/issues/778)

## [0.16.0](https://github.com/infection/infection/tree/0.16.0) (2020-03-22)
[Full Changelog](https://github.com/infection/infection/compare/0.15.3...0.16.0)

**Added:**

- Allow the initial test suite to be skipped [\#1042](https://github.com/infection/infection/pull/1042)
- Implements dry-run mode [\#1183](https://github.com/infection/infection/pull/1183)
- Add notice to console output if actual msi is higher than required msi [\#877](https://github.com/infection/infection/pull/877)
- Allow to configure ignore globally [\#1104](https://github.com/infection/infection/pull/1104)
- Parallel source file collector [\#1097](https://github.com/infection/infection/pull/1097)
- Concurrent mutation generator [\#1082](https://github.com/infection/infection/pull/1082)
- Add accepted ADR entries [\#1192](https://github.com/infection/infection/pull/1192)
- Increase niceness for mutant processes [\#1152](https://github.com/infection/infection/pull/1152)
- Enhancement: Use ondram/ci-detector to resolve build context [\#1135](https://github.com/infection/infection/pull/1135)
- Automatically install Test Framework adapter if needed [\#1102](https://github.com/infection/infection/pull/1102)
- Accounting For Codeception Cest Tests In JUnit File. [\#1074](https://github.com/infection/infection/pull/1074)
- Introduce ConfigurableMutator [\#1012](https://github.com/infection/infection/pull/1012)
- Enhancement: Allow specifying a few command line options via config [\#789](https://github.com/infection/infection/pull/789)
- Use `infection/extension-installer` to automatically register Test Framework Adapters [\#1019](https://github.com/infection/infection/pull/1019)
- Exclude --configuration from PhpUnit options [\#941](https://github.com/infection/infection/pull/941)

**Changed:**

- Drop support for PHP 7.2 [\#1132](https://github.com/infection/infection/pull/1132)
- Use coverage report as a primary source of files to mutate [\#1106](https://github.com/infection/infection/pull/1106)
- Extract PhpSpec Test Framework adapter to a separate package [\#1052](https://github.com/infection/infection/pull/1052)
- Extract Abstract TestFrameworkAdapter & Codeception to separate packages [\#933](https://github.com/infection/infection/pull/933)
- Optimize JUnit test lookups to stop on the first element [\#1172](https://github.com/infection/infection/pull/1172)
- Rename InfectionCommand to RunCommand [\#1188](https://github.com/infection/infection/pull/1188)
- Rename LineCodeCoverage to Trace [\#1164](https://github.com/infection/infection/pull/1164)
- Remove dependency on `OutputInterface` for the loggers [\#1157](https://github.com/infection/infection/pull/1157)
- Update continuous-integration.yml to use PHP 7.3 [\#1142](https://github.com/infection/infection/pull/1142)
- Bundle test framework adapters into PHAR [\#1141](https://github.com/infection/infection/pull/1141)
- Migrate to the new Stryker dashboard API [\#1136](https://github.com/infection/infection/pull/1136)
- Make PHPUnit fail on warning or risky [\#1115](https://github.com/infection/infection/pull/1115)
- Improve TextLogger [\#1110](https://github.com/infection/infection/pull/1110)
- Remove MutantWasCreated [\#1096](https://github.com/infection/infection/pull/1096)
- Introduce specific configuration objects for the mutators [\#1005](https://github.com/infection/infection/pull/1005)

**Fixed:**

- Fix the order in which the infection configuration files are loaded [\#1105](https://github.com/infection/infection/pull/1105)
- Fix scoping [\#1072](https://github.com/infection/infection/pull/1072)
- Fix false positives since update to 0.14.x [\#815](https://github.com/infection/infection/pull/815)
- Ignore bogus "not installed" exception from PackageVersions [\#1151](https://github.com/infection/infection/pull/1151)
- Don't let PHP 7.4 builds fail [\#1130](https://github.com/infection/infection/pull/1130)
- Provide a more user-friendly error when the schema path is invalid [\#1080](https://github.com/infection/infection/pull/1080)

## [0.15.0](https://github.com/infection/infection/tree/0.15.0)

[Full Changelog](https://github.com/infection/infection/compare/0.14.0...0.15.0)

**Added:**

- [MUTATOR] Add 'clone' removal mutator [\#864](https://github.com/infection/infection/pull/864)
- [MUTATOR] Add `UnwrapStrReplace` mutator [\#831](https://github.com/infection/infection/pull/831)
- Add support for Codeception Test Framework [\#800](https://github.com/infection/infection/pull/800)
- Allow text logs to be written to a PHP stream [\#821](https://github.com/infection/infection/pull/821)
- Add version number to ASCII banner \(\#809\) [\#855](https://github.com/infection/infection/pull/855)
- Infection should emit its version when run [\#808](https://github.com/infection/infection/issues/808)
- Allow to enable pcov with initial-tests-php-options [\#830](https://github.com/infection/infection/pull/830)
- Enable Symfony 5 components [\#842](https://github.com/infection/infection/pull/842)

**Changed:**

- According to PHP 7.4 changelog, `stream_set_option()` should always return false [\#837](https://github.com/infection/infection/pull/837)
- Do not call deprecated/removed method for new versions of `symfony/process` [\#843](https://github.com/infection/infection/pull/843)
- Introduce TestFrameworkAdapter interface [\#840](https://github.com/infection/infection/pull/840)
- Rework the configuration [\#750](https://github.com/infection/infection/pull/750)

**Fixed:**

- TypeError not detected as failing test [\#836](https://github.com/infection/infection/issues/836)
- Make interceptor resilient to file not found warnings \(\#846\) [\#862](https://github.com/infection/infection/pull/862)
- The profile keys are missing in the schema.json [\#732](https://github.com/infection/infection/issues/732)
- Fix Docker builds for PHP 7.4 [\#818](https://github.com/infection/infection/pull/818)
- Fatal error by UnwrapArrayMerge and unpack [\#801](https://github.com/infection/infection/issues/801)
- The console output is missing a few line breaks / new lines [\#798](https://github.com/infection/infection/issues/798)

## [0.14.0](https://github.com/infection/infection/tree/0.14.0)

[Full Changelog](https://github.com/infection/infection/compare/0.13.0...0.14.0)

**Added:**

- \[Mutator\] Mutate `mb_str_split` to `str_split` [\#787](https://github.com/infection/infection/pull/787)
- \[Mutator\] Spread operator in Array Expression - leave only the first element [\#784](https://github.com/infection/infection/pull/784)
- \[Mutator\] Leave only one element in the non empty returned array [\#735](https://github.com/infection/infection/pull/735)
- Use xdebug-filter to reduce the time needed to collect coverage [\#781](https://github.com/infection/infection/pull/781)
- Add Symfony PHPUnitBridge [\#755](https://github.com/infection/infection/pull/755)
- Use codingmachine/safe [\#745](https://github.com/infection/infection/pull/745)
- Allow installation only with the most recent versions for dev dependencies [\#744](https://github.com/infection/infection/pull/744)
- Add PCOV to TravisCI [\#741](https://github.com/infection/infection/pull/741)
- Ensure the dev tools are up to date [\#725](https://github.com/infection/infection/pull/725)

**Changed:**

- Bump requirements up to PHP 7.2 [\#700](https://github.com/infection/infection/pull/700)
- Do not round down values in MetricsCalculator [\#701](https://github.com/infection/infection/pull/701)
- Dramatically reduce memory usage by using classes instead of object-like arrays [\#710](https://github.com/infection/infection/pull/710)
- Rework infection command [\#767](https://github.com/infection/infection/pull/767)
- Remove the self-update command [\#688](https://github.com/infection/infection/pull/688)
- Move coverage data to the mutation [\#733](https://github.com/infection/infection/pull/733)
- Deactivate `stderr` redirection in phpunit.xml [\#791](https://github.com/infection/infection/pull/791)
- Add missed profile and mutator keys to the validation schema.json [\#782](https://github.com/infection/infection/pull/782)
- Move `e2e` tests to the correct place. [\#780](https://github.com/infection/infection/pull/780)
- Remove Travis' phpunit binaries since they conflicts with vendor's phpunit [\#773](https://github.com/infection/infection/pull/773)
- Consume directly the InfectionContainer instead of a generic PSR-11 [\#761](https://github.com/infection/infection/pull/761)
- Bump the versions used where appropriate [\#743](https://github.com/infection/infection/pull/743)
- Update used memory detection for PHPUnit 8 [\#739](https://github.com/infection/infection/pull/739)
- Update E2E tests to use PHPUnit 8 [\#738](https://github.com/infection/infection/pull/738)
- Update xdebug-handler to 1.3.3, remove workarounds [\#737](https://github.com/infection/infection/pull/737)
- Upgrade to PHPUnit 8.2.3 [\#713](https://github.com/infection/infection/pull/713)
- Error out when 0 lines of code were covered [\#602](https://github.com/infection/infection/pull/602)
- Add `ignore` property for each Mutator in JSON schema. [\#699](https://github.com/infection/infection/pull/699)
- PhpProcess: Reset $\_ENV if it is in use [\#693](https://github.com/infection/infection/pull/693)
- Update alt text of slack badge [\#707](https://github.com/infection/infection/pull/707)

**Fixed:**

- Multiple extra test framework options escape in the wrong way [\#615](https://github.com/infection/infection/issues/615)
- "Return value of MutatorConfig::getMutatorSettings\(\) must be of the type array, object returned" with mutator that has settings [\#666](https://github.com/infection/infection/issues/666)
- Deal with object settings [\#772](https://github.com/infection/infection/pull/772)
- initialTestsPhpOptions does not get picked from infection.json\[.dist\] [\#672](https://github.com/infection/infection/issues/672)
- Sort & Remove duplicates entries in .gitignore [\#724](https://github.com/infection/infection/pull/724)
- ArrayItemRemoval configuration doesn't support the "ignore" property [\#698](https://github.com/infection/infection/issues/698)
- Error about missing phar pubkey file on self-update [\#684](https://github.com/infection/infection/issues/684)
- Target MSI on Travis with empty --filter [\#631](https://github.com/infection/infection/issues/631)
- Zero percent code coverage is not an issue for Infection [\#488](https://github.com/infection/infection/issues/488)
- Infection config generator fails to handle multiple ignored directories [\#580](https://github.com/infection/infection/issues/580)
- Fix Infection config builder: make sure it always creates an array for excluded dirs but not object [\#714](https://github.com/infection/infection/pull/714)

## [0.13.0](https://github.com/infection/infection/tree/0.13.0)

[Full Changelog](https://github.com/infection/infection/compare/0.12.2...0.13.0) (2019-05-18)

**Added:**

- Log the InitialTestsRun command line when `--debug` is used [\#520](https://github.com/infection/infection/issues/520)
- Preliminary support for PCOV [\#667](https://github.com/infection/infection/pull/667)
- Adding Line Numbers To Mutator Ignore List [\#663](https://github.com/infection/infection/pull/663)
- Family bc\*-functions mutators \(bcmath support\) [\#658](https://github.com/infection/infection/issues/658)
- Family mb\_\*-functions mutators [\#654](https://github.com/infection/infection/issues/654)
- Add a new unwrap mutator: ucwords [\#644](https://github.com/infection/infection/issues/644)
- New @unwrap mutator: lcfirst\(\) [\#642](https://github.com/infection/infection/issues/642)
- Provide compact output for CI environments [\#613](https://github.com/infection/infection/issues/613)
- add unwrap array\_pad mutator [\#680](https://github.com/infection/infection/pull/680)
- add unwrap array\_intersect\_assoc mutator [\#679](https://github.com/infection/infection/pull/679)
- \#597 Array item removal mutator [\#649](https://github.com/infection/infection/pull/649)
- Enhancement: Implement UnwrapTrim mutator [\#638](https://github.com/infection/infection/pull/638)
- Enhancement: Implement UnwrapArrayUintersect mutator [\#637](https://github.com/infection/infection/pull/637)
- Enhancement: Implement UnwrapArrayUintersectUassoc mutator [\#633](https://github.com/infection/infection/pull/633)
- Enhancement: Implement UnwrapArrayUintersectAssoc mutator [\#628](https://github.com/infection/infection/pull/628)
- Enhancement: Implement UnwrapArrayUdiff mutator [\#624](https://github.com/infection/infection/pull/624)
- Mutator: AssignCoalesce. Upgrade PHPParser to 4.2.1 [\#641](https://github.com/infection/infection/pull/641)
- Mutator: UnwrapUcFirst \(unwrap the first argument of ucfirst\(\) function\) [\#635](https://github.com/infection/infection/pull/635)

**Fixed:**

- Multi line arrays are not properly handled by code coverage [\#652](https://github.com/infection/infection/issues/652)
- Error when parsing method that creates anonymous class [\#616](https://github.com/infection/infection/issues/616)
- Infection gets stuck in the first execution after configuration file is created [\#576](https://github.com/infection/infection/issues/576)
- Wrong constructor ownership when returning an anonymous class [\#682](https://github.com/infection/infection/issues/682)
- Do not mutate \* to / and vice versa if one of the operands is numeric ±1.0 [\#673](https://github.com/infection/infection/pull/673)


**Changed:**

- Stop traversal of interfaces and abstract methods [\#656](https://github.com/infection/infection/pull/656)
- Add a few more count esque functions to not decrement against [\#640](https://github.com/infection/infection/pull/640)
- Enhancement: Normalize composer.json [\#629](https://github.com/infection/infection/pull/629)
- Upgrade PHPUnit from ^6.5 to ^7.5 and all dependencies, including root differ [\#627](https://github.com/infection/infection/pull/627)
- Test against php 7.4 [\#625](https://github.com/infection/infection/pull/625)

## [0.12.0](https://github.com/infection/infection/tree/0.12.0) (2019-01-24)

[Full Changelog](https://github.com/infection/infection/compare/0.11.0...0.12.0)

**BC Breaks:**

- Disabling mutating "true" -\> "false" in TrueValue mutator for in\_array/array\_search [\#599](https://github.com/infection/infection/pull/599)

**Added:**

- Allow settings for Mutators [\#206](https://github.com/infection/infection/issues/206)
- Enhancement: Implement UnwrapArrayUdiffAssoc mutator [\#614](https://github.com/infection/infection/pull/614)
- Enhancement: Implement UnwrapArraySplice mutator [\#605](https://github.com/infection/infection/pull/605)
- Enhancement: Implement UnwrapArraySlice mutator [\#598](https://github.com/infection/infection/pull/598)
- Enhancement: Implement UnwrapArrayMergeRecursive mutator [\#594](https://github.com/infection/infection/pull/594)
- Enhancement: Implement UnwrapArrayIntersectUkey mutator [\#593](https://github.com/infection/infection/pull/593)
- Enhancement: Implement UnwrapArrayIntersectUassoc mutator [\#591](https://github.com/infection/infection/pull/591)
- Enhancement: Implement UnwrapArrayColumn mutator [\#590](https://github.com/infection/infection/pull/590)
- Enhancement: Implement UnwrapArrayIntersectKey mutator [\#584](https://github.com/infection/infection/pull/584)
- Enhancement: Implement UnwrapArrayDiffUkey mutator [\#583](https://github.com/infection/infection/pull/583)

**Fixed:**

- Schema does not include initialTestsPhpOptions [\#606](https://github.com/infection/infection/issues/606)
- Space in PHP interpreter path breaks Infection [\#600](https://github.com/infection/infection/issues/600)
- Starting infection via phing differs from commandline [\#592](https://github.com/infection/infection/issues/592)
- symfony/phpunit-bridge isn't supported [\#588](https://github.com/infection/infection/issues/588)
- Symfony flex should correctly detect phpunit executable [\#493](https://github.com/infection/infection/issues/493)

**Changed:**

- Running command with `--only-covered` should add in log only covered code. [\#581](https://github.com/infection/infection/issues/581)
- Add a test to check Infection works with PSR-0 compliant autoloader [\#579](https://github.com/infection/infection/pull/579)
- Update gitattributes file [\#532](https://github.com/infection/infection/pull/532)


## [0.11.0](https://github.com/infection/infection/tree/0.11.0) (2018-11-11)

[Full Changelog](https://github.com/infection/infection/compare/0.10.6...0.11.0)

**BC Breaks:**

- Add counterparts to identical mutator and remove them from default [\#391](https://github.com/infection/infection/pull/391) ([BackEndTea](https://github.com/BackEndTea))

**Added:**

- Add json-schema validation for `infection.json.dist` config file [\#451](https://github.com/infection/infection/issues/451) ([sidz](https://github.com/sidz))
- Run project's tests in a random order for InitialTestRun process [\#519](https://github.com/infection/infection/pull/519) ([borNfreee](https://github.com/borNfreee))
- Validate phpunit.xml [\#487](https://github.com/infection/infection/pull/487) ([borNfreee](https://github.com/borNfreee))
- @codeCoverageIgnore annotations support [\#491](https://github.com/infection/infection/pull/491) ([borNfreee](https://github.com/borNfreee))
- \[RFC\] Yield mutations [\#450](https://github.com/infection/infection/pull/450) ([borNfreee](https://github.com/borNfreee))
- \[Feature\] Round Family [\#449](https://github.com/infection/infection/pull/449) ([deleugpn](https://github.com/deleugpn))
- Add counterparts to identical mutator and remove them from default [\#391](https://github.com/infection/infection/pull/391) ([BackEndTea](https://github.com/BackEndTea))
- Adding Additional CLI Settings To Config [\#463](https://github.com/infection/infection/pull/463) ([Fenikkusu](https://github.com/Fenikkusu))
- [Mutator] Implement UnwrapArrayCombine mutator [\#550](https://github.com/infection/infection/pull/550) ([localheinz](https://github.com/localheinz))
- [Mutator] Use default rules of ordered\_class\_elements fixer [\#530](https://github.com/infection/infection/pull/530) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapArrayFlip mutator [\#529](https://github.com/infection/infection/pull/529) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapArrayReverse mutator [\#527](https://github.com/infection/infection/pull/527) ([localheinz](https://github.com/localheinz))
- [Mutator] Enable no\_superfluous\_phpdoc\_tags fixer [\#525](https://github.com/infection/infection/pull/525) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapStrRepeat mutator [\#539](https://github.com/infection/infection/pull/539) ([localheinz](https://github.com/localheinz))
- [Mutator] Fail with a better assertion message [\#538](https://github.com/infection/infection/pull/538) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapArrayReplace mutator [\#536](https://github.com/infection/infection/pull/536) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapStrToLower mutator [\#534](https://github.com/infection/infection/pull/534) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapArrayReduce mutator [\#533](https://github.com/infection/infection/pull/533) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapArrayReplaceRecursive mutator [\#545](https://github.com/infection/infection/pull/545) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapArrayDiff mutator [\#544](https://github.com/infection/infection/pull/544) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapArrayIntersect mutator [\#543](https://github.com/infection/infection/pull/543) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapArrayMerge mutator [\#542](https://github.com/infection/infection/pull/542) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapArrayChunk mutator [\#558](https://github.com/infection/infection/pull/558) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapArrayUnique mutator [\#556](https://github.com/infection/infection/pull/556) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapArrayKeys mutator [\#555](https://github.com/infection/infection/pull/555) ([localheinz](https://github.com/localheinz))
- [Mutator] Implement UnwrapArrayValues mutator [\#554](https://github.com/infection/infection/pull/554) ([localheinz](https://github.com/localheinz))
- [Mutator] Unwrap all available arguments to array\_map\(\) [\#553](https://github.com/infection/infection/pull/553) ([localheinz](https://github.com/localheinz))
- [Mutator] Created new UnwrapStrToUpper mutator [\#559](https://github.com/infection/infection/pull/559) ([zf2timo](https://github.com/zf2timo))

**Fixed:**

- Do not require dev packages on Deploy stage [\#445](https://github.com/infection/infection/pull/445) ([borNfreee](https://github.com/borNfreee))
- Batch file invoked with php - breaks test framework version [\#469](https://github.com/infection/infection/issues/469)
- Validate that infection.json contains valid \(writable\) file paths for loggers [\#458](https://github.com/infection/infection/pull/458) ([borNfreee](https://github.com/borNfreee))
- Fix .bat files being invoked with php [\#470](https://github.com/infection/infection/pull/470) ([johnstevenson](https://github.com/johnstevenson))
- Exclude --testsuite from PhpUnit mutant options, but allow for initial process [\#480](https://github.com/infection/infection/issues/480)
- Extend TestFrameworkExtraOptions to cover more complex usages of options [\#483](https://github.com/infection/infection/pull/483) ([tomtomau](https://github.com/tomtomau))
- Restrict installation with broken versions of symfony/console [\#523](https://github.com/infection/infection/pull/523) ([sanmai](https://github.com/sanmai))
- Fix Decrement integer [\#485](https://github.com/infection/infection/pull/485) ([BackEndTea](https://github.com/BackEndTea))
- Update ProtectedVisibility/PublicVisibility to guard against missing reflection [\#502](https://github.com/infection/infection/pull/502) ([sanmai](https://github.com/sanmai))
- Update TestFrameworkFinder to always look for a .bat first. [\#506](https://github.com/infection/infection/pull/506) ([sanmai](https://github.com/sanmai))
- TestFrameworkFinderTest fails to run on Mac OS X [\#504](https://github.com/infection/infection/issues/504)
- PublicVisibility mutator failing due to missing reflection [\#501](https://github.com/infection/infection/issues/501)
- --initial-tests-php-options ignored on Windows [\#471](https://github.com/infection/infection/issues/471)
- Do not mutate the code inside plain functions [\#466](https://github.com/infection/infection/issues/466)
- Infection does not fail gracefully on an invalid phpunit.xml [\#409](https://github.com/infection/infection/issues/409)
- Do not mutate code that is ignored from code coverage [\#407](https://github.com/infection/infection/issues/407)
- Fix: Keep mutators in mutator profiles sorted by name [\#541](https://github.com/infection/infection/pull/541) ([localheinz](https://github.com/localheinz))

**Changed:**

- Rename infection-log.txt -\> infection.log [\#454](https://github.com/infection/infection/pull/454) ([borNfreee](https://github.com/borNfreee))
- Improve compatibility with framework based applications [\#440](https://github.com/infection/infection/pull/440) ([patrickfunke](https://github.com/patrickfunke))
- Add some breathing space around our logo [\#509](https://github.com/infection/infection/pull/509) ([sanmai](https://github.com/sanmai))
- Explicitly add the default profile to the list of mutators [\#507](https://github.com/infection/infection/pull/507) ([sanmai](https://github.com/sanmai))
- Do not travers plain functions unless they are in the method or closures [\#508](https://github.com/infection/infection/pull/508) ([borNfreee](https://github.com/borNfreee))
- Do not mutate interfaces [\#548](https://github.com/infection/infection/pull/548) ([sanmai](https://github.com/sanmai))
- Include the complete license in headers [\#528](https://github.com/infection/infection/pull/528) ([sanmai](https://github.com/sanmai))

## [0.10.0](https://github.com/infection/infection/tree/HEAD)  (2018-08-11)

[Full Changelog](https://github.com/infection/infection/compare/0.9.3...0.10.0)

**BC Breaks:**

- Drop PHP 7.0 support [\#414](https://github.com/infection/infection/issues/414)

**Added:**

- Function and method removal mutators [\#418](https://github.com/infection/infection/pull/418) ([BackEndTea](https://github.com/BackEndTea))
- PregMatchMatches mutator [\#383](https://github.com/infection/infection/pull/383) ([BackEndTea](https://github.com/BackEndTea))
- Add infection to PHIVE \(PHAR Installation and Verification Environment\) [\#134](https://github.com/infection/infection/issues/134)
- Show time and memory info to the console output [\#390](https://github.com/infection/infection/pull/390) ([borNfreee](https://github.com/borNfreee))
- Add GPG signing for PHAR [\#436](https://github.com/infection/infection/pull/436) ([borNfreee](https://github.com/borNfreee))
- Sort logs [\#424](https://github.com/infection/infection/pull/424) ([BackEndTea](https://github.com/BackEndTea))
- Explicitly require the DOM extension [\#411](https://github.com/infection/infection/pull/411) ([BackEndTea](https://github.com/BackEndTea))

**Fixed:**

- 100% MSI reported even if a small number of uncovered mutations is found [\#426](https://github.com/infection/infection/issues/426)
- Round MSI down to the smallest integer, instead of nearest [\#431](https://github.com/infection/infection/pull/431) ([sanmai](https://github.com/sanmai))
- Space in directory path breaks Infection [\#413](https://github.com/infection/infection/issues/413)
- BadgeLogger now reports exact errors [\#405](https://github.com/infection/infection/pull/405) ([sanmai](https://github.com/sanmai))
- Quiet mode is as quiet as one can expect it to be [\#403](https://github.com/infection/infection/issues/403)
- Fix exclude to excludes for generated config file [\#423](https://github.com/infection/infection/pull/423) ([BackEndTea](https://github.com/BackEndTea))
- Brew installation isn't supported anymore or docs aren't updated [\#416](https://github.com/infection/infection/issues/416)

## [0.9.0](https://github.com/infection/infection/tree/HEAD) (2018-07-02)

[Full Changelog](https://github.com/infection/infection/compare/0.8.2...0.9.0)

**BC Breaks:**

- Use textual version of log verbosity [\265]()https://github.com/infection/infection/pull/265)

**Added:**

- Feature: Profiles [\#220](https://github.com/infection/infection/pull/220)
- Mutation badge [\#207](https://github.com/infection/infection/pull/207)
- Feature:disable certain mutators [\#177](https://github.com/infection/infection/pull/177)
- Add reflection classes to mutators. Do no mutate public method visibility if parent has the same one [\#67](https://github.com/infection/infection/pull/67)
- [Mutator] IdenticalEqual and NotIdenticalNotEqual mutators [\#298](https://github.com/infection/infection/pull/298)
- [Mutator] Remove type cast operators [\#297](https://github.com/infection/infection/pull/297)
- [Mutator] Add mutator that removes finally {} block [\#261](https://github.com/infection/infection/pull/261)
- [Mutator] Yield Mutator [\#242](https://github.com/infection/infection/pull/242)
- [Mutator] ArrayItem Mutator [\#240](https://github.com/infection/infection/pull/240)
- [Mutator] Add a For Loop mutator & test [\#230](https://github.com/infection/infection/pull/230)
- [Mutator] Add Assignment Equal Mutator [\#229](https://github.com/infection/infection/pull/229)
- [Mutator] Add Assignment Mutator [\#228](https://github.com/infection/infection/pull/228)
- [Mutator] Add the first regex mutator [\#333](https://github.com/infection/infection/pull/333) ([BackEndTea](https://github.com/BackEndTea))
- Use PHP-Parser 4 to preserve mutated code formatting [\#55](https://github.com/infection/infection/issues/55)
- \[epic\] Scope and deploy signed PHAR [\#338](https://github.com/infection/infection/issues/338)
- Add a per mutator logging option [\#346](https://github.com/infection/infection/pull/346) ([BackEndTea](https://github.com/BackEndTea))
- Update travis config to deploy PHAR and pubkey on releases [\#353](https://github.com/infection/infection/pull/353) ([borNfreee](https://github.com/borNfreee))


**Changed:**

- Xdebug/ phpdbg check should be later in the infection process [\#325](https://github.com/infection/infection/issues/325)
- Stop mutation of abstract methods default parameters [\#361](https://github.com/infection/infection/pull/361) ([BackEndTea](https://github.com/BackEndTea))
- Add a test to check whether classes are unit tested [\#360](https://github.com/infection/infection/pull/360) ([BackEndTea](https://github.com/BackEndTea))
- Clean up after Mutation testing has been finished [\#357](https://github.com/infection/infection/pull/357) ([sidz](https://github.com/sidz))
- Simplify the EventDispatcher [\#348](https://github.com/infection/infection/pull/348) ([BackEndTea](https://github.com/BackEndTea))


**Fixed:**

- infection ignores phpunit.xml bootstrap file [\#320](https://github.com/infection/infection/issues/320)
- False positive when calculation is in multiple lines [\#366](https://github.com/infection/infection/issues/366)
- Allow Absolute Path to phpunit.xml\(.dist\) [\#387](https://github.com/infection/infection/pull/387) ([adeptofvoltron](https://github.com/adeptofvoltron))
- Account for statements spanning multiple lines [\#375](https://github.com/infection/infection/pull/375) ([sanmai](https://github.com/sanmai))
- Look for .bat on all platforms [\#374](https://github.com/infection/infection/pull/374) ([sanmai](https://github.com/sanmai))
- False positive: count\(-1\) \(decrement integer mutator\) [\#364](https://github.com/infection/infection/issues/364)
- Infection works incorrectly on Windows [\#351](https://github.com/infection/infection/issues/351)
- Weird behaviour in Docker container [\#332](https://github.com/infection/infection/issues/332)
- Initial test suite may fail to deliver coverage, e.g. under Docker [\#306](https://github.com/infection/infection/issues/306)
- Infection infects /tmp [\#356](https://github.com/infection/infection/issues/356)
- Infection does not correctly detect whether phpunit is php-executable [\#300](https://github.com/infection/infection/issues/300)
- Infection can't report on effectiveness of mutators [\#271](https://github.com/infection/infection/issues/271)
- Do not decrement integer 0 when it is being compared with the result of count\(\) [\#365](https://github.com/infection/infection/pull/365) ([borNfreee](https://github.com/borNfreee))

## [0.8.0](https://github.com/infection/infection/tree/0.8.0) (2018-02-27)

[Full Changelog](https://github.com/infection/infection/compare/0.7.1...0.8.0)

**BC Breaks:**

- Make paths in config file relative to config file [\#165](https://github.com/infection/infection/pull/165) ([BackEndTea](https://github.com/BackEndTea))
- Remove `exclude` option from config [\#155](https://github.com/infection/infection/pull/155) ([sidz](https://github.com/sidz))

**Implemented enhancements:**

- Implement integer increment and decrement mutators [\#152](https://github.com/infection/infection/pull/152) ([localheinz](https://github.com/localheinz))
- Implement Throw mutator [\#164](https://github.com/infection/infection/pull/164) ([BackEndTea](https://github.com/BackEndTea))
- Use existing coverage reports [\#176](https://github.com/infection/infection/pull/176) ([borNfreee](https://github.com/borNfreee))
- Do not require Xdebug/phpdbg when existing coverage is provided  [\#183](https://github.com/infection/infection/pull/183) ([sidz](https://github.com/sidz))
- Add an option to pass additional parameters to the PHP binary [\#185](https://github.com/infection/infection/pull/185) ([sidz](https://github.com/sidz))
- Allow relative path for `tmpDir` config setting.  [\#151](https://github.com/infection/infection/pull/151) ([borNfreee](https://github.com/borNfreee))
- Allow coverage of function signatures of traits [\#191](https://github.com/infection/infection/pull/191) ([BackEndTea](https://github.com/BackEndTea))

**Performance:**

- Fix performance issue in SourceFilesFinder [\#186](https://github.com/infection/infection/pull/186) ([borNfreee](https://github.com/borNfreee))
- Reuse created mutant files to avoid traversing and pretty printing [\#184](https://github.com/infection/infection/pull/184) ([borNfreee](https://github.com/borNfreee))

**Fixed bugs:**

- Windows can't open this file when composer.phar is found [\#196](https://github.com/infection/infection/issues/196)
- Infection ignores coverage on trait\(s\) [\#189](https://github.com/infection/infection/issues/189)
- Existing coverage: "cannot load zend opcache" [\#182](https://github.com/infection/infection/issues/182)
- Phpunit "excludes" are not properly parsed [\#167](https://github.com/infection/infection/issues/167)
- Allow coverage of function signatures of traits [\#191](https://github.com/infection/infection/pull/191) ([BackEndTea](https://github.com/BackEndTea))
- Fix: Don't mutate abstract methods [\#169](https://github.com/infection/infection/pull/169) ([BackEndTea](https://github.com/BackEndTea))
- Don't try to expand directories with `\*` and `\*\*` \(glob pattern\) [\#171](https://github.com/infection/infection/pull/171) ([sidz](https://github.com/sidz))


## [0.7.1](https://github.com/infection/infection/tree/0.7.1) (2018-02-02)

[Full Changelog](https://github.com/infection/infection/compare/0.7.0...0.7.1)

**Deprecated:**

- `exclude` option in `infection.json` is  **Deprecated!** and will be removed in `0.8.0`. Use `excludes` instead

**Implemented enhancements:**

- PHPUnit ^7.0 support
- Remove tests/test folders when infection is run for root directory. W… [\#117](https://github.com/infection/infection/pull/117) ([borNfreee](https://github.com/borNfreee))
- Config setting for temp files [\#140](https://github.com/infection/infection/pull/140) ([sidz](https://github.com/sidz))
- Make Humbug's config to be compatible with Infection [\#120](https://github.com/infection/infection/pull/120) ([sidz](https://github.com/sidz))
- added new types of logs (debug, summary) [\#135](https://github.com/infection/infection/pull/135) ([BackEndTea](https://github.com/BackEndTea))

**Fixed bugs:**

- PHP DOM Extension not working when explicitly enabled twice [\#125](https://github.com/infection/infection/issues/125)
- Infection not working if using custom printer [\#108](https://github.com/infection/infection/issues/108)
- Infection not working with phpdbg [\#106](https://github.com/infection/infection/issues/106)
- Startup problem [\#104](https://github.com/infection/infection/issues/104)
- Default values of functions not being found by coverage [\#101](https://github.com/infection/infection/issues/101)
- disable colors options [\#99](https://github.com/infection/infection/issues/99)
- Mutations not working well with function\_exists [\#97](https://github.com/infection/infection/issues/97)
- Not covered mutant with `switch(true) -> switch(false)` mutation [\#34](https://github.com/infection/infection/issues/34)
- Fix: mutate methods but not functions [\#113](https://github.com/infection/infection/pull/113) ([BackEndTea](https://github.com/BackEndTea))

**Merged pull requests:**

- Feature: e2e tests [\#135](https://github.com/infection/infection/pull/135) ([BackEndTea](https://github.com/BackEndTea))
- Enhancement: Keep packages sorted [\#149](https://github.com/infection/infection/pull/149) ([localheinz](https://github.com/localheinz))
- Enhancement: Allow to install sebastian/diff:^3.0 [\#148](https://github.com/infection/infection/pull/148) ([localheinz](https://github.com/localheinz))
- Rework InfectionApplication class and remove 'application' service [\#146](https://github.com/infection/infection/pull/146) ([sidz](https://github.com/sidz))
- Skip XdebugHandlerTest when infection is running via phpdbg [\#145](https://github.com/infection/infection/pull/145) ([sidz](https://github.com/sidz))
- Fix: Disable xdebug before composer analyze on travis [\#136](https://github.com/infection/infection/pull/136) ([BackEndTea](https://github.com/BackEndTea))
- Fix: Use actual name [\#133](https://github.com/infection/infection/pull/133) ([localheinz](https://github.com/localheinz))
- Update IOException [\#132](https://github.com/infection/infection/pull/132) ([BackEndTea](https://github.com/BackEndTea))
- Update symfony/process as it has an issue which introduced by 3.4.2 [\#131](https://github.com/infection/infection/pull/131) ([sidz](https://github.com/sidz))
- Give higher priority to custom config path [\#130](https://github.com/infection/infection/pull/130) ([BackEndTea](https://github.com/BackEndTea))
- Ignore fixtures from cs fixer [\#128](https://github.com/infection/infection/pull/128) ([BackEndTea](https://github.com/BackEndTea))
- Add tests [\#124](https://github.com/infection/infection/pull/124) ([BackEndTea](https://github.com/BackEndTea))
- Fix: Change is function signature check [\#123](https://github.com/infection/infection/pull/123) ([BackEndTea](https://github.com/BackEndTea))
- Add header comment to php-cs fixer [\#119](https://github.com/infection/infection/pull/119) ([BackEndTea](https://github.com/BackEndTea))
- Move Files folder into the Fixtures folder [\#118](https://github.com/infection/infection/pull/118) ([BackEndTea](https://github.com/BackEndTea))
- fix small typos [\#116](https://github.com/infection/infection/pull/116) ([teiling88](https://github.com/teiling88))
- Update gitattributes [\#112](https://github.com/infection/infection/pull/112) ([BackEndTea](https://github.com/BackEndTea))
- Fix: remove printer attributes from phpunit [\#110](https://github.com/infection/infection/pull/110) ([BackEndTea](https://github.com/BackEndTea))
- Fix phpdbg issue caused by xdebug disabling feature [\#107](https://github.com/infection/infection/pull/107) ([sidz](https://github.com/sidz))
- Parse only PHP files by default [\#105](https://github.com/infection/infection/pull/105) ([borNfreee](https://github.com/borNfreee))
- Add credits [\#102](https://github.com/infection/infection/pull/102) ([theofidry](https://github.com/theofidry))
- Fix issue with --no-ansi flag [\#100](https://github.com/infection/infection/pull/100) ([sidz](https://github.com/sidz))
- PHPSPEC -  If you had a custom bootstrap file in the phpspec.yml it would generate the autoload without the semicolon. [\#98](https://github.com/infection/infection/pull/98) ([AliceIW](https://github.com/AliceIW))

## [0.7.0](https://github.com/infection/infection/tree/0.7.0) (2017-12-22)
[Full Changelog](https://github.com/infection/infection/compare/0.6.2...0.7.0)

**Performance:**

- Disable xdebug for all php processes except code coverage generator [\#85](https://github.com/infection/infection/pull/85) ([sidz](https://github.com/sidz))
- Parse each source file just 1 time, cache original file AST [\#95](https://github.com/infection/infection/pull/95) ([borNfreee](https://github.com/borNfreee))

**Merged pull requests:**

- Symfony components 4 [\#71](https://github.com/infection/infection/pull/71) ([luispabon](https://github.com/luispabon))


**Fixed bugs:**

- Fix issue when custom path for test framework exists [\#93](https://github.com/infection/infection/pull/93) ([sidz](https://github.com/sidz))

**Closed issues:**

- Add comma separated files filter [\#84](https://github.com/infection/infection/pull/84) ([Landerstraeten](https://github.com/Landerstraeten))
- Is Infection compatible with PHPUnit 5.x ? [\#83](https://github.com/infection/infection/issues/83)
- Feature Request: Add line numbers to diffs on Escaped mutants [\#72](https://github.com/infection/infection/issues/72)
- \[WIP\] Optimize PHP files parsing [\#86](https://github.com/infection/infection/issues/86)


## [0.6.2](https://github.com/infection/infection/tree/0.6.2) (2017-11-18)
[Full Changelog](https://github.com/infection/infection/compare/0.6.1...0.6.2)

**Implemented enhancements:**

- Console logger output format to be compatible with TextFile logger format [\#80](https://github.com/infection/infection/pull/80) ([sidz](https://github.com/sidz))

**Fixed bugs:**

- Do not return path of config file when dir is expected.  [\#82](https://github.com/infection/infection/pull/82) ([borNfreee](https://github.com/borNfreee))

**Closed issues:**

- Uncovered Mutations not logged? [\#78](https://github.com/infection/infection/issues/78)

**Merged pull requests:**

- Improve the order script execution for travis [\#81](https://github.com/infection/infection/pull/81) ([sidz](https://github.com/sidz))

## [0.6.1](https://github.com/infection/infection/tree/0.6.1) (2017-11-18)

[Full Changelog](https://github.com/infection/infection/compare/0.6.0...0.6.1)

**Performance:**
- Reuse Parser, Lexer, PrettyPrinter [\#76](https://github.com/infection/infection/pull/76) ([borNfreee](https://github.com/borNfreee))
- Skip `composer config bin-dir` check if custom path exists [\#66](https://github.com/infection/infection/pull/66) ([sidz](https://github.com/sidz))

**Developer Experience (DX):**
- Display test framework output when initial tests fail [\#65](https://github.com/infection/infection/pull/65) ([borNfreee](https://github.com/borNfreee))
- Show fatal errors in the console and file logs [\#64](https://github.com/infection/infection/pull/64) ([borNfreee](https://github.com/borNfreee))
- Add Log verbosity [\#56](https://github.com/infection/infection/pull/56) ([sidz](https://github.com/sidz))
- Infection can be installed via Homebrew on MacOS

**Fixed bugs:**

- Warning with empty PHPUnit bootstrap [\#74](https://github.com/infection/infection/issues/74)
- PublicVisibility mutator seen as escaped mutant for a class implementing an interface [\#60](https://github.com/infection/infection/issues/60)
- Source files outside the src folder always skipped [\#57](https://github.com/infection/infection/issues/57)
- Show correct type of error message for CI flags [\#68](https://github.com/infection/infection/pull/68) ([dmecke](https://github.com/dmecke))

**Closed issues:**

- Tests do not pass. Error code 2. "Misuse of shell builtins". STDERR [\#61](https://github.com/infection/infection/issues/61)
- Hide killed mutants in output log? [\#54](https://github.com/infection/infection/issues/54)
- Tests do not pass. Error code 255. "Unknown error". STDERR: [\#43](https://github.com/infection/infection/issues/43)
- Coverage data missing [\#30](https://github.com/infection/infection/issues/30)

**Other merged pull requests:**

- PHPUnit bootstrap attribute fix [\#77](https://github.com/infection/infection/pull/77) ([borNfreee](https://github.com/borNfreee))
- Improve coding style [\#73](https://github.com/infection/infection/pull/73) ([Landerstraeten](https://github.com/Landerstraeten))
- Small code cleanup changes [\#70](https://github.com/infection/infection/pull/70) ([Landerstraeten](https://github.com/Landerstraeten))
- Add PHP 7.2 to Travis and AppVeyor matrix [\#69](https://github.com/infection/infection/pull/69) ([borNfreee](https://github.com/borNfreee))
- Mark test with Fatal Error as failed and such Mutant as killed.  [\#62](https://github.com/infection/infection/pull/62) ([borNfreee](https://github.com/borNfreee))
- Fix PHPSpec initial yaml config generate logic [\#59](https://github.com/infection/infection/pull/59) ([borNfreee](https://github.com/borNfreee))
- Fix hardcoded framework name [\#58](https://github.com/infection/infection/pull/58) ([Landerstraeten](https://github.com/Landerstraeten))

## [0.6.0](https://github.com/infection/infection/tree/0.6.0) (2017-10-09)
[Full Changelog](https://github.com/infection/infection/compare/0.5.3...0.6.0)

**Closed issues:**

- \[New Mutator\] Swap arguments in the Spaceship operator [\#47](https://github.com/infection/infection/issues/47)

**Merged pull requests:**

- Upgrade Mockery to ^1.0 [\#50](https://github.com/infection/infection/pull/50) ([borNfreee](https://github.com/borNfreee))
- Compatibility with PHPUnit 6.4 [\#49](https://github.com/infection/infection/pull/49) ([morozov](https://github.com/morozov))
- Add composer script for static analyzing tools [\#46](https://github.com/infection/infection/pull/46) ([borNfreee](https://github.com/borNfreee))
- Fix performance bottleneck by introducing a simple instance-level object cache [\#44](https://github.com/infection/infection/pull/44) ([borNfreee](https://github.com/borNfreee))
- Zero iteration mutator [\#52](https://github.com/infection/infection/pull/52) ([sidz](https://github.com/sidz))
- Add Break-Continue mutators [\#51](https://github.com/infection/infection/pull/51) ([sidz](https://github.com/sidz))
- Swap arguments on spaceship operator [\#48](https://github.com/infection/infection/pull/48) ([marcosh](https://github.com/marcosh))

## [0.5.3](https://github.com/infection/infection/tree/0.5.3) (2017-09-15)
[Full Changelog](https://github.com/infection/infection/compare/0.5.2...0.5.3)

**Implemented enhancements:**

- Display PHPUnit/PHPSpec version under what tests are run [\#31](https://github.com/infection/infection/issues/31)
- Provide meaningful feedback on failure [\#29](https://github.com/infection/infection/issues/29)
- Add exceptions handling with printing trace for verbose level. [\#39](https://github.com/infection/infection/pull/39) ([borNfreee](https://github.com/borNfreee))

**Closed issues:**

- license is weird [\#35](https://github.com/infection/infection/issues/35)
- request: support phpdbg [\#36](https://github.com/infection/infection/issues/36)

**Merged pull requests:**

- remove timeout from initial test run [\#41](https://github.com/infection/infection/pull/41) ([JanPietrzyk](https://github.com/JanPietrzyk))
- Interface mutations fix [\#32](https://github.com/infection/infection/pull/32) ([oxidmod](https://github.com/oxidmod))
- Add option to pass test framework extra options [\#42](https://github.com/infection/infection/pull/42) ([borNfreee](https://github.com/borNfreee))
- Add Filesystem and add Directory Check for logs path. [\#40](https://github.com/infection/infection/pull/40) ([sidz](https://github.com/sidz))
- Support phpdbg [\#37](https://github.com/infection/infection/pull/37) ([keradus](https://github.com/keradus))
- Add test framework version to console [\#33](https://github.com/infection/infection/pull/33) ([borNfreee](https://github.com/borNfreee))

## [0.5.2](https://github.com/infection/infection/tree/0.5.2) (2017-09-02)
[Full Changelog](https://github.com/infection/infection/compare/0.5.1...0.5.2)

**Fixed bugs:**

- SourceDirGuesser failure [\#23](https://github.com/infection/infection/issues/23)

**Closed issues:**

- Run as project dependency [\#27](https://github.com/infection/infection/issues/27)
- Dependency on sebastian/diff [\#21](https://github.com/infection/infection/issues/21)

**Merged pull requests:**

- Add ISSUE\_TEMPLATE.md to get all required info from users [\#26](https://github.com/infection/infection/pull/26) ([borNfreee](https://github.com/borNfreee))
- Added option '-c|--configuration' for custom configuration file path. [\#28](https://github.com/infection/infection/pull/28) ([corpsee](https://github.com/corpsee))
- Fix autoload section from composer.json and allow to use multiple paths [\#25](https://github.com/infection/infection/pull/25) ([sidz](https://github.com/sidz))
- Fixed bootstrap.php for case with install by Composer as dependency and run from vendor/bin [\#22](https://github.com/infection/infection/pull/22) ([corpsee](https://github.com/corpsee))

## [0.5.1](https://github.com/infection/infection/tree/0.5.1) (2017-08-20)
[Full Changelog](https://github.com/infection/infection/compare/0.5.0...0.5.1)

**Merged pull requests:**

- Auto add coverage filter whitelist for phpunit.xml.dist to make it possible to analyze coverage [\#20](https://github.com/infection/infection/pull/20) ([borNfreee](https://github.com/borNfreee))
- Smart `ReturnValue` mutators [\#19](https://github.com/infection/infection/pull/19) ([borNfreee](https://github.com/borNfreee))

## [0.5.0](https://github.com/infection/infection/tree/0.5.0) (2017-08-08)
[Full Changelog](https://github.com/infection/infection/compare/0.4.0...0.5.0)

**Merged pull requests:**

- Add whitelist for executed mutators. New option --mutators=X,Yy,Zzz [\#18](https://github.com/infection/infection/pull/18) ([borNfreee](https://github.com/borNfreee))
- Public-\>protected, protected-\>private Visibility Mutators [\#17](https://github.com/infection/infection/pull/17) ([borNfreee](https://github.com/borNfreee))

## [0.4.0](https://github.com/infection/infection/tree/0.4.0) (2017-07-27)
[Full Changelog](https://github.com/infection/infection/compare/0.3.0...0.4.0)

**Closed issues:**

- Location of test framework [\#4](https://github.com/infection/infection/issues/4)

**Merged pull requests:**

- Add --min-msi and --min-covered-msi options to control MSI in CI and fail builds [\#16](https://github.com/infection/infection/pull/16) ([borNfreee](https://github.com/borNfreee))
- Allow to exclude files, not only dirs in the `infection.json` config file [\#15](https://github.com/infection/infection/pull/15) ([borNfreee](https://github.com/borNfreee))
- Fix issues reported by PHPStan. Run it for each build [\#14](https://github.com/infection/infection/pull/14) ([borNfreee](https://github.com/borNfreee))
- Add php-cs-fixer config, apply fixes [\#13](https://github.com/infection/infection/pull/13) ([borNfreee](https://github.com/borNfreee))
- Add arithmetic tests [\#12](https://github.com/infection/infection/pull/12) ([borNfreee](https://github.com/borNfreee))
- Fix build on Windows. Integrate Appveyor [\#10](https://github.com/infection/infection/pull/10) ([borNfreee](https://github.com/borNfreee))

## [0.3.0](https://github.com/infection/infection/tree/0.3.0) (2017-07-14)
[Full Changelog](https://github.com/infection/infection/compare/0.2.1...0.3.0)

**Fixed bugs:**

- Timeout [\#6](https://github.com/infection/infection/issues/6)
- Uncaught Error: Call to a member function appendChild\(\) on null [\#5](https://github.com/infection/infection/issues/5)

**Merged pull requests:**

- Add possibility to set custom PHPUnit executable path [\#9](https://github.com/infection/infection/pull/9) ([borNfreee](https://github.com/borNfreee))
- Pass timeout setting to Initial Process builder to control test suite [\#8](https://github.com/infection/infection/pull/8) ([borNfreee](https://github.com/borNfreee))
- Handle situation when PHPUnit \<testsuite /\> node is placed directly inside the root node [\#7](https://github.com/infection/infection/pull/7) ([borNfreee](https://github.com/borNfreee))

## [0.2.1](https://github.com/infection/infection/tree/0.2.1) (2017-07-11)
[Full Changelog](https://github.com/infection/infection/compare/0.2.0...0.2.1)

**Merged pull requests:**

- Add \Phar::loadPhar\(\) in custom autoloader with Stream Interceptor [\#3](https://github.com/infection/infection/pull/3) ([borNfreee](https://github.com/borNfreee))

## [0.2.0](https://github.com/infection/infection/tree/0.2.0) (2017-07-08)
[Full Changelog](https://github.com/infection/infection/compare/0.1.0...0.2.0)

**Closed issues:**

- Phar distribution [\#1](https://github.com/infection/infection/issues/1)

**Merged pull requests:**

- Phar distribution [\#2](https://github.com/infection/infection/pull/2) ([borNfreee](https://github.com/borNfreee))

## [0.1.0](https://github.com/infection/infection/tree/0.1.0) (2017-07-01)


\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*
