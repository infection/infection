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
BOX_URL="https://github.com/humbug/box/releases/download/3.8.5/box.phar"

PHP_CS_FIXER=./.tools/php-cs-fixer
PHP_CS_FIXER_URL="https://cs.sensiolabs.org/download/php-cs-fixer-v2.phar"
PHP_CS_FIXER_CACHE=build/cache/.php_cs.cache

PHPSTAN=./vendor/bin/phpstan

PHPUNIT=vendor/bin/phpunit

INFECTION=./build/infection.phar

DOCKER_RUN=docker run --tty --rm --volume "$$PWD":/opt/infection --workdir /opt/infection
DOCKER_RUN_74=$(FLOCK) devTools/*php74*.json $(DOCKER_RUN) infection_php74
DOCKER_RUN_74_IMAGE=devTools/Dockerfile-php74-xdebug.json
DOCKER_RUN_80=$(FLOCK) devTools/*php80*.json $(DOCKER_RUN) infection_php80
DOCKER_RUN_80_IMAGE=devTools/Dockerfile-php80-xdebug.json

FLOCK=./devTools/flock
COMMIT_HASH=$(shell git rev-parse --short HEAD)

BENCHMARK_SOURCES=tests/benchmark/MutationGenerator/sources \
				  tests/benchmark/Tracing/coverage \
				  tests/benchmark/Tracing/sources


#
# Commands (phony targets)
#---------------------------------------------------------------------------

.PHONY: compile
compile:	 	## Bundles Infection into a PHAR
compile: $(INFECTION)

.PHONY: check_trailing_whitespaces
check_trailing_whitespaces:
	./devTools/check_trailing_whitespaces.sh

.PHONY: cs
cs:	  	 	## Runs PHP-CS-Fixer
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
autoreview: phpstan validate test-autoreview

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
	$(PHPUNIT) --group default

.PHONY: test-unit-docker
test-unit-docker:	## Runs the unit tests on the different Docker platforms
test-unit-docker: test-unit-74-docker test-unit-80-docker

.PHONY: test-unit-74-docker
test-unit-74-docker: $(DOCKER_RUN_74_IMAGE) $(PHPUNIT)
	$(DOCKER_RUN_74) $(PHPUNIT) --group default

.PHONY: test-unit-80-docker
test-unit-80-docker: $(DOCKER_RUN_80_IMAGE) $(PHPUNIT)
	$(DOCKER_RUN_80) $(PHPUNIT) --group default

.PHONY: test-e2e
test-e2e: 	 	## Runs the end-to-end tests
test-e2e: test-e2e-phpunit
	./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-phpunit
test-e2e-phpunit:	## Runs PHPUnit-enabled subset of end-to-end tests
test-e2e-phpunit: $(PHPUNIT) $(BENCHMARK_SOURCES)
	$(PHPUNIT) --group integration,e2e

.PHONY: test-e2e-docker
test-e2e-docker: 	## Runs the end-to-end tests on the different Docker platforms
test-e2e-docker: test-e2e-phpdbg-docker test-e2e-xdebug-docker

.PHONY: test-e2e-phpdbg-docker
test-e2e-phpdbg-docker: test-e2e-phpdbg-74-docker test-e2e-phpdbg-80-docker

.PHONY: test-e2e-phpdbg-74-docker
test-e2e-phpdbg-74-docker: $(DOCKER_RUN_74_IMAGE) $(INFECTION)
	$(DOCKER_RUN_74) $(PHPUNIT) --group integration,e2e
	$(DOCKER_RUN_74) env PHPDBG=1 ./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-phpdbg-80-docker
test-e2e-phpdbg-80-docker: $(DOCKER_RUN_80_IMAGE) $(INFECTION)
	$(DOCKER_RUN_80) $(PHPUNIT) --group integration,e2e
	$(DOCKER_RUN_80) env PHPDBG=1 ./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-xdebug-docker
test-e2e-xdebug-docker: test-e2e-xdebug-74-docker test-e2e-xdebug-80-docker

.PHONY: test-e2e-xdebug-74-docker
test-e2e-xdebug-74-docker: $(DOCKER_RUN_74_IMAGE) $(INFECTION)
	$(DOCKER_RUN_74) $(PHPUNIT) --group integration,e2e
	$(DOCKER_RUN_74) ./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-xdebug-80-docker
test-e2e-xdebug-80-docker: $(DOCKER_RUN_80_IMAGE) $(INFECTION)
	$(DOCKER_RUN_80) $(PHPUNIT) --group integration,e2e
	$(DOCKER_RUN_80) ./tests/e2e_tests $(INFECTION)

.PHONY: test-infection
test-infection:	## Runs Infection against itself
test-infection:
	$(INFECTION) --threads=4

.PHONY: test-infection-docker
test-infection-docker:	## Runs Infection against itself on the different Docker platforms
test-infection-docker: test-infection-phpdbg-docker test-infection-xdebug-docker

.PHONY: test-infection-phpdbg-docker
test-infection-phpdbg-docker: test-infection-phpdbg-74-docker test-infection-phpdbg-80-docker

.PHONY: test-infection-phpdbg-74-docker
test-infection-phpdbg-74-docker: $(DOCKER_RUN_74_IMAGE)
	$(DOCKER_RUN_74) phpdbg -qrr bin/infection --threads=4

.PHONY: test-infection-phpdbg-80-docker
test-infection-phpdbg-80-docker: $(DOCKER_RUN_80_IMAGE)
	$(DOCKER_RUN_80) phpdbg -qrr bin/infection --threads=4

.PHONY: test-infection-xdebug-docker
test-infection-xdebug-docker: test-infection-xdebug-74-docker test-infection-xdebug-80-docker

.PHONY: test-infection-xdebug-74-docker
test-infection-xdebug-74-docker: $(DOCKER_RUN_74_IMAGE)
	$(DOCKER_RUN_74) ./bin/infection --threads=4

.PHONY: test-infection-xdebug-80-docker
test-infection-xdebug-80-docker: $(DOCKER_RUN_80_IMAGE)
	$(DOCKER_RUN_80) ./bin/infection --threads=4

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

$(DOCKER_RUN_74_IMAGE): devTools/Dockerfile-php74-xdebug
	docker build --tag infection_php74 --file devTools/Dockerfile-php74-xdebug .
	docker image inspect infection_php74 > $(DOCKER_RUN_74_IMAGE)
	touch $@

$(DOCKER_RUN_80_IMAGE): devTools/Dockerfile-php80-xdebug
	docker build --tag infection_php80 --file devTools/Dockerfile-php80-xdebug .
	docker image inspect infection_php80 >> $(DOCKER_RUN_80_IMAGE)
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
