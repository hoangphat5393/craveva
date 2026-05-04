# Routes

- Generated at: 2026-05-04T05:35:06+00:00

- Modules/Payroll/Routes/api.php (lines=?, methods=?)
- Modules/Payroll/Routes/web.php (lines=?, methods=?)

## Route samples

### Modules/Payroll/Routes/api.php

- resource payroll
- get payroll-cycle
- get payroll/cycle-data/{payrollCycleId}/{year}

### Modules/Payroll/Routes/web.php

- resource employee-hourly-rate-settings
- resource employee-salary
- get employee-salary/data
- get employee-salary/edit-salary/{id?}
- get employee-salary/get-salary
- get employee-salary/get-updated-salary
- get employee-salary/increment-edit
- post employee-salary/increment-store/{id?}
- post employee-salary/increment-update
- get employee-salary/increment/{id}
- get employee-salary/make-salary/{id}
- post employee-salary/payroll-cycle
- post employee-salary/payroll-status
- post employee-salary/update-salary/{id?}
- post overtime-change-status
- resource overtime-policies
- post overtime-policies/employee-quick-action
- get overtime-policy-remove/{id}
- get overtime-request-accept/{id}
- get overtime-request-data
- get overtime-request-policy/{id}
- resource overtime-requests
- get overtime-settings
- resource pay-codes
- resource payment-methods
- resource payroll
- resource payroll-currency-settings
- resource payroll-expenses
- get payroll-export-reports
- resource payroll-reports
- get payroll-reports/fetch-tds{id?}
- get payroll-settings
- get payroll/download/{id}
- post payroll/generate
- post payroll/get-cycle-data
- get payroll/get-status
- get payroll/get_employee/{payrollCycle?}/{departmentId?}
- post payroll/get_expense_title
- post payroll/updateStatus
- resource salary-components
- resource salary-groups
- post salary-groups/manage-employee
- resource salary-settings
- resource salary-tds
- get salary-tds/get-status
- post salary-tds/status
