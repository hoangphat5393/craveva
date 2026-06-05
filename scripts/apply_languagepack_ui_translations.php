<?php

declare(strict_types=1);

/**
 * Apply human UI translations for sidebar + company settings keys
 * (keys that were backfilled from EN during sync_languagepack_keys_from_en.php).
 *
 * Usage: php scripts/apply_languagepack_ui_translations.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\VarExporter\VarExporter;

/** @var array<string, array<string, string>> */
$appMenuOverlays = [
    'th' => [
        'settingsMenuGroupCompany' => 'บริษัท',
        'settingsMenuItemCompanyGeneral' => 'ทั่วไป',
        'settingsMenuGroupPersonal' => 'โปรไฟล์',
        'settingsMenuGroupSales' => 'การขาย',
        'settingsMenuGroupProcurement' => 'การดำเนินงาน',
        'settingsMenuGroupFinanceTax' => 'การเงิน',
        'settingsMenuGroupHumanResources' => 'ทรัพยากรบุคคล',
        'settingsMenuGroupProjectsSupport' => 'โครงการ',
        'settingsMenuGroupSystem' => 'ระบบ',
        'settingsMenuGroupAdminTechnical' => 'ผู้ดูแลระบบ',
        'financeSettings' => 'ใบแจ้งหนี้และใบเสนอราคา',
        'currencySettings' => 'สกุลเงิน',
        'taxSettings' => 'ภาษี',
        'paymentGatewayCredential' => 'เกตเวย์ชำระเงิน',
        'saleOrderSettings' => 'คำสั่งขาย',
        'contractSettings' => 'สัญญา',
        'leadSettings' => 'ลูกค้าเป้าหมาย',
        'attendanceSettings' => 'การเข้างาน',
        'leaveSettings' => 'การลา',
        'projectSettings' => 'โครงการ',
        'taskSettings' => 'งาน',
        'timeLogSettings' => 'บันทึกเวลา',
        'ticketSettings' => 'ตั๋ว',
        'messageSettings' => 'ข้อความ',
        'appSettings' => 'แอป',
        'notificationSettings' => 'การแจ้งเตือน',
        'themeSettings' => 'ธีม',
        'moduleSettings' => 'โมดูล',
        'securitySettings' => 'ความปลอดภัย',
        'storageSettings' => 'ที่เก็บข้อมูล',
        'languageSettings' => 'ภาษา',
        'socialLogin' => 'เข้าสู่ระบบโซเชียล',
        'customLinkSetting' => 'ลิงก์กำหนดเอง',
        'gdprSettings' => 'GDPR',
        'googleCalendarSetting' => 'Google Calendar',
        'databaseBackupSetting' => 'สำรองฐานข้อมูล',
        'businessAddresses' => 'ที่อยู่',
        'signUpSetting' => 'ลงทะเบียน',
        'developerTools' => 'เครื่องมือนักพัฒนา',
        'codeMap' => 'Code Map',
        'workManagement' => 'การจัดการงาน',
        'procurement' => 'การจัดซื้อ',
        'salesFulfillment' => 'การขาย',
        'inventoryWarehouse' => 'คลังสินค้า',
        'productionHub' => 'การผลิต',
        'sales' => 'ลูกค้า',
        'humanResources' => 'ทรัพยากรบุคคล',
        'payrollSidebar' => 'เงินเดือน',
        'pricing' => 'ราคา',
        'quotation' => 'ใบเสนอราคา',
        'cravevaAi' => 'Craveva AI',
        'aiWorkspace' => 'พื้นที่ทำงาน AI',
        'aiWorkspaceSubmenu' => 'Workspace',
        'aiAssistantSubmenu' => 'Assistant',
        'adminIt' => 'ผู้ดูแลและ IT',
        'helpSupport' => 'ความช่วยเหลือ',
        'operations' => 'การดำเนินงาน',
        'saleOrders' => 'คำสั่งขาย',
        'salesHistory' => 'ประวัติการขาย',
        'lettersTemplates' => 'แม่แบบจดหมาย',
        'packages' => 'แพ็กเกจ',
        'meetings' => 'การประชุม',
        'discountRules' => 'กฎส่วนลด',
        'performanceDashboard' => 'แดชบอร์ดประสิทธิภาพ',
        'okrObjectives' => 'เป้าหมาย OKR',
        'auditReport' => 'รายงานตรวจสอบ',
        'fileStorage' => 'ที่เก็บไฟล์',
        'clientPurchase' => 'การซื้อของลูกค้า',
    ],
    'ko' => [
        'settingsMenuGroupCompany' => '회사',
        'settingsMenuItemCompanyGeneral' => '일반',
        'settingsMenuGroupPersonal' => '프로필',
        'settingsMenuGroupSales' => '영업',
        'settingsMenuGroupProcurement' => '운영',
        'settingsMenuGroupFinanceTax' => '재무',
        'settingsMenuGroupHumanResources' => '인사',
        'settingsMenuGroupProjectsSupport' => '프로젝트',
        'settingsMenuGroupSystem' => '시스템',
        'settingsMenuGroupAdminTechnical' => '관리',
        'financeSettings' => '송장 및 견적',
        'currencySettings' => '통화',
        'taxSettings' => '세금',
        'paymentGatewayCredential' => '결제 게이트웨이',
        'saleOrderSettings' => '판매 주문',
        'contractSettings' => '계약',
        'leadSettings' => '리드',
        'attendanceSettings' => '근태',
        'leaveSettings' => '휴가',
        'projectSettings' => '프로젝트',
        'taskSettings' => '작업',
        'timeLogSettings' => '타임시트',
        'ticketSettings' => '티켓',
        'messageSettings' => '메시지',
        'appSettings' => '앱',
        'notificationSettings' => '알림',
        'themeSettings' => '테마',
        'moduleSettings' => '모듈',
        'securitySettings' => '보안',
        'storageSettings' => '저장소',
        'languageSettings' => '언어',
        'socialLogin' => '소셜 로그인',
        'customLinkSetting' => '사용자 지정 링크',
        'gdprSettings' => 'GDPR',
        'googleCalendarSetting' => 'Google Calendar',
        'databaseBackupSetting' => 'DB 백업',
        'businessAddresses' => '주소',
        'signUpSetting' => '가입',
        'developerTools' => '개발자 도구',
        'codeMap' => 'Code Map',
        'workManagement' => '업무 관리',
        'procurement' => '구매',
        'salesFulfillment' => '영업',
        'inventoryWarehouse' => '재고',
        'productionHub' => '생산',
        'sales' => '고객',
        'humanResources' => '인사',
        'payrollSidebar' => '급여',
        'pricing' => '가격',
        'quotation' => '견적',
        'cravevaAi' => 'Craveva AI',
        'aiWorkspace' => 'AI 워크스페이스',
        'aiWorkspaceSubmenu' => '워크스페이스',
        'aiAssistantSubmenu' => '어시스턴트',
        'adminIt' => '관리 및 IT',
        'helpSupport' => '도움말',
        'operations' => '운영',
        'saleOrders' => '판매 주문',
        'salesHistory' => '판매 이력',
        'lettersTemplates' => '편지 템플릿',
        'packages' => '패키지',
        'meetings' => '회의',
        'discountRules' => '할인 규칙',
        'performanceDashboard' => '성과 대시보드',
        'okrObjectives' => 'OKR 목표',
        'auditReport' => '감사 보고서',
        'fileStorage' => '파일 저장소',
        'clientPurchase' => '고객 구매',
    ],
    'ja' => [
        'settingsMenuGroupCompany' => '会社',
        'settingsMenuItemCompanyGeneral' => '一般',
        'settingsMenuGroupPersonal' => 'プロフィール',
        'settingsMenuGroupSales' => '販売',
        'settingsMenuGroupProcurement' => '運用',
        'settingsMenuGroupFinanceTax' => '財務',
        'settingsMenuGroupHumanResources' => '人事',
        'settingsMenuGroupProjectsSupport' => 'プロジェクト',
        'settingsMenuGroupSystem' => 'システム',
        'settingsMenuGroupAdminTechnical' => '管理',
        'financeSettings' => '請求書と見積',
        'currencySettings' => '通貨',
        'taxSettings' => '税',
        'paymentGatewayCredential' => '決済ゲートウェイ',
        'saleOrderSettings' => '販売注文',
        'contractSettings' => '契約',
        'leadSettings' => 'リード',
        'attendanceSettings' => '勤怠',
        'leaveSettings' => '休暇',
        'projectSettings' => 'プロジェクト',
        'taskSettings' => 'タスク',
        'timeLogSettings' => 'タイムシート',
        'ticketSettings' => 'チケット',
        'messageSettings' => 'メッセージ',
        'appSettings' => 'アプリ',
        'notificationSettings' => '通知',
        'themeSettings' => 'テーマ',
        'moduleSettings' => 'モジュール',
        'securitySettings' => 'セキュリティ',
        'storageSettings' => 'ストレージ',
        'languageSettings' => '言語',
        'socialLogin' => 'ソーシャルログイン',
        'customLinkSetting' => 'カスタムリンク',
        'gdprSettings' => 'GDPR',
        'googleCalendarSetting' => 'Google Calendar',
        'databaseBackupSetting' => 'DBバックアップ',
        'businessAddresses' => '住所',
        'signUpSetting' => 'サインアップ',
        'developerTools' => '開発者ツール',
        'codeMap' => 'Code Map',
        'workManagement' => '業務管理',
        'procurement' => '購買',
        'salesFulfillment' => '販売',
        'inventoryWarehouse' => '在庫',
        'productionHub' => '生産',
        'sales' => '顧客',
        'humanResources' => '人事',
        'payrollSidebar' => '給与',
        'pricing' => '価格',
        'quotation' => '見積',
        'cravevaAi' => 'Craveva AI',
        'aiWorkspace' => 'AIワークスペース',
        'aiWorkspaceSubmenu' => 'ワークスペース',
        'aiAssistantSubmenu' => 'アシスタント',
        'adminIt' => '管理とIT',
        'helpSupport' => 'ヘルプ',
        'operations' => '運用',
        'saleOrders' => '販売注文',
        'salesHistory' => '販売履歴',
        'lettersTemplates' => 'レターテンプレート',
        'packages' => 'パッケージ',
        'meetings' => '会議',
        'discountRules' => '割引ルール',
        'performanceDashboard' => 'パフォーマンスダッシュボード',
        'okrObjectives' => 'OKR目標',
        'auditReport' => '監査レポート',
        'fileStorage' => 'ファイルストレージ',
        'clientPurchase' => 'クライアント購入',
    ],
    'hi' => [
        'settingsMenuGroupCompany' => 'कंपनी',
        'settingsMenuItemCompanyGeneral' => 'सामान्य',
        'settingsMenuGroupPersonal' => 'प्रोफ़ाइल',
        'settingsMenuGroupSales' => 'बिक्री',
        'settingsMenuGroupProcurement' => 'संचालन',
        'settingsMenuGroupFinanceTax' => 'वित्त',
        'settingsMenuGroupHumanResources' => 'मानव संसाधन',
        'settingsMenuGroupProjectsSupport' => 'परियोजनाएँ',
        'settingsMenuGroupSystem' => 'सिस्टम',
        'settingsMenuGroupAdminTechnical' => 'व्यवस्थापक',
        'financeSettings' => 'चालान और अनुमान',
        'currencySettings' => 'मुद्रा',
        'taxSettings' => 'कर',
        'paymentGatewayCredential' => 'भुगतान गेटवे',
        'workManagement' => 'कार्य प्रबंधन',
        'procurement' => 'खरीद',
        'salesFulfillment' => 'बिक्री',
        'inventoryWarehouse' => 'इन्वेंटरी',
        'productionHub' => 'उत्पादन',
        'sales' => 'ग्राहक',
        'humanResources' => 'मानव संसाधन',
        'payrollSidebar' => 'पेरोल',
        'pricing' => 'मूल्य निर्धारण',
        'quotation' => 'कोटेशन',
        'lettersTemplates' => 'पत्र टेम्पलेट',
        'packages' => 'पैकेज',
        'meetings' => 'बैठकें',
        'discountRules' => 'छूट नियम',
        'auditReport' => 'ऑडिट रिपोर्ट',
    ],
    'id' => [
        'settingsMenuGroupCompany' => 'Perusahaan',
        'settingsMenuItemCompanyGeneral' => 'Umum',
        'settingsMenuGroupPersonal' => 'Profil',
        'settingsMenuGroupSales' => 'Penjualan',
        'settingsMenuGroupProcurement' => 'Operasi',
        'settingsMenuGroupFinanceTax' => 'Keuangan',
        'settingsMenuGroupHumanResources' => 'Sumber Daya Manusia',
        'settingsMenuGroupProjectsSupport' => 'Proyek',
        'settingsMenuGroupSystem' => 'Sistem',
        'settingsMenuGroupAdminTechnical' => 'Admin',
        'financeSettings' => 'Faktur & Estimasi',
        'currencySettings' => 'Mata Uang',
        'taxSettings' => 'Pajak',
        'paymentGatewayCredential' => 'Gateway Pembayaran',
        'workManagement' => 'Manajemen Pekerjaan',
        'procurement' => 'Pembelian',
        'salesFulfillment' => 'Penjualan',
        'inventoryWarehouse' => 'Inventaris',
        'productionHub' => 'Produksi',
        'sales' => 'Pelanggan',
        'humanResources' => 'Sumber Daya Manusia',
        'payrollSidebar' => 'Penggajian',
        'pricing' => 'Harga',
        'quotation' => 'Penawaran',
        'lettersTemplates' => 'Template Surat',
        'packages' => 'Paket',
        'meetings' => 'Rapat',
        'discountRules' => 'Aturan Diskon',
        'auditReport' => 'Laporan Audit',
    ],
    'zh-TW' => [
        'settingsMenuGroupCompany' => '公司',
        'settingsMenuItemCompanyGeneral' => '基本資訊',
        'settingsMenuGroupPersonal' => '個人資料',
        'settingsMenuGroupSales' => '銷售',
        'settingsMenuGroupProcurement' => '營運',
        'settingsMenuGroupFinanceTax' => '財務',
        'settingsMenuGroupHumanResources' => '人力資源',
        'settingsMenuGroupProjectsSupport' => '專案',
        'settingsMenuGroupSystem' => '系統',
        'settingsMenuGroupAdminTechnical' => '管理',
        'financeSettings' => '發票與報價',
        'currencySettings' => '貨幣',
        'taxSettings' => '稅務',
        'paymentGatewayCredential' => '支付閘道',
        'workManagement' => '工作管理',
        'procurement' => '採購',
        'salesFulfillment' => '銷售',
        'inventoryWarehouse' => '庫存',
        'productionHub' => '生產',
        'sales' => '客戶',
        'humanResources' => '人力資源',
        'payrollSidebar' => '薪資',
        'pricing' => '定價',
        'quotation' => '報價',
    ],
];

