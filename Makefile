.DEFAULT_GOAL := help

# See https://tech.davis-hansson.com/p/make/
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

.PHONY: help
help:
	@echo "\033[33mUsage:\033[0m\n  make TARGET\n\n\033[32m#\n# Commands\n#---------------------------------------------------------------------------\033[0m\n"
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | awk 'BEGIN {FS = ":"}; {printf "\033[33m%s:\033[0m%s\n", $$1, $$2}'


#
# Variables
#---------------------------------------------------------------------------
BOX=./.tools/box
BOX_URL="https://github.com/humbug/box/releases/download/3.8.4/box.phar"

PHP_CS_FIXER=./.tools/php-cs-fixer
PHP_CS_FIXER_URL="https://cs.sensiolabs.org/download/php-cs-fixer-v2.phar"
PHP_CS_FIXER_CACHE=build/cache/.php_cs.cache

PHPSTAN=./vendor/bin/phpstan

PHPUNIT=vendor/bin/phpunit

INFECTION=./build/infection.phar

DOCKER_RUN=docker run --tty --rm --volume "$$PWD":/opt/infection --workdir /opt/infection
DOCKER_RUN_72=$(FLOCK) devTools/*php72*.json $(DOCKER_RUN) infection_php72
DOCKER_RUN_72_IMAGE=devTools/Dockerfile-php72-xdebug.json
DOCKER_RUN_73=$(FLOCK) devTools/*php73*.json $(DOCKER_RUN) infection_php73
DOCKER_RUN_73_IMAGE=devTools/Dockerfile-php73-xdebug.json
DOCKER_RUN_74=$(FLOCK) devTools/*php74*.json $(DOCKER_RUN) infection_php74
DOCKER_RUN_74_IMAGE=devTools/Dockerfile-php74-xdebug.json

FLOCK=./devTools/flock
COMMIT_HASH=$(shell git rev-parse --short HEAD)


#
# Commands (phony targets)
#---------------------------------------------------------------------------

.PHONY: compile
compile:	 ## Bundles Infection into a PHAR
compile: $(INFECTION)

.PHONY: check_trailing_whitespaces
check_trailing_whitespaces:
	./devTools/check_trailing_whitespaces.sh

.PHONY: cs
cs:	  	 ## Runs PHP-CS-Fixer
cs: $(PHP_CS_FIXER)
	$(PHP_CS_FIXER) fix -v --cache-file=$(PHP_CS_FIXER_CACHE)
	LC_ALL=C sort -u .gitignore -o .gitignore
	$(MAKE) check_trailing_whitespaces

.PHONY: phpstan
phpstan: vendor $(PHPSTAN)
	$(PHPSTAN) analyse --configuration devTools/phpstan-src.neon --no-interaction --no-progress
	$(PHPSTAN) analyse --configuration devTools/phpstan-tests.neon --no-interaction --no-progress

.PHONY: validate
validate:
	composer validate --strict

.PHONY: profile
profile: 	 ## Runs Blackfire
profile: vendor tests/benchmark/MutationGenerator/sources tests/benchmark/Tracing/coverage tests/benchmark/Tracing/sources
	composer dump --classmap-authoritative
	blackfire run \
		--samples=5 \
		--title="MutationGenerator" \
		--metadata="commit=$(COMMIT_HASH)" \
		php tests/benchmark/MutationGenerator/profile.php
	blackfire run \
		--samples=5 \
		--title="Tracing" \
		--metadata="commit=$(COMMIT_HASH)" \
		php tests/benchmark/Tracing/profile.php
	composer dump


.PHONY: autoreview
autoreview: 	 ## Runs various checks (static analysis & AutoReview test suite)
autoreview: phpstan validate test-autoreview

.PHONY: test
test:		 ## Runs all the tests
test: autoreview test-unit test-e2e test-infection

.PHONY: test-autoreview
test-autoreview:
	$(PHPUNIT) --configuration=phpunit_autoreview.xml

.PHONY: test-unit
test-unit:	 ## Runs the unit tests
test-unit: test-unit-72 test-unit-73 test-unit-74

.PHONY: test-unit-72
test-unit-72: $(DOCKER_RUN_72_IMAGE) $(PHPUNIT)
	$(DOCKER_RUN_72) $(PHPUNIT) --group default

.PHONY: test-unit-73
test-unit-73: $(DOCKER_RUN_73_IMAGE) $(PHPUNIT)
	$(DOCKER_RUN_73) $(PHPUNIT) --group default

.PHONY: test-unit-74
test-unit-74: $(DOCKER_RUN_74_IMAGE) $(PHPUNIT)
	$(DOCKER_RUN_74) $(PHPUNIT) --group default

.PHONY: test-e2e
test-e2e: 	 ## Runs the end-to-end tests
test-e2e: test-e2e-phpdbg test-e2e-xdebug

.PHONY: test-e2e-phpdbg
test-e2e-phpdbg: test-e2e-phpdbg-72 test-e2e-phpdbg-73 test-e2e-phpdbg-74

.PHONY: test-e2e-phpdbg-72
test-e2e-phpdbg-72: $(DOCKER_RUN_72_IMAGE) $(INFECTION)
	$(DOCKER_RUN_72) $(PHPUNIT) --group integration,e2e
	$(DOCKER_RUN_72) env PHPDBG=1 ./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-phpdbg-73
test-e2e-phpdbg-73: $(DOCKER_RUN_73_IMAGE) $(INFECTION)
	$(DOCKER_RUN_73) $(PHPUNIT) --group integration,e2e
	$(DOCKER_RUN_73) env PHPDBG=1 ./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-phpdbg-74
test-e2e-phpdbg-74: $(DOCKER_RUN_74_IMAGE) $(INFECTION)
	$(DOCKER_RUN_74) $(PHPUNIT) --group integration,e2e
	$(DOCKER_RUN_74) env PHPDBG=1 ./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-xdebug
test-e2e-xdebug: test-e2e-xdebug-72 test-e2e-xdebug-73 test-e2e-xdebug-74

.PHONY: test-e2e-xdebug-72
test-e2e-xdebug-72: $(DOCKER_RUN_72_IMAGE) $(INFECTION)
	$(DOCKER_RUN_72) $(PHPUNIT) --group integration,e2e
	$(DOCKER_RUN_72) ./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-xdebug-73
test-e2e-xdebug-73: $(DOCKER_RUN_73_IMAGE) $(INFECTION)
	$(DOCKER_RUN_73) $(PHPUNIT) --group integration,e2e
	$(DOCKER_RUN_73) ./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-xdebug-74
test-e2e-xdebug-74: $(DOCKER_RUN_74_IMAGE) $(INFECTION)
	$(DOCKER_RUN_74) $(PHPUNIT) --group integration,e2e
	$(DOCKER_RUN_74) ./tests/e2e_tests $(INFECTION)

.PHONY: test-infection
test-infection:  ## Runs Infection against itself
test-infection: test-infection-phpdbg test-infection-xdebug

.PHONY: test-infection-phpdbg
test-infection-phpdbg: test-infection-phpdbg-72 test-infection-phpdbg-73 test-infection-phpdbg-74

.PHONY: test-infection-phpdbg-72
test-infection-phpdbg-72: $(DOCKER_RUN_72_IMAGE)
	$(DOCKER_RUN_72) phpdbg -qrr bin/infection --threads=4

.PHONY: test-infection-phpdbg-73
test-infection-phpdbg-73: $(DOCKER_RUN_73_IMAGE)
	$(DOCKER_RUN_73) phpdbg -qrr bin/infection --threads=4

.PHONY: test-infection-phpdbg-74
test-infection-phpdbg-74: $(DOCKER_RUN_74_IMAGE)
	$(DOCKER_RUN_74) phpdbg -qrr bin/infection --threads=4

.PHONY: test-infection-xdebug
test-infection-xdebug: test-infection-xdebug-72 test-infection-xdebug-73 test-infection-xdebug-74

.PHONY: test-infection-xdebug-72
test-infection-xdebug-72: $(DOCKER_RUN_72_IMAGE)
	$(DOCKER_RUN_72) ./bin/infection --threads=4

.PHONY: test-infection-xdebug-73
test-infection-xdebug-73: $(DOCKER_RUN_73_IMAGE)
	$(DOCKER_RUN_73) ./bin/infection --threads=4

.PHONY: test-infection-xdebug-74
test-infection-xdebug-74: $(DOCKER_RUN_74_IMAGE)
	$(DOCKER_RUN_74) ./bin/infection --threads=4


#
# Rules from files (non-phony targets)
#---------------------------------------------------------------------------

$(BOX): Makefile
	wget $(BOX_URL) --output-document=$(BOX)
	chmod a+x $(BOX)
	touch $@

$(PHP_CS_FIXER): Makefile
	wget $(PHP_CS_FIXER_URL) --output-document=$(PHP_CS_FIXER)
	chmod a+x $(PHP_CS_FIXER)
	touch $@

$(PHPSTAN): vendor

$(INFECTION): vendor $(shell find bin/ src/ -type f) $(BOX) box.json.dist .git/HEAD
	composer require infection/codeception-adapter infection/phpspec-adapter
	$(BOX) validate
	$(BOX) compile
	composer remove infection/codeception-adapter infection/phpspec-adapter
	touch -c $@

vendor: composer.lock
	composer install
	touch $@

composer.lock: composer.json
	composer install
	touch -c $@

$(PHPUNIT): vendor
	touch -c $@

$(DOCKER_RUN_72_IMAGE): devTools/Dockerfile-php72-xdebug
	docker build --tag infection_php72 --file devTools/Dockerfile-php72-xdebug .
	docker image inspect infection_php72 > $(DOCKER_RUN_72_IMAGE)
	touch $@

$(DOCKER_RUN_73_IMAGE): devTools/Dockerfile-php73-xdebug
	docker build --tag infection_php73 --file devTools/Dockerfile-php73-xdebug .
	docker image inspect infection_php73 > $(DOCKER_RUN_73_IMAGE)
	touch $@

$(DOCKER_RUN_74_IMAGE): devTools/Dockerfile-php74-xdebug
	docker build --tag infection_php74 --file devTools/Dockerfile-php74-xdebug .
	docker image inspect infection_php74 > $(DOCKER_RUN_74_IMAGE)
	touch $@

tests/benchmark/MutationGenerator/sources: tests/benchmark/MutationGenerator/sources.tar.gz
	cd tests/benchmark/MutationGenerator; tar -xf sources.tar.gz
	touch $@

tests/benchmark/Tracing/coverage: tests/benchmark/Tracing/coverage.tar.gz
	@echo "Untarring the coverage, this might take a while"
	cd tests/benchmark/Tracing; tar -xf coverage.tar.gz
	touch $@

tests/benchmark/Tracing/sources: tests/benchmark/Tracing/sources.tar.gz
	@echo "Untarring the sources, this might take a while"
	cd tests/benchmark/Tracing; tar -xf sources.tar.gz
	touch $@
