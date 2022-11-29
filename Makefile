SHELL=bash
.DEFAULT_GOAL := help

# See https://tech.davis-hansson.com/p/make/
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

.PHONY: help
help:
	@printf "\033[33mUsage:\033[0m\n  make TARGET\n\n\033[32m#\n# Commands\n#---------------------------------------------------------------------------\033[0m\n\n"
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | awk 'BEGIN {FS = ":"}; {printf "\033[33m%s:\033[0m%s\n", $$1, $$2}'


#
# Variables
#---------------------------------------------------------------------------
BOX=./.tools/box
BOX_URL="https://github.com/humbug/box/releases/download/3.16.0/box.phar"

PHP_CS_FIXER=./.tools/php-cs-fixer
PHP_CS_FIXER_URL="https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v3.9.5/php-cs-fixer.phar"
PHP_CS_FIXER_CACHE=.php_cs.cache

PHPSTAN=./vendor/bin/phpstan

PSALM=./.tools/psalm
PSALM_URL="https://github.com/vimeo/psalm/releases/download/v4.15.0/psalm.phar"

PHPUNIT=vendor/phpunit/phpunit/phpunit
PARATEST=vendor/bin/paratest --runner=WrapperRunner

INFECTION=./build/infection.phar

DOCKER_RUN=docker-compose run
DOCKER_RUN_80=$(DOCKER_RUN) php80 $(FLOCK) Makefile
DOCKER_RUN_81=$(DOCKER_RUN) php81 $(FLOCK) Makefile
DOCKER_FILE_IMAGE=devTools/Dockerfile.json

FLOCK=./devTools/flock
COMMIT_HASH=$(shell git rev-parse --short HEAD)

BENCHMARK_SOURCES=tests/benchmark/MutationGenerator/sources \
				  tests/benchmark/Tracing/coverage \
				  tests/benchmark/Tracing/sources

E2E_PHPUNIT_GROUP=integration,e2e
PHPUNIT_GROUP=default

#
# Commands (phony targets)
#---------------------------------------------------------------------------

.PHONY: compile
compile:	 	## Bundles Infection into a PHAR
compile:
	rm $(INFECTION) || true
	make $(INFECTION)

.PHONY: compile-docker
compile-docker:	 	## Bundles Infection into a PHAR using docker
compile-docker: $(DOCKER_FILE_IMAGE)
	$(DOCKER_RUN_81) make compile

.PHONY: check_trailing_whitespaces
check_trailing_whitespaces:
	./devTools/check_trailing_whitespaces.sh

.PHONY: cs
cs:	  	 	## Runs PHP-CS-Fixer
cs: $(PHP_CS_FIXER)
	$(PHP_CS_FIXER) fix -v --cache-file=$(PHP_CS_FIXER_CACHE) --diff
	LC_ALL=C sort -u .gitignore -o .gitignore
	$(MAKE) check_trailing_whitespaces

.PHONY: cs-check
cs-check:		## Runs PHP-CS-Fixer in dry-run mode
cs-check: $(PHP_CS_FIXER)
	$(PHP_CS_FIXER) fix -v --cache-file=$(PHP_CS_FIXER_CACHE) --diff --dry-run
	LC_ALL=C sort -c -u .gitignore
	$(MAKE) check_trailing_whitespaces

.PHONY: phpstan
phpstan: vendor $(PHPSTAN)
	$(PHPSTAN) analyse --configuration devTools/phpstan-src.neon --no-interaction --no-progress
	$(PHPSTAN) analyse --configuration devTools/phpstan-tests.neon --no-interaction --no-progress

.PHONY: phpstan-baseline
phpstan-baseline: vendor $(PHPSTAN)
	$(PHPSTAN) analyse --configuration devTools/phpstan-src.neon --no-interaction --no-progress --generate-baseline devTools/phpstan-src-baseline.neon || true
	$(PHPSTAN) analyse --configuration devTools/phpstan-tests.neon --no-interaction --no-progress --generate-baseline devTools/phpstan-tests-baseline.neon || true

.PHONY: psalm-baseline
psalm-baseline: vendor
	$(PSALM) --threads=4 --set-baseline=psalm-baseline.xml

.PHONY: psalm
psalm: vendor $(PSALM)
	$(PSALM) --threads=4

.PHONY: validate
validate:
	composer validate --strict

.PHONY: profile
profile: 	 	## Runs Blackfire
profile: vendor $(BENCHMARK_SOURCES)
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
autoreview: 	 	## Runs various checks (static analysis & AutoReview test suite)
autoreview: phpstan psalm validate test-autoreview

.PHONY: test
test:		 	## Runs all the tests
test: autoreview test-unit test-e2e test-infection

.PHONY: test-docker
test-docker:		## Runs all the tests on the different Docker platforms
test-docker: autoreview test-unit-docker test-e2e-docker test-infection-docker

.PHONY: test-autoreview
test-autoreview:
	$(PHPUNIT) --configuration=phpunit_autoreview.xml

.PHONY: test-unit
test-unit:	 	## Runs the unit tests
test-unit: $(PHPUNIT)
	$(PHPUNIT) --group $(PHPUNIT_GROUP)

.PHONY: test-unit-parallel
test-unit-parallel:	## Runs the unit tests in parallel
test-unit-parallel:
	$(PARATEST)

