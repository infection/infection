PHP_CS_FIXER_FUTURE_MODE=1
PHPSTAN=./phpstan.phar
PHP-CS-FIXER=./php-cs-fixer-v2.phar

.PHONY: all
#Run all checks, default when running 'make'
all: analyze test

#Non phony targets for phars etc.
vendor: composer.json composer.lock
	composer install

build/cache:
	mkdir -p build/cache

./php-cs-fixer-v2.phar:
	wget https://cs.sensiolabs.org/download/php-cs-fixer-v2.phar
	chmod a+x ./php-cs-fixer-v2.phar

./phpstan.phar:
	wget https://github.com/phpstan/phpstan/releases/download/0.9.1/phpstan.phar
	chmod a+x ./phpstan.phar

#All tests, (infection itself, phpunit, e2e) for different php version/ environments (xdebug or phpdbg)
.PHONY: test test-unit test-infection-phpdbg test-e2e-phpdbg test-infection-xdebug test-e2e-xdebug test-final-private
test: test-unit test-infection-phpdbg test-e2e-phpdbg test-infection-xdebug test-e2e-xdebug test-final-private

test-final-private:
	./tests/final_private_test

.PHONY: test-unit test-unit-70 test-unit-71 test-unit-72
#php unit tests
test-unit: test-unit-70 test-unit-71 test-unit-72

test-unit-70: vendor
	docker run -it --rm -v "$$PWD":/opt/infection -w /opt/infection php:7.0-cli php vendor/bin/phpunit

test-unit-71: vendor
	docker run -it --rm -v "$$PWD":/opt/infection -w /opt/infection php:7.1-cli php vendor/bin/phpunit

test-unit-72: vendor
	docker run -it --rm -v "$$PWD":/opt/infection -w /opt/infection php:7.2-cli php vendor/bin/phpunit


.PHONY: test-infection-phpdbg test-infection-phpdbg-70 test-infection-phpdbg-71 test-infection-phpdbg-72
#infection with phpdbg
test-infection-phpdbg: test-infection-phpdbg-70 test-infection-phpdbg-71 test-infection-phpdbg-72

test-infection-phpdbg-70: vendor
	docker run -it --rm -v "$$PWD":/opt/infection -w /opt/infection php:7.0-cli phpdbg -qrr bin/infection --threads=4

test-infection-phpdbg-71: vendor
	docker run -it --rm -v "$$PWD":/opt/infection -w /opt/infection php:7.1-cli phpdbg -qrr bin/infection --threads=4

test-infection-phpdbg-72: vendor
	docker run -it --rm -v "$$PWD":/opt/infection -w /opt/infection php:7.2-cli phpdbg -qrr bin/infection --threads=4


.PHONY: test-e2e-phpdbg test-e2e-phpdbg-70 test-e2e-phpdbg-71 test-e2e-phpdbg-72
#e2e tests with phpdbg
test-e2e-phpdbg: test-e2e-phpdbg-70 test-e2e-phpdbg-71 test-e2e-phpdbg-72

test-e2e-phpdbg-70: vendor
	docker run -it --rm -e PHPDBG=1 -v "$$PWD":/opt/infection -w /opt/infection php:7.0-cli ./tests/e2e_tests

test-e2e-phpdbg-71: vendor
	docker run -it --rm -e PHPDBG=1 -v "$$PWD":/opt/infection -w /opt/infection php:7.1-cli ./tests/e2e_tests

test-e2e-phpdbg-72: vendor
	docker run -it --rm -e PHPDBG=1 -v "$$PWD":/opt/infection -w /opt/infection php:7.2-cli ./tests/e2e_tests


.PHONY: test-infection-xdebug test-infection-xdebug-70 test-infection-xdebug-71 test-infection-xdebug-72
#infection with xdebug
test-infection-xdebug: test-infection-xdebug-70 test-infection-xdebug-71 test-infection-xdebug-72

test-infection-xdebug-70: build-xdebug-70
	docker run -it --rm -v "$$PWD":/opt/infection -w /opt/infection infection_php70 php bin/infection --threads=4

test-infection-xdebug-71: build-xdebug-71
	docker run -it --rm -v "$$PWD":/opt/infection -w /opt/infection infection_php71 php bin/infection --threads=4

test-infection-xdebug-72: build-xdebug-72
	docker run -it --rm -v "$$PWD":/opt/infection -w /opt/infection infection_php72 php bin/infection --threads=4


.PHONY: test-e2e-xdebug test-e2e-xdebug-70 test-e2e-xdebug-71 test-e2e-xdebug-72
#e2e tests with xdebug
test-e2e-xdebug: test-e2e-xdebug-70 test-e2e-xdebug-71 test-e2e-xdebug-72

test-e2e-xdebug-70: build-xdebug-70
	docker run -it --rm -v "$$PWD":/opt/infection -w /opt/infection infection_php70 ./tests/e2e_tests

test-e2e-xdebug-71: build-xdebug-71
	docker run -it --rm -v "$$PWD":/opt/infection -w /opt/infection infection_php71 ./tests/e2e_tests

test-e2e-xdebug-72: build-xdebug-72
	docker run -it --rm -v "$$PWD":/opt/infection -w /opt/infection infection_php72 ./tests/e2e_tests


.PHONY: build-xdebug-70 build-xdebug-71 build-xdebug-72
#Building images with xdebug
build-xdebug-70: vendor
	docker build -t infection_php70 -f "$$PWD/devTools/Dockerfile-php70-xdebug" .

build-xdebug-71: vendor
	docker build -t infection_php71 -f "$$PWD/devTools/Dockerfile-php71-xdebug" .

build-xdebug-72: vendor
	docker build -t infection_php72 -f "$$PWD/devTools/Dockerfile-php72-xdebug" .

#style checks/ static analysis
.PHONY: analyze cs-fix cs-check phpstan validate auto-review
analyze: cs-check phpstan validate auto-review

cs-fix: build/cache $(PHP-CS-FIXER)
	$(PHP-CS-FIXER) fix -v --cache-file=build/cache/.php_cs.cache

cs-check: build/cache $(PHP-CS-FIXER)
	$(PHP-CS-FIXER) fix -v --cache-file=build/cache/.php_cs.cache --dry-run --stop-on-violation

phpstan: vendor $(PHPSTAN)
	$(PHPSTAN) analyse src --level=max -c ./devTools/phpstan-src.neon --no-interaction --no-progress
	$(PHPSTAN) analyse tests --level=2 -c ./devTools/phpstan-tests.neon --no-interaction --no-progress

validate:
	composer validate --strict

auto-review: vendor
	vendor/bin/phpunit --group=auto-review

build/bin/infection.phar: bin src vendor box.json.dist scoper.inc.php box.phar
	php box.phar compile

box.phar:
	wget https://github.com/humbug/box/releases/download/3.0.0-alpha.5/box.phar
	chmod a+x box.phar
