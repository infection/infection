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
BOX_URL="https://github.com/humbug/box/releases/download/4.5.1/box.phar"

PHP_CS_FIXER=./.tools/php-cs-fixer
PHP_CS_FIXER_URL="https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v3.89.2/php-cs-fixer.phar"

PHPSTAN=./vendor/bin/phpstan
RECTOR=./vendor/bin/rector
COLLISION_DETECTOR=./vendor/bin/detect-collisions

PSALM=./.tools/psalm
PSALM_URL="https://github.com/vimeo/psalm/releases/download/5.11.0/psalm.phar"

PHPUNIT_BIN=vendor/phpunit/phpunit/phpunit
CI ?=
PHPUNIT=$(PHPUNIT_BIN)$(if $(CI), --no-progress,)
PARATEST=vendor/bin/paratest

PHPBENCH_REPORTS=--report=aggregate --report=bar_chart_iteration

INFECTION=./dist/infection.phar

DOCKER_RUN=docker compose run --rm
DOCKER_RUN_82=$(DOCKER_RUN) php82 $(FLOCK) Makefile
DOCKER_FILE_IMAGE=devTools/Dockerfile.json

FLOCK=./devTools/flock
COMMIT_HASH=$(shell git rev-parse --short HEAD)

BENCHMARK_MUTATION_GENERATOR_SOURCES=tests/benchmark/MutationGenerator/sources
BENCHMARK_PARSE_GIT_DIFF_SOURCE=tests/benchmark/ParseGitDiff/diff
BENCHMARK_TRACING_COVERAGE_DIR=tests/benchmark/Tracing/coverage
BENCHMARK_TRACING_SUBMODULE=tests/benchmark/Tracing/benchmark-source
BENCHMARK_TRACING_COVERAGE_SOURCE_DIR=$(BENCHMARK_TRACING_SUBMODULE)/dist/coverage

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
	$(DOCKER_RUN_82) make compile

.PHONY: check_trailing_whitespaces
check_trailing_whitespaces:
	./devTools/check_trailing_whitespaces.sh

.PHONY: cs
cs:	  	 	## Runs PHP-CS-Fixer
cs: $(PHP_CS_FIXER)
	$(PHP_CS_FIXER) fix -v --diff
	LC_ALL=C sort -u .gitignore -o .gitignore
	$(MAKE) check_trailing_whitespaces

.PHONY: cs-check
cs-check:		## Runs PHP-CS-Fixer in dry-run mode
cs-check: $(PHP_CS_FIXER)
	$(PHP_CS_FIXER) fix -v --diff --dry-run
	LC_ALL=C sort -c -u .gitignore
	$(MAKE) check_trailing_whitespaces

.PHONY: phpstan
phpstan: vendor $(PHPSTAN)
	$(PHPSTAN) analyse --configuration devTools/phpstan.neon --no-interaction --no-progress

.PHONY: phpstan-baseline
phpstan-baseline: vendor $(PHPSTAN)
	$(PHPSTAN) analyse --configuration devTools/phpstan.neon --no-interaction --no-progress --generate-baseline devTools/phpstan-baseline.neon || true

.PHONY: psalm-baseline
psalm-baseline: vendor
	$(PSALM) --threads=max --set-baseline=devTools/psalm-baseline.xml

.PHONY: detect-collisions
detect-collisions: vendor $(PHPSTAN)
	$(COLLISION_DETECTOR) --configuration devTools/collision-detector.json

.PHONY: psalm
psalm: vendor $(PSALM)
	$(PSALM) --threads=max --use-baseline=devTools/psalm-baseline.xml

.PHONY: rector
rector: vendor $(RECTOR)
	$(RECTOR) process

.PHONY: rector-check
rector-check: vendor $(RECTOR)
	$(RECTOR) process --dry-run

.PHONY: validate
validate:
	composer validate --strict

.PHONY: profile
profile: 	 	## Runs Blackfire
profile:
	$(MAKE) profile_mutation_generator
	$(MAKE) profile_parse_git_diff
	$(MAKE) profile_tracing

.PHONY: benchmark
benchmark: vendor \
		$(BENCHMARK_MUTATION_GENERATOR_SOURCES) \
		$(BENCHMARK_PARSE_GIT_DIFF_SOURCE) \
		$(BENCHMARK_TRACING_SUBMODULE) \
		$(BENCHMARK_TRACING_COVERAGE_DIR)
	composer dump --classmap-authoritative --quiet
	vendor/bin/phpbench run tests/benchmark $(PHPBENCH_REPORTS)
	composer dump

