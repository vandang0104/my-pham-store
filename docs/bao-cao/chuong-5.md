# CHƯƠNG 5. KIỂM THỬ

## 5.0. Phương pháp và môi trường kiểm thử

### Phương pháp

Hệ thống được kiểm thử theo hai cách bổ sung cho nhau:

1. **Kiểm thử tự động (chính).** Viết **Feature test** bằng Pest 4 (chạy trên PHPUnit). Mỗi test gửi request HTTP nội bộ tới ứng dụng (không cần trình duyệt), rồi kiểm tra mã phản hồi, nội dung trang, chuyển hướng, thông báo và trạng thái CSDL sau thao tác. Đây là kiểm thử hộp đen theo chức năng: mỗi test ứng với một test case, tên test viết bằng tiếng Việt. Ngoài ra có **Unit test** cho các enum và lớp định dạng tiền `Money`. Các kỹ thuật dùng kèm:
   - CSDL SQLite trong bộ nhớ, làm mới cho từng test (`RefreshDatabase`), dữ liệu sinh bằng factory;
   - giả lập email (`Mail::fake()`, `Notification::fake()`), giả lập lưu trữ tệp (`Storage::fake()`) và thời gian (`travel()`);
   - **dataset** để chạy một test với nhiều bộ dữ liệu (vd. 6 dạng số điện thoại sai);
   - bật `preventLazyLoading` và đếm số truy vấn để phát hiện N+1.
2. **Kiểm thử thủ công.** Chạy hệ thống với dữ liệu mẫu, thao tác trên trình duyệt (máy tính và chế độ điện thoại 390px) theo kịch bản demo. Mục đích là kiểm tra giao diện, hiển thị tiếng Việt và luồng thao tác thực tế — những thứ test tự động không quan sát được.

Mỗi bước phát triển đều có Feature test cho luồng chính và chạy lại toàn bộ bộ test trước khi commit (kiểm thử hồi quy).

### Môi trường

| Thành phần | Cấu hình |
|---|---|
| Hệ điều hành | Windows 11 Home |
| PHP / Laravel | PHP 8.4.0, Laravel 13.37 |
| Công cụ test | Pest 4 + pest-plugin-laravel |
| CSDL khi test | SQLite in-memory (`phpunit.xml`); CSDL demo: MySQL 8 (Docker) |
| Trình duyệt kiểm thử thủ công | [CẦN BỔ SUNG: tên + phiên bản trình duyệt đã dùng] |
| Lệnh chạy | `php artisan test` (toàn bộ) · `php artisan test tests/Feature/Checkout` (một nhóm) |

### Tổng hợp kết quả chạy tự động

Lần chạy toàn bộ ngày 20/10/2026: **410/410 test đạt, 1.998 assertion, thời gian ≈ 29 giây, 0 lỗi** (sau khi thu hẹp dữ liệu mẫu còn 21 sản phẩm chăm sóc da mặt). Số liệu trong bảng dưới tính theo từng bộ dữ liệu (một test có dataset 4 bộ được tính 4 lần).

| Nhóm (thư mục `tests/`) | Nội dung | Số test | Assertion |
|---|---|---:|---:|
| Feature/Auth | Đăng ký, đăng nhập, đăng xuất, quên/đặt lại mật khẩu, phân quyền | 63 | 174 |
| Feature/Account | Khu tài khoản khách hàng | 29 | 120 |
| Feature/Products | Danh sách, tìm kiếm, lọc, chi tiết sản phẩm | 33 | 95 |
| Feature/Cart | Giỏ hàng, gộp giỏ | 22 | 65 |
| Feature/Checkout | Thanh toán, đặt hàng, email | 29 | 133 |
| Feature/Admin | Khu quản trị | 29 | 133 |
| Feature/Support | FAQ, liên hệ, chat | 21 | 90 |
| Feature/Final | Kịch bản demo end-to-end, kiểm link nội bộ, trang lỗi | 9 | 61 |
| Feature/Database, Models, Policies | Lược đồ, seeder, model, policy | 92 | 724 |
| Feature/Components, Layout, Home, Styleguide… | Giao diện dùng chung, trang chủ | 56 | 306 |
| Unit | Enum, định dạng tiền | 27 | 97 |
| **Tổng** | | **410** | **1.998** |

[HÌNH 5.1: Kết quả lệnh `php artisan test` trên terminal (dòng tổng "410 passed")]

