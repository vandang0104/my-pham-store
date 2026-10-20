# KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN

## 1. Kết quả đạt được

Đề tài đã xây dựng website bán mỹ phẩm **Mộc Lam** chạy được trọn luồng mua bán: khách xem, tìm và lọc sản phẩm, đặt hàng, theo dõi đơn; nhân viên và quản trị viên xử lý đơn, quản lý sản phẩm và xem doanh thu. Đối chiếu với các mục tiêu cụ thể ở mục 1.2:

| Mục tiêu (mục 1.2) | Kết quả |
|---|---|
| 1. Xem theo danh mục / thương hiệu; tìm kiếm, lọc | Đạt. Danh mục hai cấp, trang thương hiệu, tìm theo tên/SKU/thương hiệu; lọc giá (theo giá hiện hành), danh mục, nhiều thương hiệu, đang giảm giá; phân trang giữ bộ lọc |
| 2. Giỏ hàng và đặt hàng cho cả khách vãng lai; COD và chuyển khoản; xác nhận đơn | Đạt. Giỏ session/CSDL, gộp khi đăng nhập; đặt hàng trong giao dịch có khoá dòng, chống gửi trùng; trang và email xác nhận (email ghi vào log) |
| 3. Tài khoản khách hàng | Đạt. Đăng ký, đăng nhập, quên mật khẩu, hồ sơ, đổi mật khẩu, lịch sử đơn, huỷ đơn có hoàn kho |
| 4. Hỗ trợ khách hàng | Đạt. Chat trực tuyến (polling), form liên hệ chống spam, trang câu hỏi thường gặp |
| 5. Khu quản trị phân quyền hai vai trò | Đạt. Sản phẩm, danh mục, đơn hàng, tài khoản, tin liên hệ, chat; thống kê doanh thu theo ngày/tháng và top bán chạy (chỉ admin) |
| 6. Đúng nghiệp vụ, có kiểm chứng tự động | Đạt. 410 test tự động (2.148 assertion) đều đạt, gồm một kịch bản demo end-to-end |

Về mặt kỹ thuật, đề tài đã vận dụng mô hình MVC kết hợp lớp Service của Laravel; Eloquent ORM với scope và accessor; phân quyền hai tầng bằng middleware và Policy; giao dịch CSDL với `lockForUpdate()`; lưu snapshot dòng đơn; xoá mềm; và quy trình kiểm thử tự động trước mỗi lần commit. Toàn bộ quá trình được quản lý bằng Git, mỗi bước một commit theo chuẩn Conventional Commits.

## 2. Hạn chế

- **Phạm vi bị rút gọn** do thời gian (chốt ngày 19/10/2026): chưa có đánh giá và bình luận sản phẩm, mã giảm giá, quản lý banner/tin tức/FAQ qua giao diện, sổ địa chỉ, bộ lọc nâng cao, nhiều ảnh cho một sản phẩm, thống kê theo loại sản phẩm/khách hàng và xuất báo cáo. Một số bảng (`reviews`, `coupons`, `banners`, `posts`, `addresses`) đã có trong CSDL nhưng chưa được dùng.
- **Giao diện đơn giản:** trang chủ và các thành phần chung được thiết kế riêng, còn các trang làm sau (tài khoản, quản trị) dùng gần như nguyên thành phần Bootstrap; chưa được kiểm thử với người dùng thật.
- **Email chỉ ghi vào log** (driver `log`), chưa gửi thật qua máy chủ SMTP.
- **Chat dùng polling 5 giây:** có độ trễ tới vài giây và phát sinh request đều đặn kể cả khi không có tin mới; chưa có thông báo cho nhân viên khi có hội thoại mới.
- **Thanh toán chuyển khoản xác nhận thủ công:** nhân viên tự đối soát rồi bấm "Đánh dấu đã thanh toán"; chưa có cổng thanh toán trực tuyến.
- **Phí giao hàng cố định**, chưa tính theo khu vực hay khối lượng; chưa tích hợp đơn vị vận chuyển.
- **Kiểm thử:** test chạy trên SQLite nên chưa kiểm chứng tình huống đặt hàng đồng thời trên MySQL thật; chưa có kiểm thử trình duyệt tự động và kiểm thử tải.
- Hệ thống mới chạy ở môi trường phát triển (demo qua Cloudflare Tunnel), **chưa triển khai** lên máy chủ thật.

## 3. Hướng phát triển

