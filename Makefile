.PHONY: up down shell test run migrate

up:
	docker compose up --build

down:
	docker compose down

shell:
	docker compose exec app /bin/bash

test:
	docker compose -f docker-compose.yml -f docker-compose.test.yml run --rm app vendor/bin/phpunit --colors=always --testdox --coverage-html /var/www/coverage-report/

run:
	docker compose exec app php symfony/bin/console vending-machine:run

migrate:
	docker compose exec app php symfony/bin/console doctrine:migrations:migrate --no-interaction