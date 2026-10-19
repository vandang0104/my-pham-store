# Mộc Lam

Website thương mại điện tử bán mỹ phẩm thảo mộc Việt (đồ án) — Laravel 13, MySQL 8, Blade, Bootstrap 5 (Sass + Vite). Thiết kế: [DESIGN.md](DESIGN.md).

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
php artisan storage:link      # để ảnh trong storage/app/public hiển thị được qua /storage

npm run build                 # hoặc `npm run dev` khi đang phát triển giao diện
php artisan serve
```

Mở http://localhost:8000.

**Dùng MySQL trên máy thay vì Docker:** tạo database rồi sửa `DB_USERNAME`, `DB_PASSWORD` trong `.env` cho khớp:

```sql
CREATE DATABASE mypham_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Tài khoản demo

> ⚠️ **Chỉ dùng để demo trên máy phát triển.** Không dùng các tài khoản/mật khẩu này trên môi trường thật.

Mọi tài khoản dưới đây có chung mật khẩu **`Demo@123`**.

| Vai trò | Email |
|---|---|
| Quản trị (admin) | `admin@mypham.test` |
| Nhân viên (staff) | `nhanvien1@mypham.test`, `nhanvien2@mypham.test` |
| Khách hàng | `khachhang01@mypham.test` … `khachhang20@mypham.test` |

Khách hàng 01–07 có đơn đã giao và đánh giá; mọi tài khoản đều có địa chỉ mẫu.

## Dữ liệu mẫu & ảnh

`php artisan migrate:fresh --seed` tạo: 23 tài khoản, 8 danh mục cha + 26 danh mục con, 10 thương hiệu **hư cấu**, 48 sản phẩm (168 ảnh), 4 banner, 6 coupon, 8 FAQ, 6 bài tin tức, 15 đơn hàng đủ trạng thái, 30 đánh giá. Nội dung soạn sẵn nằm trong `database/seeders/data/`.

**Ảnh:** mặc định seeder tự sinh ảnh placeholder SVG vào `storage/app/public/placeholders/` (không tải ảnh từ Internet). Để dùng ảnh thật cho sản phẩm:

1. Chép ảnh (`.jpg`, `.jpeg`, `.png`, `.webp`, `.avif`, `.svg`) vào `storage/app/public/seed/` (đặt trực tiếp trong thư mục, không để trong thư mục con).
2. Chạy lại `php artisan migrate:fresh --seed` — ảnh được gán **luân phiên** theo thứ tự tên file cho 168 ảnh sản phẩm (nên đặt tên `001.jpg`, `002.jpg`… để kiểm soát thứ tự).
3. Chưa chạy `php artisan storage:link` thì chạy để ảnh hiển thị.

Banner, ảnh danh mục, logo thương hiệu và ảnh bài viết vẫn dùng placeholder; thay sau qua trang quản trị. Thư mục `storage/app/public` không được đưa lên Git.

## Lệnh thường dùng

| Lệnh | Tác dụng |
|---|---|
| `php artisan test` | Chạy kiểm thử (Pest) |
| `./vendor/bin/pint` | Định dạng code |
| `npm run dev` | Vite dev server (hot reload) |
| `php artisan migrate:fresh --seed` | Tạo lại DB và dữ liệu mẫu |
| `docker compose down` | Tắt MySQL (dữ liệu giữ trong volume `mysql-data`) |