1. **Đánh giá sản phẩm:** khách đã mua (đơn đã giao) được chấm sao và nhận xét; quản trị duyệt; dùng bảng `reviews` và các cột `rating_avg`, `rating_count` đã có.
2. **Mã giảm giá:** áp mã ở giỏ/thanh toán, kiểm tra hạn dùng, số lượt, giá trị đơn tối thiểu; dùng bảng `coupons` và cột `orders.coupon_id`, `orders.discount` đã có.
3. **Thanh toán trực tuyến VNPay** (giá trị `vnpay` đã có trong enum `PaymentMethod`), tự cập nhật tình trạng thanh toán qua IPN.
4. **Quản lý nội dung:** CRUD banner, tin tức, FAQ, thương hiệu; nhiều ảnh cho sản phẩm.
5. **Chat thời gian thực bằng WebSocket** (Laravel Reverb hoặc dịch vụ tương đương) thay cho polling; thông báo cho nhân viên khi có tin mới.
6. **Triển khai thật:** máy chủ Linux (Nginx + PHP-FPM + MySQL), HTTPS, gửi email qua SMTP và hàng đợi (queue), sao lưu CSDL, giới hạn `trustProxies` theo proxy cụ thể.
7. Bổ sung: sổ địa chỉ, tra cứu đơn cho khách vãng lai, thống kê theo danh mục/khách hàng và xuất CSV, tối ưu SEO (sitemap, dữ liệu có cấu trúc), tích hợp đơn vị vận chuyển (GHN/GHTK).

## 4. Tài liệu tham khảo

Tài liệu trực tuyến (truy cập tháng 10/2026):

1. Laravel. *Laravel Documentation* (13.x). https://laravel.com/docs
2. The PHP Group. *PHP Manual*. https://www.php.net/manual/en/
3. Oracle. *MySQL 8.0 Reference Manual*. https://dev.mysql.com/doc/refman/8.0/en/ — mục *Locking Reads*: https://dev.mysql.com/doc/refman/8.0/en/innodb-locking-reads.html
4. Bootstrap. *Bootstrap 5.3 Documentation*. https://getbootstrap.com/docs/5.3/
5. Pest. *Pest Documentation*. https://pestphp.com/docs
6. Chart.js. *Chart.js Documentation*. https://www.chartjs.org/docs/latest/
7. Vite. *Vite Guide*. https://vite.dev/guide/
8. OWASP Foundation. *OWASP Top Ten*. https://owasp.org/www-project-top-ten/
9. Mermaid. *Mermaid Documentation* (vẽ sơ đồ). https://mermaid.js.org/

Sách, giáo trình và báo cáo:

10. [CẦN TRÍCH DẪN: giáo trình Phân tích và thiết kế hệ thống / UML dùng ở trường]
11. [CẦN TRÍCH DẪN: giáo trình Cơ sở dữ liệu]
12. [CẦN TRÍCH DẪN: báo cáo thị trường thương mại điện tử Việt Nam dùng ở mục 1.1, vd. Sách trắng TMĐT Việt Nam (Bộ Công Thương) hoặc báo cáo e-Conomy SEA]

---

## Phụ lục A. Dàn ý slide bảo vệ (14 slide)

| # | Tiêu đề | Ý chính |
|---|---|---|
| 1 | Xây dựng website bán mỹ phẩm Mộc Lam | Tên đề tài, sinh viên, GVHD · Laravel 13 + MySQL 8 · Ngày bảo vệ |
| 2 | Lý do và mục tiêu | Mỹ phẩm là ngành hàng mua online phổ biến [CẦN TRÍCH DẪN] · Cửa hàng nhỏ quản lý đơn thủ công, dễ sai tồn kho · Mục tiêu: website bán hàng + khu quản trị đúng nghiệp vụ |
| 3 | Phạm vi và đối tượng | 4 tác nhân: khách vãng lai, khách hàng, nhân viên, admin · 9/10 nhóm chức năng theo đề cương · Phần đã cắt chuyển thành hướng phát triển |
| 4 | Công nghệ | PHP 8.4, Laravel 13, MySQL 8, Eloquent, Blade · Bootstrap 5 (Sass, Vite), JavaScript thuần, Chart.js · Pest (410 test), Pint, Git |
| 5 | Kiến trúc | MVC + Service (Cart/Order/Report/Chat) · Controller mỏng, FormRequest kiểm tra dữ liệu · Phân quyền hai tầng: middleware role + Policy |
| 6 | Use case | Sơ đồ use case tổng quát · Khách: mua hàng, tài khoản, hỗ trợ · Quản trị: sản phẩm, đơn, tài khoản, thống kê |
| 7 | Cơ sở dữ liệu | ERD 20 bảng · Snapshot giá trong `order_items`, xoá mềm sản phẩm, không xoá đơn · Chỉ mục `status + created_at` cho thống kê |
| 8 | Nghiệp vụ đặt hàng | Sơ đồ tuần tự đặt hàng · Transaction + `lockForUpdate` theo thứ tự id · Token một lần chống gửi trùng |
| 9 | Luồng trạng thái đơn | pending → confirmed → shipping → delivered; huỷ chỉ từ pending/confirmed · Huỷ hoàn kho, đã thanh toán → hoàn tiền · Lịch sử ghi người thực hiện |
| 10 | Giao diện phía khách | Ảnh trang chủ, danh sách có lọc, chi tiết · Responsive trên điện thoại · Tiếng Việt, ảnh có alt, form báo lỗi từng trường |
| 11 | Khu quản trị | Ảnh quản lý đơn, sản phẩm · Thống kê doanh thu bằng Chart.js · Chat với khách bằng polling |
| 12 | Kiểm thử | 410 test / 2.148 assertion đều đạt · Test case theo nhóm chức năng + kịch bản end-to-end · Lỗi đã phát hiện và sửa (test chập chờn, proxy https, cache URL) |
| 13 | Kết quả và hạn chế | Đạt 6/6 mục tiêu ở mức cơ bản · Hạn chế: phạm vi rút gọn, email ghi log, chat polling · Chưa triển khai thật |
| 14 | Hướng phát triển — Hỏi đáp | Đánh giá, mã giảm giá, VNPay · WebSocket, CRUD nội dung · Triển khai máy chủ thật · Cảm ơn và hỏi đáp |