.PHONY: test-unit-docker
test-unit-docker:	## Runs the unit tests on the different Docker platforms
test-unit-docker: test-unit-80-docker

.PHONY: test-unit-80-docker
test-unit-80-docker: $(DOCKER_FILE_IMAGE) $(PHPUNIT)
	$(DOCKER_RUN_80) $(PHPUNIT) --group $(PHPUNIT_GROUP)

.PHONY: test-e2e
test-e2e: 	 	## Runs the end-to-end tests
test-e2e: test-e2e-phpunit
	./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-phpunit
test-e2e-phpunit:	## Runs PHPUnit-enabled subset of end-to-end tests
test-e2e-phpunit: $(PHPUNIT) $(BENCHMARK_SOURCES)
	$(PHPUNIT) --group $(E2E_PHPUNIT_GROUP)

.PHONY: test-e2e-docker
test-e2e-docker: 	## Runs the end-to-end tests on the different Docker platforms
test-e2e-docker: test-e2e-phpdbg-docker test-e2e-xdebug-docker

.PHONY: test-e2e-phpdbg-docker
test-e2e-phpdbg-docker: test-e2e-phpdbg-80-docker

.PHONY: test-e2e-phpdbg-80-docker
test-e2e-phpdbg-80-docker: $(DOCKER_FILE_IMAGE) $(INFECTION)
	$(DOCKER_RUN_80) $(PHPUNIT) --group $(E2E_PHPUNIT_GROUP)
	$(DOCKER_RUN_80) env PHPDBG=1 ./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-xdebug-docker
test-e2e-xdebug-docker: test-e2e-xdebug-80-docker

.PHONY: test-e2e-xdebug-80-docker
test-e2e-xdebug-80-docker: $(DOCKER_FILE_IMAGE) $(INFECTION)
	$(DOCKER_RUN_80) $(PHPUNIT) --group $(E2E_PHPUNIT_GROUP)
	$(DOCKER_RUN_80) ./tests/e2e_tests $(INFECTION)

.PHONY: test-infection
test-infection:		## Runs Infection against itself
test-infection:
	$(INFECTION) --threads=4

.PHONY: test-infection-docker
test-infection-docker:	## Runs Infection against itself on the different Docker platforms
test-infection-docker: test-infection-phpdbg-docker test-infection-xdebug-docker

.PHONY: test-infection-phpdbg-docker
test-infection-phpdbg-docker: test-infection-phpdbg-80-docker

.PHONY: test-infection-phpdbg-80-docker
test-infection-phpdbg-80-docker: $(DOCKER_FILE_IMAGE)
	$(DOCKER_RUN_80) phpdbg -qrr bin/infection --threads=4

.PHONY: test-infection-xdebug-docker
test-infection-xdebug-docker: test-infection-xdebug-80-docker

.PHONY: test-infection-xdebug-80-docker
test-infection-xdebug-80-docker: $(DOCKER_FILE_IMAGE)
	$(DOCKER_RUN_80) ./bin/infection --threads=4

#
# Rules from files (non-phony targets)
#---------------------------------------------------------------------------

$(BOX): Makefile
	wget -q $(BOX_URL) --output-document=$(BOX)
	chmod a+x $(BOX)
	touch $@

$(PHP_CS_FIXER): Makefile
	wget -q $(PHP_CS_FIXER_URL) --output-document=$(PHP_CS_FIXER)
	chmod a+x $(PHP_CS_FIXER)
	touch $@

$(PHPSTAN): vendor

$(PSALM): Makefile
	wget -q $(PSALM_URL) --output-document=$(PSALM)
	chmod a+x $(PSALM)
	touch $@

$(INFECTION): vendor $(shell find bin/ src/ -type f) $(BOX) box.json.dist .git/HEAD
	composer require infection/codeception-adapter infection/phpspec-adapter
	$(BOX) --version
	$(BOX) validate
	$(BOX) compile
	composer remove infection/codeception-adapter infection/phpspec-adapter
	touch -c $@

vendor: composer.lock
	composer install --prefer-dist
	touch $@

composer.lock: composer.json
	composer install --prefer-dist
	touch -c $@

$(PHPUNIT): vendor phpunit.xml.dist
	touch -c $@

phpunit.xml.dist:
	# Not updating phpunit.xml with:
	# phpunit --migrate-configuration || true

$(DOCKER_FILE_IMAGE): devTools/Dockerfile
	docker-compose build
	touch $@

tests/benchmark/MutationGenerator/sources: tests/benchmark/MutationGenerator/sources.tar.gz
	cd tests/benchmark/MutationGenerator; tar -xzf sources.tar.gz
	touch -c $@

tests/benchmark/Tracing/coverage: tests/benchmark/Tracing/coverage.tar.gz
	@echo "Untarring the coverage, this might take a while"
	cd tests/benchmark/Tracing; tar -xzf coverage.tar.gz
	touch -c $@

tests/benchmark/Tracing/sources: tests/benchmark/Tracing/sources.tar.gz
	@echo "Untarring the sources, this might take a while"
	cd tests/benchmark/Tracing; tar -xzf sources.tar.gz
	touch -c $@

clean:
	rm -fr tests/benchmark/MutationGenerator/sources
	rm -fr tests/benchmark/Tracing/coverage
	rm -fr tests/benchmark/Tracing/sources
	git clean -f -X tests/e2e/
