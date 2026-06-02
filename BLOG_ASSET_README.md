# ブログ投稿システム & アセットアップロード機能

このドキュメントでは、ブログ投稿システムとアセットアップロード機能の使用方法を説明します。

## 機能概要

### ブログシステム
- ブログポストの作成、編集、削除
- ステータス管理（下書き、公開、アーカイブ）
- ポスト一覧表示
- 公開されたポストのみを表示

### アセットアップロードシステム
- 3種類のアセットタイプ: ワールド、アセット、プロップ
- ファイルアップロード（最大100MB）
- アセット検索機能
- タイプ別フィルター
- ダウンロード機能

## インストール手順

### 1. マイグレーション実行
```bash
./bin/console doctrine:migrations:migrate
```

このコマンドで以下のテーブルが自動作成されます：
- `blog_posts` - ブログポスト
- `assets` - アセット

### 2. アップロードディレクトリ作成
```bash
mkdir -p public/uploads/assets
chmod 755 public/uploads/assets
```

## ルート一覧

### ブログ
- `GET /blog/` - ブログ一覧（公開されたポストのみ）
- `GET /blog/post/{id}` - ブログポスト表示
- `GET /blog/create` - ブログポスト作成フォーム
- `POST /blog/create` - ブログポスト作成実行
- `GET /blog/edit/{id}` - ブログポスト編集フォーム
- `POST /blog/edit/{id}` - ブログポスト編集実行
- `POST /blog/delete/{id}` - ブログポスト削除
- `GET /blog/manage` - 全ブログポスト管理画面

### アセット
- `GET /assets/` - アセット一覧
- `GET /assets/type/{type}` - タイプ別アセット一覧
  - `world` - ワールド
  - `asset` - アセット
  - `prop` - プロップ
- `GET /assets/{id}` - アセット詳細表示
- `GET /assets/upload` - アップロードフォーム
- `POST /assets/upload` - ファイルアップロード実行
- `GET /assets/{id}/download` - ファイルダウンロード
- `POST /assets/{id}/delete` - アセット削除
- `GET /assets/search` - アセット検索

## 開発メモ
- システムチェック:
  - `./bin/console doctrine:migrations:status` でマイグレーション状態を確認
  - `./bin/console cache:clear` でキャッシュをリセット
  - Docker構成では `docker-compose ps` / `docker-compose logs` を使ってコンテナ状態を確認
- テスト駆動開発(TDD):
  - PHPUnit構成は `phpunit.dist.xml` にあり、テストは `tests/` 配下に追加します
  - 既存テストがない場合でも、まず `tests/` に仕様ベースのケースを作成してから実装を進めると良い
  - 実行例: `./bin/phpunit` または `docker-compose exec app ./bin/phpunit`
- アセットアップロードメモ:
  - アップロード先は `public/uploads/assets/` です
  - 権限確認: `chmod 755 public/uploads/assets`
  - PHP設定の `upload_max_filesize` と `post_max_size` は 100MB 以上に設定する必要があります
  - ファイルサイズ制限、MIMEタイプチェック、ファイル名サニタイズを必ず通すこと

## エンティティ

### BlogPost
```php
- id: int (PK)
- title: string (255)
- content: text
- createdAt: datetime
- updatedAt: datetime (nullable)
- status: string (draft|published|archived)
```

### Asset
```php
- id: int (PK)
- name: string (255)
- description: text (nullable)
- type: string (world|asset|prop)
- filename: string (255)
- fileSize: int
- mimeType: string (50)
- uploadedAt: datetime
- updatedAt: datetime (nullable)
- uploadedBy: string (255, nullable)
- thumbnailPath: string (255, nullable)
```

## 使用例

### ブログポストを作成する

1. `/blog/create` へアクセス
2. タイトルと内容を入力
3. ステータスを選択（デフォルト: 下書き）
4. 保存ボタンをクリック

公開するには、ステータスを「公開」に変更して保存します。

### アセットをアップロードする

1. `/assets/upload` へアクセス
2. 以下の情報を入力：
   - アセット名
   - 説明（オプション）
   - タイプ（ワールド/アセット/プロップ）
   - ファイル（最大100MB）
3. アップロードボタンをクリック

## ファイル構成

```
src/
  Entity/
    - BlogPost.php          # ブログポスト エンティティ
    - Asset.php             # アセット エンティティ
  Repository/
    - BlogPostRepository.php # ブログポスト リポジトリ
    - AssetRepository.php    # アセット リポジトリ
  Controller/
    - BlogPostController.php # ブログポスト コントローラ
    - AssetController.php    # アセット コントローラ
  Form/
    - BlogPostType.php       # ブログポスト フォーム
    - AssetType.php          # アセット フォーム

templates/
  blog/
    - index.html.twig        # ブログ一覧
    - show.html.twig         # ブログ表示
    - form.html.twig         # ブログフォーム
    - manage.html.twig       # ブログ管理
  asset/
    - index.html.twig        # アセット一覧
    - show.html.twig         # アセット詳細
    - upload.html.twig       # アップロードフォーム
    - search.html.twig       # 検索結果

migrations/
  - Version20260603000000.php # データベース マイグレーション

config/
  packages/
    - upload.yaml            # アップロード設定
  routes/
    - controllers.yaml       # ルーティング設定

public/uploads/assets/       # アップロードされたファイル保存先
```

## セキュリティに関する注意

1. **ファイルアップロード**
   - ファイルサイズは最大100MBに制限されています
   - 許可されたMIMEタイプ: ZIP, RAR, 7Z, PNG, JPEG, GIF, octet-stream
   - ファイル名は自動でサニタイズされます

2. **CSRF保護**
   - 全てのフォーム送信にはCSRFトークン検証が実装されています
   - 削除操作には確認ダイアログが表示されます

3. **アセットアクセス**
   - ファイルは `public/uploads/assets/` に保存されます
   - 直接アクセスには `asset_download` ルートを使用してください

## トラブルシューティング

### マイグレーションが実行されない場合
```bash
./bin/console doctrine:migrations:status
./bin/console doctrine:migrations:migrate -n
```

### ファイルアップロードが失敗する場合
- アップロードディレクトリの権限を確認：`chmod 755 public/uploads/assets`
- PHPのアップロード設定を確認：`php.ini` の `upload_max_filesize` と `post_max_size`
- ディスク容量を確認

### テンプレートエラーが出る場合
- キャッシュをクリア：`./bin/console cache:clear`
- アセットをリビルド：`php bin/console asset-map:compile`

## 今後の機能拡張案

- [ ] ユーザー認証機能
- [ ] コメント機能
- [ ] タグ機能
- [ ] サムネイル生成
- [ ] ページネーション
- [ ] バージョン管理
- [ ] ドラッグ&ドロップアップロード
- [ ] 複数ファイル同時アップロード
- [ ] 画像プレビュー機能
