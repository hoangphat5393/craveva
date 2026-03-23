# Chạy Pest / PHPUnit trên CI

Mục tiêu: tái lập **`./vendor/bin/pest --no-coverage`** (hoặc `php artisan test`) trên pipeline sau khi đã nâng Laravel 11.

**Triển khai an toàn trong repo:** workflow **`.github/workflows/pest-mysql-manual.yml`** (chỉ **`workflow_dispatch`**) — quy trình & phân biệt Staging/Hub: **`docs/PROCEDURE_CI_PEST_SAFE.md`**.

## Điều kiện

- **PHP** cùng major với production (vd. **8.3**).
- **Composer** `install` với lock hiện tại.
- **Database:** hầu hết test Feature dùng **`DatabaseTransactions`** / DB thật — CI cần **MySQL/MariaDB** (service) hoặc bản **`.env.testing`** trỏ DB staging chỉ dùng cho test.
- File **`.env.testing`** (hoặc biến môi trường trong workflow): `APP_ENV=testing`, `DB_*`, `APP_KEY` (cùng logic mã hóa nếu có dữ liệu đã mã hóa).

## Lệnh tối thiểu

```bash
composer install --no-interaction --prefer-dist
cp .env.example .env   # hoặc inject secrets qua CI
php artisan key:generate --force
./vendor/bin/pest --no-coverage
```

Nếu migrate chưa chạy trên DB test:

```bash
php artisan migrate --force
```

## Gợi ý GitHub Actions (khung — chỉnh `DB_*` / secrets)

```yaml
name: tests
on: [push, pull_request]
jobs:
    pest:
        runs-on: ubuntu-latest
        services:
            mysql:
                image: mysql:8.0
                env:
                    MYSQL_ROOT_PASSWORD: root
                    MYSQL_DATABASE: craveva_test
                ports:
                    - 3306:3306
                options: --health-cmd="mysqladmin ping" --health-interval=10s
        steps:
            - uses: actions/checkout@v4
            - uses: shivammathur/setup-php@v2
              with:
                  php-version: "8.3"
                  extensions: mbstring, dom, curl, libxml, zip, pcntl, pdo, mysql
                  coverage: none
            - run: composer install --no-interaction --prefer-dist
            - run: cp .env.example .env && php artisan key:generate --force
              env:
                  DB_CONNECTION: mysql
                  DB_HOST: 127.0.0.1
                  DB_PORT: 3306
                  DB_DATABASE: craveva_test
                  DB_USERNAME: root
                  DB_PASSWORD: root
            - run: php artisan migrate --force
              env:
                  DB_CONNECTION: mysql
                  DB_HOST: 127.0.0.1
                  DB_DATABASE: craveva_test
                  DB_USERNAME: root
                  DB_PASSWORD: root
            - run: ./vendor/bin/pest --no-coverage
              env:
                  DB_CONNECTION: mysql
                  DB_HOST: 127.0.0.1
                  DB_DATABASE: craveva_test
                  DB_USERNAME: root
                  DB_PASSWORD: root
```

**Lưu ý:** ERP lớn có thể cần **seed tối thiểu** hoặc **dump SQL** staging — điều chỉnh theo team.

## PHPUnit 12 / metadata

Đã chuyển `/** @test */` sang **`#[Test]`** (PHPUnit `Attributes\Test`) trong một số file Feature — tránh cảnh báo “doc-comment metadata deprecated”.

---

_Xem thêm: `docs/LARAVEL_11_UPGRADE_GUIDE.md` §7.6._
