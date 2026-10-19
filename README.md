# Mỹ Phẩm Shop

Website thương mại điện tử bán mỹ phẩm (đồ án) — Laravel 13, MySQL 8, Blade, Bootstrap 5 (Sass + Vite).

Tài liệu dự án: [CLAUDE.md](CLAUDE.md) (quy ước, nghiệp vụ) · [docs/architecture.md](docs/architecture.md) (kiến trúc).

## Yêu cầu

- PHP 8.3+ (có `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`) và Composer 2
- Node.js 20+ và npm
- MySQL 8 — dùng sẵn container trong `compose.yaml` (cần Docker) hoặc MySQL cài trên máy

> Windows chưa có PHP/Composer: cài nhanh bằng [php.new](https://php.new) rồi mở lại terminal.

## Cài đặt nhanh

```bash
git clone <url-repo> mypham-shop
cd mypham-shop

composer install
npm install

cp .env.example .env          # Windows PowerShell: Copy-Item .env.example .env
php artisan key:generate

docker compose up -d          # khởi động MySQL (bỏ qua nếu dùng MySQL trên máy)
php artisan migrate --seed

npm run build                 # hoặc `npm run dev` khi đang phát triển giao diện
php artisan serve
```

Mở http://localhost:8000.

**Dùng MySQL trên máy thay vì Docker:** tạo database rồi sửa `DB_USERNAME`, `DB_PASSWORD` trong `.env` cho khớp:

```sql
CREATE DATABASE mypham_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Lệnh thường dùng

| Lệnh | Tác dụng |
|---|---|
| `php artisan test` | Chạy kiểm thử (Pest) |
| `./vendor/bin/pint` | Định dạng code |
| `npm run dev` | Vite dev server (hot reload) |
| `php artisan migrate:fresh --seed` | Tạo lại DB và dữ liệu mẫu |
| `docker compose down` | Tắt MySQL (dữ liệu giữ trong volume `mysql-data`) |
