import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/scss/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // Bootstrap 5.3 còn dùng cú pháp Sass cũ: ẩn cảnh báo deprecation đến từ node_modules
                quietDeps: true,
                // Tuỳ biến biến Bootstrap bắt buộc dùng @import (Bootstrap 5.3 chưa hỗ trợ @use with):
                // tắt cảnh báo của chính các deprecation này trong file của dự án
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
