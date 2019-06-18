.DEFAULT_GOAL := help

.PHONY: help
# TODO: add a check to ensure the result is always the expected one
# TODO: add a check to ensure whenever a PHONY is declared, the following target has the same name; e.g. here .PHONY=help
# so the next target should be `help:`. Otherwise this is most certainly a mistake.
help:
	@echo "\033[33mUsage:\033[0m\n  make TARGET\n\n\033[32m#\n# Commands\n#---------------------------------------------------------------------------\033[0m\n"
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | awk 'BEGIN {FS = ":"}; {printf "\033[33m%s:\033[0m%s\n", $$1, $$2}'


#
# Variables
#---------------------------------------------------------------------------
BOX=./.tools/box
BOX_URL="https://github.com/humbug/box/releases/download/3.8.0/box.phar"

PHP_CS_FIXER=./.tools/php-cs-fixer
PHP_CS_FIXER_URL="https://cs.sensiolabs.org/download/php-cs-fixer-v2.phar"
PHP_CS_FIXER_CACHE=build/cache/.php_cs.cache

PHPSTAN=./.tools/phpstan
PHPSTAN_URL="https://github.com/phpstan/phpstan/releases/download/0.11.5/phpstan.phar"

PHPUNIT=vendor/bin/phpunit

INFECTION=./build/infection.phar