Các bảng dưới đây trích **test case tiêu biểu**. Danh sách đầy đủ (kèm các bước và tên test tương ứng) nằm trong `docs/test-cases/`. Cột "Kết quả thực tế" ghi theo lần chạy ngày 20/10/2026.

## 5.1. Đăng nhập và tài khoản

Đầy đủ: `docs/test-cases/5.1-dang-nhap.md` (41 test case: đăng ký, đăng nhập, đăng xuất, quên/đặt lại mật khẩu, phân quyền) và `docs/test-cases/5.1-tai-khoan.md` (17 test case: hồ sơ, đổi mật khẩu, đơn hàng của tôi, huỷ đơn).

| Mã TC | Chức năng | Mô tả | Dữ liệu vào | Kết quả mong đợi | Kết quả thực tế | Pass/Fail |
|---|---|---|---|---|---|---|
| TC-DK-02 | Đăng ký | Đăng ký thành công | Họ tên `Nguyễn Thị Lan`, email `Lan@Example.com `, SĐT `0912345678`, mật khẩu `MatKhau123` | Tạo tài khoản `lan@example.com` vai trò khách hàng, mật khẩu băm; tự đăng nhập, về trang chủ | Như mong đợi | Pass |
| TC-DK-07 | Đăng ký | SĐT không hợp lệ | `0123456789`, `0241234567`, `091234567`, `09123456789`, `09123abcde`, `+84212345678` | Báo lỗi số điện thoại, không tạo tài khoản | Như mong đợi (6/6 bộ) | Pass |
| TC-DK-09 | Đăng ký | Không cho tự nâng quyền | Form hợp lệ kèm `role=admin` | Tài khoản vẫn là khách hàng | Như mong đợi | Pass |
| TC-DN-02 | Đăng nhập | Đăng nhập đúng | `lan@example.com` / `MatKhau123` | Đăng nhập, về trang chủ, "Đăng nhập thành công." | Như mong đợi | Pass |
| TC-DN-05 | Đăng nhập | Email không tồn tại | `khongco@example.com` | Cùng thông báo lỗi chung như sai mật khẩu (không lộ tài khoản) | Như mong đợi | Pass |
| TC-DN-07 | Đăng nhập | Vượt giới hạn thử | Sai 5 lần, lần 6 đúng | Lần 6 bị chặn, báo chờ | Như mong đợi | Pass |
| TC-DN-11 | Đăng nhập | Chống session fixation | Đăng nhập đúng | Session ID đổi sau đăng nhập | Như mong đợi | Pass |
| TC-QMK-03 | Quên mật khẩu | Chống dò tài khoản | Email tồn tại và không tồn tại | Hai phản hồi giống hệt nhau | Như mong đợi | Pass |
| TC-DLMK-03 | Đặt lại mật khẩu | Token dùng một lần | Dùng lại token đã dùng | Báo liên kết không hợp lệ | Như mong đợi | Pass |
| TC-PQ-05 | Phân quyền | `/admin/users` theo vai trò | guest, customer, staff, admin | `/admin/login`, 403, 403, 200 | Như mong đợi (4/4 bộ) | Pass |
| TC-PQ-08 | Phân quyền | Khoá tài khoản giữa phiên | Admin khoá tài khoản đang đăng nhập | Bị đăng xuất, về `/dang-nhap` kèm thông báo | Như mong đợi | Pass |
| TC-TK-03 | Hồ sơ | Cập nhật hợp lệ | SĐT `+84 987 654 321`, ngày sinh, loại da | "Đã cập nhật hồ sơ."; SĐT lưu `0987654321` | Như mong đợi | Pass |
| TC-TK-08 | Đổi mật khẩu | Không hợp lệ | Sai mật khẩu hiện tại; mới `abc`; không khớp; trùng cũ | Báo lỗi đúng trường, mật khẩu không đổi | Như mong đợi (4/4 bộ) | Pass |
| TC-TK-12 | Đơn hàng | Xem đơn người khác | Mã đơn của khách khác | 404 | Như mong đợi | Pass |
| TC-TK-13 | Huỷ đơn | Huỷ đơn chờ / đã xác nhận | Đơn × 2, tồn kho 5 | Đơn "Đã huỷ"; tồn kho 7; ghi lịch sử | Như mong đợi (2/2 bộ) | Pass |
| TC-TK-14 | Huỷ đơn | Không huỷ được | Đơn đang giao / đã giao / đã huỷ | Không có nút huỷ; gửi yêu cầu bị từ chối; kho không đổi | Như mong đợi (3/3 bộ) | Pass |

