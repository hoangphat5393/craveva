客戶代號 | Customer Code → client_code
客戶簡稱 | Customer Short Name → name (bắt buộc)
統一編號 | Tax ID | gst_number → gst_number
業務員 | Salesperson → salesperson (custom field)
業務助理名稱 | Sales Assistant Name → sales_assistant_name (custom field)
客戶(集團)分級 | Customer Grade → customer_grade (custom field)
通路別 | Channel Type → channel_type (custom field)
地區別 | Geographical distinction → geographical_distinction
型態別 | Business Type → business_type (custom field)
送貨地址 | Shipping Address → address
TEL_NO(一) | mobile → mobile
TEL_NO(二) | company_phone → company_phone
交易條件 | Payment Terms → payment_terms (custom field)
最近交易 | last_transaction_at → last_transaction_at (custom field)
歇業日期 | Business Closure Date → business_closure_date (custom field)
指定庫別名稱 | designated_warehouse_name → designated_warehouse_name

Tier 1 (nên chuyển ngay)
-- business_closure_date
Vì đã có tác động nghiệp vụ thật: import có giá trị này thì hệ thống set users.status = inactive.
Nếu để custom field lâu dài sẽ khó kiểm soát nhất quán.
Tier 2 (chuyển khi bắt đầu dùng cho rule/filter/report chính thức)
-- payment_terms — 交易條件 | Payment Terms | cách thức và thời hạn thanh toán: 30 ngày, 60 ngày, 90 ngày, 120 ngày, 180 ngày
-- customer_grade — 客戶(集團)分級 | Customer Grade (Phân cấp / phân hạng khách)
-- channel_type — 通路別 | Channel Type | bán lẻ, đại lý, siêu thị, chuỗi, đơn lẻ
-- business_type — 型態別 | Business Type | sỉ / lẻ / nhà hàng / spa / chuỗi / đơn lẻ  
Lý do: có ý nghĩa vận hành/report rõ, nhưng hiện chưa thấy query/join/rule xuyên module dùng trực tiếp.

Giữ custom (hiện tại)
-- salesperson
-- sales_assistant_name
-- geographical_distinction
-- last_transaction_at (nên tính từ giao dịch thực tế thay vì nhập tay)
