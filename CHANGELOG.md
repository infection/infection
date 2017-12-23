# Change Log

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