/** @var array<string, array<string, array<string, string>>> */
$moduleOverlays = [
    'Purchase' => [
        'th' => [
            'menu.purchaseSettings' => 'การจัดซื้อ',
            'menu.goodsReceivedNote' => 'ใบรับสินค้า (GRN)',
            'menu.saleDeliveryOrder' => 'ใบส่งมอบการขาย',
            'menu.deliveryOrderSettings' => 'ตั้งค่าใบรับสินค้า',
        ],
        'ko' => [
            'menu.purchaseSettings' => '구매',
            'menu.goodsReceivedNote' => '입고증 (GRN)',
            'menu.saleDeliveryOrder' => '판매 출고 주문',
            'menu.deliveryOrderSettings' => '입고증 설정',
        ],
        'ja' => [
            'menu.purchaseSettings' => '購買',
            'menu.goodsReceivedNote' => '入庫伝票 (GRN)',
            'menu.saleDeliveryOrder' => '販売出庫伝票',
            'menu.deliveryOrderSettings' => '入庫伝票設定',
        ],
        'hi' => [
            'menu.purchaseSettings' => 'खरीद',
            'menu.goodsReceivedNote' => 'माल प्राप्ति नोट (GRN)',
            'menu.saleDeliveryOrder' => 'बिक्री डिलीवरी ऑर्डर',
        ],
        'id' => [
            'menu.purchaseSettings' => 'Pembelian',
            'menu.goodsReceivedNote' => 'Nota Penerimaan Barang (GRN)',
            'menu.saleDeliveryOrder' => 'Order Pengiriman Penjualan',
        ],
        'zh-TW' => [
            'menu.purchaseSettings' => '採購',
            'menu.goodsReceivedNote' => '收貨單',
            'menu.saleDeliveryOrder' => '銷售出庫單',
        ],
    ],
    'Warehouse' => [
        'th' => [
            'warehouseFlowSettingsMenu' => 'คลังสินค้า',
            'warehouses' => 'คลังสินค้า',
            'adjustStock' => 'ภาพรวมสต็อก',
            'stockMovements' => 'การเคลื่อนไหวสต็อก',
        ],
        'ko' => [
            'warehouseFlowSettingsMenu' => '창고',
            'warehouses' => '창고',
            'adjustStock' => '재고 현황',
            'stockMovements' => '재고 이동',
        ],
        'ja' => [
            'warehouseFlowSettingsMenu' => '倉庫',
            'warehouses' => '倉庫',
            'adjustStock' => '在庫概要',
            'stockMovements' => '在庫移動',
        ],
        'hi' => [
            'warehouseFlowSettingsMenu' => 'गोदाम',
            'warehouses' => 'गोदाम',
            'adjustStock' => 'स्टॉक अवलोकन',
            'stockMovements' => 'स्टॉक गतिविधि',
        ],
        'id' => [
            'warehouseFlowSettingsMenu' => 'Gudang',
            'warehouses' => 'Gudang',
            'adjustStock' => 'Ringkasan Stok',
            'stockMovements' => 'Pergerakan Stok',
        ],
        'zh-TW' => [
            'warehouseFlowSettingsMenu' => '倉庫',
            'warehouses' => '倉庫',
            'adjustStock' => '庫存概覽',
            'stockMovements' => '庫存流水',
        ],
    ],
    'Production' => [
        'th' => [
            'productionSettingsMenu' => 'การผลิต',
            'bomComponentLines' => 'รายการส่วนประกอบ',
            'menuProductionOrders' => 'คำสั่งผลิต',
            'menuBillOfMaterials' => 'รายการวัสดุ (BOM)',
        ],
        'ko' => [
            'productionSettingsMenu' => '생산',
            'bomComponentLines' => '구성품 라인',
            'menuProductionOrders' => '생산 주문',
            'menuBillOfMaterials' => '자재명세서 (BOM)',
        ],
        'ja' => [
            'productionSettingsMenu' => '生産',
            'bomComponentLines' => 'コンポーネント行',
            'menuProductionOrders' => '生産指示',
            'menuBillOfMaterials' => '部品表 (BOM)',
        ],
        'hi' => [
            'productionSettingsMenu' => 'उत्पादन',
            'bomComponentLines' => 'Component Lines',
            'menuProductionOrders' => 'उत्पादन आदेश',
            'menuBillOfMaterials' => 'सामग्री सूची (BOM)',
        ],
        'id' => [
            'productionSettingsMenu' => 'Produksi',
            'bomComponentLines' => 'Baris Komponen',
            'menuProductionOrders' => 'Order Produksi',
            'menuBillOfMaterials' => 'Daftar Material (BOM)',
        ],
        'zh-TW' => [
            'productionSettingsMenu' => '生產',
            'bomComponentLines' => '組件行',
            'menuProductionOrders' => '生產訂單',
            'menuBillOfMaterials' => '物料清單',
        ],
    ],
    'Asset' => [
        'th' => ['menu.assetSettings' => 'สินทรัพย์'],
        'ko' => ['menu.assetSettings' => '자산'],
        'ja' => ['menu.assetSettings' => '資産'],
        'hi' => ['menu.assetSettings' => 'संपत्ति'],
        'id' => ['menu.assetSettings' => 'Aset'],
        'zh-TW' => ['menu.assetSettings' => '資產'],
    ],
    'Payroll' => [
        'th' => [
            'menu.payrollSettings' => 'เงินเดือน',
            'menu.overtimeSettings' => 'ล่วงเวลา',
        ],
        'ko' => [
            'menu.payrollSettings' => '급여',
            'menu.overtimeSettings' => '초과 근무',
        ],
        'ja' => [
            'menu.payrollSettings' => '給与',
            'menu.overtimeSettings' => '残業',
        ],
    ],
    'Recruit' => [
        'th' => ['menu.recruitSetting' => 'การสรรหา'],
        'ko' => ['menu.recruitSetting' => '채용'],
        'ja' => ['menu.recruitSetting' => '採用'],
    ],
    'Performance' => [
        'th' => ['performanceSettings' => 'ประสิทธิภาพ'],
        'ko' => ['performanceSettings' => '성과'],
        'ja' => ['performanceSettings' => 'パフォーマンス'],
    ],
    'EInvoice' => [
        'th' => ['menu.einvoiceSettings' => 'ใบแจ้งหนี้อิเล็กทรอนิกส์'],
        'ko' => ['menu.einvoiceSettings' => '전자세금계산서'],
        'ja' => ['menu.einvoiceSettings' => '電子請求書'],
    ],
    'Sms' => [
        'th' => ['smsSetting' => 'SMS'],
        'ko' => ['smsSetting' => 'SMS'],
        'ja' => ['smsSetting' => 'SMS'],
    ],
    'Zoom' => [
        'th' => ['menu.zoomSetting' => 'Zoom'],
        'ko' => ['menu.zoomSetting' => 'Zoom'],
        'ja' => ['menu.zoomSetting' => 'Zoom'],
    ],
    'Pricing' => [
        'th' => [
            'menu.pricing' => 'ราคา',
            'menu.tiers' => 'กฎระดับราคา',
            'menu.clientTiers' => 'กำหนดระดับลูกค้า',
            'menu.volumeDiscounts' => 'กฎส่วนลดตามปริมาณ',
            'menu.contractPricing' => 'ราคาตามสัญญา',
            'menu.clientPricing' => 'ราคาเฉพาะลูกค้า',
            'menu.companyPricing' => 'ราคาสัญญาลูกค้า',
            'assignPricingTier' => 'กำหนดระดับราคา',
            'addPricingTier' => 'เพิ่มระดับราคา',
            'volumeDiscountRules' => 'กฎส่วนลดตามปริมาณ',
            'addContractPricing' => 'เพิ่มราคาตามสัญญา',
        ],
        'ko' => [
            'menu.pricing' => '가격',
            'menu.tiers' => '가격 등급 규칙',
            'menu.clientTiers' => '고객 등급 할당',
            'menu.volumeDiscounts' => '수량 할인 규칙',
            'menu.contractPricing' => '계약 가격',
            'menu.clientPricing' => '고객 가격 재정의',
            'menu.companyPricing' => '고객 계약 가격',
            'assignPricingTier' => '가격 등급 할당',
            'addPricingTier' => '가격 등급 추가',
            'volumeDiscountRules' => '수량 할인 규칙',
            'addContractPricing' => '계약 가격 추가',
        ],
        'ja' => [
            'menu.pricing' => '価格',
            'menu.tiers' => '価格ティア規則',
            'menu.clientTiers' => '顧客ティア割当',
            'menu.volumeDiscounts' => '数量割引規則',
            'menu.contractPricing' => '契約価格',
            'menu.clientPricing' => '顧客価格上書き',
            'menu.companyPricing' => '顧客契約価格',
            'assignPricingTier' => '価格ティアを割当',
            'addPricingTier' => '価格ティアを追加',
            'volumeDiscountRules' => '数量割引規則',
            'addContractPricing' => '契約価格を追加',
        ],
        'hi' => [
            'menu.pricing' => 'मूल्य निर्धारण',
            'menu.tiers' => 'मूल्य स्तर नियम',
            'menu.clientTiers' => 'ग्राहक स्तर असाइनमेंट',
            'menu.volumeDiscounts' => 'मात्रा छूट नियम',
            'menu.contractPricing' => 'अनुबंध मूल्य',
            'menu.clientPricing' => 'ग्राहक मूल्य ओवरराइड',
            'menu.companyPricing' => 'ग्राहक अनुबंध मूल्य',
        ],
        'id' => [
            'menu.pricing' => 'Harga',
            'menu.tiers' => 'Aturan Tingkat Harga',
            'menu.clientTiers' => 'Penetapan Tingkat Klien',
            'menu.volumeDiscounts' => 'Aturan Diskon Volume',
            'menu.contractPricing' => 'Harga Kontrak',
            'menu.clientPricing' => 'Override Harga Klien',
            'menu.companyPricing' => 'Harga Kontrak Klien',
        ],
        'zh-TW' => [
            'menu.tiers' => '定價層級規則',
            'menu.clientTiers' => '客戶層級分配',
            'menu.volumeDiscounts' => '批量折扣規則',
            'menu.contractPricing' => '合約定價',
            'menu.companyPricing' => '客戶合約定價',
        ],
    ],
    'Letter' => [
        'th' => [
            'menu.letter' => 'จดหมาย',
            'menu.template' => 'แม่แบบ',
            'menu.generate' => 'สร้าง',
            'addTemplate' => 'เพิ่มแม่แบบ',
            'editTemplate' => 'แก้ไขแม่แบบ',
            'addLetter' => 'เพิ่มจดหมาย',
        ],
        'ko' => [
            'menu.letter' => '편지',
            'menu.template' => '템플릿',
            'menu.generate' => '생성',
            'addTemplate' => '템플릿 추가',
            'editTemplate' => '템플릿 수정',
            'addLetter' => '편지 추가',
        ],
        'ja' => [
            'menu.letter' => 'レター',
            'menu.template' => 'テンプレート',
            'menu.generate' => '生成',
            'addTemplate' => 'テンプレート追加',
            'editTemplate' => 'テンプレート編集',
            'addLetter' => 'レター追加',
        ],
        'hi' => [
            'menu.letter' => 'पत्र',
            'menu.template' => 'टेम्पलेट',
            'menu.generate' => 'जनरेट',
        ],
        'id' => [
            'menu.letter' => 'Surat',
            'menu.template' => 'Template',
            'menu.generate' => 'Buat',
        ],
    ],
];

