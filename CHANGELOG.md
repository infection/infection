# Change Log

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