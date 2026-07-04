# Payroll Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Payroll
- Alias: payroll
- Provider: Modules\Payroll\Providers\PayrollServiceProvider, Modules\Payroll\Providers\EventServiceProvider
- Source root: Modules/Payroll/

## Business Purpose

Quản lý salary component/group, payroll cycle, payslip, overtime, pay code và payment method.

## Main Business Flow Draft

- Cấu hình salary component/group/payroll cycle.
- Gán salary group/cycle cho nhân viên.
- Tính và phát hành salary slip, ghi nhận overtime/payment/expense khi có.

## Code Evidence

### Routes

- Modules/Payroll/Routes/api.php
- Modules/Payroll/Routes/web.php

### Route Entry Points Snapshot

- Modules/Payroll/Routes/api.php:8 ApiRoute::resource('payroll', PayrollApiController::class);
- Modules/Payroll/Routes/api.php:9 ApiRoute::get('payroll-cycle', [PayrollApiController::class, 'getCycle']);
- Modules/Payroll/Routes/api.php:10 ApiRoute::get('payroll/cycle-data/{payrollCycleId}/{year}', [PayrollApiController::class, 'getCycleData']);
- Modules/Payroll/Routes/web.php:34 Route::post('payroll/generate', [PayrollController::class, 'generatePaySlip'])->name('payroll.generate_pay_slip');
- Modules/Payroll/Routes/web.php:35 Route::post('payroll/updateStatus', [PayrollController::class, 'updateStatus'])->name('payroll.update_status');
- Modules/Payroll/Routes/web.php:36 Route::get('payroll/get-status', [PayrollController::class, 'getStatus'])->name('payroll.get_status');
- Modules/Payroll/Routes/web.php:37 Route::get('payroll/download/{id}', [PayrollController::class, 'downloadPdf'])->name('payroll.download_pdf');
- Modules/Payroll/Routes/web.php:38 Route::post('payroll/get-cycle-data', [PayrollController::class, 'getCycleData'])->name('payroll.get-cycle-data');
- Modules/Payroll/Routes/web.php:39 Route::post('payroll/get_expense_title', [PayrollController::class, 'getExpenseTitle'])->name('payroll.get_expense_title');
- Modules/Payroll/Routes/web.php:40 Route::get('payroll/get_employee/{payrollCycle?}/{departmentId?}', [PayrollController::class, 'byDepartment'])->name('payroll.get-employee');
- Modules/Payroll/Routes/web.php:42 Route::resource('payroll', PayrollController::class);
- Modules/Payroll/Routes/web.php:44 Route::get('employee-salary/data', [EmployeeMonthlySalaryController::class, 'data'])->name('employee-salary.data');
- Modules/Payroll/Routes/web.php:45 Route::post('employee-salary/payroll-cycle', [EmployeeMonthlySalaryController::class, 'employeePayrollCycle'])->name('employee-salary.payroll-cycle');
- Modules/Payroll/Routes/web.php:46 Route::post('employee-salary/payroll-status', [EmployeeMonthlySalaryController::class, 'employeePayrollStatus'])->name('employee-salary.payroll-status');
- Modules/Payroll/Routes/web.php:47 Route::get('employee-salary/make-salary/{id}', [EmployeeMonthlySalaryController::class, 'makeSalary'])->name('employee-salary.make-salary');
- Modules/Payroll/Routes/web.php:48 Route::get('employee-salary/edit-salary/{id?}', [EmployeeMonthlySalaryController::class, 'editSalary'])->name('employee-salary.edit-salary');
- Modules/Payroll/Routes/web.php:49 Route::post('employee-salary/update-salary/{id?}', [EmployeeMonthlySalaryController::class, 'updateSalary'])->name('employee-salary.update-salary');
- Modules/Payroll/Routes/web.php:50 Route::get('employee-salary/get-salary', [EmployeeMonthlySalaryController::class, 'getSalary'])->name('employee-salary.get-salary');
- Modules/Payroll/Routes/web.php:51 Route::get('employee-salary/get-updated-salary', [EmployeeMonthlySalaryController::class, 'getUpdateSalary'])->name('employee-salary.get_update_salary');
- Modules/Payroll/Routes/web.php:52 Route::get('employee-salary/increment/{id}', [EmployeeMonthlySalaryController::class, 'increment'])->name('employee-salary.increment');
- Modules/Payroll/Routes/web.php:53 Route::post('employee-salary/increment-store/{id?}', [EmployeeMonthlySalaryController::class, 'incrementStore'])->name('employee-salary.increment-store');
- Modules/Payroll/Routes/web.php:54 Route::get('employee-salary/increment-edit', [EmployeeMonthlySalaryController::class, 'incrementEdit'])->name('employee-salary.increment_edit');
- Modules/Payroll/Routes/web.php:55 Route::post('employee-salary/increment-update', [EmployeeMonthlySalaryController::class, 'incrementUpdate'])->name('employee-salary.increment_update');

### Controllers

