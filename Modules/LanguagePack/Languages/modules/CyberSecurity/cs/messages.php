<?php 
return [
  'maxRetriesToolTip' => 'Maximální povolený počet neúspěšných pokusů před uzamčením',
  'extendLockoutToolTip' => 'Prodlužte dobu uzamčení po prvním uzamčení',
  'emailNotificationToolTip' => 'Po (počet výluk) výlukách. 0 pro deaktivaci e-mailových upozornění',
  'ipToolTip' => 'Zadejte jednu IP na řádek',
  'loginExpiry' => 'Platnost vašeho účtu vypršela. Kontaktujte prosím správce.',
  'sessionDriverRequired' => 'Nastavte ovladač relace na databázi. Jinak tato funkce nebude fungovat. Můžete přejít na databázi z :setting.',
  'maxRetries' => 'Dosáhli jste maximálního počtu pokusů. Zkuste to znovu po :time.',
  'ipRequired' => 'Pokud chcete povolit kontrolu IP, zadejte IP adresu.',
  'blacklistEmail' => 'Váš email je na černé listině. Kontaktujte prosím správce.',
  'blacklistIp' => 'Vaše IP je na černé listině. Kontaktujte prosím správce.',
  'infoBox' => [
    'lockoutForMinutes' => 'Uživatel se zablokuje po :maxRetries neúspěšných pokusech po dobu :lockoutTime minut.',
    'extendedLockout' => 'Doba uzamčení se prodlouží na :extendedLockoutTime hodin po prvním uzamčení.',
    'maxLockoutsAvailable' => 'Maximální povolené uzamčení je :maxLockouts.',
    'resetRetries' => 'Opakované pokusy budou resetovány po :resetRetries hodině.',
    'alertAfterLockouts' => 'E-mailové upozornění bude odesláno po :alertAfterLockouts uzamčení na :email.',
    'sendEmailDifferentIp' => 'Odeslat e-mailové upozornění, pokud se přihlašujete z jiné IP :ip.',
    'notSendEmailDifferentIp' => 'Neposílat upozornění e-mailem, pokud se přihlašujete z jiné IP adresy.',
  ],
];