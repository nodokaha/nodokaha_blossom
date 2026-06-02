# ブログ・アセットシステム セットアップガイド

このガイドでは、ブログ投稿システムとアセットアップロード機能のセットアップ手順を説明します。

## 📋 概要

実装されたシステム：
- ✅ ブログ投稿システム（作成、編集、削除、ステータス管理）
- ✅ アセットアップロード機能（ワールド、アセット、プロップの3種類）
- ✅ ファイル管理（アップロード、ダウンロード、削除）
- ✅ 検索・フィルター機能

## 🚀 クイックスタート

### 1. 初期セットアップ
```bash
# セットアップスクリプトを実行
bash setup-blog.sh

# または手動で実行
mkdir -p public/uploads/assets
chmod 755 public/uploads/assets
```

### 2. Dockerコンテナの起動
```bash
docker-compose up -d
```

### 3. データベースマイグレーション
```bash
docker-compose exec app ./bin/console doctrine:migrations:migrate
```

### 4. アプリケーションアクセス
- ブログ: [http://localhost:8000/blog/](http://localhost:8000/blog/)
- アセット: [http://localhost:8000/assets/](http://localhost:8000/assets/)

## 📁 プロジェクト構成

### 新規作成ファイル

#### エンティティ
- `src/Entity/BlogPost.php` - ブログポスト
- `src/Entity/Asset.php` - アセット

#### リポジトリ
- `src/Repository/BlogPostRepository.php` - ブログポスト用
- `src/Repository/AssetRepository.php` - アセット用

#### コントローラー
- `src/Controller/BlogPostController.php`
  - ブログ一覧、表示、作成、編集、削除、管理
- `src/Controller/AssetController.php`
  - アセット一覧、表示、アップロード、ダウンロード、削除、検索

#### フォーム
- `src/Form/BlogPostType.php` - ブログ投稿フォーム
- `src/Form/AssetType.php` - アセットアップロードフォーム

#### テンプレート
ブログ:
- `templates/blog/index.html.twig` - ブログ一覧
- `templates/blog/show.html.twig` - ブログ表示
- `templates/blog/form.html.twig` - ブログフォーム
- `templates/blog/manage.html.twig` - 管理画面

アセット:
- `templates/asset/index.html.twig` - アセット一覧
- `templates/asset/show.html.twig` - アセット詳細
- `templates/asset/upload.html.twig` - アップロードフォーム
- `templates/asset/search.html.twig` - 検索結果

#### 設定
- `config/routes/controllers.yaml` - ルーティング設定
- `config/packages/upload.yaml` - アップロード設定
- `migrations/Version20260603000000.php` - データベースマイグレーション

#### ドキュメント
- `BLOG_ASSET_README.md` - 詳細ドキュメント
- `SETUP_GUIDE.md` - このファイル

## 🎯 主要機能

### ブログシステム

#### ブログポストの作成
```
POST /blog/create
- タイトル（必須）
- 内容（必須）
- ステータス（下書き/公開/アーカイブ）
```

#### ブログポストの閲覧
```
GET /blog/ - 公開されたポストのみ表示
GET /blog/post/{id} - 特定のポストを表示
GET /blog/manage - 全ポストの管理画面
```

#### ブログポストの編集・削除
```
POST /blog/edit/{id} - ポストを編集
POST /blog/delete/{id} - ポストを削除
```

### アセットシステム

#### アセットのアップロード
```
POST /assets/upload
- アセット名（必須）
- 説明（オプション）
- タイプ: world(ワールド), asset(アセット), prop(プロップ)
- ファイル（最大100MB）
```

#### アセットの閲覧
```
GET /assets/ - 全アセット表示
GET /assets/type/{type} - タイプ別表示
GET /assets/search - 検索機能
```

#### アセットのダウンロード・削除
```
GET /assets/{id}/download - ファイルをダウンロード
POST /assets/{id}/delete - アセットを削除
```

## 🔒 セキュリティ機能

✅ CSRF保護が実装されています
✅ ファイルサイズ制限（最大100MB）
✅ MIMEタイプ検証
✅ ファイル名サニタイズ
✅ DELETE操作に確認ダイアログ

## 📦 データベーススキーマ

### blog_posts テーブル
```
id (SERIAL PRIMARY KEY)
title (VARCHAR 255)
content (TEXT)
created_at (TIMESTAMP)
updated_at (TIMESTAMP, nullable)
status (VARCHAR 50)
```

### assets テーブル
```
id (SERIAL PRIMARY KEY)
name (VARCHAR 255)
description (TEXT, nullable)
type (VARCHAR 50)
filename (VARCHAR 255)
file_size (INT)
mime_type (VARCHAR 50)
uploaded_at (TIMESTAMP)
updated_at (TIMESTAMP, nullable)
uploaded_by (VARCHAR 255, nullable)
thumbnail_path (VARCHAR 255, nullable)

インデックス:
- idx_assets_type (type)
- idx_assets_uploaded_at (uploaded_at)
```

## 🔧 管理コマンド

```bash
# データベースマイグレーション
docker-compose exec app ./bin/console doctrine:migrations:migrate

# マイグレーション状態確認
docker-compose exec app ./bin/console doctrine:migrations:list

# キャッシュクリア
docker-compose exec app ./bin/console cache:clear

# アセットコンパイル
docker-compose exec app php bin/console asset-map:compile

# データベース接続テスト
docker-compose exec app ./bin/console doctrine:database:create
```

## 開発チェック & TDDメモ
- システムチェック:
  - `docker-compose ps` でサービスが起動しているか確認
  - `docker-compose exec app ./bin/console doctrine:migrations:status` でDBマイグレーション状態を確認
  - `docker-compose exec app ./bin/console cache:clear` でテンプレートと設定のキャッシュをリセット
- テスト駆動開発:
  - テストは `tests/` 配下に追加し、 `phpunit.dist.xml` を使って実行します
  - 既存ケースがない場合は、まず期待される振る舞いをテストとして書いてから実装を進めてください
  - 実行例: `docker-compose exec app ./bin/phpunit` または `./bin/phpunit`
- アセットアップロード時のメモ:
  - アップロード先ディレクトリ: `public/uploads/assets/`
  - パーミッションは `chmod 755 public/uploads/assets`
  - PHPの `upload_max_filesize` と `post_max_size` は 100MB 以上に設定
  - アップロード処理では MIMEタイプチェックとファイル名サニタイズが必要

## 🐛 トラブルシューティング

### データベース接続エラー
```bash
# マイグレーション前にDBが存在するか確認
docker-compose exec app ./bin/console doctrine:database:create

# マイグレーション実行
docker-compose exec app ./bin/console doctrine:migrations:migrate
```

### ファイルアップロード失敗
```bash
# ディレクトリの権限を確認
ls -la public/uploads/assets/

# 権限を修正
chmod 755 public/uploads/assets
```

### テンプレートエラー
```bash
# キャッシュをクリア
docker-compose exec app ./bin/console cache:clear

# アセットをリコンパイル
docker-compose exec app php bin/console asset-map:compile
```

## 📚 詳細ドキュメント

詳しい使用方法は `BLOG_ASSET_README.md` を参照してください。

## 🎨 カスタマイズ例

### アセットタイプを追加する場合
1. `src/Entity/Asset.php` の type フィールドの choices を更新
2. `src/Form/AssetType.php` のフォームを更新
3. `src/Controller/AssetController.php` のバリデーションを更新

### ブログのステータスを追加する場合
1. `src/Entity/BlogPost.php` の status フィールドの choices を更新
2. `src/Form/BlogPostType.php` のフォームを更新
3. テンプレートのバッジ色を更新

## 📖 参考リンク

- [Symfony公式ドキュメント](https://symfony.com/doc)
- [Doctrine ORM](https://www.doctrine-project.org/)
- [Twig テンプレートエンジン](https://twig.symfony.com/)

## ✨ 今後の拡張機能候補

- ユーザー認証とロール管理
- コメント・リアクション機能
- タグ・カテゴリ機能
- サムネイル生成
- バージョン管理
- バッチ処理
- API実装
- キャッシング最適化

---

**セットアップガイド作成日**: 2026年6月3日
**Symfony バージョン**: 8.1
**PHP バージョン**: 8.4以上