- Modules/Payroll/Http/Controllers/API/PayrollApiController.php
- Modules/Payroll/Http/Controllers/EmployeeHourlyRateSettingController.php
- Modules/Payroll/Http/Controllers/EmployeeMonthlySalaryController.php
- Modules/Payroll/Http/Controllers/OvertimePolicyController.php
- Modules/Payroll/Http/Controllers/OvertimeRequestController.php
- Modules/Payroll/Http/Controllers/OvertimeSettingController.php
- Modules/Payroll/Http/Controllers/PayCodeController.php
- Modules/Payroll/Http/Controllers/PayrollController.php
- Modules/Payroll/Http/Controllers/PayrollCurrencyController.php
- Modules/Payroll/Http/Controllers/PayrollExpenseController.php
- Modules/Payroll/Http/Controllers/PayrollReportController.php
- Modules/Payroll/Http/Controllers/PayrollSettingController.php
- Modules/Payroll/Http/Controllers/SalaryComponentController.php
- Modules/Payroll/Http/Controllers/SalaryGroupController.php
- Modules/Payroll/Http/Controllers/SalaryPaymentMethodController.php
- Modules/Payroll/Http/Controllers/SalarySettingController.php
- Modules/Payroll/Http/Controllers/SalaryTdsController.php

### Entities / Models

- Modules/Payroll/Entities/API/SalarySlip.php
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

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/Payroll/Resources/views/employee-salary/ajax/create.blade.php
- Modules/Payroll/Resources/views/employee-salary/ajax/edit.blade.php
- Modules/Payroll/Resources/views/employee-salary/ajax/salary-component.blade.php
- Modules/Payroll/Resources/views/employee-salary/ajax/salary-update-component.blade.php
- Modules/Payroll/Resources/views/employee-salary/create.blade.php
- Modules/Payroll/Resources/views/employee-salary/edit-increment.blade.php
- Modules/Payroll/Resources/views/employee-salary/increment.blade.php
- Modules/Payroll/Resources/views/employee-salary/index.blade.php
- Modules/Payroll/Resources/views/employee-salary/show.blade.php
- Modules/Payroll/Resources/views/layouts/master.blade.php
- Modules/Payroll/Resources/views/overtime-request/ajax/create.blade.php
- Modules/Payroll/Resources/views/overtime-request/ajax/edit.blade.php
- Modules/Payroll/Resources/views/overtime-request/create.blade.php
- Modules/Payroll/Resources/views/overtime-request/index.blade.php
- Modules/Payroll/Resources/views/overtime-request/show.blade.php
- Modules/Payroll/Resources/views/overtime-setting/ajax/overtime-policy.blade.php
- Modules/Payroll/Resources/views/overtime-setting/ajax/overtime-policy-employee.blade.php
- Modules/Payroll/Resources/views/overtime-setting/ajax/pay-code.blade.php
- Modules/Payroll/Resources/views/overtime-setting/ajax/pay-code/create.blade.php
- Modules/Payroll/Resources/views/overtime-setting/ajax/pay-code/edit.blade.php
- Modules/Payroll/Resources/views/overtime-setting/ajax/policy/create.blade.php
- Modules/Payroll/Resources/views/overtime-setting/ajax/policy/edit.blade.php
- Modules/Payroll/Resources/views/overtime-setting/create-salary-component-modal.blade.php
- Modules/Payroll/Resources/views/overtime-setting/edit-salary-group-modal.blade.php
- Modules/Payroll/Resources/views/overtime-setting/index.blade.php
- Modules/Payroll/Resources/views/payroll/ajax/edit-modal.blade.php
- Modules/Payroll/Resources/views/payroll/ajax/show-modal.blade.php
- Modules/Payroll/Resources/views/payroll/ajax/status-modal.blade.php
- Modules/Payroll/Resources/views/payroll/create.blade.php
- Modules/Payroll/Resources/views/payroll/cycle.blade.php

## Business Rules To Confirm

- Những trạng thái chính của từng object trong module là gì.
- Object nào là master data, object nào là transaction data.
- Có cần ràng buộc company/tenant, role, permission hoặc approval riêng không.
- Có phát sinh dữ liệu kế toán, kho, invoice, payroll hoặc notification qua module khác không.
- Xóa/sửa record trong module này có ảnh hưởng module nào khác không.

## Integration Points To Audit

- Controllers gọi service/helper/model ngoài module.
- Routes hoặc menu trong core app trỏ vào module này.
- Language keys trong Modules/LanguagePack hoặc lang.
- Tests hiện có liên quan module này.
- Seed/migration và permission/module setting liên quan.

## Related Existing Docs

- Chưa map tài liệu liên quan.

## Next Audit Checklist

- [ ] Đọc controller chính và ghi lại từng action create/update/delete/status.
- [ ] Đối chiếu DB schema/migration với entity fillable/casts/relations.
- [ ] Mở UI route chính và xác nhận workflow thực tế.
- [ ] Kiểm tra permission/menu/role gating.
- [ ] Ghi test URL và dữ liệu mẫu để UAT.
