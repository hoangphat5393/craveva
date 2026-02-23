$ErrorActionPreference = "Stop"
$StagingHost = "craveva-staging"
# We need to escape $ in double quotes for PowerShell, and also for the remote shell if needed.
# For PowerShell: `$u` becomes `\$u`? No, `$u` is variable. `'$u'` is literal.
# But we are passing a string to ssh.
# Safer to use single quotes for the php command, but we need to put it inside the ssh command.

$PhpCommand = '$u = \App\Models\User::find(1); auth()->login($u); dump("User 1 Role:", user_roles()); dump("User 1 Modules:", user_modules());'
$RemoteCommand = "cd /var/www/craveva-staging/current/craveva && sudo -u www-data php artisan tinker --execute='$PhpCommand'"

ssh -t $StagingHost $RemoteCommand
