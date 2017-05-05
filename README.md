[![PHP Version](https://img.shields.io/badge/php-7.0%2B-blue.svg)](https://packagist.org/packages/infection/infection)

exec /usr/local/php5-7.1.0-20161202-092124/bin/php /Users/user/sites/simplehabits/vendor/phpunit/phpunit/phpunit --configuration=/var/folders/zb/crskfnsx48l9jnrd33wvc1z80000gn/T/humbug/phpunit.humbug.xml --stop-on-failure --tap

время выполнение конкретного теста (функции) 
    junit.xml

строка ИСХОДНОГО кода покрыта каким тестом (функцией в тестовом файле)
    --coverage-xml 
    --coverage-php
    
// TODO think about adding questinair with Guessers about phpunit locations (like app/phpunit.xml) and etc.____
// TODO add Text Logger to file
// TODO show "Infection.json config file has invalid json"
// TODO may be run infection for any php library and post Issue with @infection mentions to make it popular
// TODO add peridot support?
    
    
export XDEBUG_CONFIG="idekey=PHPSTORM"

собирается в пхп формате для каждого файла какая стпрока покрыта каким тестом

cat /var/folders/zb/crskfnsx48l9jnrd33wvc1z80000gn/T/humbug/phpunit.humbug.xml

<?xml version="1.0" encoding="UTF-8"?>
<!-- https://phpunit.de/manual/current/en/appendixes.configuration.html -->
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/4.8/phpunit.xsd" backupGlobals="false" colors="true" bootstrap="/var/folders/zb/crskfnsx48l9jnrd33wvc1z80000gn/T/humbug.phpunit.bootstrap.php" cacheTokens="false">
  <php>
    <ini name="error_reporting" value="-1"/>
    <server name="KERNEL_DIR" value="app/"/>
  </php>
  <testsuites>
    <testsuite name="Project Test Suite">
      <directory>/Users/user/sites/simplehabits/tests</directory>
    </testsuite>
  </testsuites>
  <listeners>
    <listener class="\MyBuilder\PhpunitAccelerator\TestListener">
      <arguments>
        <boolean>true</boolean>
      </arguments>
    </listener>
    <listener class="\Humbug\Adapter\Phpunit\Listeners\TestSuiteFilterListener">
      <arguments>
        <integer>0</integer>
        <string>/var/folders/zb/crskfnsx48l9jnrd33wvc1z80000gn/T/humbug/phpunit.times.humbug.json</string>
        <string>SimpleHabits\Domain\Model\Goal\GoalTest</string>
      </arguments>
    </listener>
  </listeners>
</phpunit>

cat /var/folders/zb/crskfnsx48l9jnrd33wvc1z80000gn/T/humbug/phpunit.times.humbug.json
{
    "suites": {
        "SimpleHabits\\Domain\\Model\\Abstinence\\DayStreakTest": 0.014634132385253906,
        "SimpleHabits\\Domain\\Model\\Goal\\GoalStepTest": 0.03519606590270996,
        "SimpleHabits\\Domain\\Model\\Goal\\GoalTest": 0.1006770133972168
    },
    "tests": {
        "SimpleHabits\\Domain\\Model\\Abstinence\\DayStreakTest": [
            {
                "title": "it_correctly_calculate_day_streak_count",
                "time": 0.010872125625610352
            },
            {
                "title": "it_correctly_returns_start_and_finish_date",
                "time": 0.0037620067596435547
            }
        ],
        "SimpleHabits\\Domain\\Model\\Goal\\GoalStepTest": [
            {
                "title": "it_correctly_returns_id",
                "time": 0.029711008071899414
            },
            {
                "title": "it_correctly_returns_value",
                "time": 0.002933979034423828
            },
            {
                "title": "it_correctly_returns_recorded_at_date",
                "time": 0.0025510787963867188
            }
        ],
        "SimpleHabits\\Domain\\Model\\Goal\\GoalTest::it_correctly_determines_increasing_flag": [
            {
                "title": "it_correctly_determines_increasing_flag with data set #0",
                "time": 0.013492107391357422
            },
            {
                "title": "it_correctly_determines_increasing_flag with data set #1",
                "time": 0.005076169967651367
            },
            {
                "title": "it_correctly_determines_increasing_flag with data set #2",
                "time": 0.004371166229248047
            }
        ],
        "SimpleHabits\\Domain\\Model\\Goal\\GoalTest::it_should_determine_whether_delta_is_positive": [
            {
                "title": "it_should_determine_whether_delta_is_positive with data set #0",
                "time": 0.0038192272186279297
            },
            {
                "title": "it_should_determine_whether_delta_is_positive with data set #1",
                "time": 0.003632068634033203
            },
            {
                "title": "it_should_determine_whether_delta_is_positive with data set #2",
                "time": 0.0037419795989990234
            },
            {
                "title": "it_should_determine_whether_delta_is_positive with data set #3",
                "time": 0.005771160125732422
            },
            {
                "title": "it_should_determine_whether_delta_is_positive with data set #4",
                "time": 0.003952980041503906
            },
            {
                "title": "it_should_determine_whether_delta_is_positive with data set #5",
                "time": 0.004166841506958008
            }
        ],
        "SimpleHabits\\Domain\\Model\\Goal\\GoalTest::it_calculates_percentage": [
            {
                "title": "it_calculates_percentage with data set #0",
                "time": 0.014976024627685547
            },
            {
                "title": "it_calculates_percentage with data set #1",
                "time": 0.005446195602416992
            },
            {
                "title": "it_calculates_percentage with data set #2",
                "time": 0.007105112075805664
            },
            {
                "title": "it_calculates_percentage with data set #3",
                "time": 0.006157875061035156
            },
            {
                "title": "it_calculates_percentage with data set #4",
                "time": 0.006916999816894531
            },
            {
                "title": "it_calculates_percentage with data set #5",
                "time": 0.006746053695678711
            },
            {
                "title": "it_calculates_percentage with data set #6",
                "time": 0.005305051803588867
            }
        ]
    }
}