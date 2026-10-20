<?php

// Thông báo đặt hàng (CheckoutController, OrderService, mail OrderPlaced)
return [
    'empty_cart' => 'Giỏ hàng đang trống, hãy chọn sản phẩm trước khi đặt hàng.',
    'cart_changed' => 'Giỏ hàng vừa được cập nhật theo tồn kho: :details Vui lòng kiểm tra lại trước khi đặt hàng.',
    'out_of_stock' => 'Không đủ hàng cho: :items. Đơn hàng chưa được tạo, vui lòng điều chỉnh giỏ hàng.',
    'out_of_stock_item' => '“:name” (còn :stock)',
    'duplicate' => 'Đơn hàng này đã được gửi. Nếu chưa thấy trang xác nhận, vui lòng kiểm tra email hoặc giỏ hàng.',
    'placed' => 'Đặt hàng thành công! Mã đơn của bạn là :code.',
    'history_placed' => 'Khách đặt hàng.',
    'history_cancelled_by_customer' => 'Khách huỷ đơn.',
    'invalid_transition' => 'Không thể chuyển đơn từ “:from” sang “:to”.',
    'cancelled' => 'Đã huỷ đơn :code. Số lượng sản phẩm đã được hoàn lại kho.',
    'mail_subject' => 'Xác nhận đơn hàng :code — :app',
];
