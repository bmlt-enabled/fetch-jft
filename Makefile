COMMIT := $(shell git rev-parse --short=8 HEAD)
ZIP_FILENAME := $(or $(ZIP_FILENAME), $(shell echo "$${PWD\#\#*/}.zip"))
BUILD_DIR := $(or $(BUILD_DIR),"build")
VENDOR_AUTOLOAD := vendor/autoload.php
ZIP_FILE := build/fetch-jft.zip

ifeq ($(PROD)x, x)
	COMPOSER_ARGS := --prefer-dist --no-progress
else
	COMPOSER_ARGS := --no-dev
endif

help:  ## Print the help documentation
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

$(ZIP_FILE): $(VENDOR_AUTOLOAD)
	git archive --format=zip --output=${ZIP_FILENAME} $(COMMIT)
	zip -r ${ZIP_FILENAME} vendor/
	mkdir ${BUILD_DIR} && mv ${ZIP_FILENAME} ${BUILD_DIR}/

.PHONY: build
build: $(ZIP_FILE)  ## Build

.PHONY: clean
clean:  ## clean
	rm -rf build vendor

$(VENDOR_AUTOLOAD):
	composer install $(COMPOSER_ARGS)

.PHONY: composer
composer: $(VENDOR_AUTOLOAD) ## Runs composer install

.PHONY: lint
lint: composer ## PHP Lint
	vendor/squizlabs/php_codesniffer/bin/phpcs

.PHONY: fmt
fmt: composer ## PHP Format
	vendor/squizlabs/php_codesniffer/bin/phpcbf
