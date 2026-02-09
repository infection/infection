SHELL=bash
.DEFAULT_GOAL := help

# See https://tech.davis-hansson.com/p/make/
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules


PHPUNIT_BIN = vendor/bin/phpunit
PHPUNIT_COVERAGE_DIR = var/coverage
PHPUNIT_COVERAGE_XML_DIR = $(PHPUNIT_COVERAGE_DIR)/xml
PHPUNIT_COVERAGE_JUNIT = $(PHPUNIT_COVERAGE_DIR)/junit.xml


.PHONY: help
help:
	@printf "\033[33mUsage:\033[0m\n  make TARGET\n\n\033[32m#\n# Commands\n#---------------------------------------------------------------------------\033[0m\n\n"
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | awk 'BEGIN {FS = ":"}; {printf "\033[33m%s:\033[0m%s\n", $$1, $$2}'


.PHONY: cs
cs: ## Fix the ordering of the .gitignore entries
cs:
	LC_ALL=C sort -u .gitignore -o .gitignore


.PHONY: test
test: ## Execute the tests
test: $(PHPUNIT_BIN)
	$(PHPUNIT_BIN)


.PHONY: phpunit-coverage
phpunit-coverage: ## Generate coverage
phpunit-coverage: $(PHPUNIT_BIN)
	rm -rf $(PHPUNIT_COVERAGE_XML_DIR) || true
	XDEBUG_MODE=coverage $(PHPUNIT_BIN) --coverage-xml=$(PHPUNIT_COVERAGE_XML_DIR) --log-junit=$(PHPUNIT_COVERAGE_JUNIT)


$(PHPUNIT_BIN): vendor

vendor: composer.lock

composer.lock: composer.json
	composer update --prefer-dist
	touch -c vendor
	touch -c composer.lock
	touch -c composer.json
