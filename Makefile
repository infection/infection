PHP_CS_FIXER_FUTURE_MODE=1
PHPSTAN=./phpstan.phar
PHP-CS-FIXER=./php-cs-fixer-v2.phar
PHPUNIT=vendor/bin/phpunit
INFECTION=build/bin/infection.phar

# URLs to download all tools
BOX_URL="https://github.com/humbug/box/releases/download/3.1.0/box.phar"
PHP-CS-FIXER_URL="https://cs.sensiolabs.org/download/php-cs-fixer-v2.phar"
PHPSTAN_URL="https://github.com/phpstan/phpstan/releases/download/0.10.3/phpstan.phar"

TEST_TRAVIS_OPTIONS ?= false
ifeq ($(TEST_TRAVIS_OPTIONS),false)
else
	FILTER_OPTIONS := --filter=$(shell git diff master --name-only | grep \.php | tr '\n' ',')
	INFECTION_TRAVIS_OPTIONS := --ignore-msi-with-no-mutations --only-covered --min-msi=90 --threads=4 --min-covered-msi=75 $(FILTER_OPTIONS)
endif

INFECTION_EXTRA_OPTIONS ?= --threads=4
INFECTION_OPTIONS ?= $(INFECTION_EXTRA_OPTIONS) $(INFECTION_TRAVIS_OPTIONS)

FLOCK=./devTools/flock

