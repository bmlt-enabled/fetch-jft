COMMIT := $(shell git rev-parse --short=8 HEAD)
ZIP_FILENAME := $(or $(ZIP_FILENAME),"bmlt-build-file.zip")
BUILD_DIR := $(or $(BUILD_DIR),"build")

help:  ## Print the help documentation
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: build
build:  ## Build
	git archive --format=zip --output=${ZIP_FILENAME} $(COMMIT)
	mkdir ${BUILD_DIR} && mv ${ZIP_FILENAME} ${BUILD_DIR}/

.PHONY: clean
clean:  ## clean
	rm -rf build dist

.PHONY: composer
composer:  ## Composer Install
	composer install --prefer-dist --no-progress --no-suggest

.PHONY: lint
lint:  ## PHP Lint
	vendor/squizlabs/php_codesniffer/bin/phpcs

.PHONY: lint-fix
lint-fix:  ## PHP Lint Fix
	vendor/squizlabs/php_codesniffer/bin/phpcbf
