<?php

return [
    'core' => [
        'subdomain' => 'Subdomain',
        'domain' => 'Domain',
        'customDomain' => 'Custom Domain',
        'domainType' => 'Domain Type',
        'continue' => 'Continue',
        'backToSignin' => 'Go Back To Sign In Page',
        'alreadyKnow' => 'Oh, I Just Remembered The URL!',
        'workspaceTitle' => 'Sign In To Your Company URL',
        'forgotCompanyTitle' => 'Find Your Company Login URL',
        'signInTitle' => 'Don’t Know Your Company\'s Login URL?',
        'signInTitleDescription' => 'Welcome to the login page! Please enter your credentials to access your account and start using the platform\'s features. If you don\'t have an account yet, you can easily sign up for one.',
        'bannedSubdomains' => 'Enter The List Of Subdomains You Want To Restrict From Getting Registered',
        'sendDomainNotification' => 'Send Domain Notification',
        'enterYourSubdomain' => 'Enter Your Subdomain To Get Started',
        'dontHaveAccount' => 'Don\'t have account? <b>Click to Sign up</b>',
        'companyNotFound' => 'COMPANY DOES NOT EXISTS FOR THAT URL',
    ],
    'messages' => [
        'forgetMailSuccess' => 'Please check your email. We have sent an email with your login url',
        'forgetMailFail' => 'Your provided email is not found. Please provide a valid email address.',
        'forgotPageMessage' => 'We will send a confirmation email to you in order to verify your email address and determine the presence of a pre-existing company URL.',
        'findCompanyUrl' => 'Find Your Company\'s Login URL',
        'deleteSubdomain' => 'Are You Sure You Want To Delete',
        'notAllowedToUseThisSubdomain' => 'Sorry, You are not allowed to use this subdomain',
        'noCompanyLined' => 'No Company Linked With This Email',
        'notifyAllAdmins' => 'This will notify all admins their domain urls',
    ],
    'email' => [
        'subject' => 'Important Update: New Login URL for Your Company',
        'line2' => 'Welcome ',
        'line3' => 'We would like to inform you that the login URL for your company has been changed. Please take note of the new login URL and use it going forward.',
        'line4' => 'We apologize for any inconvenience this may have caused, but rest assured that the new URL has been implemented for enhanced security and easier access to your account.',
        'line5' => 'If you have any questions or concerns, please don\'t hesitate to reach out to our support team. We are always here to help. ',
        'noteLoginUrlChanged' => 'Login URL: ',
        'noteLoginUrl' => 'Please note your Login URL ',
        'thankYou' => 'Thank You For Your Continued Business. ',
    ],
    'emailSuperAdmin' => [
        'subject' => 'New Superadmin Login URL- Subdomain Module Activation',
        'line3' => 'This is to inform you that the Superadmin Login URL has been updated following the activation of the <strong>Subdomain Module</strong>. The new URL is now ',
        'noteLoginUrlChanged' => 'Superadmin Login URL: ',
        'noteLoginUrl' => 'Please note your Superadmin Login URL ',
    ],
    'match' => [
        'title' => 'You Can Even Follow Below Pattern',
        'pattern' => '<p>1. <b>test</b> (exact match)</p>'."\r\n"
            .'                            <p>2. <b>%test%</b> (match anywhere in the string)</p>'."\r\n"
            .'                            <p>3. <b>%test</b> (match anywhere but must end with \'test\')</p>',
    ],
    'companyNotFound' => 'Company Not Found',
];
