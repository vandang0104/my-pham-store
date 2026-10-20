# CHƯƠNG 1. TỔNG QUAN ĐỀ TÀI

## 1.1. Lý do chọn đề tài

Thương mại điện tử tại Việt Nam tăng trưởng nhanh trong những năm gần đây, và mỹ phẩm – chăm sóc cá nhân là một trong các ngành hàng được mua trực tuyến nhiều nhất [CẦN TRÍCH DẪN: số liệu quy mô thị trường TMĐT Việt Nam và tỷ trọng ngành mỹ phẩm, ví dụ báo cáo e-Conomy SEA hoặc Bộ Công Thương]. Đặc thù của ngành mỹ phẩm là khách hàng cần thông tin rõ ràng về thành phần, công dụng, loại da phù hợp và giá trước khi quyết định mua; đồng thời cửa hàng cần quản lý tồn kho, đơn hàng và phản hồi khách hàng kịp thời.

Nhiều cửa hàng mỹ phẩm quy mô nhỏ và vừa vẫn bán chủ yếu qua mạng xã hội, quản lý đơn bằng tin nhắn và bảng tính, dẫn tới sai sót tồn kho, khó tra cứu lịch sử đơn và khó thống kê doanh thu [CẦN TRÍCH DẪN nếu đưa số liệu]. Một website bán hàng riêng giúp cửa hàng chủ động trình bày sản phẩm, nhận đơn 24/7, kiểm soát tồn kho tự động và có số liệu kinh doanh tin cậy.

Từ góc độ học tập, đề tài cho phép vận dụng tổng hợp kiến thức về phân tích – thiết kế hệ thống, cơ sở dữ liệu quan hệ, lập trình web phía máy chủ theo mô hình MVC, bảo mật ứng dụng web và kiểm thử tự động. Vì vậy, đề tài **"Xây dựng website bán mỹ phẩm"** được lựa chọn, với thương hiệu giả định **Mộc Lam** — cửa hàng mỹ phẩm thảo mộc Việt (tên và toàn bộ dữ liệu thương hiệu, sản phẩm đều là hư cấu phục vụ demo).

## 1.2. Mục tiêu

**Mục tiêu tổng quát:** xây dựng một website thương mại điện tử bán mỹ phẩm hoàn chỉnh ở mức cơ bản, chạy được thực tế, phục vụ đồng thời khách mua hàng và bộ phận vận hành cửa hàng.

**Mục tiêu cụ thể:**

1. Cho phép khách xem sản phẩm theo danh mục, thương hiệu; tìm kiếm theo từ khoá và lọc theo giá, danh mục, thương hiệu, sản phẩm đang giảm giá.
2. Cho phép khách (kể cả chưa có tài khoản) quản lý giỏ hàng và đặt hàng với hai phương thức thanh toán: thanh toán khi nhận hàng (COD) và chuyển khoản; nhận trang và email xác nhận đơn.
3. Cung cấp tài khoản khách hàng: đăng ký, đăng nhập, quên mật khẩu, cập nhật hồ sơ, đổi mật khẩu, xem lịch sử và huỷ đơn khi đơn chưa giao.
4. Hỗ trợ khách hàng qua chat trực tuyến, form liên hệ và trang câu hỏi thường gặp.
5. Cung cấp khu quản trị phân quyền hai vai trò (quản trị viên, nhân viên) để quản lý sản phẩm, danh mục, đơn hàng, tài khoản, tin liên hệ và xem thống kê doanh thu.
6. Bảo đảm tính đúng đắn nghiệp vụ (giá hiện hành, lưu giá tại thời điểm đặt, trừ kho trong giao dịch, luồng trạng thái đơn hợp lệ) và kiểm chứng bằng bộ kiểm thử tự động.

## 1.3. Phạm vi

### 1.3.1. Phạm vi thực hiện

Đề tài thực hiện các chức năng sau (đối chiếu chi tiết trong `docs/checklist-chuc-nang.md`):

| Nhóm chức năng (đề cương) | Nội dung đã thực hiện |
|---|---|
| 1. Trang chủ | Giới thiệu cửa hàng; banner; sản phẩm nổi bật; sản phẩm đang khuyến mãi; danh mục |
| 2. Sản phẩm | Danh sách theo danh mục / thương hiệu; chi tiết sản phẩm (tên, mô tả, giá, ảnh, tồn kho, sản phẩm liên quan) |
| 3. Giỏ hàng | Thêm, cập nhật, xoá; tính tạm tính theo giá hiện hành; gộp giỏ khi đăng nhập |
| 4. Thanh toán | Nhập thông tin giao hàng; COD / chuyển khoản; trang và email xác nhận đơn |
| 5. Tài khoản người dùng | Đăng ký, đăng nhập, quên mật khẩu; hồ sơ, đổi mật khẩu, lịch sử đơn, huỷ đơn |
| 6. Quản trị | Đăng nhập quản trị; quản lý sản phẩm, danh mục, đơn hàng, tài khoản (khách + nhân viên); thống kê doanh thu theo ngày/tháng và top sản phẩm bán chạy |
| 7. Tìm kiếm và lọc | Tìm theo từ khoá; lọc theo giá, danh mục, thương hiệu, đang giảm giá |
| 9. Hỗ trợ khách hàng | Chat trực tuyến (cơ chế polling), form liên hệ, câu hỏi thường gặp |
| 10. Khuyến mãi | Khuyến mãi theo giá bán (giá khuyến mãi, nhãn phần trăm giảm, khối "Đang ưu đãi") |

