# Business Logic Module Index

Generated: 2026-07-04

| Module | Alias | File | Purpose Draft |
| --- | --- | --- | --- |
| Affiliate | affiliate | [MODULE_AFFILIATE.md](MODULE_AFFILIATE.md) | Quản lý affiliate, referral và payout hoa hồng. |
| Asset | asset | [MODULE_ASSET.md](MODULE_ASSET.md) | Quản lý tài sản, loại tài sản, lịch sử bàn giao/thu hồi và cấu hình tài sản. |
| Biolinks | biolinks | [MODULE_BIOLINKS.md](MODULE_BIOLINKS.md) | Quản lý trang bio link công khai và các block nội dung. |
| Biometric | biometric | [MODULE_BIOMETRIC.md](MODULE_BIOMETRIC.md) | Quản lý máy chấm công sinh trắc học, nhân viên đồng bộ và dữ liệu attendance. |
| CyberSecurity | cybersecurity | [MODULE_CYBERSECURITY.md](MODULE_CYBERSECURITY.md) | Quản lý thiết lập bảo mật, blacklist IP/email và giới hạn đăng nhập. |
| DeveloperTools | developertools | [MODULE_DEVELOPERTOOLS.md](MODULE_DEVELOPERTOOLS.md) | Công cụ nội bộ để theo dõi file, dependency, mapping user DB, credential và log truy cập DB. |
| EInvoice | einvoice | [MODULE_EINVOICE.md](MODULE_EINVOICE.md) | Cấu hình hóa đơn điện tử cấp hệ thống và cấp công ty. |
| LanguagePack | languagepack | [MODULE_LANGUAGEPACK.md](MODULE_LANGUAGEPACK.md) | Quản lý gói ngôn ngữ, bản dịch module và thiết lập language pack. |
| Letter | letter | [MODULE_LETTER.md](MODULE_LETTER.md) | Quản lý mẫu thư và phát hành letter theo template. |
| LineIntegration | lineintegration | [MODULE_LINEINTEGRATION.md](MODULE_LINEINTEGRATION.md) | Tích hợp LINE; cần xác nhận thêm luồng nghiệp vụ vì module hiện có ít entity. |
| Onboarding | Onboarding | [MODULE_ONBOARDING.md](MODULE_ONBOARDING.md) | Quản lý onboarding task, cấu hình onboarding và task hoàn tất. |
| Payroll | payroll | [MODULE_PAYROLL.md](MODULE_PAYROLL.md) | Quản lý salary component/group, payroll cycle, payslip, overtime, pay code và payment method. |
| Performance | performance | [MODULE_PERFORMANCE.md](MODULE_PERFORMANCE.md) | Quản lý OKR, objective, key result, check-in, meeting, agenda/action và scoring. |
| Policy | policy | [MODULE_POLICY.md](MODULE_POLICY.md) | Quản lý chính sách nội bộ, file đính kèm và xác nhận đã đọc của nhân viên. |
| Pricing | pricing | [MODULE_PRICING.md](MODULE_PRICING.md) | Quản lý bảng giá khách hàng/sản phẩm, tier pricing và volume discount. |
| Production | production | [MODULE_PRODUCTION.md](MODULE_PRODUCTION.md) | Quản lý BOM, production order, batch, consumption/output, rework, variance và nhập kho thành phẩm. |
| ProjectRoadmap | projectroadmap | [MODULE_PROJECTROADMAP.md](MODULE_PROJECTROADMAP.md) | Quản lý cấu hình hoặc màn hình roadmap dự án; cần xác nhận thêm phạm vi thực tế. |
| Purchase | purchase | [MODULE_PURCHASE.md](MODULE_PURCHASE.md) | Quản lý sản phẩm mua, vendor, purchase order, delivery/GRN, bill, vendor credit, payment và một phần sales fulfillment. |
| QRCode | qrcode | [MODULE_QRCODE.md](MODULE_QRCODE.md) | Quản lý QR code data và cấu hình QR. |
| Recruit | recruit | [MODULE_RECRUIT.md](MODULE_RECRUIT.md) | Quản lý tuyển dụng: job, application, candidate database, interview, offer letter và cấu hình. |
| ServerManager | servermanager | [MODULE_SERVERMANAGER.md](MODULE_SERVERMANAGER.md) | Quản lý provider, server hosting, domain, log và cấu hình server. |
| Sms | sms | [MODULE_SMS.md](MODULE_SMS.md) | Quản lý SMS setting, notification setting và template id. |
| Subdomain | subdomain | [MODULE_SUBDOMAIN.md](MODULE_SUBDOMAIN.md) | Quản lý subdomain tenant và cấu hình subdomain. |
| Warehouse | warehouse | [MODULE_WAREHOUSE.md](MODULE_WAREHOUSE.md) | Quản lý warehouse, stock, batch, movement, transfer, reservation và flow setting theo công ty. |
| Webhooks | webhooks | [MODULE_WEBHOOKS.md](MODULE_WEBHOOKS.md) | Quản lý webhook setting, request và log tích hợp ngoài. |
| Zoom | zoom | [MODULE_ZOOM.md](MODULE_ZOOM.md) | Quản lý meeting Zoom, category, note, webhook và notification setting. |

## Notes

- Đây là bộ tài liệu nghiệp vụ theo module, tạo từ source scan ban đầu.
- Các file có trạng thái Draft từ source code scan cần được audit sâu trước khi coi là đặc tả chính thức.
- File playbook: [MODULE_PLAYBOOK.md](MODULE_PLAYBOOK.md).
- Các file `*_BUSINESS.md` cũ không bị coi là trùng nếu đang là deep-dive nghiệp vụ; file `MODULE_*.md` giữ vai trò hub module và trỏ về deep-dive liên quan.
- Worktree hiện có nhiều thay đổi sẵn; bộ tài liệu này chỉ thêm mới trong FUNC_LOGIC/.
