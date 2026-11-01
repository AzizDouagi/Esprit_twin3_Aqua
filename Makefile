COMPOSE = docker compose --env-file .env.docker
.PHONY: build up down logs shell migrate seed test lint analyse ci health prod-config
build:
	$(COMPOSE) build
up:
	$(COMPOSE) up -d --wait
down:
	$(COMPOSE) down
logs:
	$(COMPOSE) logs -f --tail=100
shell:
	$(COMPOSE) exec app sh
migrate:
	$(COMPOSE) run --rm migrate
seed:
	$(COMPOSE) exec app php artisan db:seed --force
test:
	php artisan test
lint:
	vendor/bin/pint --test
analyse:
	vendor/bin/phpstan analyse --memory-limit=1G
ci:
	composer ci
health:
	curl --fail http://localhost:8080/up
	curl --fail http://localhost:8080/health
prod-config:
	docker compose --env-file .env.production -f docker-compose.prod.yml config --quiet