.PHONY: profile_mutation_generator
profile_mutation_generator: vendor $(BENCHMARK_MUTATION_GENERATOR_SOURCES)
	composer dump --classmap-authoritative --quiet
	blackfire run \
		--title="MutationGenerator" \
		--metadata="commit=$(COMMIT_HASH)" \
		php tests/benchmark/MutationGenerator/profile.php
	composer dump

.PHONY: benchmark_mutation_generator
benchmark_mutation_generator: vendor $(BENCHMARK_MUTATION_GENERATOR_SOURCES)
	composer dump --classmap-authoritative --quiet
	vendor/bin/phpbench run tests/benchmark/MutationGenerator $(PHPBENCH_REPORTS)
	composer dump

.PHONY: profile_parse_git_diff
profile_parse_git_diff: vendor $(BENCHMARK_PARSE_GIT_DIFF_SOURCE)
	composer dump --classmap-authoritative --quiet
	blackfire run \
		--title="ParseGitDiff" \
		--metadata="commit=$(COMMIT_HASH)" \
		php tests/benchmark/ParseGitDiff/profile.php
	composer dump

.PHONY: benchmark_parse_git_diff
benchmark_parse_git_diff: vendor $(BENCHMARK_PARSE_GIT_DIFF_SOURCE)
	composer dump --classmap-authoritative --quiet
	vendor/bin/phpbench run tests/benchmark/ParseGitDiff $(PHPBENCH_REPORTS)
	composer dump

.PHONY: profile_tracing
profile_tracing: vendor $(BENCHMARK_TRACING_SUBMODULE) $(BENCHMARK_TRACING_COVERAGE_DIR)
	composer dump --classmap-authoritative --quiet
	blackfire run \
		--title="Tracing" \
		--metadata="commit=$(COMMIT_HASH)" \
		php tests/benchmark/Tracing/profile.php
	composer dump

.PHONY: benchmark_tracing
benchmark_tracing: vendor $(BENCHMARK_TRACING_SUBMODULE) $(BENCHMARK_TRACING_COVERAGE_DIR)
	composer dump --classmap-authoritative --quiet
	vendor/bin/phpbench run tests/benchmark/Tracing $(PHPBENCH_REPORTS)
	composer dump


.PHONY: autoreview
autoreview: 	 	## Runs various checks (static analysis & AutoReview test suite)
autoreview: cs-check phpstan psalm validate test-autoreview rector-check detect-collisions

.PHONY: test
test:		 	## Runs all the tests
test: autoreview test-unit test-benchmark test-e2e test-infection

.PHONY: test-docker
test-docker:		## Runs all the tests on the different Docker platforms
test-docker: autoreview test-unit-docker test-e2e-docker test-infection-docker

.PHONY: test-autoreview
test-autoreview: $(PHPUNIT_BIN) vendor
	$(PHPUNIT) --configuration=phpunit_autoreview.xml

.PHONY: test-unit
test-unit:	 	## Runs the unit tests
test-unit: $(PHPUNIT_BIN) vendor
	$(PHPUNIT) --group $(PHPUNIT_GROUP) --exclude-group e2e

.PHONY: test-unit-parallel
test-unit-parallel:	## Runs the unit tests in parallel
test-unit-parallel: $(PARATEST) vendor
	$(PARATEST) --runner=WrapperRunner

.PHONY: test-unit-docker
test-unit-docker:	## Runs the unit tests on the different Docker platforms
test-unit-docker: test-unit-82-docker

test-unit-82-docker: $(DOCKER_FILE_IMAGE) $(PHPUNIT_BIN)
	$(DOCKER_RUN_82) $(PHPUNIT) --group $(PHPUNIT_GROUP)

.PHONY: test-benchmark
test-benchmark:	 	## Runs the benchmark tests
test-benchmark: $(PHPUNIT_BIN) \
		vendor \
		$(BENCHMARK_MUTATION_GENERATOR_SOURCES) \
		$(BENCHMARK_TRACING_SUBMODULE) \
		$(BENCHMARK_TRACING_COVERAGE_DIR)
	$(PHPUNIT) --group=benchmark

.PHONY: test-e2e
test-e2e: 	 	## Runs the end-to-end tests
test-e2e: test-e2e-phpunit
	./tests/e2e_tests $(INFECTION)

.PHONY: test-e2e-phpunit
test-e2e-phpunit:	## Runs PHPUnit-enabled subset of end-to-end tests
test-e2e-phpunit: $(PHPUNIT_BIN) vendor
	@if [ -x $(PARATEST) ]; then \
		$(PARATEST) --group $(E2E_PHPUNIT_GROUP); \
	else \
		$(PHPUNIT) --group $(E2E_PHPUNIT_GROUP); \
	fi

.PHONY: test-e2e-docker
test-e2e-docker: 	## Runs the end-to-end tests on the different Docker platforms
test-e2e-docker: test-e2e-xdebug-docker

