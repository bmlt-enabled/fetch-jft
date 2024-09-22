COMMIT := $(shell git rev-parse --short=8 HEAD)
ZIP_FILENAME := $(or $(ZIP_FILENAME), $(shell echo "$${PWD\#\#*/}.zip"))
BUILD_DIR := $(or $(BUILD_DIR),"build")
BASENAME := $(shell basename $(PWD))
VENDOR_AUTOLOAD := vendor/autoload.php

help:  ## Print the help documentation
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: build
build:  ## Build
	git archive --format=zip --output=${ZIP_FILENAME} $(COMMIT)
	mkdir ${BUILD_DIR} && mv ${ZIP_FILENAME} ${BUILD_DIR}/

.PHONY: clean
clean:  ## clean
	rm -rf build dist

$(VENDOR_AUTOLOAD):
	composer install --prefer-dist --no-progress

.PHONY: composer
composer: $(VENDOR_AUTOLOAD) ## Runs composer install

.PHONY: lint
lint: composer ## PHP Lint
	vendor/squizlabs/php_codesniffer/bin/phpcs

.PHONY: fmt
fmt: composer ## PHP Fmt
	vendor/squizlabs/php_codesniffer/bin/phpcbf

.PHONY: dev
dev:  ## Docker up
	docker-compose up

.PHONY: mysql
mysql:  ## Runs mysql cli in mysql container
	docker exec -it $(BASENAME)-db-1 mariadb -u root -psomewordpress wordpress

.PHONY: bash
bash:  ## Runs bash shell in wordpress container
	docker exec -it -w /var/www/html $(BASENAME)-wordpress-1 bash
