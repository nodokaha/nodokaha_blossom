.PHONY: setup test rebuild-db cache-clear down phpstan ci

COMPOSE=docker compose -f docker-compose.yml
APP=app

setup:
	@$(COMPOSE) up -d --build
	@$(COMPOSE) exec -T $(APP) composer install
	@$(COMPOSE) exec -T $(APP) php bin/console doctrine:database:create --if-not-exists --no-interaction
	@$(COMPOSE) exec -T $(APP) php bin/console doctrine:schema:create --no-interaction
	@$(COMPOSE) exec -T $(APP) php bin/console doctrine:fixtures:load --no-interaction
	@$(COMPOSE) exec -T $(APP) php bin/console cache:clear
	@$(COMPOSE) exec -T $(APP) php bin/console cache:warm
	@$(COMPOSE) exec -T $(APP) php bin/console assets:install --symlink --relative public

test:
	@$(COMPOSE) exec -T $(APP) sh -lc 'APP_ENV=test php bin/phpunit'

phpstan:
	@$(COMPOSE) exec -T $(APP) vendor/bin/phpstan analyse -c phpstan.neon

ci: test phpstan

rebuild-db:
	@$(COMPOSE) exec -T $(APP) php bin/console doctrine:database:drop --force --if-exists --no-interaction
	@$(COMPOSE) exec -T $(APP) php bin/console doctrine:database:create --if-not-exists --no-interaction
	@$(COMPOSE) exec -T $(APP) php bin/console doctrine:schema:create --no-interaction
	@$(COMPOSE) exec -T $(APP) php bin/console doctrine:fixtures:load --no-interaction
	@$(COMPOSE) exec -T $(APP) php bin/console cache:clear
	@$(COMPOSE) exec -T $(APP) php bin/console cache:warm
	@$(COMPOSE) exec -T $(APP) php bin/console assets:install --symlink --relative public

cache-clear:
	@$(COMPOSE) exec -T $(APP) php bin/console cache:clear

down:
	@$(COMPOSE) down