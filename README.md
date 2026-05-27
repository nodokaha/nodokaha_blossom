# nodokaha_blossom

SymfonyアプリをDocker Composeで起動するための手順です。

## 前提
- Docker / Docker Compose v2 がインストール済み

## セットアップ
1. 依存イメージをビルドして起動
   ```bash
   docker compose up -d --build
   ```
2. PHP依存ライブラリをインストール（初回）
   ```bash
   docker compose exec app composer install
   ```
3. 環境変数を配置（必要なら）
   ```bash
   cp .env.docker .env.local
   ```
4. DBスキーマ作成・マイグレーション
   ```bash
   docker compose exec app php bin/console doctrine:database:create --if-not-exists
   docker compose exec app php bin/console doctrine:migrations:migrate -n
   ```

## 起動確認
- アプリ: http://localhost:8000

## よく使うコマンド
- 停止
  ```bash
  docker compose down
  ```
- ログ確認
  ```bash
  docker compose logs -f app
  ```
- テスト
  ```bash
  docker compose exec app php bin/phpunit
  ```
