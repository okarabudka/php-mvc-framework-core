# Makefile — runs Composer and the quality checks in a PHP 8.5 container. Run `make help` for the list of targets.

SHELL := /bin/bash
.DEFAULT_GOAL := help

IMAGE := php-mvc-framework-core-dev
RUN := docker run --rm -u $(shell id -u):$(shell id -g) -v "$(CURDIR)":/app -w /app $(IMAGE)

.PHONY: help image install test coverage stan cs fix check

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-9s\033[0m %s\n", $$1, $$2}'

image: ## Build the PHP 8.5 development image
	docker build -q -t $(IMAGE) .

install: image ## Install Composer dependencies
	$(RUN) composer install --no-interaction

test: ## Run the PHPUnit test suite
	$(RUN) composer test

coverage: ## Run the tests with a text coverage report
	$(RUN) composer coverage

stan: ## Run PHPStan (level max, sources and tests)
	$(RUN) composer stan

cs: ## Check code style (PHP CS Fixer, dry run)
	$(RUN) composer cs

fix: ## Fix code style (PHP CS Fixer)
	$(RUN) composer fix

check: cs stan test ## Run all checks: code style, static analysis, tests
