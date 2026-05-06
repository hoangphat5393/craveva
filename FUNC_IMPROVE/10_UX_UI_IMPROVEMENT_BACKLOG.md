# UX/UI Improvement Backlog

## Muc tieu

- Day la file backlog tap trung cho cac yeu cau cai thien UX/UI.
- Tu nay, moi yeu cau UX/UI moi se duoc ghi them vao file nay.
- Giu dinh dang thong nhat de team de theo doi va uu tien.

## Cach ghi yeu cau moi

- Them 1 dong moi vao bang backlog ben duoi.
- Bat buoc co: `Ngay`, `Module`, `URL/Man hinh`, `Van de`, `De xuat`, `Uu tien`, `Trang thai`.
- Neu da fix, cap nhat `Trang thai` va bo sung `Ghi chu`.

## Backlog

| ID     | Ngay       | Module     | URL / Man hinh                                                                                            | Van de UX/UI                                                                                                       | De xuat cai thien                                                                              | Uu tien | Trang thai  | Ghi chu                                                 |
| ------ | ---------- | ---------- | --------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------- | ------- | ----------- | ------------------------------------------------------- |
| UX-001 | 2026-05-06 | Production | `/account/production/boms/create`                                                                         | Form BOM truoc day render nhieu dong trong gay roi, va cho phep chon RM trung FG trong mot so tinh huong thao tac. | Dung giao dien them/xoa dong theo nut Add; hard block RM = FG o UI (change + submit guard).    | High    | In Progress | Can xac nhan lai tren staging sau deploy.               |
| UX-002 | 2026-05-06 | Production | `/account/production/orders/create`, `/account/production/orders/edit`, `/account/production/orders/show` | Label kho viet tat RM/FG kho hieu voi nguoi dung moi.                                                              | Doi label hien thi sang Raw Material / Finished Goods bang key moi, giu key cu de tuong thich. | Medium  | Done        | Da them key moi LanguagePack, da doi view sang key moi. |
| UX-003 | 2026-05-06 | Production | `/account/production/boms`, sidebar Production                                                            | Ten "Production BOMs" chua than thien voi nguoi dung khong ky thuat.                                               | Doi sang "Bill of Materials" bang key moi, khong xoa key cu.                                   | Medium  | Done        | Da doi title/page/menu va key LanguagePack.             |

## Quy uoc trang thai

- `Open`: moi ghi nhan, chua bat dau.
- `In Progress`: dang xu ly.
- `Done`: da xong va da verify.
- `Blocked`: bi chan, can them thong tin/phan quyen/deploy.