DOCKER_RUN=docker run --tty --rm --volume "$$PWD":/opt/infection --workdir /opt/infection
DOCKER_RUN_72=$(FLOCK) devTools/*php72*.json $(DOCKER_RUN) infection_php72
DOCKER_RUN_72_IMAGE=devTools/Dockerfile-php72-xdebug.json
DOCKER_RUN_73=$(FLOCK) devTools/*php73*.json $(DOCKER_RUN) infection_php73
DOCKER_RUN_73_IMAGE=devTools/Dockerfile-php73-xdebug.json

FLOCK=./devTools/flock


#
# Commands (phony targets)
#---------------------------------------------------------------------------

.PHONY: compile
compile:	## Bundles Infection into a PHAR
compile: $(INFECTION)

.PHONY: cs-fix
cs-fix:	  	## Runs PHP-CS-Fixer
cs-fix: $(PHP_CS_FIXER)
	$(PHP_CS_FIXER) fix -v --cache-file=$(PHP_CS_FIXER_CACHE)

.PHONY: cs-check
cs-check:	## Runs PHP-CS-Fixer in dry mode
cs-check: $(PHP_CS_FIXER)
	$(PHP_CS_FIXER) fix -v --cache-file=$(PHP_CS_FIXER_CACHE) --dry-run --stop-on-violation

.PHONY: phpstan
phpstan:  	## Runs PHPStan
phpstan: vendor $(PHPSTAN)
	$(PHPSTAN) analyse src --level=max --configuration ./devTools/phpstan-src.neon --no-interaction --no-progress
	$(PHPSTAN) analyse tests --level=4 --configuration ./devTools/phpstan-tests.neon --no-interaction --no-progress

.PHONY: analyze
analyze:	## Runs CS fixers, static analyzers and various other checks
analyze: cs-check analyze-ci

.PHONY: analyze-ci
analyze-ci:	## Runs static analyzers and various other checks
analyze-ci: phpstan validate

.PHONY: validate
validate:	## Checks that the composer.json file is valid
validate:
	composer validate --strict

.PHONY: test
test:		## Runs all the tests
# TODO: add a test to ensure we are not missing targets here
test: test-unit test-e2e-phpdbg test-e2e-xdebug test-infection-phpdbg test-infection-xdebug

.PHONY: test-unit
test-unit:	## Runs the unit tests
test-unit: test-unit-72 test-unit-73

.PHONY: test-unit-72
test-unit-72: $(DOCKER_RUN_72_IMAGE) $(PHPUNIT)
	$(DOCKER_RUN_72) $(PHPUNIT)

.PHONY: test-unit-73
test-unit-73: $(DOCKER_RUN_73_IMAGE) $(PHPUNIT)
	$(DOCKER_RUN_73) $(PHPUNIT)

.PHONY: test-e2e-phpdbg
test-e2e-phpdbg:	## Runs the end-to-end tests for PHPDBG
test-e2e-phpdbg: test-e2e-phpdbg-72 test-e2e-phpdbg-73

.PHONY: test-e2e-phpdbg-72
test-e2e-phpdbg-72: $(DOCKER_RUN_72_IMAGE) $(INFECTION)
	$(DOCKER_RUN_72) env PHPDBG=1 ./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-phpdbg-73
test-e2e-phpdbg-73: $(DOCKER_RUN_73_IMAGE) $(INFECTION)
	$(DOCKER_RUN_73) env PHPDBG=1 ./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-xdebug
test-e2e-xdebug:	## Runs the end-to-end tests for xdebug
test-e2e-xdebug: test-e2e-xdebug-72 test-e2e-xdebug-73

.PHONY: test-e2e-xdebug-72
test-e2e-xdebug-72: $(DOCKER_RUN_72_IMAGE) $(INFECTION)
	$(DOCKER_RUN_72) ./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-xdebug-73
test-e2e-xdebug-73: $(DOCKER_RUN_73_IMAGE) $(INFECTION)
	$(DOCKER_RUN_73) ./tests/e2e_tests $(INFECTION)

.PHONY: test-infection-phpdbg
test-infection-phpdbg:		## Runs Infection with PHPDBG against itself
test-infection-phpdbg: test-infection-phpdbg-72 test-infection-phpdbg-73

.PHONY: test-infection-phpdbg-72
test-infection-phpdbg-72: $(DOCKER_RUN_72_IMAGE)
	# TODO: calculate the number of thread here instead of using the arbitrary number 4
	# TODO: check other occurrences
	$(DOCKER_RUN_72) phpdbg -qrr bin/infection --threads=4

.PHONY: test-infection-phpdbg-73
test-infection-phpdbg-73: $(DOCKER_RUN_73_IMAGE)
	$(DOCKER_RUN_73) phpdbg -qrr bin/infection --threads=4

.PHONY: test-infection-xdebug
test-infection-xdebug:		## Runs Infection with xdebug against itself
test-infection-xdebug: test-infection-xdebug-72 test-infection-xdebug-73

.PHONY: test-infection-xdebug-72
test-infection-xdebug-72: $(DOCKER_RUN_72_IMAGE)
	$(DOCKER_RUN_72) ./bin/infection --threads=4

.PHONY: test-infection-xdebug-73
test-infection-xdebug-73: $(DOCKER_RUN_73_IMAGE)
	$(DOCKER_RUN_73) ./bin/infection --threads=4


#
# Rules from files (non-phony targets)
#---------------------------------------------------------------------------

# TODO: some tools such as Box & PHP-CS-Fixer could be handled in a better way to ensure we are using the last version.

$(BOX):
	wget $(BOX_URL) --output-document=$(BOX)
	chmod a+x $(BOX)
	touch $@

$(PHP_CS_FIXER):
	wget $(PHP_CS_FIXER_URL) --output-document=$(PHP_CS_FIXER)
	chmod a+x $(PHP_CS_FIXER)
	touch $@

$(PHPSTAN):
	wget $(PHPSTAN_URL) --output-document=$(PHPSTAN)
	chmod a+x $(PHPSTAN)
	touch $@

$(INFECTION): vendor $(shell find bin/ src/ -type f) $(BOX) box.json.dist .git/HEAD
	$(BOX) validate
	$(BOX) compile
	touch $@

vendor: composer.lock
	composer install
	touch $@

composer.lock: composer.json
	composer install
	touch $@

$(PHPUNIT): vendor
	touch $@

$(DOCKER_RUN_72_IMAGE): devTools/Dockerfile-php72-xdebug
	docker build --tag infection_php72 --file devTools/Dockerfile-php72-xdebug .
	docker image inspect infection_php72 >> $(DOCKER_RUN_72_IMAGE)
	touch $@

$(DOCKER_RUN_73_IMAGE): devTools/Dockerfile-php73-xdebug
	docker build --tag infection_php73 --file devTools/Dockerfile-php73-xdebug .
	docker image inspect infection_php73 >> $(DOCKER_RUN_73_IMAGE)
	touch $@
