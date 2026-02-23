# Khách hàng (Customer) = Người dùng có vai trò Client — Giải thích & ERD

## 1. Khái niệm cốt lõi

- Customer trong hệ thống hiện tại được biểu diễn bằng một bản ghi **User** có gán **vai trò `client`**.
- Thông tin chi tiết của khách hàng được lưu ở bảng **client_details** (gắn 1-1 với user) như company_name, địa chỉ, website…
- Mỗi **Company** (tenant) sở hữu tập khách hàng (users có role `client`) của riêng mình.

## 2. Bằng chứng trong code

- Lọc khách theo vai trò `client`: [ClientsDataTable.php](file:///f:/web/new.craveva.com/app/DataTables/ClientsDataTable.php#L109-L168)
    - Truy vấn join `role_user` và `roles`, where `roles.name = 'client'`.
- Quan hệ Company → Clients: [Company::clients](file:///f:/web/new.craveva.com/app/Models/Company.php#L620-L630)
    - Trả về các `users` thuộc company có `ClientDetails`.
- Bảng chi tiết khách: [ClientDetails.php](file:///f:/web/new.craveva.com/app/Models/ClientDetails.php)

## 3. ERD (ASCII) — Khách hàng dựa trên User/Role

```
┌───────────────────────┐         ┌───────────────────────┐
│        roles          │         │       role_user       │
│  id (PK)              │         │  role_id (FK→roles.id)│
│  name ('client', ...) │         │  user_id (FK→users.id)│
└──────────┬────────────┘         └──────────┬────────────┘
           │                                   │
           │                                   │
┌──────────▼────────────┐         ┌──────────▼────────────┐
│         users         │         │     client_details     │
│  id (PK)              │         │  id (PK)              │
│  company_id (tenant)  │         │  user_id (FK→users.id)│
│  name, email, status  │         │  company_name, address│
│  image, mobile, ...   │         │  website, note, ...   │
└──────────┬────────────┘         └──────────┬────────────┘
           │                                   │
           │ (clients of a company)            │ (1-1 details)
┌──────────▼────────────┐
│       companies       │
│  id (PK)              │
│  company_name, ...    │
└───────────────────────┘

Logic nghiệp vụ:
  - Một User có thể mang vai trò `client` → được coi là Customer.
  - Mỗi Customer có chi tiết ở client_details.
  - Mỗi Company quản lý tập Customer của riêng mình (qua users.company_id).
```

## 4. Ánh xạ với Pricing System

- Corporate Pricing (giá theo khách): map theo cặp `(seller_company_id → client_user_id)` thay vì `customer_company_id`.
- Tier áp dụng theo company hoặc client: tra cứu theo resolution order tại: [B2B_PRICING_SYSTEM_PROCESS.md](file:///f:/web/new.craveva.com/FUNCTIONAL%20DEVELOPMENT/B2B_PRICING_SYSTEM_PROCESS.md#L82-L96)
- Import Tier Pricing: dùng email/mã khách hoặc `client_user_id` làm khóa nhận diện. Tránh dùng “tên khách”. Tham chiếu cột import: [Gap_Price_Tier.md](file:///f:/web/new.craveva.com/FUNCTIONAL%20DEVELOPMENT/Gap_Price_Tier.md#L246-L266)

## 5. Gợi ý mở rộng (tùy tương lai)

- Nếu cần quản lý **tổ chức người mua** ở cấp công ty chuỗi, có thể thêm bảng `customer_company` để gom nhóm nhiều client (liên hệ) → không bắt buộc giai đoạn đầu.

## 6. ERD — UserAuth ↔ Users ↔ Company ↔ Roles (đăng nhập & quyền)

### 6.1 Vai trò từng bảng

- UserAuth: lưu thông tin đăng nhập (email/password), trạng thái; provider chính trong [auth.php](file:///f:/web/new.craveva.com/config/auth.php#L51-L69). Ràng buộc đăng nhập, kiểm tra subdomain/công ty bị khóa: [UserAuth::validateLoginActiveDisabled](file:///f:/web/new.craveva.com/app/Models/UserAuth.php#L133-L177).
- Users: hồ sơ/membership theo công ty; chứa `company_id`, `user_auth_id`, vai trò, trạng thái. Sau đăng nhập, session gắn user hiện tại: [start.php:user()](file:///f:/web/new.craveva.com/app/Helper/start.php#L42-L80).
- Companies: tenant; Global scope tự động lọc dữ liệu theo `company_id` sau login: [CompanyScope.php](file:///f:/web/new.craveva.com/app/Scopes/CompanyScope.php#L1-L39).
- Roles/role_user: phân quyền, xác định user là admin/employee/client.
- ClientDetails: chi tiết khách (customer) gắn 1-1 với user có role `client`.

### 6.2 Sơ đồ ERD (ASCII)

```
┌───────────────────────┐          ┌───────────────────────┐          ┌───────────────────────┐
│       UserAuth        │          │         users         │          │       companies       │
│  id (PK)              │◄────────▶│  id (PK)              │◄────────▶│  id (PK)              │
│  email, password      │  user_auth_id (FK→UserAuth.id)   │  company_name, status  │
│  status/login flags   │          │  company_id (FK→companies.id)     │  approved            │
└──────────┬────────────┘          │  name, email, status              │
           │                        │  …                                │
           │                        └──────────┬────────────┘
           │                                    │
           │                                    │ (1-1 details for client)
┌──────────▼────────────┐          ┌──────────▼────────────┐          ┌───────────────────────┐
│        roles          │          │     client_details     │          │       role_user       │
│  id (PK)              │          │  id (PK)              │          │  role_id (FK→roles.id)│
│  name ('admin',       │          │  user_id (FK→users.id)│          │  user_id (FK→users.id)│
│   'employee','client')│          │  company_name, address│          └───────────────────────┘
└───────────────────────┘          │  website, note, …     │
                                   └───────────────────────┘

Luồng đăng nhập & quyền:
  1) Fortify xác thực bằng UserAuth → tạo session user (Users).
  2) CompanyScope áp dụng `company_id` để lọc dữ liệu theo tenant.
  3) Vai trò từ roles/role_user quyết định quyền truy cập Admin panel/Company panel.
  4) Khách hàng (customer) = user có role `client` + chi tiết ở client_details.
```
