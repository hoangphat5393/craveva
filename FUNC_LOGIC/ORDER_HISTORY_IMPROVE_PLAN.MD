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

**Số liệu cụ thể khi Upload (chỉ `SalesHistoryImport`, bước mở form map cột):**

| Hạng mục                              | Giá trị               | Ghi chú               |
|---------------------------------------|-----------------------|----------------------------------------------------------|
| Số sheet **nạp nội dung**             | **1**                 | Chỉ sheet đầu (`setLoadSheetsOnly` tên sheet đầu). Tên các sheet khác có thể được đọc metadata qua `listWorksheetNames` nhưng không load dữ liệu các sheet đó ở bước này. |
| Header (khi bật “có dòng tiêu đề”)    | **1 dòng**            | Dòng **1** của sheet đầu, filter `ChunkReadFilter` 1–1. |
| Vùng quét để lấy sample               | **tối đa 100 dòng**   | Từ dòng 2 nếu có header, từ dòng 1 nếu không; `maxScanRows = 100` trong `readFirstSheetSampleRows`. |
| Số dòng **hiển thị** trên UI sample   | **tối đa 5**          | Lấy tối đa 5 dòng **không rỗng** đầu tiên trong vùng quét trên. |
| Thời gian phản hồi UI                 | **~0.5s**             | Chỉ đọc sheet đầu, metadata và sample — tải nhẹ. |


- [x] Loại bỏ key nội bộ `__sheet` khỏi sample rows để tránh lỗi `Undefined array key "sheet"` ở `import.process-form`.
- [x] Chỉnh sample scan từ 300 dòng xuống 100 dòng để giảm tải request hơn nữa.
- [x] Chuyển bước process sang batch nhiều jobs theo `sheet + row range` (chunk size 2000), thay vì 1 job lớn duy nhất.
- [x] Vẫn giữ UX mapping cột hiện tại (không bỏ bước map), nhưng process đã chạy queue kiểu chunk giống client import.
- [ ] Chưa áp dụng full (1)+(2)+(3) thuần vì vẫn giữ màn map cột theo yêu cầu.

Ghi chú quyết định

- Mục tiêu hiện tại là "partial (1)": bỏ đọc nặng ở bước mapping nhưng vẫn giữ màn map cột.
- Bước process (sau khi submit mapping) đã chuyển sang mô hình queue chunk nhiều jobs, giúp progress tăng dần thay vì nhảy 0% -> 100%.
