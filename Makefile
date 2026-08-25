.PHONY: up down shell test run migrate

COMPOSE := docker compose -f docker-compose.yml
ifdef DEBUG
COMPOSE := docker compose -f docker-compose.yml -f docker-compose.debug.yml
endif

up:
	$(COMPOSE) run --rm app composer install
	$(COMPOSE) up --build

down:
	$(COMPOSE) down

shell:
	$(COMPOSE) exec app /bin/bash

test:
	$(COMPOSE) run --rm app vendor/bin/phpunit --colors=always --testdox

run:
	$(COMPOSE) exec app php symfony/bin/console vending-machine:run

migrate:
	$(COMPOSE) exec app php symfony/bin/console doctrine:migrations:migrate --no-interaction