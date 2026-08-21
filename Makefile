.PHONY: up down shell test run

up:
	docker compose up --build

down:
	docker compose down

shell:
	docker compose exec app /bin/bash

test:
	docker compose run --rm app vendor/bin/phpunit tests --colors=always --testdox

run:
	docker compose exec app php symfony/bin/console vending-machine:run