.PHONY: test-e2e-xdebug-docker
test-e2e-xdebug-docker: test-e2e-xdebug-82-docker

.PHONY: test-e2e-xdebug-82-docker
test-e2e-xdebug-82-docker: $(DOCKER_FILE_IMAGE) $(INFECTION)
	$(DOCKER_RUN_82) $(PHPUNIT) --group $(E2E_PHPUNIT_GROUP)
	$(DOCKER_RUN_82) ./tests/e2e_tests $(INFECTION)

.PHONY: test-infection
test-infection:		## Runs Infection against itself
test-infection: $(INFECTION) vendor
	$(INFECTION) --threads=max

.PHONY: test-infection-docker
test-infection-docker:	## Runs Infection against itself on the different Docker platforms
test-infection-docker: test-infection-xdebug-docker

.PHONY: test-infection-xdebug-docker
test-infection-xdebug-docker: test-infection-xdebug-82-docker

.PHONY: test-infection-xdebug-82-docker
test-infection-xdebug-82-docker: $(DOCKER_FILE_IMAGE)
	$(DOCKER_RUN_82) ./bin/infection --threads=max

#
# Rules from files (non-phony targets)
#---------------------------------------------------------------------------

$(BOX): Makefile
	wget -q $(BOX_URL) --output-document=$(BOX)
	chmod a+x $(BOX)
	touch -c $@

$(PHP_CS_FIXER): Makefile
	wget -q $(PHP_CS_FIXER_URL) --output-document=$(PHP_CS_FIXER)
	chmod a+x $(PHP_CS_FIXER)
	touch -c $@

$(PHPSTAN): vendor
	touch -c $@

$(PSALM): Makefile
	wget -q $(PSALM_URL) --output-document=$(PSALM)
	chmod a+x $(PSALM)
	touch -c $@

$(INFECTION): vendor $(shell find bin/ src/ -type f) $(BOX) box.json.dist .git/HEAD
	composer require infection/codeception-adapter infection/phpspec-adapter
	# Workaround for https://github.com/box-project/box/issues/580
	composer install --no-dev
	$(BOX) --version
	$(BOX) validate
	$(BOX) compile
	composer remove infection/codeception-adapter infection/phpspec-adapter
	composer install
	touch -c $@

vendor: composer.lock
	composer install --prefer-dist
	touch -c $@

composer.lock: composer.json
	composer install --prefer-dist
	touch -c $@

$(PHPUNIT_BIN): vendor phpunit.xml.dist
	touch -c $@

phpunit.xml.dist:
	# Not updating phpunit.xml with:
	# phpunit --migrate-configuration || true
	touch -c $@

$(DOCKER_FILE_IMAGE): devTools/Dockerfile
	docker compose build php82
	docker image inspect infection-php82 >> $(DOCKER_FILE_IMAGE)
	touch -c $@

$(BENCHMARK_MUTATION_GENERATOR_SOURCES): tests/benchmark/MutationGenerator/sources.tar.gz
	cd tests/benchmark/MutationGenerator; tar -xzf sources.tar.gz
	touch -c $@

$(BENCHMARK_PARSE_GIT_DIFF_SOURCE):
	php tests/benchmark/ParseGitDiff/generate-diff.php
	touch -c $@

$(BENCHMARK_TRACING_COVERAGE_DIR): $(BENCHMARK_TRACING_COVERAGE_SOURCE_DIR)
	@echo "Generating coverage"
	@rm -rf $(BENCHMARK_TRACING_COVERAGE_DIR) || true
	cp -R $(BENCHMARK_TRACING_COVERAGE_SOURCE_DIR) $(BENCHMARK_TRACING_COVERAGE_DIR)
	@# Correct the absolute paths found
	PROJECT_ROOT=$$(pwd)/$(BENCHMARK_TRACING_SUBMODULE) \
		&& find $(BENCHMARK_TRACING_COVERAGE_DIR) \
			-type f \
			-exec sed -i.bak "s|/path/to/project|$${PROJECT_ROOT}|g" {} \; \
		&& find $(BENCHMARK_TRACING_COVERAGE_DIR) \
			-name "*.bak" \
			-type f \
			-delete
	touch -c $@

$(BENCHMARK_TRACING_COVERAGE_SOURCE_DIR):
	git submodule update --init $(BENCHMARK_TRACING_SUBMODULE)

clean:
	rm -fr tests/benchmark/MutationGenerator/sources
	@rm -fr tests/benchmark/Tracing/sources
	rm -fr $(BENCHMARK_TRACING_COVERAGE_DIR)
	rm -fr $(BENCHMARK_TRACING_VENDOR)
	git clean -f -X tests/e2e/
