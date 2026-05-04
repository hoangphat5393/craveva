# Routes

- Generated at: 2026-05-04T05:35:06+00:00

- Modules/Biometric/Routes/api.php (lines=?, methods=?)
- Modules/Biometric/Routes/web.php (lines=?, methods=?)

## Route samples

### Modules/Biometric/Routes/api.php

- get /iclock/cdata
- post /iclock/cdata
- post /iclock/devicecmd
- get /iclock/getrequest
- get /iclock/ping
- get /iclock/test

### Modules/Biometric/Routes/web.php

- get biometric-commands
- resource biometric-devices
- post biometric-devices/change-status
- post biometric-devices/sync-employees
- resource biometric-employees
- get biometric-employees/fetch-all
- get biometric-employees/fetch-biometric-data/{id?}
- get biometric-employees/get-employees-to-sync
- get biometric-employees/get-info/{id}
- delete biometric-employees/{id}/remove-from-device
- get get-biometric-attendance