/** @var array<string, array<string, string>> nested paths in app/{locale}/app.php (top-level groups) */
$appRootOverlays = [
    'th' => [
        'quotation_ui.menu' => 'ใบเสนอราคา',
        'quotation_ui.page_title' => 'ใบเสนอราคา',
        'quotation_ui.plural_list' => 'ใบเสนอราคา',
        'quotation_ui.singular' => 'ใบเสนอราคา',
        'quotation_ui.create' => 'สร้างใบเสนอราคา',
        'quotation_ui.column_number' => 'เลขที่ใบเสนอราคา',
        'quotation_ui.cancel_action' => 'ยกเลิกใบเสนอราคา',
        'quotation_ui.estimate_template' => 'แม่แบบใบเสนอราคา',
    ],
    'ko' => [
        'quotation_ui.menu' => '견적',
        'quotation_ui.page_title' => '견적',
        'quotation_ui.plural_list' => '견적',
        'quotation_ui.singular' => '견적',
        'quotation_ui.create' => '견적 작성',
        'quotation_ui.column_number' => '견적 번호',
        'quotation_ui.cancel_action' => '견적 취소',
        'quotation_ui.estimate_template' => '견적 템플릿',
    ],
    'ja' => [
        'quotation_ui.menu' => '見積',
        'quotation_ui.page_title' => '見積',
        'quotation_ui.plural_list' => '見積',
        'quotation_ui.singular' => '見積',
        'quotation_ui.create' => '見積作成',
        'quotation_ui.column_number' => '見積番号',
        'quotation_ui.cancel_action' => '見積キャンセル',
        'quotation_ui.estimate_template' => '見積テンプレート',
    ],
    'hi' => [
        'quotation_ui.menu' => 'कोटेशन',
        'quotation_ui.page_title' => 'कोटेशन',
        'quotation_ui.plural_list' => 'कोटेशन',
        'quotation_ui.singular' => 'कोटेशन',
        'quotation_ui.create' => 'कोटेशन बनाएं',
        'quotation_ui.column_number' => 'कोटेशन #',
        'quotation_ui.cancel_action' => 'कोटेशन रद्द करें',
        'quotation_ui.estimate_template' => 'कोटेशन टेम्पलेट',
    ],
    'id' => [
        'quotation_ui.menu' => 'Penawaran',
        'quotation_ui.page_title' => 'Penawaran',
        'quotation_ui.plural_list' => 'Penawaran',
        'quotation_ui.singular' => 'Penawaran',
        'quotation_ui.create' => 'Buat Penawaran',
        'quotation_ui.column_number' => 'No. Penawaran',
        'quotation_ui.cancel_action' => 'Batalkan Penawaran',
        'quotation_ui.estimate_template' => 'Template Penawaran',
    ],
    'zh-TW' => [
        'quotation_ui.menu' => '報價',
        'quotation_ui.page_title' => '報價',
        'quotation_ui.plural_list' => '報價',
        'quotation_ui.singular' => '報價',
        'quotation_ui.create' => '建立報價',
        'quotation_ui.column_number' => '報價編號',
        'quotation_ui.cancel_action' => '取消報價',
        'quotation_ui.estimate_template' => '報價範本',
    ],
];

