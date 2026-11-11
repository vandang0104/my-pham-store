<?php

return [
    // position: home_hero (slider trang chủ), category_top (đầu trang danh mục)
    // 'anh': tên ảnh thật trong database/seeders/images/banner/ (<anh>-desktop, <anh>-mobile); thiếu thì dùng placeholder
    // 'mau': [nền, màu khối] của ảnh placeholder — bảng màu DESIGN.md §3 (lụa, giấy dó, ngọc nhạt, champagne, sơn mài, men ngọc)
    'banners' => [
        ['title' => 'Dưỡng da mùa thu – êm dịu từ thảo mộc', 'subtitle' => 'Toner, serum và kem dưỡng cấp ẩm cho làn da chuyển mùa', 'link' => '/danh-muc/duong-da', 'anh' => 'home-1', 'position' => 'home_hero', 'mau' => ['#D3DDD4', '#2E5A4C']],
        ['title' => 'Chống nắng mỗi ngày – ưu đãi đến 15%', 'subtitle' => 'Màng lọc phổ rộng, mỏng nhẹ cho da mặt trong khí hậu nhiệt đới', 'link' => '/danh-muc/chong-nang', 'anh' => 'home-2', 'position' => 'home_hero', 'mau' => ['#EBE5DA', '#C2A878']],
        ['title' => 'Làm sạch dịu nhẹ – nền tảng của làn da khoẻ', 'subtitle' => 'Sữa rửa mặt, tẩy trang và tẩy tế bào chết cho từng loại da', 'link' => '/danh-muc/lam-sach', 'anh' => 'home-3', 'position' => 'home_hero', 'mau' => ['#D3DDD4', '#C2A878']],
        ['title' => 'Mặt nạ cho buổi tối thư giãn', 'subtitle' => 'Đất sét làm sạch sâu và mặt nạ giấy cấp ẩm tức thì', 'link' => '/danh-muc/mat-na', 'anh' => 'category-top', 'position' => 'category_top', 'mau' => ['#EBE5DA', '#1E1B18']],
    ],

    'faqs' => [
        ['group' => 'Đặt hàng', 'question' => 'Tôi có cần tạo tài khoản để đặt hàng không?', 'answer' => 'Không bắt buộc. Bạn có thể đặt hàng với tư cách khách; sau khi đặt, hệ thống hiển thị mã đơn và gửi email xác nhận. Tạo tài khoản giúp bạn xem lại lịch sử đơn và tự huỷ đơn khi đơn chưa được giao.'],
        ['group' => 'Đặt hàng', 'question' => 'Tôi có thể huỷ đơn sau khi đã đặt không?', 'answer' => 'Bạn có thể huỷ đơn khi đơn đang ở trạng thái "Chờ xác nhận" hoặc "Đã xác nhận". Khi đơn đã chuyển sang "Đang giao hàng", vui lòng liên hệ hotline để được hỗ trợ.'],
        ['group' => 'Thanh toán', 'question' => 'Cửa hàng hỗ trợ những hình thức thanh toán nào?', 'answer' => 'Hiện cửa hàng hỗ trợ thanh toán khi nhận hàng (COD) và chuyển khoản ngân hàng. Với chuyển khoản, đơn được xác nhận sau khi chúng tôi nhận được thanh toán.'],
        ['group' => 'Đặt hàng', 'question' => 'Tôi theo dõi đơn hàng ở đâu?', 'answer' => 'Đăng nhập rồi vào Tài khoản → Đơn hàng của tôi để xem trạng thái từng đơn. Nếu bạn đặt hàng không dùng tài khoản, hãy gọi hotline hoặc chat với chúng tôi kèm mã đơn.'],
        ['group' => 'Vận chuyển', 'question' => 'Phí giao hàng được tính ra sao?', 'answer' => 'Phí giao hàng đồng giá 30.000 ₫ toàn quốc. Đơn từ 500.000 ₫ trở lên được miễn phí giao hàng.'],
        ['group' => 'Vận chuyển', 'question' => 'Bao lâu tôi nhận được hàng?', 'answer' => 'Nội thành TP. Hồ Chí Minh và Hà Nội: 1–2 ngày làm việc. Các tỉnh thành khác: 3–5 ngày làm việc kể từ khi đơn được xác nhận.'],
        ['group' => 'Đổi trả', 'question' => 'Chính sách đổi trả như thế nào?', 'answer' => 'Bạn được đổi trả trong 7 ngày nếu sản phẩm lỗi do nhà sản xuất, giao sai hoặc hư hỏng khi vận chuyển. Sản phẩm cần còn nguyên tem niêm phong và hoá đơn mua hàng.'],
        ['group' => 'Sản phẩm', 'question' => 'Làm sao để chọn sản phẩm phù hợp với loại da?', 'answer' => 'Mỗi sản phẩm đều ghi rõ loại da phù hợp ngay trên trang sản phẩm. Nếu chưa chắc, hãy chat với chúng tôi hoặc gửi tin qua trang Liên hệ để được tư vấn.'],
    ],

    'posts' => [
        [
            'title' => 'Quy trình chăm sóc da cơ bản 5 bước cho người mới bắt đầu',
            'excerpt' => 'Không cần mười bước cầu kỳ — năm bước nền tảng dưới đây đủ để làn da khoẻ, ổn định và sẵn sàng cho các hoạt chất chuyên sâu.',
            'content' => <<<'MD'
Chăm sóc da không cần phức tạp. Với người mới, điều quan trọng nhất là **đều đặn** và **phù hợp với loại da**.

## 1. Tẩy trang (buổi tối)

Kể cả khi không trang điểm, kem chống nắng và bụi mịn vẫn bám trên da. Dùng nước tẩy trang hoặc dầu tẩy trang là bước làm sạch đầu tiên.

## 2. Rửa mặt

Chọn sữa rửa mặt có độ pH dịu nhẹ. Da dầu có thể ưu tiên gel tạo bọt; da khô nên chọn dạng kem ít bọt.

## 3. Cân bằng da

Toner giúp cân bằng độ pH và cấp một lớp ẩm mỏng, để các bước sau thẩm thấu tốt hơn.

## 4. Dưỡng ẩm

Dù da dầu hay da khô, bước dưỡng ẩm đều cần thiết. Da thiếu nước thường tiết dầu nhiều hơn để bù lại.

## 5. Chống nắng (buổi sáng)

Đây là bước **quan trọng nhất** để ngừa lão hoá và thâm sạm. Thoa đủ lượng và thoa lại sau 2–3 giờ khi ở ngoài trời.

> Mẹo: Khi thêm sản phẩm mới, hãy thêm từng món một và theo dõi trong 2 tuần để biết da phản ứng ra sao.
MD,
        ],
        [
            'title' => 'Chọn kem chống nắng theo loại da: đừng chỉ nhìn chỉ số SPF',
            'excerpt' => 'SPF cao chưa chắc đã tốt nếu kết cấu khiến bạn ngại thoa lại. Cách chọn kem chống nắng phù hợp nằm ở loại da và thói quen sinh hoạt.',
            'content' => <<<'MD'
Chỉ số SPF cho biết khả năng chống tia UVB, còn PA (hoặc chữ "phổ rộng") cho biết khả năng chống UVA — tác nhân chính gây lão hoá.

## Da dầu, mụn

- Ưu tiên dạng **sữa** hoặc **gel** mỏng nhẹ, có bột kiềm dầu.
- Tránh kết cấu quá đặc dễ gây bí da.

## Da khô

- Chọn dạng **kem** hoặc lotion có thêm glycerin, hyaluronic acid.
- Có thể dùng kem chống nắng thay luôn bước kem dưỡng ban ngày.

## Da nhạy cảm

- Ưu tiên màng lọc **vật lý** (zinc oxide, titanium dioxide), không hương liệu.
- Thử trên vùng da nhỏ sau tai trước khi dùng toàn mặt.

## Bao nhiêu là đủ?

Khoảng **hai đốt ngón tay** cho toàn mặt và cổ. Thoa thiếu lượng sẽ làm hiệu quả giảm đáng kể so với chỉ số in trên bao bì.
MD,
        ],
        [
            'title' => 'Niacinamide và Vitamin C: có dùng chung được không?',
            'excerpt' => 'Lời đồn hai hoạt chất này "kỵ nhau" đã cũ. Dưới đây là cách kết hợp an toàn để tận dụng cả hai.',
            'content' => <<<'MD'
Từng có quan niệm niacinamide và vitamin C phản ứng với nhau tạo ra chất gây đỏ da. Thực tế, phản ứng đó chỉ xảy ra ở **nhiệt độ cao** trong thời gian dài — điều không xảy ra khi bạn thoa lên da.

## Cách kết hợp gợi ý

1. **Buổi sáng:** vitamin C để chống oxy hoá, sau đó kem chống nắng.
2. **Buổi tối:** niacinamide để điều tiết dầu và làm đều màu da.

Nếu da bạn đã quen, có thể dùng cả hai trong cùng một buổi: thoa vitamin C trước, đợi 1–2 phút rồi đến niacinamide.

## Lưu ý

- Vitamin C dễ bị oxy hoá — bảo quản nơi tối, đóng nắp kín.
- Niacinamide nồng độ 10% có thể gây châm chích nhẹ lúc đầu; hãy bắt đầu cách ngày.
MD,
        ],
        [
            'title' => 'Da dầu trong thời tiết nồm ẩm: làm sạch và dưỡng thế nào cho đủ',
            'excerpt' => 'Độ ẩm cao khiến da tiết dầu nhiều và dễ bí lỗ chân lông. Vài điều chỉnh nhỏ trong bước làm sạch và dưỡng ẩm sẽ tạo khác biệt lớn.',
            'content' => <<<'MD'
Thời tiết nồm ẩm làm da tiết dầu nhiều, bề mặt bóng loáng và lỗ chân lông dễ bít tắc.

## Làm sạch

- Rửa mặt **hai lần mỗi ngày** bằng sữa rửa mặt dịu nhẹ; không rửa quá nhiều lần khiến da càng tiết dầu.
- Buổi tối, tẩy trang trước rồi mới rửa mặt để lấy sạch kem chống nắng.

## Dưỡng ẩm

- Chuyển sang **gel dưỡng mỏng nhẹ** thay vì kem đặc.
- Toner có diếp cá hoặc tràm trà giúp da sạch thoáng hơn.

## Mỗi tuần

Tẩy tế bào chết dạng BHA 2–3 lần và đắp mặt nạ đất sét 1 lần để thông thoáng lỗ chân lông.
MD,
        ],
        [
            'title' => 'Tẩy tế bào chết cho da mặt: hoá học hay vật lý?',
            'excerpt' => 'Hạt chà xát dễ gây trầy xước li ti trên da mặt. Tẩy tế bào chết dạng hoá học (AHA, BHA, PHA, enzyme) nhẹ nhàng và đều tay hơn.',
            'content' => <<<'MD'
Lớp tế bào chết tích tụ khiến da xỉn màu, sần sùi và dễ nổi mụn ẩn.

## Các nhóm hoạt chất

- **BHA (salicylic acid):** tan trong dầu, đi sâu vào lỗ chân lông — hợp với da dầu, mụn đầu đen.
- **AHA, PHA:** tác động trên bề mặt, làm da mịn và đều màu; PHA dịu hơn cho da nhạy cảm.
- **Enzyme (đu đủ, dứa):** nhẹ nhất, phù hợp người mới bắt đầu.

## Dùng bao nhiêu lần?

Bắt đầu **2 lần mỗi tuần** vào buổi tối, tăng dần nếu da không đỏ rát.

> Sau khi tẩy tế bào chết, da nhạy nắng hơn — luôn dùng kem chống nắng vào ban ngày.
MD,
        ],
        [
            'title' => 'Đắp mặt nạ đúng cách: bao lâu một lần là đủ?',
            'excerpt' => 'Mặt nạ đất sét và mặt nạ giấy có vai trò khác nhau. Chọn đúng loại và đúng tần suất giúp da khoẻ mà không bị quá tải.',
            'content' => <<<'MD'
Mặt nạ là bước chăm sóc bổ sung, không thay thế các bước cơ bản hằng ngày.

## Theo loại mặt nạ

| Loại | Công dụng | Tần suất |
| --- | --- | --- |
| Mặt nạ đất sét | Hút dầu, làm sạch sâu | 1–2 lần/tuần |
| Mặt nạ giấy | Cấp ẩm tức thì | 2–3 lần/tuần |

## Lưu ý khi đắp

- Đắp sau bước làm sạch và toner.
- Không để mặt nạ đất sét **khô nứt** hoàn toàn; không đắp mặt nạ giấy quá 20 phút.
- Sau khi đắp, tiếp tục với serum và kem dưỡng để khoá ẩm.
MD,
        ],
    ],
];
