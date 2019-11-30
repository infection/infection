# Change Log

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