## Phụ lục B. Kịch bản demo 7 phút

**Chuẩn bị trước buổi bảo vệ:** `php artisan migrate:fresh --seed`, `npm run build`, `php artisan serve`. Mở sẵn hai cửa sổ trình duyệt: (A) cửa sổ thường cho khách, (B) cửa sổ ẩn danh đã đăng nhập `/admin/login` bằng `nhanvien1@mypham.test`. Mở sẵn `storage/logs/laravel.log` trong VS Code. Mật khẩu mọi tài khoản demo: `Demo@123`.

| Thời gian | Thao tác | Điểm cần nói |
|---|---|---|
| 0:00–0:45 | (A) Trang chủ: banner, sản phẩm nổi bật, đang ưu đãi; thu nhỏ cửa sổ / bật chế độ điện thoại | Giao diện tiếng Việt, responsive; giá khuyến mãi hiển thị nhãn −% |
| 0:45–1:45 | (A) Tìm "serum"; lọc danh mục *Chăm sóc da*, chọn thương hiệu, khoảng giá, "Đang giảm giá"; chuyển trang 2; mở chi tiết một sản phẩm | Lọc theo giá hiện hành; phân trang giữ bộ lọc; trang chi tiết có thành phần, tồn kho |
| 1:45–2:30 | (A) Thêm 2 sản phẩm vào giỏ khi chưa đăng nhập; mở giỏ, đổi số lượng vượt tồn kho | Giỏ khách vãng lai lưu session; chặn vượt tồn kho; dòng "còn … để được miễn phí giao hàng" |
| 2:30–3:45 | (A) Đăng nhập `khachhang01@mypham.test` → giỏ vẫn còn (đã gộp); thanh toán (thông tin điền sẵn), chọn chuyển khoản, đặt hàng; mở log xem email | Gộp giỏ khi đăng nhập; đặt hàng trong transaction, trừ kho; trang xác nhận có thông tin chuyển khoản; email xác nhận |
| 3:45–4:15 | (A) Bấm Back và gửi lại form | Chống gửi trùng: không tạo đơn thứ hai |
| 4:15–5:30 | (B) Nhân viên: `/admin/orders` lọc "Chờ xác nhận" → mở đơn vừa đặt → Đánh dấu đã thanh toán → Xác nhận → Đang giao → Đã giao; thử chuyển sai luồng | Luồng trạng thái hợp lệ; lịch sử ghi người thực hiện; sai luồng bị chặn |
| 5:30–6:00 | (B) Mở `/admin/reports` bằng nhân viên → 403; đăng xuất, đăng nhập `admin@mypham.test`, mở lại | Phân quyền: chỉ admin xem thống kê; biểu đồ doanh thu có đơn vừa giao |
| 6:00–6:40 | (A) Mở chat, gửi "Shop còn hàng không?"; (B) admin mở `/admin/chats`, trả lời; (A) tin trả lời hiện sau vài giây | Chat tự làm bằng polling 5 giây |
| 6:40–7:00 | Terminal: `php artisan test` (chạy sẵn, chỉ cho xem kết quả) | 410 test tự động đều đạt |

Phương án dự phòng: nếu mạng hoặc máy chủ gặp sự cố, chiếu ảnh chụp màn hình ở Chương 4 theo đúng thứ tự trên.