## 5.2. Sản phẩm

Đầy đủ: `docs/test-cases/5.2-san-pham.md` (28 test case).

| Mã TC | Chức năng | Mô tả | Dữ liệu vào | Kết quả mong đợi | Kết quả thực tế | Pass/Fail |
|---|---|---|---|---|---|---|
| TC-SP-01 | Danh sách | Chỉ hiện SP đang bán | 3 SP bán, 1 ẩn, 1 xoá mềm | "3 sản phẩm"; không có SP ẩn/xoá | Như mong đợi | Pass |
| TC-SP-03 | Phân trang | 12 SP/trang, giữ bộ lọc | 21 SP khớp lọc | Trang 1 có 12 SP; link trang 2 giữ tham số lọc | Như mong đợi | Pass |
| TC-TK-02 (SP) | Tìm kiếm | Theo mã SKU | `q=VLV-002` | Chỉ *Son kem lì* | Như mong đợi | Pass |
| TC-LOC-01 | Lọc | Danh mục cha gồm danh mục con | `danh-muc=cham-soc-da` | Có SP của danh mục con Serum | Như mong đợi | Pass |
| TC-LOC-03 | Lọc | Khoảng giá theo giá hiện hành | `gia-tu=200000&gia-den=330000` … | Lọc theo giá đã giảm, không theo giá gốc | Như mong đợi (3/3 bộ) | Pass |
| TC-LOC-05 | Lọc | Kết hợp nhiều bộ lọc | thương hiệu + danh mục + từ khoá | Chỉ *Son kem lì* | Như mong đợi | Pass |
| TC-LOC-06 | Lọc | Tham số sai bị bỏ qua | `gia-tu=abc`, `gia-tu=-5`, `q[]=serum`… | HTTP 200, không lỗi; tham số sai không áp dụng | Như mong đợi (6/6 bộ) | Pass |
| TC-CT-01 | Chi tiết | Hiển thị đúng thông tin | `/san-pham/serum-niacinamide-10` | Tên, thương hiệu, mã SP, giá 279.000 trước 345.000, "−19%", "Còn hàng" | Như mong đợi | Pass |
| TC-CT-04 | Chi tiết | Mô tả an toàn XSS | Mô tả chứa `<script>`, `javascript:` | Không có thẻ `<script>` và link `javascript:` trong trang | Như mong đợi | Pass |
| TC-CT-08 | Chi tiết | SP ẩn / xoá mềm / không tồn tại | 3 trường hợp | 404 | Như mong đợi (3/3) | Pass |
| TC-HN-01 | Hiệu năng | Không N+1 | Đếm truy vấn trước/sau khi thêm 8 SP | Số truy vấn không đổi | Như mong đợi | Pass |

## 5.3. Giỏ hàng

Đầy đủ: `docs/test-cases/5.3-gio-hang.md` (19 test case).

| Mã TC | Chức năng | Mô tả | Dữ liệu vào | Kết quả mong đợi | Kết quả thực tế | Pass/Fail |
|---|---|---|---|---|---|---|
| TC-GH-01 | Thêm vào giỏ | Thêm SP mới | Serum × 2 | Thông báo "Đã thêm …"; giỏ có Serum × 2 | Như mong đợi | Pass |
| TC-GH-03 | Thêm vào giỏ | Vượt tồn kho | Có 4, thêm 2 (kho 5) | Báo "Chỉ còn 5 sản phẩm…"; giỏ giữ 4 | Như mong đợi | Pass |
| TC-GH-04 | Thêm vào giỏ | SP hết hàng / ẩn / xoá mềm | 3 bộ | Báo lỗi; giỏ trống | Như mong đợi (3/3 bộ) | Pass |
| TC-GH-06 | Lưu trữ | Khách vãng lai dùng session | Chưa đăng nhập | Không tạo bản ghi `carts` | Như mong đợi | Pass |
| TC-GH-09 | Trang giỏ | Tạm tính theo giá hiện hành | Serum × 2 (giá KM 279.000), Kem × 1 (250.000) | Thành tiền Serum 558.000; tạm tính 808.000 | Như mong đợi | Pass |
| TC-GH-12 | Trang giỏ | SP bị ẩn sau khi thêm | Admin ẩn Serum | Tự bỏ khỏi giỏ và báo cho khách | Như mong đợi | Pass |
| TC-GH-13 | Trang giỏ | Tồn kho giảm | Kem × 8, kho còn 3 | Hạ còn 3 và báo | Như mong đợi | Pass |
| TC-GH-16 | Gộp giỏ | Đăng nhập: cộng dồn, cắt theo kho | DB: Serum × 3; session: Serum × 4, Kem × 2 | DB: Serum × 5 (kho 5), Kem × 2; session bị xoá | Như mong đợi | Pass |
| TC-GH-17 | Gộp giỏ | Đăng ký | Session: Kem × 2 | Tài khoản mới có giỏ DB Kem × 2 | Như mong đợi | Pass |

