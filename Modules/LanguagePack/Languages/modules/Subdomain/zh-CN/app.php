<?php

return [
    'core' => [
        'subdomain' => '子域名',
        'domain' => '域名',
        'customDomain' => '自定义域名',
        'domainType' => '域名类型',
        'continue' => '继续',
        'backToSignin' => '返回登录页',
        'alreadyKnow' => '哦，我刚记起网址了！',
        'workspaceTitle' => '登录到您的公司网址',
        'forgotCompanyTitle' => '查找您的公司登录网址',
        'signInTitle' => '不知道您公司的登录网址？',
        'signInTitleDescription' => '欢迎来到登录页面！请输入您的凭据以访问您的帐户并开始使用平台功能。如果您还没有帐户，可以轻松注册一个。',
        'bannedSubdomains' => '输入您想要限制注册的子域名列表',
        'sendDomainNotification' => '发送域名通知',
        'enterYourSubdomain' => '输入您的子域名以开始',
        'dontHaveAccount' => '没有帐户？<b>点击注册</b>',
        'companyNotFound' => '该网址不存在公司',
    ],
    'messages' => [
        'forgetMailSuccess' => '请检查您的电子邮件。我们已发送一封包含您登录网址的电子邮件',
        'forgetMailFail' => '未找到您提供的电子邮件。请提供有效的电子邮件地址。',
        'forgotPageMessage' => '我们将向您发送确认电子邮件，以验证您的电子邮件地址并确定是否存在预先存在的公司网址。',
        'findCompanyUrl' => '查找您公司的登录网址',
        'deleteSubdomain' => '您确定要删除吗',
        'notAllowedToUseThisSubdomain' => '抱歉，您不允许使用此子域名',
        'noCompanyLined' => '此电子邮件未链接任何公司',
        'notifyAllAdmins' => '这将通知所有管理员他们的域名网址',
    ],
    'email' => [
        'subject' => '重要更新：您公司的新登录网址',
        'line2' => '欢迎 ',
        'line3' => '我们想通知您，您公司的登录网址已更改。请记下新的登录网址并以后使用它。',
        'line4' => '对于由此造成的任何不便，我们深表歉意，但请放心，新网址已实施以增强安全性并更轻松地访问您的帐户。',
        'line5' => '如果您有任何问题或疑虑，请随时联系我们的支持团队。我们随时为您提供帮助。 ',
        'noteLoginUrlChanged' => '登录网址：',
        'noteLoginUrl' => '请注意您的登录网址 ',
        'thankYou' => '感谢您一直以来的支持。 ',
    ],
    'emailSuperAdmin' => [
        'subject' => '新超级管理员登录网址 - 子域名模块激活',
        'line3' => '特此通知，在激活<strong>子域名模块</strong>后，超级管理员登录网址已更新。新网址现在是 ',
        'noteLoginUrlChanged' => '超级管理员登录网址：',
        'noteLoginUrl' => '请注意您的超级管理员登录网址 ',
    ],
    'match' => [
        'title' => '您甚至可以遵循以下模式',
        'pattern' => '<p>1. <b>test</b> (完全匹配)</p>
                            <p>2. <b>%test%</b> (匹配字符串中的任何位置)</p>
                            <p>3. <b>%test</b> (匹配任何位置但必须以 \'test\' 结尾)</p>'
    ]
];