/** @var array<string, array<string, string>> nested paths in app/{locale}/modules.php */
$appModulesOverlays = [
    'th' => [
        'estimates.bomLinesHeading' => 'รายการ BOM สูตร (ต่อหน่วยสินค้าสำเร็จ)',
        'estimates.bomLinesHelp' => 'วัตถุดิบและบรรจุภัณฑ์สำหรับหนึ่งหน่วยขาย หน่วยวัดตามแคตตalogสินค้า แยกจากรายการสินค้าด้านล่าง',
        'estimates.bomLinesEmpty' => 'ยังไม่มีรายการ BOM ในใบเสนอราคานี้',
        'estimates.bomMaterial' => 'วัตถุดิบ',
        'estimates.bomSelectProduct' => 'เลือกสินค้า (ไม่บังคับ)',
        'estimates.bomMaterialNamePlaceholder' => 'ชื่อวัตถุดิบ (ถ้าไม่เลือกจากแคตตalog)',
        'estimates.recipeHeaderSection' => 'สูตรและสินค้า (OEM)',
        'estimates.recipeHeaderSectionHelp' => 'MOQ บรรจุภัณฑ์ และ SKU OEM สำหรับการผลิต',
        'estimates.recipeMoq' => 'MOQ (จำนวนสั่งขั้นต่ำ)',
        'estimates.recipeOemSku' => 'SKU / รหัส OEM',
        'estimates.copyProductionBomHeading' => 'คัดลอกจาก BOM การผลิต',
        'estimates.copyProductionBomHelp' => 'โหลดรายการวัตถุดิบจาก BOM หลัก (ใช้ต้นทุนจาก Purchase → Products หากมี)',
        'estimates.copyProductionBomSelect' => 'เลือก BOM การผลิต',
        'estimates.copyProductionBomButton' => 'โหลดรายการ BOM',
    ],
    'ko' => [
        'estimates.bomLinesHeading' => '레시피 BOM 라인 (완제품 1단위당)',
        'estimates.bomLinesHelp' => '판매 가능 1단위를 만드는 원자재 및 포장. 단위는 제품 카탈로그 기준. 아래 상품 라인과 별도.',
        'estimates.bomLinesEmpty' => '이 견적에 BOM 라인이 없습니다.',
        'estimates.bomMaterial' => '자재',
        'estimates.bomSelectProduct' => '제품 선택 (선택)',
        'estimates.bomMaterialNamePlaceholder' => '자재명 (카탈로그 미선택 시)',
        'estimates.recipeHeaderSection' => '레시피 및 제품 (OEM)',
        'estimates.recipeHeaderSectionHelp' => 'MOQ, 포장, OEM SKU — 제조용',
        'estimates.recipeMoq' => 'MOQ (최소 주문 수량)',
        'estimates.recipeOemSku' => 'OEM SKU / 코드',
        'estimates.copyProductionBomHeading' => '생산 BOM에서 복사',
        'estimates.copyProductionBomHelp' => '마스터 BOM에서 자재 라인 로드 (Purchase → Products 단가 사용)',
        'estimates.copyProductionBomSelect' => '생산 BOM 선택',
        'estimates.copyProductionBomButton' => 'BOM 라인 로드',
    ],
    'ja' => [
        'estimates.bomLinesHeading' => 'レシピBOM行（完成品1単位あたり）',
        'estimates.bomLinesHelp' => '販売1単位の原材料と包装。単位は製品カタログに準拠。下の商品行とは別。',
        'estimates.bomLinesEmpty' => 'この見積にBOM行はありません。',
        'estimates.bomMaterial' => '材料',
        'estimates.bomSelectProduct' => '製品を選択（任意）',
        'estimates.bomMaterialNamePlaceholder' => '材料名（カタログ未選択時）',
        'estimates.recipeHeaderSection' => 'レシピと製品（OEM）',
        'estimates.recipeHeaderSectionHelp' => 'MOQ、包装、OEM SKU — 製造用',
        'estimates.recipeMoq' => 'MOQ（最小発注数量）',
        'estimates.recipeOemSku' => 'OEM SKU / コード',
        'estimates.copyProductionBomHeading' => '生産BOMからコピー',
        'estimates.copyProductionBomHelp' => 'マスタBOMから材料行を読み込み（Purchase → Products の単価を使用）',
        'estimates.copyProductionBomSelect' => '生産BOMを選択',
        'estimates.copyProductionBomButton' => 'BOM行を読み込む',
    ],
];

