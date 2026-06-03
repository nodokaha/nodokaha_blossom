# Symfony MySQL + Test Environment Skill

## 目的
Symfonyプロジェクトのテスト環境をMySQLに移行し、テスト実行時に必要なデータベースとスキーマを自動生成できるようにする。

## 変更内容

- `config/packages/test/framework.yaml`
  - テスト環境でも `asset_mapper` を有効化し、アセットルーティングをテスト実行で維持。

- `docker-compose.yml`
  - MySQLコンテナに `docker/mysql-init.sql` をマウントして、`app_test` データベースと `app` ユーザーの権限を初期化。

- `docker/mysql-init.sql`
  - `app_test` データベースを作成。
  - `app` ユーザーに対して `app_test` への権限を付与。

- `tests/bootstrap.php`
  - `APP_ENV=test` でテスト実行時にSymfonyカーネルを起動し、Doctrineのスキーマを自動更新してテストDBを準備。

## 確認手順

1. `docker compose -f docker-compose.yml down`
2. `docker volume rm nodokaha_blossom_mysql_data`
3. `docker compose -f docker-compose.yml up -d --build`
4. `docker compose -f docker-compose.yml exec -T app sh -lc 'APP_ENV=test php ./vendor/bin/phpunit -c phpunit.dist.xml tests/Controller/AssetControllerTest.php'`

## 結果

- `AssetControllerTest` が `OK (2 tests, 4 assertions)` で通過。

## 補足

- 既存のMySQLボリュームを再生成する必要があるため、ローカルDBデータはリセットされる。
- `tests/bootstrap.php` のスキーマ生成はテスト専用であり、本番環境では使用されない。
