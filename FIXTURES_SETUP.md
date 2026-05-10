# Doctrine Fixtures Bundle Setup Guide

## インストール状況
DoctrineFixturesBundleはすでにインストールされています：
```bash
composer show | grep fixtures
# doctrine/doctrine-fixtures-bundle ^4.3
```

## 設定ファイル
- **config/packages/doctrine_fixtures.yaml**: DoctrineFixturesBundleの設定

## Fixturesディレクトリ構成
```
src/DataFixtures/
├── AppFixtures.php         # メインのFixturesクラス
└── TestFixtures.php        # テスト用Fixturesクラス
```

## 使用方法

### 1. Fixuresクラスの作成
`src/DataFixtures/` に新しいFixturesクラスを作成：

```php
<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password');
        
        $manager->persist($user);
        $manager->flush();
    }
}
```

### 2. テストでのFixturesの使用
`FixtureWebTestCase` を継承してテストを作成：

```php
<?php

namespace App\Tests\Controller;

use App\Tests\FixtureWebTestCase;

class UserControllerTest extends FixtureWebTestCase
{
    public function testUserList(): void
    {
        // Fixturesを自動的に読み込む
        $this->loadFixtures([UserFixtures::class]);
        
        $client = static::createClient();
        $client->request('GET', '/users');
        
        $this->assertResponseIsSuccessful();
    }
}
```

### 3. コマンドラインでのFixtures読み込み
開発環境でデータベースにFixturesを読み込む：

```bash
# すべてのFixturesを読み込む
php bin/console doctrine:fixtures:load

# 特定のFixturesのみ読み込む
php bin/console doctrine:fixtures:load --fixtures=src/DataFixtures/UserFixtures.php

# 確認なしで読み込む
php bin/console doctrine:fixtures:load --no-interaction
```

## テスト環境設定
- テスト用データベース: SQLite (var/test.db)
- 環境設定: `.env.test.local`
- 自動的にデータベースをパージしてFixturesを読み込み

## Fixturesの順序制御
複数のFixturesクラスの読み込み順序を制御する場合は、
`OrderedFixtureInterface`を実装：

```php
<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderedUserFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Fixturesコード
    }

    public function getOrder(): int
    {
        return 1; // 優先順位
    }
}
```

## 参考資料
- https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
