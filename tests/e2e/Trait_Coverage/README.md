# Correct Coverage of Traits

* https://github.com/infection/infection/issues/189

## Summary
Although coverage of traits is correctly set in the coverage files, infections thinks some mutants are not covered, even though they are

## Full Ticket

| Question    | Answer
| ------------| ---------------
| Infection version | 0.7.1
| Test Framework version | PHPUnit 6.5.6 
| PHP version | PHP 7.1.14-1+ubuntu16.04.1+deb.sury.org+1
| Platform    | Ubuntu 16.04.3 LTS
| Github Repo | https://github.com/addiks/symfony_rdm

Infection seems to ignore coverage on traits and just marks them as "not covered" even if they are marked as covered in the coverage-xml. The phpunit-test's also fail when i apply the proposed mutant-patch and execute the tests. That proves that the trait actually was executed in the tests.

To demonstrate the issue as directly as possible i have created an example in my most recent project on github in a separate branch. You can see the [failing build here](https://travis-ci.org/addiks/symfony_rdm/builds/343762710). In that build you can also see the actual error and how infection get's executed.

Also see: [The trait getting tested](https://github.com/addiks/symfony_rdm/blob/example_for_infection_trait_issue/Symfony/ExampleTrait.php), [the class implementing the trait that get's tested](https://github.com/addiks/symfony_rdm/blob/example_for_infection_trait_issue/Symfony/ExampleClass.php) and the [test that test's them both](https://github.com/addiks/symfony_rdm/blob/example_for_infection_trait_issue/Tests/Symfony/ExampleClassTest.php) and the  [coverage-xml that states that the trait actually get's executed](https://github.com/infection/infection/files/1739768/infection-trait-coverage.xml.zip) (lines 968 to 978).

<!-- Please past your phpunit.xml[.dist] if no Github link to the repo provided -->
<details>
 <summary>phpunit.xml</summary>
 
 ```xml
<?xml version="1.0" encoding="UTF-8"?>

<!-- https://phpunit.de/manual/current/en/appendixes.configuration.html -->
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/4.8/phpunit.xsd"
         backupGlobals="false"
         colors="true"
         bootstrap="vendor/autoload.php"
>

    <testsuites>
        <testsuite name="Addiks-RDMBundle">
            <directory>Tests</directory>
        </testsuite>
    </testsuites>

    <filter>
        <whitelist>
            <directory>DataLoader</directory>
            <directory>Doctrine</directory>
            <directory>Exception</directory>
            <directory>Hydration</directory>
            <directory>Mapping</directory>
            <directory>Symfony</directory>
            <directory>ValueResolver</directory>
            <exclude>
                <directory>vendor</directory>
            </exclude>
        </whitelist>
    </filter>

</phpunit>
 ```
</details>

<!-- Remove this section if not needed -->
<details>
 <summary>Output with issue</summary>
 
 ```bash
$ vendor/bin/infection -s -vv --min-msi=100
    ____      ____          __  _
   /  _/___  / __/__  _____/ /_(_)___  ____ 
   / // __ \/ /_/ _ \/ ___/ __/ / __ \/ __ \
 _/ // / / / __/  __/ /__/ /_/ / /_/ / / / /
/___/_/ /_/_/  \___/\___/\__/_/\____/_/ /_/
 
    0 [>---------------------------] < 1 secRunning initial test suite...

PHPUnit version: 6.5.6

   88 [============================] 7 secs

Generate mutants...

Processing source code files: 49/49
Creating mutated files and processes: 174/174
.: killed, M: escaped, S: uncovered, E: fatal error, T: timed out

...EEEEE..........EEEE........................EE..   ( 50 / 174)
..E...EEE...EE.E.EEE.E...EEEEEEE...E......EEEE....   (100 / 174)
E................E......EEE.E.EEEE.S....EEEE.EE...   (150 / 174)
.E...E...E.EE...E.EEEEEE                             (174 / 174)
Escaped mutants:
================


Not covered mutants:
====================


1) /home/travis/build/addiks/symfony_rdm/Symfony/ExampleTrait.php:28    [M] PublicVisibility

--- Original
+++ New
@@ @@
-    public function getFoo() : string
+    protected function getFoo() : string



174 mutations were generated:
     111 mutants were killed
       1 mutants were not covered by tests
       0 covered mutants were not detected
      62 errors were encountered
       0 time outs were encountered

Metrics:
         Mutation Score Indicator (MSI): 99%
         Mutation Code Coverage: 99%
         Covered Code MSI: 100%

Please note that some mutants will inevitably be harmless (i.e. false positives).
                                                                                
 [ERROR] The minimum required MSI percentage should be 100%, but actual is 99%. 
         Improve your tests!                                                    
                                                                                
The command "vendor/bin/infection -s -vv --min-msi=100" exited with 1.
 ```
</details>

<details>
 <summary>PHPUnit output with applied mutant-patch</summary>

```bash
$ vendor/bin/phpunit
PHPUnit 6.5.6 by Sebastian Bergmann and contributors.

............................................................E.... 65 / 82 ( 79%)
.................                                                 82 / 82 (100%)

Time: 351 ms, Memory: 14.00MB

There was 1 error:

1) Addiks\RDMBundle\Tests\Symfony\ExampleClassTest::shouldHaveFoo
Error: Call to protected method Addiks\RDMBundle\Symfony\ExampleClass::getFoo() from context 'Addiks\RDMBundle\Tests\Symfony\ExampleClassTest'

/usr/workspace/Privat/SymfonyRDM/Tests/Symfony/ExampleClassTest.php:34

ERRORS!
Tests: 82, Assertions: 109, Errors: 1.
```
</details>