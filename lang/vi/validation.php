<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Thông báo kiểm tra dữ liệu (Validation)
    |--------------------------------------------------------------------------
    |
    | :Attribute viết hoa chữ cái đầu khi đứng đầu câu; tên trường tiếng Việt
    | khai báo ở mục 'attributes' phía dưới.
    |
    */

    'accepted' => ':Attribute phải được chấp nhận.',
    'accepted_if' => ':Attribute phải được chấp nhận khi :other là :value.',
    'active_url' => ':Attribute phải là một URL hợp lệ.',
    'after' => ':Attribute phải là ngày sau :date.',
    'after_or_equal' => ':Attribute phải là ngày từ :date trở về sau.',
    'alpha' => ':Attribute chỉ được chứa chữ cái.',
    'alpha_dash' => ':Attribute chỉ được chứa chữ cái, chữ số, dấu gạch ngang và gạch dưới.',
    'alpha_num' => ':Attribute chỉ được chứa chữ cái và chữ số.',
    'any_of' => ':Attribute không hợp lệ.',
    'array' => ':Attribute phải là một mảng.',
    'array_keys' => ':Attribute chỉ được chứa các khoá sau: :values.',
    'ascii' => ':Attribute chỉ được chứa ký tự chữ, số và ký hiệu một byte.',
    'base64' => ':Attribute phải là chuỗi Base64 hợp lệ.',
    'before' => ':Attribute phải là ngày trước :date.',
    'before_or_equal' => ':Attribute phải là ngày từ :date trở về trước.',
    'between' => [
        'array' => ':Attribute phải có từ :min đến :max phần tử.',
        'file' => ':Attribute phải có dung lượng từ :min đến :max kilobyte.',
        'numeric' => ':Attribute phải nằm trong khoảng :min đến :max.',
        'string' => ':Attribute phải dài từ :min đến :max ký tự.',
    ],
    'boolean' => ':Attribute phải là đúng hoặc sai.',
    'can' => ':Attribute chứa giá trị không được phép.',
    'confirmed' => 'Xác nhận :attribute không khớp.',
    'contains' => ':Attribute thiếu một giá trị bắt buộc.',
    'current_password' => 'Mật khẩu không đúng.',
    'date' => ':Attribute phải là ngày hợp lệ.',
    'date_equals' => ':Attribute phải là ngày :date.',
    'date_format' => ':Attribute phải đúng định dạng :format.',
    'decimal' => ':Attribute phải có :decimal chữ số thập phân.',
    'declined' => ':Attribute phải bị từ chối.',
    'declined_if' => ':Attribute phải bị từ chối khi :other là :value.',
    'different' => ':Attribute và :other phải khác nhau.',
    'digits' => ':Attribute phải gồm :digits chữ số.',
    'digits_between' => ':Attribute phải có từ :min đến :max chữ số.',
    'dimensions' => ':Attribute có kích thước ảnh không hợp lệ.',
    'distinct' => ':Attribute có giá trị bị trùng.',
    'doesnt_contain' => ':Attribute không được chứa các giá trị sau: :values.',
    'doesnt_end_with' => ':Attribute không được kết thúc bằng một trong các giá trị sau: :values.',
    'doesnt_start_with' => ':Attribute không được bắt đầu bằng một trong các giá trị sau: :values.',
    'email' => ':Attribute phải là địa chỉ email hợp lệ.',
    'encoding' => ':Attribute phải được mã hoá theo :encoding.',
    'ends_with' => ':Attribute phải kết thúc bằng một trong các giá trị sau: :values.',
    'enum' => ':Attribute đã chọn không hợp lệ.',
    'exists' => ':Attribute đã chọn không hợp lệ.',
    'extensions' => ':Attribute phải có một trong các phần mở rộng sau: :values.',
    'file' => ':Attribute phải là một tệp.',
    'filled' => ':Attribute không được để trống.',
    'gt' => [
        'array' => ':Attribute phải có nhiều hơn :value phần tử.',
        'file' => ':Attribute phải lớn hơn :value kilobyte.',
        'numeric' => ':Attribute phải lớn hơn :value.',
        'string' => ':Attribute phải dài hơn :value ký tự.',
    ],
    'gte' => [
        'array' => ':Attribute phải có ít nhất :value phần tử.',
        'file' => ':Attribute phải lớn hơn hoặc bằng :value kilobyte.',
        'numeric' => ':Attribute phải lớn hơn hoặc bằng :value.',
        'string' => ':Attribute phải dài ít nhất :value ký tự.',
    ],
    'hex_color' => ':Attribute phải là mã màu hệ thập lục phân hợp lệ.',
    'image' => ':Attribute phải là hình ảnh.',
    'in' => ':Attribute đã chọn không hợp lệ.',
    'in_array' => ':Attribute phải có trong :other.',
    'in_array_keys' => ':Attribute phải chứa ít nhất một trong các khoá sau: :values.',
    'integer' => ':Attribute phải là số nguyên.',
    'ip' => ':Attribute phải là địa chỉ IP hợp lệ.',
    'ipv4' => ':Attribute phải là địa chỉ IPv4 hợp lệ.',
    'ipv6' => ':Attribute phải là địa chỉ IPv6 hợp lệ.',
    'json' => ':Attribute phải là chuỗi JSON hợp lệ.',
    'list' => ':Attribute phải là một danh sách.',
    'lowercase' => ':Attribute phải viết thường.',
    'lt' => [
        'array' => ':Attribute phải có ít hơn :value phần tử.',
        'file' => ':Attribute phải nhỏ hơn :value kilobyte.',
        'numeric' => ':Attribute phải nhỏ hơn :value.',
        'string' => ':Attribute phải ngắn hơn :value ký tự.',
    ],
    'lte' => [
        'array' => ':Attribute không được có nhiều hơn :value phần tử.',
        'file' => ':Attribute phải nhỏ hơn hoặc bằng :value kilobyte.',
        'numeric' => ':Attribute phải nhỏ hơn hoặc bằng :value.',
        'string' => ':Attribute không được dài quá :value ký tự.',
    ],
    'mac_address' => ':Attribute phải là địa chỉ MAC hợp lệ.',
    'max' => [
        'array' => ':Attribute không được có nhiều hơn :max phần tử.',
        'file' => ':Attribute không được lớn hơn :max kilobyte.',
        'numeric' => ':Attribute không được lớn hơn :max.',
        'string' => ':Attribute không được dài quá :max ký tự.',
    ],
    'max_digits' => ':Attribute không được có nhiều hơn :max chữ số.',
    'mimes' => ':Attribute phải là tệp thuộc loại: :values.',
    'mimetypes' => ':Attribute phải là tệp thuộc loại: :values.',
    'min' => [
        'array' => ':Attribute phải có ít nhất :min phần tử.',
        'file' => ':Attribute phải có dung lượng ít nhất :min kilobyte.',
        'numeric' => ':Attribute phải lớn hơn hoặc bằng :min.',
        'string' => ':Attribute phải có ít nhất :min ký tự.',
    ],
    'min_digits' => ':Attribute phải có ít nhất :min chữ số.',
    'missing' => ':Attribute không được xuất hiện.',
    'missing_if' => ':Attribute không được xuất hiện khi :other là :value.',
    'missing_unless' => ':Attribute không được xuất hiện trừ khi :other là :value.',
    'missing_with' => ':Attribute không được xuất hiện khi có :values.',
    'missing_with_all' => ':Attribute không được xuất hiện khi có đủ :values.',
    'multiple_of' => ':Attribute phải là bội số của :value.',
    'not_in' => ':Attribute đã chọn không hợp lệ.',
    'not_regex' => ':Attribute không đúng định dạng.',
    'numeric' => ':Attribute phải là số.',
    'password' => [
        'letters' => ':Attribute phải chứa ít nhất một chữ cái.',
        'mixed' => ':Attribute phải chứa ít nhất một chữ hoa và một chữ thường.',
        'numbers' => ':Attribute phải chứa ít nhất một chữ số.',
        'symbols' => ':Attribute phải chứa ít nhất một ký hiệu.',
        'uncompromised' => ':Attribute này đã xuất hiện trong một vụ lộ dữ liệu. Vui lòng chọn :attribute khác.',
    ],
    'present' => ':Attribute phải có mặt.',
    'present_if' => ':Attribute phải có mặt khi :other là :value.',
    'present_unless' => ':Attribute phải có mặt trừ khi :other là :value.',
    'present_with' => ':Attribute phải có mặt khi có :values.',
    'present_with_all' => ':Attribute phải có mặt khi có đủ :values.',
    'prohibited' => ':Attribute không được phép.',
    'prohibited_if' => ':Attribute không được phép khi :other là :value.',
    'prohibited_if_accepted' => ':Attribute không được phép khi :other được chấp nhận.',
    'prohibited_if_declined' => ':Attribute không được phép khi :other bị từ chối.',
    'prohibited_unless' => ':Attribute không được phép trừ khi :other thuộc :values.',
    'prohibits' => ':Attribute không cho phép :other xuất hiện.',
    'regex' => ':Attribute không đúng định dạng.',
    'required' => ':Attribute không được để trống.',
    'required_array_keys' => ':Attribute phải có các mục: :values.',
    'required_if' => ':Attribute không được để trống khi :other là :value.',
    'required_if_accepted' => ':Attribute không được để trống khi :other được chấp nhận.',
    'required_if_declined' => ':Attribute không được để trống khi :other bị từ chối.',
    'required_unless' => ':Attribute không được để trống trừ khi :other thuộc :values.',
    'required_with' => ':Attribute không được để trống khi có :values.',
    'required_with_all' => ':Attribute không được để trống khi có đủ :values.',
    'required_without' => ':Attribute không được để trống khi không có :values.',
    'required_without_all' => ':Attribute không được để trống khi không có giá trị nào trong :values.',
    'same' => ':Attribute phải khớp với :other.',
    'size' => [
        'array' => ':Attribute phải có đúng :size phần tử.',
        'file' => ':Attribute phải có dung lượng :size kilobyte.',
        'numeric' => ':Attribute phải bằng :size.',
        'string' => ':Attribute phải dài đúng :size ký tự.',
    ],
    'starts_with' => ':Attribute phải bắt đầu bằng một trong các giá trị sau: :values.',
    'string' => ':Attribute phải là chuỗi ký tự.',
    'timezone' => ':Attribute phải là múi giờ hợp lệ.',
    'unique' => ':Attribute đã được sử dụng.',
    'uploaded' => 'Tải lên :attribute thất bại.',
    'uppercase' => ':Attribute phải viết hoa.',
    'url' => ':Attribute phải là URL hợp lệ.',
    'ulid' => ':Attribute phải là ULID hợp lệ.',
    'uuid' => ':Attribute phải là UUID hợp lệ.',

    /*
    |--------------------------------------------------------------------------
    | Thông báo riêng cho từng trường
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'phone' => [
            'regex' => 'Số điện thoại phải gồm 10 chữ số, bắt đầu bằng 03, 05, 07, 08, 09 (hoặc +84).',
        ],
        'email' => [
            'unique' => 'Email này đã được đăng ký.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tên trường hiển thị trong thông báo
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'họ tên',
        'email' => 'email',
        'phone' => 'số điện thoại',
        'password' => 'mật khẩu',
        'password_confirmation' => 'xác nhận mật khẩu',
        'current_password' => 'mật khẩu hiện tại',
        'remember' => 'ghi nhớ đăng nhập',
        'token' => 'mã đặt lại mật khẩu',
    ],

];
