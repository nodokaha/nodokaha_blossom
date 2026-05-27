#!/bin/bash
cd /mnt/c/Users/genne/nodokaha_blossom-1.worktrees/agents-online-sandbox-game-design-symfony
docker compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction
