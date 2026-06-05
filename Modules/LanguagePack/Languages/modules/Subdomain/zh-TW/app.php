<?php

return [
    'core' => [
        'subdomain' => '子域名',
        'domain' => '域名',
        'customDomain' => '自定義域名',
        'domainType' => '域名類型',
        'continue' => '繼續',
        'backToSignin' => '返回登錄頁',
        'alreadyKnow' => '哦，我剛記起網址了！',
        'workspaceTitle' => '登錄到您的公司網址',
        'forgotCompanyTitle' => '查找您的公司登錄網址',
        'signInTitle' => '不知道您公司的登錄網址？',
        'signInTitleDescription' => '歡迎來到登錄頁面！請輸入您的憑據以訪問您的帳戶並開始使用平台功能。如果您還沒有帳戶，可以輕鬆註冊一個。',
        'bannedSubdomains' => '輸入您想要限制註冊的子域名列表',
        'sendDomainNotification' => '發送域名通知',
        'enterYourSubdomain' => '輸入您的子域名以開始',
        'dontHaveAccount' => '沒有帳戶？<b>點擊註冊</b>',
        'companyNotFound' => '該網址不存在公司',
    ],
    'messages' => [
        'forgetMailSuccess' => '請檢查您的電子郵件。我們已發送一封包含您登錄網址的電子郵件',
        'forgetMailFail' => '未找到您提供的電子郵件。請提供有效的電子郵件地址。',
        'forgotPageMessage' => '我們將向您發送確認電子郵件，以驗證您的電子郵件地址並確定是否存在預先存在的公司網址。',
        'findCompanyUrl' => '查找您公司的登錄網址',
        'deleteSubdomain' => '您確定要刪除嗎',
        'notAllowedToUseThisSubdomain' => '抱歉，您不允許使用此子域名',
        'noCompanyLined' => '此電子郵件未鏈接任何公司',
        'notifyAllAdmins' => '這將通知所有管理員他們的域名網址',
    ],
    'email' => [
        'subject' => '重要更新：您公司的新登錄網址',
        'line2' => '歡迎 ',
        'line3' => '我們想通知您，您公司的登錄網址已更改。請記下新的登錄網址並以後使用它。',
        'line4' => '對於由此造成的任何不便，我們深表歉意，但請放心，新網址已實施以增強安全性並更輕鬆地訪問您的帳戶。',
        'line5' => '如果您有任何問題或疑慮，請隨時聯繫我們的支持團隊。我們隨時為您提供幫助。 ',
        'noteLoginUrlChanged' => '登錄網址：',
        'noteLoginUrl' => '請注意您的登錄網址 ',
        'thankYou' => '感謝您一直以來的支持。 ',
    ],
    'emailSuperAdmin' => [
        'subject' => '新超級管理員登錄網址 - 子域名模塊激活',
        'line3' => '特此通知，在激活<strong>子域名模塊</strong>後，超級管理員登錄網址已更新。新網址現在是 ',
        'noteLoginUrlChanged' => '超級管理員登錄網址：',
        'noteLoginUrl' => '請注意您的超級管理員登錄網址 ',
    ],
    'match' => [
        'title' => '您甚至可以遵循以下模式',
        'pattern' => '<p>1. <b>test</b> (完全匹配)</p>'."\r\n"
            .'                            <p>2. <b>%test%</b> (匹配字符串中的任何位置)</p>'."\r\n"
            .'                            <p>3. <b>%test</b> (匹配任何位置但必須以 \'test\' 結尾)</p>',
    ],
    'companyNotFound' => 'Company Not Found',
];