## 5.4. Đặt hàng

Đầy đủ: `docs/test-cases/5.4-dat-hang.md` (21 test case).

| Mã TC | Chức năng | Mô tả | Dữ liệu vào | Kết quả mong đợi | Kết quả thực tế | Pass/Fail |
|---|---|---|---|---|---|---|
| TC-TT-01 | Thanh toán | Giỏ trống | Mở `/thanh-toan` | Về giỏ hàng kèm thông báo | Như mong đợi | Pass |
| TC-DH-01 | Đặt hàng | Khách vãng lai, COD | Serum × 2, Kem × 1; người nhận mẫu | Đơn `MP-YYYYMMDD-XXXX` chờ xác nhận; tổng 808.000 (miễn ship); snapshot dòng đơn; tồn kho Serum 5 → 3; xoá giỏ; gửi mail | Như mong đợi | Pass |
| TC-DH-03 | Đặt hàng | Phí ship theo settings | `shipping_fee = 25000`; Serum × 1 | Tổng 279.000 + 25.000 = 304.000 | Như mong đợi | Pass |
| TC-DH-05 | Đặt hàng | Snapshot dòng đơn | Đổi tên/giá SP sau khi đặt | Đơn giữ tên và giá cũ | Như mong đợi | Pass |
| TC-DH-07 | Kiểm tra dữ liệu | Trường sai / thiếu | 9 bộ (SĐT sai, email sai, thiếu địa chỉ, `vnpay`…) | Báo lỗi đúng trường, không tạo đơn | Như mong đợi (9/9 bộ) | Pass |
| TC-DH-09 | Tồn kho | Kho giảm sau khi thêm giỏ | Giỏ Serum × 4, kho còn 2 | Không tạo đơn; kho vẫn 2; về giỏ kèm thông báo | Như mong đợi | Pass |
| TC-DH-10 | Tồn kho | Thiếu hàng lúc khoá dòng | Serum × 4, DB còn 2 | Rollback toàn bộ; báo rõ SP thiếu; giỏ giữ nguyên | Như mong đợi | Pass |
| TC-DH-12 | Gửi trùng | Gửi lại cùng form | Cùng token gửi 2 lần | Chỉ 1 đơn; kho trừ 1 lần; lần 2 về trang xác nhận | Như mong đợi | Pass |
| TC-DH-14 | Xác nhận | Chuyển khoản | Đặt bằng chuyển khoản | Trang xác nhận có số tài khoản và nội dung chuyển khoản = mã đơn | Như mong đợi | Pass |
| TC-DH-16 | Xác nhận | Quyền xem | Chủ đơn / người khác / phiên khác | 200 / 404 / 404 | Như mong đợi | Pass |
| TC-DH-17 | Email | Nội dung mail | Đơn chuyển khoản | Tiêu đề "Xác nhận đơn hàng <mã> — Mộc Lam"; có SP, tổng, hướng dẫn chuyển khoản | Như mong đợi | Pass |

## 5.5. Quản trị

Đầy đủ: `docs/test-cases/5.5-admin.md` (23 test case).

