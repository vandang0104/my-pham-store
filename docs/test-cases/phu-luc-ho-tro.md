# Phụ lục Chương 5 — Kiểm thử trang hỗ trợ (FAQ, liên hệ)

**Phạm vi:** `/cau-hoi-thuong-gap` (chỉ hiển thị từ seed), `/lien-he` (lưu `contact_messages`, honeypot, giới hạn 3 lượt / 10 phút theo IP).
**Mã nguồn test:** `tests/Feature/Support/FaqContactTest.php` — chạy `php artisan test tests/Feature/Support`
**Kết quả lần chạy gần nhất:** 14/14 test case đạt (52 assertion).

| Mã TC | Chức năng | Mô tả | Các bước | Dữ liệu vào | Kết quả mong đợi | Tên test |
|---|---|---|---|---|---|---|
| TC-HT-01 | FAQ | Hiển thị theo nhóm | Mở `/cau-hoi-thuong-gap` | 3 câu đang bật (2 nhóm), 1 câu đã tắt | HTTP 200; accordion; nhóm và câu đúng thứ tự `sort_order`; không có câu đã tắt | FaqContactTest › câu hỏi thường gặp › trả 200, nhóm theo chủ đề, câu hỏi dạng accordion, đúng thứ tự, ẩn câu đã tắt |
| TC-HT-02 | FAQ | Rỗng | Mở trang khi chưa có FAQ | — | "Chưa có câu hỏi nào" | … › chưa có câu hỏi nào → trạng thái trống |
| TC-HT-03 | Liên hệ | Hiển thị form | Mở `/lien-he` | — | HTTP 200; có hotline, email, giờ làm việc từ settings | FaqContactTest › liên hệ › form 200, có thông tin cửa hàng từ settings và ô honeypot ẩn |
| TC-HT-04 | Liên hệ | Gửi hợp lệ | Gửi form | `Lan@Example.com`, SĐT `0912 345 678` | Báo thành công; lưu tin chưa xử lý, email viết thường, SĐT `0912345678` | … › gửi hợp lệ: lưu tin chưa xử lý (email viết thường, SĐT chuẩn hoá), báo thành công |
| TC-HT-05 | Liên hệ | Dữ liệu sai | Gửi form sai từng trường | Thiếu họ tên / email sai / SĐT sai / thiếu chủ đề / nội dung < 10 hoặc > 2000 ký tự (6 bộ) | Báo lỗi đúng trường; không lưu | … › thiếu hoặc sai trường thì báo lỗi, không lưu |
| TC-HT-06 | Chống spam | Honeypot | Gửi form có điền ô ẩn `website` | `http://spam.example` | Báo thành công nhưng không lưu | … › honeypot: bot điền ô ẩn → vẫn báo thành công nhưng không lưu |
| TC-HT-07 | Chống spam | Giới hạn tần suất | Gửi hợp lệ 4 lần liên tiếp | — | 3 lần đầu lưu; lần 4 báo "Bạn đã gửi quá nhiều tin. Vui lòng thử lại sau 10 phút." | … › giới hạn 3 lượt / 10 phút theo IP: lượt thứ 4 bị chặn, báo lỗi tiếng Việt |
