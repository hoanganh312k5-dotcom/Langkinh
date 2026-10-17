#!/usr/bin/env bash
# Chạy website bằng PHP built-in server (không cần Apache). Cần bật MySQL trước.
cd "$(dirname "$0")"
echo "Mở trình duyệt tại http://localhost:8000"
php -S localhost:8000 -t public
