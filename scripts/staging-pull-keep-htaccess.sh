#!/bin/bash
# Trên staging: pull nhưng giữ .htaccess local, không kéo .htaccess từ repo
# Chạy trong thư mục gốc project: bash scripts/staging-pull-keep-htaccess.sh

if [ ! -f .htaccess ]; then
    echo "Không có .htaccess local, chạy git pull bình thường."
    git pull
    exit 0
fi

echo "Backup .htaccess local..."
mv .htaccess .htaccess.staging

git pull
if [ $? -ne 0 ]; then
    mv .htaccess.staging .htaccess
    echo "Pull lỗi, đã khôi phục .htaccess"
    exit 1
fi

echo "Khôi phục .htaccess của staging..."
rm -f .htaccess
mv .htaccess.staging .htaccess

# Tránh lần pull sau ghi đè .htaccess (file đang được track trên remote)
git update-index --assume-unchanged .htaccess 2>/dev/null
echo "Đã đánh dấu .htaccess assume-unchanged để pull sau không ghi đè."
