# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### employee_monthly_salaries

- Columns: amount, date, type, user_id
- Migrations: 2019_10_18_111743_create_employee_monthly_salary_table.php

### employee_payroll_cycles

- Columns: payroll_cycle_id, user_id
- Migrations: 2021_09_21_081203_create_employee_payroll_cycle_table.php

### employee_salary_groups

- Columns: salary_group_id, user_id
- Migrations: 2019_10_10_115749_create_employee_salary_groups_table.php

### employee_variable_salaries

- Columns: monthly_salary_id, variable_component_id, variable_value
- Migrations: 2022_08_24_095802_create_employee_variable_commponent_salaries_table.php

### overtime_policies

- Columns: action_by, allow_reporting_manager, allow_roles, amount, batch_key, code, company_id, date, description, end_date, fixed, fixed_amount, holiday, hours, minutes, name, overtime_hourly_rate, overtime_policy_id, overtime_reason, pay_code_id, request_before_days, save_type, start_date, status, time, type, user_id, week_end, working_days
- Migrations: 2024_07_05_110403_add_hourly_rate_column.php

### overtime_policy_employees

- Columns: action_by, allow_reporting_manager, allow_roles, amount, batch_key, code, company_id, date, description, end_date, fixed, fixed_amount, holiday, hours, minutes, name, overtime_hourly_rate, overtime_policy_id, overtime_reason, pay_code_id, request_before_days, save_type, start_date, status, time, type, user_id, week_end, working_days
- Migrations: 2024_07_05_110403_add_hourly_rate_column.php

### overtime_requests

- Columns: action_by, allow_reporting_manager, allow_roles, amount, batch_key, code, company_id, date, description, end_date, fixed, fixed_amount, holiday, hours, minutes, name, overtime_hourly_rate, overtime_policy_id, overtime_reason, pay_code_id, request_before_days, save_type, start_date, status, time, type, user_id, week_end, working_days
- Migrations: 2024_07_05_110403_add_hourly_rate_column.php

### pay_codes

- Columns: action_by, allow_reporting_manager, allow_roles, amount, batch_key, code, company_id, date, description, end_date, fixed, fixed_amount, holiday, hours, minutes, name, overtime_hourly_rate, overtime_policy_id, overtime_reason, pay_code_id, request_before_days, save_type, start_date, status, time, type, user_id, week_end, working_days
- Migrations: 2024_07_05_110403_add_hourly_rate_column.php

### payroll_cycles

- Columns: cycle, payroll_cycle_id, salary_from, salary_to, semi_monthly_end, semi_monthly_start, status
- Migrations: 2021_09_21_073111_create_payroll_cycle_table.php

### payroll_global_settings

- Columns: license_type, purchase_code, supported_until
- Migrations: 2022_09_01_000000_create_payroll_global_settings.php

### payroll_settings

- Columns: finance_month, tds_salary, tds_status
- Migrations: 2019_10_23_181854_create_payroll_settings_table.php

### salary_components

- Columns: component_name, component_type, component_value, value_type
- Migrations: 2019_10_07_191322_create_salary_components_table.php

### salary_group_components

- Columns: salary_component_id, salary_group_id
- Migrations: 2019_10_10_114655_create_salary_group_components_table.php

### salary_groups

- Columns: group_name
- Migrations: 2019_10_01_184956_create_salary_groups_table.php

### salary_payment_methods

- Columns: default, expense_claims, extra_json, pay_days, payment_method, salary_json, salary_payment_method_id
- Migrations: 2019_10_22_124159_add_columns_salary_slips_table.php

### salary_slips

- Columns: basic_salary, month, net_salary, paid_on, salary_group_id, status, user_id, year
- Migrations: 2019_10_18_095840_create_salary_slips_table.php

### salary_tds

- Columns: salary_from, salary_percent, salary_to
- Migrations: 2019_10_07_192042_create_salary_tds_table_table.php

## Entities (table + casts)

- Modules/Payroll/Entities/API/SalarySlip.php (table=salary_slips)
- Modules/Payroll/Entities/EmployeeMonthlySalary.php
- Modules/Payroll/Entities/EmployeePayrollCycle.php
- Modules/Payroll/Entities/EmployeeSalaryGroup.php
- Modules/Payroll/Entities/EmployeeVariableComponent.php
- Modules/Payroll/Entities/OvertimePolicy.php
- Modules/Payroll/Entities/OvertimePolicyEmployee.php
- Modules/Payroll/Entities/OvertimeRequest.php
- Modules/Payroll/Entities/OvertimeRequestRecord.php
- Modules/Payroll/Entities/PayCode.php
- Modules/Payroll/Entities/PayrollCycle.php
- Modules/Payroll/Entities/PayrollGlobalSetting.php
- Modules/Payroll/Entities/PayrollSetting.php
- Modules/Payroll/Entities/SalaryComponent.php
- Modules/Payroll/Entities/SalaryGroup.php
- Modules/Payroll/Entities/SalaryGroupComponent.php
- Modules/Payroll/Entities/SalaryPaymentMethod.php
- Modules/Payroll/Entities/SalarySlip.php
- Modules/Payroll/Entities/SalaryTds.php