### 1.3.2. Ngoài phạm vi (hướng phát triển)

Do giới hạn thời gian (chốt phạm vi ngày 19/10/2026, hạn nộp 25/10/2026), các chức năng sau **chưa thực hiện** và được đề xuất là hướng phát triển; một số bảng dữ liệu liên quan đã có trong lược đồ CSDL để mở rộng về sau:

- Đánh giá và bình luận sản phẩm (nhóm 8) — bảng `reviews` đã có.
- Mã giảm giá / voucher (nhóm 10) — bảng `coupons` đã có; cột `orders.coupon_id` hiện luôn rỗng.
- Quản lý nội dung: banner, tin tức, FAQ qua giao diện quản trị (hiện nạp từ dữ liệu mẫu).
- Sổ địa chỉ, mua lại đơn cũ, tra cứu đơn cho khách vãng lai.
- Bộ lọc nâng cao (loại da, còn hàng, nhiều kiểu sắp xếp), gợi ý tìm kiếm tự động.
- Nhiều ảnh cho một sản phẩm qua giao diện quản trị (hiện quản lý một ảnh chính).
- Thống kê theo loại sản phẩm / khách hàng, xuất báo cáo CSV.
- Thanh toán trực tuyến qua cổng (VNPay), tích hợp đơn vị vận chuyển thật.
- Tối ưu SEO (sitemap), trang tĩnh, ứng dụng di động.

## 1.4. Đối tượng

### 1.4.1. Đối tượng sử dụng

| Tác nhân | Mô tả | Quyền chính |
|---|---|---|
| Khách vãng lai | Người truy cập chưa đăng nhập | Xem, tìm, lọc sản phẩm; giỏ hàng (lưu trong phiên); đặt hàng; chat; gửi liên hệ |
| Khách hàng | Người dùng đã đăng ký (vai trò `customer`) | Như khách vãng lai + giỏ hàng lưu CSDL, hồ sơ, đổi mật khẩu, lịch sử và huỷ đơn của mình |
| Nhân viên | Vai trò `staff` | Khu quản trị: sản phẩm (thêm/sửa), danh mục (thêm/sửa), đơn hàng, chat, tin liên hệ; không quản lý tài khoản, không xem doanh thu |
| Quản trị viên | Vai trò `admin` | Toàn quyền khu quản trị, gồm xoá sản phẩm/danh mục, quản lý tài khoản, thống kê doanh thu |

Ma trận phân quyền đầy đủ: `docs/permissions.md`.

### 1.4.2. Đối tượng nghiên cứu

- Quy trình nghiệp vụ bán lẻ mỹ phẩm trực tuyến: trình bày sản phẩm, giỏ hàng, đặt hàng, xử lý đơn, quản lý tồn kho, chăm sóc khách hàng.
- Phương pháp phân tích – thiết kế hướng đối tượng với UML (use case, activity, sequence, class) và thiết kế CSDL quan hệ.
- Framework Laravel và các kỹ thuật liên quan: mô hình MVC kết hợp lớp Service, Eloquent ORM, Blade, phân quyền bằng middleware và Policy, giao dịch CSDL có khoá dòng, kiểm thử tự động với Pest.

## 1.5. Công nghệ sử dụng

Phiên bản lấy theo `composer.json`, `package.json` và môi trường phát triển thực tế.

| Thành phần | Công nghệ | Phiên bản | Vai trò |
|---|---|---|---|
| Ngôn ngữ phía máy chủ | PHP | ^8.3 (môi trường phát triển 8.4) | Ngôn ngữ chạy ứng dụng |
| Framework | Laravel | ^13.17 (đang dùng 13.37) | MVC, định tuyến, ORM, xác thực, kiểm thử |
| Cơ sở dữ liệu | MySQL | 8 (utf8mb4; container Docker khi phát triển) | Lưu trữ dữ liệu nghiệp vụ |
| Truy cập dữ liệu | Eloquent ORM | đi kèm Laravel | Ánh xạ bảng ↔ model, quan hệ, scope |
| Giao diện phía máy chủ | Blade | đi kèm Laravel | Template, component |
| CSS framework | Bootstrap | ^5.3.8 (Sass tuỳ biến) | Lưới, thành phần giao diện, responsive |
| Biên dịch tài nguyên | Vite + laravel-vite-plugin, Sass | ^8.0 / ^3.1, ^1.105 | Đóng gói CSS/JS |
| JavaScript | JavaScript thuần (fetch API) | — | Chat polling, đổi ảnh sản phẩm, tải biểu đồ |
| Biểu đồ | Chart.js | ^4.4.6 | Biểu đồ doanh thu trang thống kê |
| Kiểm thử | Pest (+ plugin Laravel) | ^4.7 / ^4.1 | Kiểm thử tự động (feature test) |
| Định dạng mã | Laravel Pint | ^1.27 | Chuẩn hoá phong cách mã PHP |
| Quản lý mã nguồn | Git | — | Quản lý phiên bản |
