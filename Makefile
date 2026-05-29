setup:
	mkdir -p var/cache/uploader/chunks
	mkdir -p public/uploads/tmp
	@docker compose up -d --build
	@docker compose exec -T app composer install
	@docker compose exec -T app php bin/console doctrine:database:create --if-not-exists --no-interaction
	@docker compose exec -T app php bin/console doctrine:schema:create --no-interaction
	@docker compose exec -T app php bin/console doctrine:schema:create --no-interaction
	@docker compose exec -T app php bin/console doctrine:fixtures:load --no-interaction
	@docker compose exec -T app php bin/console cache:clear
	@docker compose exec -T app php bin/console cache:warm
	@docker compose exec -T app php bin/console assets:install --symlink --relative public
test:
	@docker compose exec -T app php bin/phpunit
rebuild-db:
	@docker compose exec -T app php bin/console doctrine:database:drop --force --if-exists --no-interaction
	@docker compose exec -T app php bin/console doctrine:database:create --if-not-exists --no-interaction
	@docker compose exec -T app php bin/console doctrine:schema:create --no-interaction
	@docker compose exec -T app php bin/console doctrine:fixtures:load --no-interaction
	@docker compose exec -T app php bin/console cache:clear
	@docker compose exec -T app php bin/console cache:warm
	@docker compose exec -T app php bin/console assets:install --symlink --relative public
cache-clear:
	@docker compose exec -T app php bin/console cache:clear
down:
	@docker compose down