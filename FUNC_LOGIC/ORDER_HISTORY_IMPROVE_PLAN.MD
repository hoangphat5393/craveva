Đề xuất cải thiện (ưu tiên từ hiệu quả nhất)

1) Bỏ parse file trong request, chuyển 100% sang queue background

    Upload xong trả về ngay batch_id, không đọc toàn bộ file trong importStore.
    Đây là cải thiện lớn nhất: UI phản hồi nhanh, không timeout/hết RAM trên request web.

2) Không đọc file 2 lần

    Luồng hiện tại đang có khả năng parse file ở bước upload + parse lại ở bước process.
    Chỉ nên parse 1 lần trong worker queue.

3) Không gom toàn bộ rows vào mảng RAM (processedData)

    Dù có chunk reading, nếu vẫn append toàn bộ vào array thì vẫn nổ RAM.
    Chuyển sang xử lý từng chunk và ghi DB ngay.

4) Tối ưu DB khi import

    Cache map client_code -> user_id và sku -> product_id theo chunk (tránh query từng dòng).
    Dùng insert/upsert theo lô thay vì save() từng dòng + transaction từng row.

5) Cấu hình PhpSpreadsheet dùng cache ra disk

    Giảm dùng RAM khi workbook lớn (đổi cache driver sang file/illuminate trong config excel).

6) Giải pháp vận hành tạm thời

    Tách file lớn thành CSV theo tháng rồi import nhiều lần (nhanh và ổn định hơn xlsx lớn nhiều sheet).

---

Các phương án đã thực hiện (trạng thái hiện tại)

- [x] Giữ màn mapping cột, nhưng tối ưu bước upload/map chỉ đọc sheet đầu cho `SalesHistoryImport`.
- [x] Ở bước mapping đã đổi sang đọc nhẹ:
  - chỉ đọc header dòng 1 của sheet đầu;
  - chỉ đọc sample data của sheet đầu;
  - dùng `ChunkReadFilter` để giới hạn dòng nạp vào RAM.
- [x] Loại bỏ key nội bộ `__sheet` khỏi sample rows để tránh lỗi `Undefined array key "sheet"` ở `import.process-form`.
- [x] Chỉnh sample scan từ 300 dòng xuống 100 dòng để giảm tải request hơn nữa.
- [ ] Chưa áp dụng full (1)+(2)+(3) vì đang giữ UX mapping cột theo yêu cầu.

Ghi chú quyết định

- Mục tiêu hiện tại là "partial (1)": bỏ đọc nặng ở bước mapping nhưng vẫn giữ màn map cột.
- Bước process (sau khi submit mapping) vẫn chạy theo flow hiện tại để tránh thay đổi UX quá lớn.