| Mã TC | Chức năng | Mô tả | Dữ liệu vào | Kết quả mong đợi | Kết quả thực tế | Pass/Fail |
|---|---|---|---|---|---|---|
| TC-AD-02 | Đăng nhập QT | Khách hàng bị từ chối | Tài khoản khách, đúng mật khẩu, tại `/admin/login` | Thông báo lỗi chung; không giữ phiên | Như mong đợi | Pass |
| TC-AD-04 | Dashboard | Theo quyền | Staff / admin | Staff không thấy doanh thu và menu Thống kê; admin thấy | Như mong đợi | Pass |
| TC-AD-07 | Danh mục | Xoá | Staff xoá; admin xoá danh mục còn SP / trống | 403; chặn kèm lý do; xoá được | Như mong đợi | Pass |
| TC-AD-08 | Sản phẩm | Thêm có ảnh | "Sữa rửa mặt Đào Đỏ", SKU `sp-moi-01`, giá 250.000, KM 199.000 | Slug `sua-rua-mat-dao-do`, SKU `SP-MOI-01`, có ảnh chính | Như mong đợi | Pass |
| TC-AD-09 | Sản phẩm | Dữ liệu sai | Giá KM ≥ giá; SKU trùng; thiếu danh mục | Báo lỗi đúng trường | Như mong đợi (4/4 bộ) | Pass |
| TC-AD-11 | Sản phẩm | Xoá mềm / khôi phục | Staff xoá; admin xoá rồi khôi phục | 403; trang khách 404 sau khi xoá; khôi phục được | Như mong đợi | Pass |
| TC-AD-13 | Đơn hàng | Đổi trạng thái đúng luồng | Chờ → xác nhận → đang giao → đã giao | Có các mốc thời gian; lịch sử ghi người thực hiện | Như mong đợi | Pass |
| TC-AD-14 | Đơn hàng | Sai luồng | Chờ → đã giao; đã giao → huỷ; huỷ → xác nhận | Báo lỗi; trạng thái giữ nguyên | Như mong đợi (3/3 bộ) | Pass |
| TC-AD-15 | Đơn hàng | Huỷ: hoàn kho, hoàn tiền | Đơn đã xác nhận, đã thanh toán, × 2, kho 4 | "Đã huỷ", "Đã hoàn tiền", kho 6 | Như mong đợi | Pass |
| TC-AD-16 | Đơn hàng | Không xoá đơn | DELETE `/admin/orders/{mã}` | 405; đơn còn nguyên | Như mong đợi | Pass |
| TC-AD-18 | Phân quyền | Staff vào tài khoản / thống kê | `/admin/users`, `/admin/reports` | 403 | Như mong đợi | Pass |
| TC-AD-20 | Tài khoản | Bảo vệ admin | Tự hạ quyền; tự khoá; hạ admin cuối | Đều bị chặn | Như mong đợi (3/3) | Pass |
| TC-AD-22 | Thống kê | Số liệu đúng | Đơn giao 18/10 (200.000), 20/10 (300.000), đơn huỷ 999.000, đơn giao 05/09 (100.000) | Theo ngày [200.000, 0, 300.000]; tháng 9: 100.000, tháng 10: 500.000; đơn huỷ không tính | Như mong đợi | Pass |

## 5.6. Phụ lục: hỗ trợ khách hàng và kịch bản end-to-end

**Hỗ trợ khách hàng** — đầy đủ: `docs/test-cases/phu-luc-ho-tro.md` (13 test case: FAQ, liên hệ, chat).

| Mã TC | Chức năng | Mô tả | Dữ liệu vào | Kết quả mong đợi | Kết quả thực tế | Pass/Fail |
|---|---|---|---|---|---|---|
| TC-HT-04 | Liên hệ | Gửi hợp lệ | `Lan@Example.com`, SĐT `0912 345 678` | Lưu tin chưa xử lý; email thường; SĐT chuẩn hoá | Như mong đợi | Pass |
| TC-HT-06 | Chống spam | Honeypot | Điền ô ẩn `website` | Báo thành công nhưng không lưu | Như mong đợi | Pass |
| TC-HT-07 | Chống spam | Giới hạn tần suất | Gửi 4 lần liên tiếp | Lần 4 bị chặn, báo thử lại sau 10 phút | Như mong đợi | Pass |
| TC-CHAT-01 | Chat | Khách vãng lai | Tin đầu không tên, rồi có tên `Lan` | Lần 1: 422; sau đó tạo hội thoại, đọc lại được tin | Như mong đợi | Pass |
| TC-CHAT-03 | Chat | Không đọc hội thoại người khác | User B tải tin | Danh sách rỗng | Như mong đợi | Pass |
| TC-CHAT-05 | Chat quản trị | Staff trả lời | Khách gửi, staff trả lời rồi đóng | Tin khách đã đọc; khách nhận được trả lời; hội thoại đóng | Như mong đợi | Pass |

**Kiểm thử cuối (`tests/Feature/Final`):**

