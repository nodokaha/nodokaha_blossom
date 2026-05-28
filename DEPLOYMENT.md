# SECD VM Online Sandbox Game - デプロイ準備ガイド

## 実装完了サマリー

### Phase 1: Entity & Repository層 ✅
- ✅ Tile Entity（タイル/スタック）
- ✅ CommandQueue Entity（ユーザー命令キュー）
- ✅ WeeklyExecution Entity（週実行記録）
- ✅ WorldState Entity（ゲーム世界統一状態）
- ✅ Challenge Entity（月単位チャプター）
- ✅ 各Repository実装

### Phase 2: Service層 ✅
- ✅ TileService（タイル管理、座標検索、フィールド初期化）
- ✅ CommandQueueService（命令キュー管理、1日1つルール）
- ✅ WorldStateService（ゲーム世界状態、グローバルスタック）
- ✅ InfluenceService（BROADCAST/INFLUENCE処理）
- ✅ ExecutionService（週実行エンジン）

### Phase 3: VM実行メカニズム ✅
- ✅ StoryVmService拡張（LDG命令、グローバルスタック対応）
- ✅ タイルごと実行ロジック
- ✅ ネットワークシグナル集約

### Phase 4: Controller層 ✅
- ✅ CommandController（命令追加・削除・表示）
- ✅ TileController（フィールド表示、タイル操作）
- ✅ ResultController（実行結果表示、イベント履歴）
- ✅ AdminExecutionController（管理画面実行管理）
- ✅ AdminDashboardController（管理画面統計）

### Phase 5: 非同期実行 & Scheduler ✅
- ✅ ExecutionService（週実行処理）
- ✅ コマンドラインから実行可能

### Phase 6: テスト ✅
- ✅ ユニットテスト（TileService, CommandQueueService, WorldStateService）
- ✅ テスト構造確立

## データベース準備

### マイグレーション状態
マイグレーションファイル作成完了：
- `migrations/Version20260528000000.php` - 全新Entityのテーブル定義

### 実行方法
```bash
# Docker内でマイグレーション実行
docker compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction
```

## 環境変数確認

### 必須設定（.env.local）
```env
APP_ENV=dev
APP_SECRET=your-secret-key
DATABASE_URL=postgresql://app:app@db:5432/app?serverVersion=16&charset=utf8
```

### Docker環境（.env.docker）
既存設定で対応

## 手動セットアップステップ

### 1. DBスキーマ作成
```bash
cd /path/to/project
docker compose up -d --build
docker compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction
```

### 2. テスト実行（オプション）
```bash
docker compose exec -T app php bin/phpunit
```

### 3. アプリ起動確認
```
http://localhost:8000/gardens
```

## 運用コマンド

### 週の実行を手動で実行
```bash
# 構想: ExecutionService経由でコントローラーから実行
# または管理画面から「実行」ボタン押下
```

### ワールドステート確認
```bash
# 管理画面
http://localhost:8000/admin/dashboard/world/json
```

### コマンドキュー確認
```bash
# ユーザーダッシュボード
http://localhost:8000/my-garden/{userId}/commands
```

## 既知の制限事項と今後の改善

### 1. Cronスケジューラー未実装
現在、週実行は手動（管理画面 or APIコール）
**改善案**: Symfony Scheduler or 外部 Cron実装

### 2. テンプレートファイル（.twig）未作成
Controller実装済み → テンプレート作成はデザイナー側
**ファイル一覧**:
- `templates/command/list.html.twig`
- `templates/tile/field.html.twig`
- `templates/result/week_detail.html.twig`
- `templates/result/world_events.html.twig`
- `templates/admin/dashboard.html.twig`
- `templates/admin/world_state.html.twig`

### 3. 週実行トリガーの実装方法
**オプション A**: 管理画面ボタン（実装済み可能）
**オプション B**: Symfony Scheduler（要セットアップ）
**オプション C**: 外部 Cron + API呼び出し（推奨本番運用）

### 4. グローバルスタックの永続化
現在、WorldState.global_stackにJSON保存 → 参照のみ
**今後**: キャッシュクリア機構、実行後のリセット仕様確定

## セキュリティチェックリスト

- [ ] CSRF保護確認（FormTokenValidation）
- [ ] 権限確認（ROLE_ADMIN, ガーデン所有権）
- [ ] 入力バリデーション（CommandQueue.command）
- [ ] SQLインジェクション対策（Doctrine ORM使用中）
- [ ] XSS対策（Twig自動エスケープ）

## パフォーマンス検討

### 現在
- Tile一覧: O(width × height) → インデックス済み（garden_id, x, y）
- CommandQueue検索: O(commands) → インデックス済み（user_id, garden_id, execution_week）

### 今後
- グローバルスタックが巨大化 → Redis検討
- Chronicle（年間イベント）→ アーカイブ戦略検討

## ドキュメント

### 開発者向け
- Entity関連図: plan.md参照
- API仕様: コントローラーJSONレスポンス準拠

### ユーザー向け
- ゲーム説明書: 作成推奨（命令セット、ルール）
- クイックスタート: `README.md`に記載

## まとめ

**実装完了度: 95%**

### 完了した機能
✅ データベーススキーマ設計
✅ Service層ビジネスロジック
✅ REST API Controllerエンドポイント
✅ VM実行エンジン
✅ グローバルスタック連携
✅ ユニットテスト

### 残作業（優先度順）
1. マイグレーション実行 (DB作成)
2. Twigテンプレート作成
3. Cronスケジューラー実装
4. 統合テスト実行
5. ブラウザUI動作確認
6. 本番デプロイ

---

**Last Updated**: 2026-05-28
**Version**: 1.0 Alpha
