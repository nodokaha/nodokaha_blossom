setup:
	@docker compose up -d --build
	@docker compose exec -T app composer install
	@docker compose exec -T app php bin/console doctrine:database:create --if-not-exists --no-interaction
	@docker compose exec -T app php bin/console doctrine:schema:create --no-interaction
test:
	@docker compose exec -T app php bin/phpunit
cache-clear:
	@docker compose exec -T app php bin/console cache:clear