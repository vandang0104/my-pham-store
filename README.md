# Mộc Lam — website bán mỹ phẩm thảo mộc Việt

Đồ án website thương mại điện tử bán mỹ phẩm: khách xem / tìm / lọc sản phẩm, đặt hàng (kể cả không cần tài khoản), theo dõi đơn, chat với cửa hàng; nhân viên và quản trị quản lý sản phẩm, danh mục, đơn hàng, tài khoản và xem thống kê doanh thu. Giao diện và nội dung tiếng Việt, tiền tệ VND.

Tài liệu: [CLAUDE.md](CLAUDE.md) (quy ước, nghiệp vụ, phạm vi) · [docs/architecture.md](docs/architecture.md) · [docs/permissions.md](docs/permissions.md) · [docs/checklist-chuc-nang.md](docs/checklist-chuc-nang.md) · [docs/test-cases/](docs/test-cases/) · [docs/erd.mmd](docs/erd.mmd), [docs/data-dictionary.md](docs/data-dictionary.md).

## Chức năng chính

- **Khách:** trang chủ (banner, sản phẩm nổi bật, đang ưu đãi, danh mục, giới thiệu); danh sách theo danh mục / thương hiệu; tìm theo từ khoá + lọc giá, danh mục, thương hiệu, đang giảm giá; chi tiết sản phẩm.
- **Giỏ hàng & đặt hàng:** giỏ cho khách vãng lai (session) và tài khoản (DB, gộp khi đăng nhập); thanh toán COD / chuyển khoản; trang + email xác nhận đơn.
- **Tài khoản:** đăng ký, đăng nhập, quên mật khẩu; hồ sơ, đổi mật khẩu, lịch sử đơn, huỷ đơn chờ xác nhận / đã xác nhận.
- **Hỗ trợ:** chat trực tuyến (tự làm, polling), form liên hệ, câu hỏi thường gặp.
- **Quản trị (`/admin/login`):** tổng quan; sản phẩm (1 ảnh chính, xoá mềm); danh mục; đơn hàng (đổi trạng thái theo luồng, huỷ hoàn kho); chat; tin liên hệ. Chỉ admin: tài khoản (đổi vai trò, khoá), thống kê doanh thu theo ngày / tháng + top bán chạy (Chart.js).
- **Đã cắt** theo phạm vi rút gọn 19/10/2026: đánh giá, mã giảm giá, CRUD banner / tin tức / FAQ, sổ địa chỉ, VNPay… — chi tiết trong [checklist](docs/checklist-chuc-nang.md).

## Công nghệ

PHP 8.3+ · Laravel 13 · MySQL 8 (utf8mb4) · Blade + Bootstrap 5 (Sass, Vite) · JavaScript thuần · Chart.js · Pest 4 · Laravel Pint.

## Yêu cầu môi trường

- PHP 8.3+ (có `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`) và Composer 2 — Windows: cài nhanh bằng [php.new](https://php.new)
- Node.js 20+ và npm
- MySQL 8 — dùng container trong `compose.yaml` (cần Docker) hoặc MySQL cài trên máy

## Cài đặt

```bash
git clone <url-repo> mypham-shop && cd mypham-shop
composer install
cp .env.example .env              # Windows PowerShell: Copy-Item .env.example .env
php artisan key:generate

# Tạo database mypham_shop: dùng Docker…
docker compose up -d
# …hoặc MySQL trên máy (rồi sửa DB_USERNAME / DB_PASSWORD trong .env):
#   CREATE DATABASE mypham_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

php artisan migrate --seed
php artisan storage:link          # ảnh trong storage/app/public hiển thị qua /storage
npm install && npm run build
php artisan serve
```

Mở http://localhost:8000 (cửa hàng) và http://localhost:8000/admin/login (quản trị). Email gửi bằng driver `log` — xem trong `storage/logs/laravel.log`.

## Tài khoản demo

> ⚠️ Chỉ dùng để demo trên máy phát triển. Mọi tài khoản có chung mật khẩu **`Demo@123`**.

| Vai trò | Email | Đăng nhập tại |
|---|---|---|
| Quản trị (admin) | `admin@mypham.test` | `/admin/login` |
| Nhân viên (staff) | `nhanvien1@mypham.test`, `nhanvien2@mypham.test` | `/admin/login` |
| Khách hàng | `khachhang01@mypham.test` … `khachhang20@mypham.test` (01–07 có đơn đã giao) | `/dang-nhap` |

Dữ liệu mẫu (`php artisan migrate:fresh --seed`): chỉ chăm sóc da mặt — 4 danh mục cha (Làm sạch, Dưỡng da, Mặt nạ, Chống nắng) + 9 danh mục con, 5 thương hiệu **hư cấu**, 21 sản phẩm, 4 banner, 8 FAQ, 15 đơn đủ trạng thái, cài đặt cửa hàng (phí ship 30.000 ₫, miễn phí từ 500.000 ₫, tài khoản ngân hàng **mẫu**). Ảnh thật (sản phẩm, danh mục, logo thương hiệu, banner) nằm sẵn trong repo ở `database/seeders/images/`, đặt tên theo slug; khi seed được chép sang `storage/app/public/anh-that/` nên máy nào clone về chạy `migrate --seed` + `storage:link` cũng có đủ ảnh. Thiếu ảnh nào thì seeder tự dùng placeholder SVG. Muốn đổi ảnh: thay file cùng tên trong `database/seeders/images/` rồi seed lại, hoặc tải ảnh từng sản phẩm trong trang quản trị.

## Kiểm thử

```bash
php artisan test          # toàn bộ test (SQLite in-memory, không đụng DB thật)
./vendor/bin/pint         # định dạng code
```

Bảng test case cho Chương 5 nằm trong `docs/test-cases/`.

## Demo qua Cloudflare Tunnel

`bootstrap/app.php` đã bật `trustProxies(at: '*')` để Laravel nhận đúng `https` và tên miền từ header của tunnel; ảnh dùng đường dẫn tương đối `/storage/...` nên hiển thị đúng dưới mọi tên miền.

```bash
php artisan serve
cloudflared tunnel --url http://localhost:8000
```

Khi triển khai thật nên đổi `trustProxies(at: '*')` thành địa chỉ proxy cụ thể và đặt `APP_URL` đúng tên miền (link tuyệt đối trong email dùng `APP_URL`).
