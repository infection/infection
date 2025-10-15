SHELL=bash
.DEFAULT_GOAL := help

# See https://tech.davis-hansson.com/p/make/
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules


ROOT_PATH = ../../../../..
ROOT_PHPUNIT_BIN = vendor/phpunit/phpunit/phpunit
PHPUNIT_BIN = $(ROOT_PATH)/$(ROOT_PHPUNIT_BIN)


.PHONY: help
help:
	@printf "\033[33mUsage:\033[0m\n  make TARGET\n\n\033[32m#\n# Commands\n#---------------------------------------------------------------------------\033[0m\n\n"
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | awk 'BEGIN {FS = ":"}; {printf "\033[33m%s:\033[0m%s\n", $$1, $$2}'


.PHONY: test
test: ## Execute the tests
test: $(PHPUNIT_BIN)
	XDEBUG_MODE=coverage $(PHPUNIT_BIN)
	@# Replace absolute paths with placeholders in XML coverage reports
	@find phpunit/ -name "*.xml" -type f -exec sed -i.bak 's|$(CURDIR)|/path/to/test-project|g' {} \; -exec rm {}.bak \;


$(PHPUNIT_BIN):
	cd $(ROOT_PATH); $(MAKE) --file Makefile $(ROOT_PHPUNIT_BIN) --debug