/** @param array<string, mixed> $array */
function setNestedValue(array &$array, string $path, mixed $value): void
{
    $parts = explode('.', $path);
    $ref = &$array;

    foreach ($parts as $index => $part) {
        if ($index === count($parts) - 1) {
            $ref[$part] = $value;

            return;
        }

        if (! isset($ref[$part]) || ! is_array($ref[$part])) {
            $ref[$part] = [];
        }

        $ref = &$ref[$part];
    }
}

/** @param array<string, mixed> $array */
function writeLangFile(string $path, array $array): void
{
    $exported = VarExporter::export($array);
    file_put_contents($path, "<?php\n\nreturn {$exported};\n");
}

$base = dirname(__DIR__) . '/Modules/LanguagePack/Languages';
$updated = 0;

/** @var list<string> */
$modulesNeedingLocaleBootstrap = ['Production', 'Warehouse', 'Pricing', 'Letter'];

/** @var list<string> */
$bootstrapLocales = ['th', 'ko', 'ja', 'hi', 'id', 'zh-TW'];

foreach ($modulesNeedingLocaleBootstrap as $module) {
    $enFile = "{$base}/modules/{$module}/en/app.php";

    if (! file_exists($enFile)) {
        continue;
    }

    foreach ($bootstrapLocales as $locale) {
        $targetDir = "{$base}/modules/{$module}/{$locale}";
        $targetFile = "{$targetDir}/app.php";

        if (file_exists($targetFile)) {
            continue;
        }

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        copy($enFile, $targetFile);
        echo "Bootstrapped modules/{$module}/{$locale}/app.php from en\n";
    }
}

