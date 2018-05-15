#!/usr/bin/env sh

# Checking for this file we can see if our wrapper was really used
touch phpunit.bat.canary

vendor/bin/phpunit-actual "$@"
