DC = docker compose
RUN = $(DC) run --rm php72

.PHONY: build composer test phpstan ci

build:
	$(DC) build

composer:
	$(RUN) composer install

test:
	$(RUN) composer test-unit && $(RUN) composer test-integration

phpstan:
	$(RUN) composer phpstan

ci: composer phpstan test