foreach ($appMenuOverlays as $locale => $overlays) {
    $file = "{$base}/app/{$locale}/app.php";

    if (! file_exists($file)) {
        continue;
    }

    $data = include $file;

    if (! is_array($data) || ! isset($data['menu']) || ! is_array($data['menu'])) {
        continue;
    }

    $changed = false;

    foreach ($overlays as $key => $value) {
        if (! array_key_exists($key, $data['menu'])) {
            continue;
        }

        if ($data['menu'][$key] === $value) {
            continue;
        }

        $data['menu'][$key] = $value;
        $changed = true;
        $updated++;
    }

    if ($changed) {
        writeLangFile($file, $data);
        echo "Updated app/{$locale}/app.php\n";
    }
}

foreach ($moduleOverlays as $module => $localeMaps) {
    foreach ($localeMaps as $locale => $overlays) {
        $file = "{$base}/modules/{$module}/{$locale}/app.php";

        if (! file_exists($file)) {
            continue;
        }

        $data = include $file;

        if (! is_array($data)) {
            continue;
        }

        $changed = false;

        foreach ($overlays as $path => $value) {
            $parts = explode('.', $path);
            $ref = $data;

            foreach ($parts as $part) {
                if (! is_array($ref) || ! array_key_exists($part, $ref)) {
                    $ref = null;
                    break;
                }
                $ref = $ref[$part];
            }

            if ($ref === null || $ref === $value) {
                continue;
            }

            setNestedValue($data, $path, $value);
            $changed = true;
            $updated++;
        }

        if ($changed) {
            writeLangFile($file, $data);
            echo "Updated modules/{$module}/{$locale}/app.php\n";
        }
    }
}