| Mã TC | Mô tả | Kết quả mong đợi | Kết quả thực tế | Pass/Fail |
|---|---|---|---|---|
| TC-E2E-01 | Kịch bản demo trọn vẹn (`DemoScenarioTest`): xem trang chủ → tìm "niacinamide" → lọc danh mục + thương hiệu + khuyến mãi → xem chi tiết → khách đăng nhập, thêm Serum × 2 → đặt COD → admin xác nhận → đang giao → đã giao → thống kê → khách đặt đơn thứ hai bằng chuyển khoản rồi tự huỷ | Tổng đơn 558.000; tồn kho 10 → 8; đơn "Đã giao"; thống kê ngày và dashboard hiện 558.000; đơn thứ hai trừ kho còn 7, huỷ xong hoàn về 8 | Như mong đợi | Pass |
| TC-E2E-02 | Kiểm link nội bộ (`InternalLinksTest`) với 4 vai trò: khách vãng lai, khách hàng, nhân viên, admin | Mọi link nội bộ trên các trang được phép đều trả 200 (không có link gãy) | Như mong đợi (4/4) | Pass |
| TC-E2E-03 | Trang lỗi tiếng Việt (`ErrorPagesTest`): 404, 403, 419, 500 | Thông báo tiếng Việt, có nút về trang chủ; 500 không lộ chi tiết lỗi | Như mong đợi (4/4) | Pass |

**Kiểm thử thủ công** theo kịch bản demo (mục cuối của phần Kết luận) trên trình duyệt ở hai độ rộng 1280px và 390px: [CẦN BỔ SUNG: ngày thực hiện, trình duyệt, các lỗi giao diện phát hiện được (nếu có)].

## 5.7. Lỗi phát hiện và khắc phục trong quá trình phát triển

| # | Hiện tượng | Nguyên nhân | Cách khắc phục | Commit |
|---|---|---|---|---|
| 1 | Test tìm kiếm lúc đạt lúc trượt (chập chờn) | Factory sinh mô tả sản phẩm từ một tên ngẫu nhiên khác với tên sản phẩm, nên từ khoá đôi khi khớp cả mô tả của sản phẩm khác | Factory sinh mô tả từ chính tên sản phẩm (closure `fn (array $attributes)`) | `eb35448` |
| 2 | Ảnh và CSS không tải khi demo qua Cloudflare Tunnel (trang `https` nhưng tài nguyên `http`) | Laravel không tin header `X-Forwarded-Proto` của proxy nên sinh URL `http` | Bật `trustProxies(at: '*')`; URL ảnh dùng đường dẫn tương đối `/storage/...` | `352efe0`, `3f1d6f9` |
| 3 | Link menu trỏ về tên miền cũ khi đổi giữa `localhost` và tunnel | Menu được cache kèm URL tuyệt đối của lần sinh đầu tiên | Cache đường dẫn tương đối; đổi khoá cache để bỏ dữ liệu cũ (dữ liệu cache cũ sai định dạng từng gây lỗi 500) | `132b1e8` |
| 4 | Lỗi nghiệp vụ bình thường (hết hàng, sai luồng trạng thái) bị ghi mức ERROR vào `laravel.log` | Ngoại lệ nghiệp vụ được xử lý như lỗi hệ thống | Ngoại lệ nghiệp vụ cài `ShouldntReport` và tự render thành thông báo | `31fa5a2` |
| 5 | Lọc "giá đến" bị bỏ qua khi để trống "giá từ" | Luật `gte:gia-tu` luôn trượt khi `gia-tu` rỗng nên "giá đến" bị loại | Chỉ áp `gte` khi "giá từ" là số hợp lệ | `3b5d327` |
| 6 | Test đếm số truy vấn (chống N+1) cho kết quả khác nhau giữa hai lần đo | Lần đo đầu cache trống (thêm truy vấn đọc menu/settings), lần sau cache đã có | Xoá cache (`Cache::flush()`) trước mỗi lần đo | `e0965da` |
| 7 | Không tạo được ảnh giả trong test upload | Bản PHP phát triển không có extension GD | Dùng `UploadedFile::fake()->create(tên, kb, mime)` thay cho `image()` | `e8e327f` |

Nhận xét: nhờ bộ test tự động được chạy lại sau mỗi bước, các lỗi hồi quy (vd. thay đổi giỏ hàng làm hỏng đặt hàng) được phát hiện ngay trước khi commit. Hạn chế: test chạy trên SQLite nên không kiểm chứng được hành vi khoá dòng đồng thời thật của MySQL (`lockForUpdate` chỉ được kiểm bằng ca thiếu hàng giả lập, TC-DH-10); chưa có kiểm thử trình duyệt tự động và kiểm thử tải.
