#!/bin/bash

# 日本語クイックリファレンス

cat << 'EOF'

╔════════════════════════════════════════════════════════════╗
║     ブログ・アセットシステム クイックリファレンス          ║
╚════════════════════════════════════════════════════════════╝

📚 主要なURL
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  ブログ
  ───────────────────────────────────────────────
  📖  一覧表示      http://localhost:8000/blog/
  📝  新規作成      http://localhost:8000/blog/create
  ⚙️  管理画面      http://localhost:8000/blog/manage
  
  アセット
  ───────────────────────────────────────────────
  📦  一覧表示      http://localhost:8000/assets/
  ⬆️  アップロード  http://localhost:8000/assets/upload
  🔍  検索         http://localhost:8000/assets/search

🚀 セットアップ
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  1. ディレクトリ作成
     mkdir -p public/uploads/assets
     chmod 755 public/uploads/assets

  2. Docker起動
     docker-compose up -d

  3. データベース初期化
     docker-compose exec app \\
       ./bin/console doctrine:migrations:migrate

  4. キャッシュクリア（オプション）
     docker-compose exec app \\
       ./bin/console cache:clear

💻 主要なコマンド
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  コンテナ管理
  ───────────────────────────────────────────────
  起動:   docker-compose up -d
  停止:   docker-compose down
  ログ:   docker-compose logs -f app
  
  Symfony コマンド
  ───────────────────────────────────────────────
  # コンソール実行
  docker-compose exec app ./bin/console {command}
  
  # マイグレーション
  doctrine:migrations:migrate       # 実行
  doctrine:migrations:list          # 確認
  doctrine:migrations:rollback      # ロールバック
  
  # キャッシュ
  cache:clear                       # クリア
  cache:warmup                      # ウォームアップ
  
  # アセット
  asset-map:compile                # コンパイル
  
  # データベース
  doctrine:database:create          # 作成
  doctrine:database:drop            # 削除

🔑 実装されている機能
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  ブログシステム
  ───────────────────────────────────────────────
  ✅ ポスト作成・編集・削除
  ✅ ステータス管理（下書き/公開/アーカイブ）
  ✅ 公開済みポストのみ表示
  ✅ 全ポスト管理画面
  ✅ 作成日時・更新日時の自動記録

  アセットシステム
  ───────────────────────────────────────────────
  ✅ ファイルアップロード（最大100MB）
  ✅ 3種類のタイプ（ワールド/アセット/プロップ）
  ✅ タイプ別フィルター
  ✅ キーワード検索
  ✅ ダウンロード・削除機能
  ✅ ファイル情報の自動保存

  セキュリティ
  ───────────────────────────────────────────────
  ✅ CSRF保護
  ✅ ファイル検証
  ✅ MIMEタイプチェック
  ✅ ファイルサイズ制限
  ✅ 削除確認ダイアログ

📁 ファイル構成
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  新規作成ファイル (23個)
  ───────────────────────────────────────────────
  
  src/Entity/
  ├── BlogPost.php              ブログ定義
  └── Asset.php                 アセット定義

  src/Repository/
  ├── BlogPostRepository.php     ブログ検索
  └── AssetRepository.php        アセット検索

  src/Controller/
  ├── BlogPostController.php     ブログ管理（11メソッド）
  └── AssetController.php        アセット管理（7メソッド）

  src/Form/
  ├── BlogPostType.php           ブログフォーム
  └── AssetType.php              アップロードフォーム

  templates/
  ├── blog/
  │   ├── index.html.twig        一覧
  │   ├── show.html.twig         表示
  │   ├── form.html.twig         フォーム
  │   └── manage.html.twig       管理
  └── asset/
      ├── index.html.twig        一覧
      ├── show.html.twig         詳細
      ├── upload.html.twig       アップロード
      └── search.html.twig       検索結果

  config/
  ├── routes/controllers.yaml    ルーティング
  └── packages/upload.yaml       アップロード設定

  migrations/
  └── Version20260603000000.php  DB初期化

📖 ドキュメント
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  SETUP_GUIDE.md                詳しいセットアップ手順
  BLOG_ASSET_README.md          機能の詳細説明
  IMPLEMENTATION.md             実装内容サマリー
  QUICK_REFERENCE.sh            このファイル

🐛 トラブルシューティング
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  問題: データベース接続エラー
  解決: docker-compose exec app \\
        ./bin/console doctrine:database:create

  問題: ファイルアップロード失敗
  解決: chmod 755 public/uploads/assets

  問題: テンプレートエラー
  解決: docker-compose exec app \\
        ./bin/console cache:clear

  問題: マイグレーション失敗
  解決: docker-compose exec app \\
        ./bin/console doctrine:migrations:migrate

❓ よくある質問
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  Q: ポートを変更するには？
  A: docker-compose.yml の "ports" を変更

  Q: ファイルサイズ上限を変更するには？
  A: src/Form/AssetType.php の maxSize を変更

  Q: アセットタイプを追加するには？
  A: src/Entity/Asset.php の type フィールドを編集

  Q: ブログのステータスを追加するには？
  A: src/Entity/BlogPost.php の status フィールドを編集

  Q: ファイル保存先を変更するには？
  A: src/Controller/AssetController.php の
     uploadDirectory を変更

✨ さらに詳しい情報
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  詳細なドキュメント:
  📖 SETUP_GUIDE.md
  📖 BLOG_ASSET_README.md

  実装完了チェックリスト:
  ✅ IMPLEMENTATION.md

═════════════════════════════════════════════════════════════

  実装日: 2026年6月3日
  バージョン: 1.0.0
  Symfony: 8.1
  PHP: 8.4+

═════════════════════════════════════════════════════════════

EOF