foreach ($appRootOverlays as $locale => $overlays) {
    $file = "{$base}/app/{$locale}/app.php";

    if (! file_exists($file)) {
        continue;
    }

    $data = include $file;

    if (! is_array($data)) {
        continue;
    }

    $changed = false;

    foreach ($overlays as $path => $value) {
        $parts = explode('.', $path);
        $ref = $data;

        foreach ($parts as $part) {
            if (! is_array($ref) || ! array_key_exists($part, $ref)) {
                $ref = null;
                break;
            }
            $ref = $ref[$part];
        }

        if ($ref === null || $ref === $value) {
            continue;
        }

        setNestedValue($data, $path, $value);
        $changed = true;
        $updated++;
    }

    if ($changed) {
        writeLangFile($file, $data);
        echo "Updated app/{$locale}/app.php (root overlays)\n";
    }
}

foreach ($appModulesOverlays as $locale => $overlays) {
    $file = "{$base}/app/{$locale}/modules.php";

    if (! file_exists($file)) {
        continue;
    }

    $data = include $file;

    if (! is_array($data)) {
        continue;
    }

    $changed = false;

    foreach ($overlays as $path => $value) {
        $parts = explode('.', $path);
        $ref = $data;

        foreach ($parts as $part) {
            if (! is_array($ref) || ! array_key_exists($part, $ref)) {
                $ref = null;
                break;
            }
            $ref = $ref[$part];
        }

        if ($ref === null) {
            continue;
        }

        if ($ref === $value) {
            continue;
        }

        setNestedValue($data, $path, $value);
        $changed = true;
        $updated++;
    }

    if ($changed) {
        writeLangFile($file, $data);
        echo "Updated app/{$locale}/modules.php\n";
    }
}

echo "Done. {$updated} string(s) updated.\n";
