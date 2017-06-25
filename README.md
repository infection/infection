[![PHP Version](https://img.shields.io/badge/php-7.0%2B-blue.svg)](https://packagist.org/packages/infection/infection)

Infection - Mutation Testing framework
=========

exec /usr/local/php5-7.1.0-20161202-092124/bin/php /Users/user/sites/simplehabits/vendor/phpunit/phpunit/phpunit --configuration=/var/folders/zb/crskfnsx48l9jnrd33wvc1z80000gn/T/humbug/phpunit.humbug.xml --stop-on-failure --tap

время выполнение конкретного теста (функции) 
    junit.xml

строка ИСХОДНОГО кода покрыта каким тестом (функцией в тестовом файле)
    --coverage-xml 
    --coverage-php
    
// TODO may be run infection for any php library and post Issue with @infection mentions to make it popular
// TODO add peridot support?
    
    
export XDEBUG_CONFIG="idekey=PHPSTORM"



1. parse junit
2. compare with reflectionClass->getFileName (time)