DOCKER_RUN=docker run -t --rm -v "$$PWD":/opt/infection -w /opt/infection
DOCKER_RUN_71=$(FLOCK) devTools/*php71*.json $(DOCKER_RUN) infection_php71
DOCKER_RUN_72=$(FLOCK) devTools/*php72*.json $(DOCKER_RUN) infection_php72
DOCKER_RUN_73=$(FLOCK) devTools/*php73*.json $(DOCKER_RUN) infection_php73

.PHONY: all
#Run all checks, default when running 'make'
all: analyze test

#Non phony targets for phars etc.
vendor: composer.json composer.lock
	composer install

build/cache:
	mkdir -p build/cache

./php-cs-fixer-v2.phar:
	wget $(PHP-CS-FIXER_URL)
	chmod a+x ./php-cs-fixer-v2.phar

./phpstan.phar:
	wget $(PHPSTAN_URL)
	chmod a+x ./phpstan.phar

#All tests, (infection itself, phpunit, e2e) for different php version/ environments (xdebug or phpdbg)
.PHONY: test test-unit test-infection-phpdbg test-e2e-phpdbg test-infection-xdebug test-e2e-xdebug
test: test-unit test-infection-phpdbg test-e2e-phpdbg test-infection-xdebug test-e2e-xdebug
	# All tests finished without errors

.PHONY: test-unit test-unit-71 test-unit-72 test-unit-73
#php unit tests
test-unit: test-unit-71 test-unit-72 test-unit-73

test-unit-71: build-xdebug-71
	$(DOCKER_RUN_71) $(PHPUNIT)

test-unit-72: build-xdebug-72
	$(DOCKER_RUN_72) $(PHPUNIT)

test-unit-73: build-xdebug-73
	$(DOCKER_RUN_73) $(PHPUNIT)

.PHONY: test-infection-phpdbg test-infection-phpdbg-71 test-infection-phpdbg-72 test-infection-phpdbg-73
#infection with phpdbg
test-infection-phpdbg: test-infection-phpdbg-71 test-infection-phpdbg-72 test-infection-phpdbg-73

test-infection-phpdbg-71: build-xdebug-71
	$(DOCKER_RUN_71) phpdbg $(PHP_OPTIONS) -qrr bin/infection $(INFECTION_OPTIONS)

test-infection-phpdbg-72: build-xdebug-72
	$(DOCKER_RUN_72) phpdbg $(PHP_OPTIONS) -qrr bin/infection $(INFECTION_OPTIONS)

test-infection-phpdbg-73: build-xdebug-73
	$(DOCKER_RUN_73) phpdbg $(PHP_OPTIONS) -qrr bin/infection $(INFECTION_OPTIONS)


.PHONY: test-e2e-phpdbg test-e2e-phpdbg-71 test-e2e-phpdbg-72 test-e2e-phpdbg-73
#e2e tests with phpdbg
test-e2e-phpdbg:test-e2e-phpdbg-71 test-e2e-phpdbg-72 test-e2e-phpdbg-73

test-e2e-phpdbg-71: build-xdebug-71 $(INFECTION)
	$(DOCKER_RUN_71) env PHPDBG=1 ./tests/e2e_tests $(INFECTION)

test-e2e-phpdbg-72: build-xdebug-72 $(INFECTION)
	$(DOCKER_RUN_72) env PHPDBG=1 ./tests/e2e_tests $(INFECTION)

test-e2e-phpdbg-73: build-xdebug-73 $(INFECTION)
	$(DOCKER_RUN_73) env PHPDBG=1 ./tests/e2e_tests $(INFECTION)


.PHONY: test-infection-xdebug test-infection-xdebug-71 test-infection-xdebug-72 test-infection-xdebug-73
#infection with xdebug
test-infection-xdebug: test-infection-xdebug-71 test-infection-xdebug-72 test-infection-xdebug-73

test-infection-xdebug-71: build-xdebug-71
	$(DOCKER_RUN_71) php $(PHP_OPTIONS) bin/infection $(INFECTION_OPTIONS)

test-infection-xdebug-72: build-xdebug-72
	$(DOCKER_RUN_72) php $(PHP_OPTIONS) bin/infection $(INFECTION_OPTIONS)

test-infection-xdebug-73: build-xdebug-73
	$(DOCKER_RUN_73) php $(PHP_OPTIONS) bin/infection $(INFECTION_OPTIONS)

.PHONY: test-e2e-xdebug test-e2e-xdebug-71 test-e2e-xdebug-72 test-e2e-xdebug-73
#e2e tests with xdebug
test-e2e-xdebug: test-e2e-xdebug-71 test-e2e-xdebug-72 test-e2e-xdebug-73

test-e2e-xdebug-71: build-xdebug-71 $(INFECTION)
	$(DOCKER_RUN_71) ./tests/e2e_tests $(INFECTION)

test-e2e-xdebug-72: build-xdebug-72 $(INFECTION)
	$(DOCKER_RUN_72) ./tests/e2e_tests $(INFECTION)

test-e2e-xdebug-73: build-xdebug-73 $(INFECTION)
	$(DOCKER_RUN_73) ./tests/e2e_tests $(INFECTION)

.PHONY: build-xdebug-71 build-xdebug-72 build-xdebug-73
#Building images with xdebug

build-xdebug-71: vendor devTools/Dockerfile-php71-xdebug.json
devTools/Dockerfile-php71-xdebug.json: devTools/Dockerfile-php71-xdebug
	docker build -t infection_php71 -f devTools/Dockerfile-php71-xdebug .
	docker image inspect infection_php71 >> devTools/Dockerfile-php71-xdebug.json

build-xdebug-72: vendor devTools/Dockerfile-php72-xdebug.json
devTools/Dockerfile-php72-xdebug.json: devTools/Dockerfile-php72-xdebug
	docker build -t infection_php72 -f devTools/Dockerfile-php72-xdebug .
	docker image inspect infection_php72 >> devTools/Dockerfile-php72-xdebug.json

build-xdebug-73: vendor devTools/Dockerfile-php73-xdebug.json
devTools/Dockerfile-php73-xdebug.json: devTools/Dockerfile-php73-xdebug
	docker build -t infection_php73 -f devTools/Dockerfile-php73-xdebug .
	docker image inspect infection_php73 >> devTools/Dockerfile-php73-xdebug.json

#style checks/ static analysis
.PHONY: analyze cs-fix cs-check phpstan validate auto-review
analyze: cs-check phpstan validate

# PHP-CS-Fixer is checked by PrettyCI
.PHONY: analyze-ci
analyze-ci: phpstan validate

cs-fix: build/cache $(PHP-CS-FIXER)
	$(PHP-CS-FIXER) fix -v --cache-file=build/cache/.php_cs.cache

cs-check: build/cache $(PHP-CS-FIXER)
	$(PHP-CS-FIXER) fix -v --cache-file=build/cache/.php_cs.cache --dry-run --stop-on-violation

phpstan: vendor $(PHPSTAN)
	$(PHPSTAN) analyse src --level=max -c ./devTools/phpstan-src.neon --no-interaction --no-progress
	$(PHPSTAN) analyse tests --level=4 -c ./devTools/phpstan-tests.neon --no-interaction --no-progress

validate:
	composer validate --strict

auto-review: vendor
	vendor/bin/phpunit --group=auto-review

build/bin/infection.phar: $(shell find bin/ src/ -type f) box.phar box.json.dist .git/HEAD
	php box.phar validate
	php box.phar compile

box.phar:
	wget $(BOX_URL)
	chmod a+x box.phar
