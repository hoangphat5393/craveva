<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\AcceptEstimate
 *
 * @property int $id
 * @property int $estimate_id
 * @property string $full_name
 * @property string $email
 * @property string $signature
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate query()
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Estimate|null $estimate
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereCompanyId($value)
 * @mixin \Eloquent
 */
	class AcceptEstimate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Appreciation
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $award_id
 * @property int $award_to
 * @property \Illuminate\Support\Carbon $award_date
 * @property string|null $image
 * @property string|null $summary
 * @property int $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $addedBy
 * @property-read \App\Models\Award $award
 * @property-read \App\Models\User $awardTo
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $image_url
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereAwardDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereAwardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereAwardTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appreciation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Appreciation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Attendance
 *
 * @property int $id
 * @property int $user_id
 * @property int $lateCount
 * @property \Illuminate\Support\Carbon $clock_in_time
 * @property \Illuminate\Support\Carbon|null $clock_out_time
 * @property string $clock_in_ip
 * @property string $clock_out_ip
 * @property string $working_from
 * @property string $late
 * @property string $half_day
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $clock_in_date
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereClockInIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereClockInTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereClockOutIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereClockOutTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereHalfDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereLate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereWorkingFrom($value)
 * @property string|null $latitude
 * @property string|null $longitude
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereLongitude($value)
 * @property int|null $company_id
 * @property int|null $location_id
 * @property \Illuminate\Support\Carbon|null $shift_start_time
 * @property \Illuminate\Support\Carbon|null $shift_end_time
 * @property int|null $employee_shift_id
 * @property string $work_from_type
 * @property \Illuminate\Support\Carbon $attendance
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\CompanyAddress|null $location
 * @property-read \App\Models\EmployeeShift|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereEmployeeShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereShiftEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereShiftStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereWorkFromType($value)
 * @property string $overwrite_attendance
 * @property string $status
 * @property string $occassion
 * @property string $date
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereOverwriteAttendance($value)
 * @mixin \Eloquent
 * @property string|null $clock_in_type
 * @property string|null $clock_out_type
 * @property int $auto_clock_out
 * @property string|null $half_day_type
 * @property string|null $clock_out_time_work_from_type
 * @property int|null $clock_out_time_location_id
 * @property string|null $clock_out_time_working_from
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereAutoClockOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereClockInType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereClockOutTimeLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereClockOutTimeWorkFromType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereClockOutTimeWorkingFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereClockOutType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereHalfDayType($value)
 */
	class Attendance extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AttendanceSetting
 *
 * @property int $id
 * @property string $office_start_time
 * @property string $office_end_time
 * @property string|null $halfday_mark_time
 * @property int $late_mark_duration
 * @property int $clockin_in_day
 * @property string $employee_clock_in_out
 * @property string $office_open_days
 * @property string|null $ip_address
 * @property int|null $radius
 * @property string $radius_check
 * @property string $ip_check
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereClockinInDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereEmployeeClockInOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereHalfdayMarkTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereIpCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereLateMarkDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereOfficeEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereOfficeOpenDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereOfficeStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereRadius($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereRadiusCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereUpdatedAt($value)
 * @property int|null $alert_after
 * @property int $alert_after_status
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereAlertAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereAlertAfterStatus($value)
 * @property int $save_current_location
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereSaveCurrentLocation($value)
 * @property int|null $company_id
 * @property string $auto_clock_in
 * @property int|null $default_employee_shift
 * @property string $week_start_from
 * @property int $allow_shift_change
 * @property string $show_clock_in_button
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\EmployeeShift|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereAllowShiftChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereAutoClockIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereDefaultEmployeeShift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereShowClockInButton($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereWeekStartFrom($value)
 * @property string $restrict_clockin
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereRestrictClockin($value)
 * @property string $auto_clock_in_location
 * @property int $monthly_report
 * @property string|null $monthly_report_roles
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereAutoClockInLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereMonthlyReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereMonthlyReportRoles($value)
 * @property string|null $early_clock_in
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereEarlyClockIn($value)
 * @mixin \Eloquent
 * @property string $qr_enable
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceSetting whereQrEnable($value)
 */
	class AttendanceSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AutomateShift
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $employee_shift_rotation_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ShiftRotation|null $rotation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ShiftRotationSequence> $sequences
 * @property-read int|null $sequences_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|AutomateShift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AutomateShift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AutomateShift query()
 * @method static \Illuminate\Database\Eloquent\Builder|AutomateShift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AutomateShift whereEmployeeShiftRotationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AutomateShift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AutomateShift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AutomateShift whereUserId($value)
 */
	class AutomateShift extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Award
 *
 * @property-read \App\Models\Appreciation|null $appreciation
 * @property-read \App\Models\AwardIcon|null $awardIcon
 * @property-read \App\Models\Company|null $company
 * @method static Builder|Award newModelQuery()
 * @method static Builder|Award newQuery()
 * @method static Builder|Award query()
 * @property int $id
 * @property int|null $company_id
 * @property string $title
 * @property int|null $award_icon_id
 * @property string|null $summary
 * @property string $status
 * @property string $color_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Appreciation[] $appreciations
 * @property-read int|null $appreciations_count
 * @method static Builder|Award whereAwardIconId($value)
 * @method static Builder|Award whereColorCode($value)
 * @method static Builder|Award whereCompanyId($value)
 * @method static Builder|Award whereCreatedAt($value)
 * @method static Builder|Award whereId($value)
 * @method static Builder|Award whereStatus($value)
 * @method static Builder|Award whereSummary($value)
 * @method static Builder|Award whereTitle($value)
 * @method static Builder|Award whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Award extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AwardIcon
 *
 * @property-read \App\Models\Award|null $award
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon query()
 * @property int $id
 * @property string $title
 * @property string $icon
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AwardIcon whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class AwardIcon extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BankAccount
 *
 * @property int $id
 * @property int|null $company_id
 * @property string|null $type
 * @property string|null $bank_name
 * @property string|null $account_name
 * @property string|null $account_number
 * @property string|null $account_type
 * @property int|null $currency_id
 * @property string|null $contact_number
 * @property float|null $opening_balance
 * @property string|null $bank_logo
 * @property int|null $status
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property float|null $bank_balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $file_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BankTransaction[] $transaction
 * @property-read int|null $transaction_count
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereBankBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereBankLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereOpeningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $sort_code
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Expense> $expenses
 * @property-read int|null $expenses_count
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereSortCode($value)
 */
	class BankAccount extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BankTransaction
 *
 * @property int $id
 * @property int|null $company_id
 * @property int|null $bank_account_id
 * @property int|null $payment_id
 * @property int|null $invoice_id
 * @property int|null $expense_id
 * @property float|null $amount
 * @property string $type
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property string|null $memo
 * @property string|null $transaction_relation
 * @property string|null $transaction_related_to
 * @property string|null $title
 * @property \Illuminate\Support\Carbon|null $transaction_date
 * @property float|null $bank_balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read BankAccount|null $bankAccount
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereBankBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereExpenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereMemo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereTransactionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereTransactionRelatedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereTransactionRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction whereUpdatedAt($value)
 * @property int|null $purchase_payment_id
 * @method static \Illuminate\Database\Eloquent\Builder|BankTransaction wherePurchasePaymentId($value)
 * @mixin \Eloquent
 */
	class BankTransaction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BaseModel
 *
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel query()
 * @mixin \Eloquent
 */
	class BaseModel extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ClientCategory
 *
 * @property int $id
 * @property string $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCategory whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ClientCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ClientContact
 *
 * @property int $id
 * @property int $user_id
 * @property string $contact_name
 * @property string|null $phone
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $client
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereUserId($value)
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @method static Builder|ClientContact whereAddedBy($value)
 * @method static Builder|ClientContact whereLastUpdatedBy($value)
 * @property string|null $title
 * @method static Builder|ClientContact whereTitle($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static Builder|ClientContact whereCompanyId($value)
 * @mixin \Eloquent
 * @property int|null $client_id
 * @property string|null $address
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientContact whereClientId($value)
 */
	class ClientContact extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ClientDetails
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $company_name
 * @property string|null $address
 * @property string|null $shipping_address
 * @property string|null $postal_code
 * @property string|null $state
 * @property string|null $city
 * @property string|null $office
 * @property string|null $website
 * @property string|null $note
 * @property string|null $linkedin
 * @property string|null $facebook
 * @property string|null $twitter
 * @property string|null $skype
 * @property string|null $gst_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $category_id
 * @property int|null $sub_category_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereFacebook($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereGstNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereLinkedin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereSkype($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereSubCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereTwitter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereWebsite($value)
 * @property int|null $company_id
 * @property-read \App\Models\User|null $addedBy
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereCompanyId($value)
 * @property string|null $company_logo
 * @property int|null $quickbooks_client_id
 * @property-read mixed $image_url
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereCompanyLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereQuickbooksClientId($value)
 * @property string|null $electronic_address
 * @property string|null $electronic_address_scheme
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereElectronicAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereElectronicAddressScheme($value)
 * @mixin \Eloquent
 * @property string|null $tax_name
 * @property string|null $mobile
 * @property string|null $office_phone
 * @property int|null $country_id
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereOfficePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDetails whereTaxName($value)
 */
	class ClientDetails extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Holiday
 *
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $filename
 * @property string $hashname
 * @property string|null $size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $doc_url
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereUserId($value)
 * @property-read \App\Models\User|null $client
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ClientDocument whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ClientDocument extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ClientNote
 *
 * @property int $id
 * @property int|null $client_id
 * @property string $title
 * @property int $type
 * @property int|null $member_id
 * @property int $is_client_show
 * @property int $ask_password
 * @property string $details
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereAskPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereIsClientShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereUpdatedAt($value)
 * @property-read \App\Models\User|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ClientUserNote[] $members
 * @property-read int|null $members_count
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ClientNote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ClientSubCategory
 *
 * @property int $id
 * @property int $category_id
 * @property string $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ClientCategory $clientCategory
 * @method static \Illuminate\Database\Eloquent\Builder|ClientSubCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientSubCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientSubCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientSubCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientSubCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientSubCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientSubCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientSubCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ClientSubCategory whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ClientSubCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ClientUserNote
 *
 * @property int $id
 * @property int $user_id
 * @property int $client_note_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClientUserNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientUserNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientUserNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientUserNote whereClientNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientUserNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientUserNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientUserNote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientUserNote whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ClientUserNote whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ClientUserNote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Company
 *
 * @property int $id
 * @property string $company_name
 * @property string $app_name
 * @property string $company_email
 * @property string $company_phone
 * @property string|null $logo
 * @property string|null $login_background
 * @property string $address
 * @property string|null $website
 * @property int|null $currency_id
 * @property string $timezone
 * @property string $date_format
 * @property string|null $date_picker_format
 * @property string|null $moment_format
 * @property string $time_format
 * @property string $locale
 * @property string $latitude
 * @property string $longitude
 * @property string $leaves_start_from
 * @property string $active_theme
 * @property int|null $last_updated_by
 * @property string|null $currency_converter_key
 * @property string|null $google_map_key
 * @property string $task_self
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $purchase_code
 * @property string|null $supported_until
 * @property string $google_recaptcha_status
 * @property string $google_recaptcha_v2_status
 * @property string|null $google_recaptcha_v2_site_key
 * @property string|null $google_recaptcha_v2_secret_key
 * @property string $google_recaptcha_v3_status
 * @property string|null $google_recaptcha_v3_site_key
 * @property string|null $google_recaptcha_v3_secret_key
 * @property int $app_debug
 * @property int $rounded_theme
 * @property int $system_update
 * @property string $logo_background_color
 * @property int $before_days
 * @property int $after_days
 * @property string $on_deadline
 * @property int $default_task_status
 * @property int $show_review_modal
 * @property int $dashboard_clock
 * @property int $taskboard_length
 * @property string|null $favicon
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $dark_logo_url
 * @property-read mixed $favicon_url
 * @property-read mixed $icon
 * @property-read mixed $light_logo_url
 * @property-read mixed $masked_default_logo
 * @property-read mixed $login_background_url
 * @property-read mixed $logo_url
 * @property-read mixed $moment_date_format
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereActiveTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAfterDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAppDebug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereBeforeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCompanyEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCompanyPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCurrencyConverterKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDashboardClock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDateFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDatePickerFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDefaultTaskStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereFavicon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleMapKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV2SecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV2SiteKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV2Status($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV3SecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV3SiteKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV3Status($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereHideCronMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLeavesStartFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLoginBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLogoBackgroundColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereMomentFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereOnDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting wherePurchaseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereRoundedTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereShowReviewModal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSupportedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSystemUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTaskSelf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTaskboardLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTimeFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereWeatherKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereWebsite($value)
 * @property int $ticket_form_google_captcha
 * @property int $lead_form_google_captcha
 * @property string|null $last_cron_run
 * @property string $auth_theme
 * @property string|null $light_logo
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAuthTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLastCronRun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLeadFormGoogleCaptcha($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLightLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTicketFormGoogleCaptcha($value)
 * @property string $sidebar_logo_style
 * @property string $session_driver
 * @property int $allow_client_signup
 * @property int $admin_client_signup_approval
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAdminClientSignupApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAllowClientSignup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAllowedFileTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSessionDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSidebarLogoStyle($value)
 * @property string $google_calendar_status
 * @property string|null $google_client_id
 * @property string|null $google_client_secret
 * @property string $google_calendar_verification_status
 * @property string|null $google_id
 * @property string|null $name
 * @property string|null $token
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleCalendarStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleCalendarVerificationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAllowedFileSize($value)
 * @property string $status
 * @property string|null $last_login
 * @property int $rtl
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereAppName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereRtl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereStatus($value)
 * @property-read \App\Models\AttendanceSetting|null $attendanceSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CompanyAddress[] $companyAddress
 * @property-read int|null $company_address_count
 * @property-read \App\Models\InvoiceSetting|null $invoiceSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LeadAgent[] $leadAgents
 * @property-read int|null $lead_agents_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LeadCategory[] $leadCategories
 * @property-read int|null $lead_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LeadSource[] $leadSources
 * @property-read int|null $lead_sources_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LeadStatus[] $leadStats
 * @property-read int|null $lead_stats_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LeaveType[] $leaveTypes
 * @property-read int|null $leave_types_count
 * @property-read \App\Models\MessageSetting|null $messageSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OfflinePaymentMethod[] $offlinePaymentMethod
 * @property-read int|null $offline_payment_method_count
 * @property-read \App\Models\PaymentGatewayCredentials|null $paymentGatewayCredentials
 * @property-read \App\Models\ProjectSetting|null $projectSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectStatusSetting[] $projectStatusSettings
 * @property-read int|null $project_status_settings_count
 * @property-read \App\Models\TaskSetting|null $taskSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tax[] $taxes
 * @property-read int|null $taxes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketChannel[] $ticketChannels
 * @property-read int|null $ticket_channels_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketType[] $ticketTypes
 * @property-read int|null $ticket_types_count
 * @property-read \App\Models\ProjectTimeLog|null $timeLogSetting
 * @property string|null $hash
 * @property-read \App\Models\LeaveSetting|null $leaveSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ModuleSetting[] $moduleSetting
 * @property-read int|null $module_setting_count
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereHash($value)
 * @property int|null $package_id
 * @property string $package_type
 * @property string|null $stripe_id
 * @property string|null $card_brand
 * @property string|null $card_last_four
 * @property string|null $trial_ends_at
 * @property string|null $licence_expire_on
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Contract[] $contracts
 * @property-read int|null $contracts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Currency[] $currencies
 * @property-read int|null $currencies_count
 * @property-read \App\Models\CompanyAddress|null $defaultAddress
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $employees
 * @property-read int|null $employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Estimate[] $estimates
 * @property-read int|null $estimates_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FileStorage[] $fileStorage
 * @property-read int|null $file_storage_count
 * @property-read mixed $extras
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lead[] $leads
 * @property-read int|null $leads_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $orders
 * @property-read int|null $orders_count
 * @property-read Package|null $package
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $projects
 * @property-read int|null $projects_count
 * @property-read \App\Models\SlackSetting|null $slackSetting
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $tickets
 * @property-read int|null $tickets_count
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereYearStartsFrom($value)
 * @property string $header_color
 * @property int $datatable_row_limit
 * @property int $show_new_webhook_alert
 * @property string|null $pm_type
 * @property string|null $pm_last_four
 * @property-read \App\Models\CompanyAddress|null $defaultAddress
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereDatatableRowLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereHeaderColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company wherePmLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company wherePmType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereShowNewWebhookAlert($value)
 * @property string $auth_theme_text
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereAuthThemeText($value)
 * @property int $employee_can_export_data
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereEmployeeCanExportData($value)
 * @mixin \Eloquent
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereCardBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereCardLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereLicenceExpireOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company wherePackageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereTrialEndsAt($value)
 * @property string $year_starts_from
 * @property string|null $license_type
 * @property int $hide_cron_message
 * @property string|null $allowed_file_types
 * @property int $allowed_file_size
 * @property string $currency_key_version
 * @property string|null $license_updated_at
 * @property \Illuminate\Support\Carbon|null $subscription_updated_at
 * @property int $approved
 * @property int|null $approved_by
 * @property string|null $headers
 * @property string|null $register_ip
 * @property string|null $location_details
 * @property-read \App\Models\User|null $approvalBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Event> $events
 * @property-read int|null $events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SuperAdmin\GlobalInvoice> $globalInvoices
 * @property-read int|null $global_invoices_count
 * @property-read mixed $masked_dark_logo_url
 * @property-read mixed $masked_favicon_url
 * @property-read mixed $masked_light_logo_url
 * @property-read mixed $masked_login_background_url
 * @property-read mixed $masked_logo_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Cashier\Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Company active()
 * @method static \Illuminate\Database\Eloquent\Builder|Company hasExpiredGenericTrial()
 * @method static \Illuminate\Database\Eloquent\Builder|Company onGenericTrial()
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereCurrencyKeyVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereHeaders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereLicenseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereLicenseUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereLocationDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereRegisterIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereSubscriptionUpdatedAt($value)
 */
	class Company extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CompanyAddress
 *
 * @property int $id
 * @property string $address
 * @property int $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $tax_number
 * @property string|null $tax_name
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress whereTaxName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress whereTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress whereUpdatedAt($value)
 * @property string|null $location
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress whereLocation($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property int|null $country_id
 * @property-read \App\Models\Country|null $country
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress whereCompanyId($value)
 * @property string|null $latitude
 * @property string|null $longitude
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyAddress whereCountryId($value)
 * @mixin \Eloquent
 */
	class CompanyAddress extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Contract
 *
 * @property int $id
 * @property int $client_id
 * @property string $subject
 * @property string $amount
 * @property string $original_amount
 * @property int|null $contract_type_id
 * @property \Illuminate\Support\Carbon $start_date
 * @property string $original_start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property string|null $original_end_date
 * @property string|null $description
 * @property string|null $contract_name
 * @property string|null $company_logo
 * @property string|null $alternate_address
 * @property string|null $cell
 * @property string|null $office
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $postal_code
 * @property string|null $contract_detail
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User $client
 * @property-read \App\Models\ContractType|null $contractType
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContractDiscussion[] $discussion
 * @property-read int|null $discussion_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContractFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $icon
 * @property-read mixed $image_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContractRenew[] $renewHistory
 * @property-read int|null $renew_history_count
 * @property-read \App\Models\ContractSign|null $signature
 * @method static \Database\Factories\ContractFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract query()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereAlternateAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCell($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCompanyLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereContractDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereContractName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereContractTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereOriginalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereOriginalEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereOriginalStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereUpdatedAt($value)
 * @property string|null $hash
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereHash($value)
 * @property int|null $currency_id
 * @property-read \App\Models\Currency|null $currency
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCurrencyId($value)
 * @property string|null $event_id
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereEventId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCompanyId($value)
 * @property int|null $contract_number
 * @property int|null $project_id
 * @property string|null $contract_note
 * @property-read mixed $extras
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereContractNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereContractNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereProjectId($value)
 * @property string|null $company_sign
 * @property string|null $sign_date
 * @property-read mixed $company_signature
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCompanySign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereSignDate($value)
 * @property string|null $original_contract_number
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereOriginalContractNumber($value)
 * @mixin \Eloquent
 * @property int|null $sign_by
 * @property-read \App\Models\User|null $signer
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereSignBy($value)
 */
	class Contract extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ContractDiscussion
 *
 * @property int $id
 * @property int $contract_id
 * @property int $from
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereUpdatedAt($value)
 * @property-read \App\Models\Contract $contract
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ContractDiscussion whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ContractDiscussion extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ContractFile
 *
 * @property int $id
 * @property int $user_id
 * @property int $contract_id
 * @property string $filename
 * @property string $hashname
 * @property string $size
 * @property string $google_url
 * @property string $dropbox_link
 * @property string $external_link_name
 * @property string $external_link
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Contract $contract
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereCompanyId($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class ContractFile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ContractRenew
 *
 * @property int $id
 * @property int $renewed_by
 * @property int $contract_id
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property string $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Contract $contract
 * @property-read mixed $icon
 * @property-read \App\Models\User $renewedBy
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew whereRenewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ContractRenew whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ContractRenew extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ContractSign
 *
 * @property int $id
 * @property int $contract_id
 * @property string $full_name
 * @property string $email
 * @property string $signature
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Contract $contract
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ContractSign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractSign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractSign query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractSign whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractSign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractSign whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractSign whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractSign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractSign whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractSign whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ContractSign whereCompanyId($value)
 * @property string|null $place
 * @property string|null $date
 * @method static \Illuminate\Database\Eloquent\Builder|ContractSign whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractSign wherePlace($value)
 * @mixin \Eloquent
 */
	class ContractSign extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ContractTemplate
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $subject
 * @property string|null $description
 * @property string $amount
 * @property int $contract_type_id
 * @property int|null $currency_id
 * @property string|null $contract_detail
 * @property int $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ContractType $contractType
 * @property-read \App\Models\Currency|null $currency
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereContractDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereContractTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereUpdatedAt($value)
 * @property int|null $contract_template_number
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ContractTemplate whereContractTemplateNumber($value)
 * @mixin \Eloquent
 */
	class ContractTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ContractType
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ContractType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Country
 *
 * @property int $id
 * @property string $iso
 * @property string $name
 * @property string $nicename
 * @property string|null $iso3
 * @property int|null $numcode
 * @property int $phonecode
 * @method static \Illuminate\Database\Eloquent\Builder|Country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Country query()
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereIso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereIso3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereNicename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereNumcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country wherePhonecode($value)
 * @property-read mixed $nationality
 * @mixin \Eloquent
 */
	class Country extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CreditNoteItem
 *
 * @property int $id
 * @property int $credit_note_id
 * @property string $item_name
 * @property string $type
 * @property int $quantity
 * @property float $unit_price
 * @property float $amount
 * @property string|null $taxes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $hsn_sac_code
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereCreditNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereUpdatedAt($value)
 * @property-read \App\Models\CreditNoteItemImage|null $creditNoteItemImage
 * @property string|null $item_summary
 * @property-read mixed $tax_list
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereItemSummary($value)
 * @property-read \App\Models\UnitType|null $unit
 * @property int|null $unit_id
 * @property int|null $product_id
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItem whereUnitId($value)
 * @mixin \Eloquent
 */
	class CreditNoteItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CreditNoteItemImage
 *
 * @property int $id
 * @property int $credit_note_item_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItemImage whereCreditNoteItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNoteItemImage whereUpdatedAt($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class CreditNoteItemImage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CreditNotes
 *
 * @property int $id
 * @property int|null $project_id
 * @property int|null $client_id
 * @property string $cn_number
 * @property int|null $invoice_id
 * @property int|null $unit_id
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon $due_date
 * @property float $discount
 * @property string $discount_type
 * @property float $sub_total
 * @property float $total
 * @property int|null $currency_id
 * @property string $status
 * @property string $recurring
 * @property string|null $billing_frequency
 * @property int|null $billing_interval
 * @property int|null $billing_cycle
 * @property string|null $file
 * @property string|null $file_original_name
 * @property string|null $note
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User|null $client
 * @property-read \App\Models\ClientDetails|null $clientdetails
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $icon
 * @property-read mixed $issue_on
 * @property-read mixed $total_amount
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CreditNoteItem[] $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[] $payment
 * @property-read int|null $payment_count
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes query()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereBillingCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereBillingFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereBillingInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereCnNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereFileOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereUpdatedAt($value)
 * @property float|null $adjustment_amount
 * @property string $calculate_tax
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereAdjustmentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereCalculateTax($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereCompanyId($value)
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereUnitId($value)
 * @property string|null $original_credit_note_number
 * @method static \Illuminate\Database\Eloquent\Builder|CreditNotes whereOriginalCreditNoteNumber($value)
 * @mixin \Eloquent
 */
	class CreditNotes extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Currency
 *
 * @property int $id
 * @property string $currency_name
 * @property string|null $currency_symbol
 * @property string $currency_code
 * @property float|null $exchange_rate
 * @property string $is_cryptocurrency
 * @property float|null $usd_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency query()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCurrencyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCurrencySymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereIsCryptocurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereUsdPrice($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCompanyId($value)
 * @property string $currency_position
 * @property int $no_of_decimal
 * @property string|null $thousand_separator
 * @property string|null $decimal_separator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCurrencyPosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereDecimalSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereNoOfDecimal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereThousandSeparator($value)
 * @mixin \Eloquent
 */
	class Currency extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CurrencyFormatSetting
 *
 * @property int $id
 * @property string $currency_position
 * @property int $no_of_decimal
 * @property string|null $thousand_separator
 * @property string|null $decimal_separator
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyFormatSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyFormatSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyFormatSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyFormatSetting whereCurrencyPosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyFormatSetting whereDecimalSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyFormatSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyFormatSetting whereNoOfDecimal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyFormatSetting whereThousandSeparator($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyFormatSetting whereCompanyId($value)
 * @property-read \App\Models\Currency $currency
 * @mixin \Eloquent
 */
	class CurrencyFormatSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CustomField
 *
 * @property int $id
 * @property int|null $custom_field_group_id
 * @property string $label
 * @property string $name
 * @property bool $export
 * @property string $type
 * @property string $required
 * @property string|null $values
 * @property string|null $visible
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereCustomFieldGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereValues($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\LeadCustomForm|null $leadCustomForm
 * @property-read \App\Models\TicketCustomForm|null $ticketCustomForm
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereExport($value)
 * @property-read \App\Models\CustomFieldGroup|null $customFieldGroup
 * @property-read \App\Models\CustomFieldGroup|null $fieldGroup
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereVisible($value)
 * @mixin \Eloquent
 */
	class CustomField extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CustomFieldGroup
 *
 * @property int $id
 * @property string $name
 * @property string|null $model
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup whereName($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CustomField[] $customField
 * @property-read int|null $custom_field_count
 * @mixin \Eloquent
 * @property-read mixed $fields
 */
	class CustomFieldGroup extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CustomLinkSetting
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $link_title
 * @property string $url
 * @property string|null $can_be_viewed_by
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereCanBeViewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereLinkTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereUrl($value)
 * @mixin \Eloquent
 */
	class CustomLinkSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CustomModulePermission
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CustomModulePermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomModulePermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomModulePermission query()
 * @mixin \Eloquent
 */
	class CustomModulePermission extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DashboardWidget
 *
 * @property int $id
 * @property string $widget_name
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $dashboard_type
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget query()
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereDashboardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereWidgetName($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereCompanyId($value)
 * @mixin \Eloquent
 * @property int $active
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereActive($value)
 */
	class DashboardWidget extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DatabaseBackup
 *
 * @property int $id
 * @property string|null $filename
 * @property string|null $size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackup query()
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackup whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackup whereSize($value)
 * @mixin \Eloquent
 */
	class DatabaseBackup extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DatabaseBackupSetting
 *
 * @property int $id
 * @property string $status
 * @property string|null $hour_of_day
 * @property string|null $backup_after_days
 * @property string|null $delete_backup_after_days
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackupSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackupSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackupSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackupSetting whereBackupAfterDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackupSetting whereDeleteBackupAfterDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackupSetting whereHourOfDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackupSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DatabaseBackupSetting whereStatus($value)
 * @mixin \Eloquent
 */
	class DatabaseBackupSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Lead
 *
 * @property int $id
 * @property int|null $client_id
 * @property int|null $source_id
 * @property int|null $status_id
 * @property int $column_priority
 * @property int|null $agent_id
 * @property string|null $company_name
 * @property string|null $website
 * @property string|null $address
 * @property string|null $salutation
 * @property string $client_name
 * @property string $client_email
 * @property string|null $mobile
 * @property string|null $cell
 * @property string|null $office
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $postal_code
 * @property string|null $note
 * @property string $next_follow_up
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $value
 * @property float|null $total_value
 * @property int|null $currency_id
 * @property int|null $category_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User|null $client
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DealFile[] $files
 * @property-read int|null $files_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DealFollowUp[] $follow
 * @property-read int|null $follow_count
 * @property-read \App\Models\DealFollowUp|null $followup
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $image_url
 * @property-read \App\Models\LeadAgent|null $leadAgent
 * @property-read \App\Models\LeadSource|null $leadSource
 * @property-read \App\Models\LeadStatus|null $leadStatus
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\LeadFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lead newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lead query()
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCell($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereClientEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereColumnPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereNextFollowUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereSalutation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereWebsite($value)
 * @property string|null $hash
 * @property-read \App\Models\LeadCategory|null $category
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereHash($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read int|null $follow_up_date_next
 * @property-read int|null $follow_up_date_past
 * @property string|null $name
 * @property int|null $lead_pipeline_id
 * @property int|null $pipeline_stage_id
 * @property int|null $lead_id
 * @property \Illuminate\Support\Carbon|null $close_date
 * @property-read \App\Models\Lead|null $contact
 * @property-read \App\Models\PipelineStage|null $leadStage
 * @property-read \App\Models\LeadPipeline|null $pipeline
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereCloseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereLeadPipelineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal wherePipelineStageId($value)
 * @mixin \Eloquent
 * @property int|null $deal_watcher
 * @property string $create_client
 * @property-read \App\Models\User|null $addedBy
 * @property-read \App\Models\User|null $dealWatcher
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereCreateClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereDealWatcher($value)
 */
	class Deal extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Holiday
 *
 * @package App\Models
 * @property int $id
 * @property int $lead_id
 * @property int $user_id
 * @property string $filename
 * @property string $hashname
 * @property string $size
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $dropbox_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read \App\Models\Lead $lead
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereUserId($value)
 * @mixin \Eloquent
 * @property int|null $deal_id
 * @method static \Illuminate\Database\Eloquent\Builder|DealFile whereDealId($value)
 */
	class DealFile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DealFollowUp
 *
 * @property int $id
 * @property int $lead_id
 * @property string|null $remark
 * @property \Illuminate\Support\Carbon|null $next_follow_up_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp query()
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereNextFollowUpDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereUpdatedAt($value)
 * @property string|null $event_id
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereEventId($value)
 * @property string|null $send_reminder
 * @property string|null $remind_time
 * @property string|null $remind_type
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereRemindTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereRemindType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereSendReminder($value)
 * @property-read \App\Models\User|null $addedBy
 * @property string|null $status
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereStatus($value)
 * @property int|null $deal_id
 * @method static \Illuminate\Database\Eloquent\Builder|DealFollowUp whereDealId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Deal|null $lead
 */
	class DealFollowUp extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DealHistory
 *
 * @property int $id
 * @property int $deal_id
 * @property string $event_type
 * @property int|null $created_by
 * @property int|null $deal_stage_from_id
 * @property int|null $file_id
 * @property int|null $task_id
 * @property int|null $follow_up_id
 * @property int|null $note_id
 * @property int|null $proposal_id
 * @property int|null $agent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $deal_stage_to_id
 * @property-read \App\Models\PipelineStage|null $dealStageFrom
 * @property-read \App\Models\PipelineStage|null $dealStageTo
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereDealId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereDealStageFromId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereDealStageToId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereFollowUpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereProposalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealHistory whereUpdatedAt($value)
 */
	class DealHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ClientNote
 *
 * @property int $id
 * @property int|null $client_id
 * @property string $title
 * @property int $type
 * @property int|null $member_id
 * @property int $is_client_show
 * @property int $ask_password
 * @property string $details
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereAskPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereIsClientShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereUpdatedAt($value)
 * @property-read \App\Models\User|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ClientUserNote[] $members
 * @property-read int|null $members_count
 * @property int|null $deal_id
 * @property int $is_lead_show
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote whereIsLeadShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DealNote whereDealId($value)
 * @mixin \Eloquent
 */
	class DealNote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Designation
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property int|null $parent_id
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmployeeDetails[] $members
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Designation[] $childs
 * @property-read int|null $members_count
 * @method static \Illuminate\Database\Eloquent\Builder|Designation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Designation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Designation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read int|null $childs_count
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereParentId($value)
 * @mixin \Eloquent
 */
	class Designation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Discussion
 *
 * @property int $id
 * @property int $discussion_category_id
 * @property int|null $project_id
 * @property string $title
 * @property string|null $color
 * @property int $user_id
 * @property int $pinned
 * @property int $closed
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon $last_reply_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $best_answer_id
 * @property int|null $last_reply_by_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\DiscussionCategory $category
 * @property-read \App\Models\User|null $lastReplyBy
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DiscussionReply[] $replies
 * @property-read int|null $replies_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereBestAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereClosed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereDiscussionCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereLastReplyAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereLastReplyById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion wherePinned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DiscussionFile[] $files
 * @property-read int|null $files_count
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionDiscussion
 * @property-read int|null $mention_discussion_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionDiscussion
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @mixin \Eloquent
 */
	class Discussion extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DiscussionCategory
 *
 * @property int $id
 * @property int $order
 * @property string $name
 * @property string $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionCategory whereCompanyId($value)
 * @mixin \Eloquent
 */
	class DiscussionCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DiscussionFile
 *
 * @property string|null $external_link
 * @property int $id
 * @property int $user_id
 * @property int|null $discussion_id
 * @property int|null $discussion_reply_id
 * @property string $filename
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $dropbox_link
 * @property string|null $external_link_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Discussion|null $discussion
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereDiscussionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereDiscussionReplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionFile whereCompanyId($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class DiscussionFile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DiscussionReply
 *
 * @property int $id
 * @property int $discussion_id
 * @property int $user_id
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Discussion $discussion
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply newQuery()
 * @method static \Illuminate\Database\Query\Builder|DiscussionReply onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereDiscussionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|DiscussionReply withTrashed()
 * @method static \Illuminate\Database\Query\Builder|DiscussionReply withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DiscussionFile[] $files
 * @property-read int|null $files_count
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionDiscussionReply
 * @property-read int|null $mention_discussion_reply_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionDiscussionReply
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @mixin \Eloquent
 * @property int|null $added_by
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereAddedBy($value)
 */
	class DiscussionReply extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmailNotificationSetting
 *
 * @property int $id
 * @property string $setting_name
 * @property string $send_email
 * @property string $send_slack
 * @property string $send_push
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $slug
 * @property-read mixed $icon
 * @method static Builder|EmailNotificationSetting newModelQuery()
 * @method static Builder|EmailNotificationSetting newQuery()
 * @method static Builder|EmailNotificationSetting query()
 * @method static Builder|EmailNotificationSetting whereCreatedAt($value)
 * @method static Builder|EmailNotificationSetting whereId($value)
 * @method static Builder|EmailNotificationSetting whereSendEmail($value)
 * @method static Builder|EmailNotificationSetting whereSendPush($value)
 * @method static Builder|EmailNotificationSetting whereSendSlack($value)
 * @method static Builder|EmailNotificationSetting whereSettingName($value)
 * @method static Builder|EmailNotificationSetting whereSlug($value)
 * @method static Builder|EmailNotificationSetting whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read Company|null $company
 * @method static Builder|EmailNotificationSetting whereCompanyId($value)
 * @property string $send_twilio
 * @method static Builder|EmailNotificationSetting whereSendTwilio($value)
 * @mixin Eloquent
 */
	class EmailNotificationSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmergencyContact
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $mobile
 * @property string|null $relation
 * @property string|null $address
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|EmergencyContact whereCompanyId($value)
 * @mixin \Eloquent
 */
	class EmergencyContact extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeActivity
 *
 * @property int $id
 * @property int $emp_id
 * @property string $employee_activity
 * @property int|null $leave_id
 * @property int|null $task_id
 * @property int|null $proj_id
 * @property int|null $invoice_id
 * @property int|null $ticket_id
 * @property int|null $proposal_id
 * @property int|null $estimate_id
 * @property int|null $deal_id
 * @property int|null $deal_followup_id
 * @property int|null $client_id
 * @property int|null $expenses_id
 * @property int|null $timelog_id
 * @property int|null $event_id
 * @property int|null $product_id
 * @property int|null $credit_note_id
 * @property int|null $payment_id
 * @property int|null $order_id
 * @property int|null $contract_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereCreditNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereDealFollowupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereDealId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereEmployeeActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereExpensesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereLeaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereProjId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereProposalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereTimelogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeActivity whereUpdatedAt($value)
 */
	class EmployeeActivity extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeDetails
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $employee_id
 * @property string|null $address
 * @property float|null $hourly_rate
 * @property string|null $slack_username
 * @property int|null $department_id
 * @property int|null $designation_id
 * @property int|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon $joining_date
 * @property \Illuminate\Support\Carbon|null $last_date
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Team|null $department
 * @property-read \App\Models\Designation|null $designation
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static Builder|EmployeeDetails newModelQuery()
 * @method static Builder|EmployeeDetails newQuery()
 * @method static Builder|EmployeeDetails query()
 * @method static Builder|EmployeeDetails whereAddedBy($value)
 * @method static Builder|EmployeeDetails whereAddress($value)
 * @method static Builder|EmployeeDetails whereCreatedAt($value)
 * @method static Builder|EmployeeDetails whereDepartmentId($value)
 * @method static Builder|EmployeeDetails whereDesignationId($value)
 * @method static Builder|EmployeeDetails whereEmployeeId($value)
 * @method static Builder|EmployeeDetails whereHourlyRate($value)
 * @method static Builder|EmployeeDetails whereId($value)
 * @method static Builder|EmployeeDetails whereJoiningDate($value)
 * @method static Builder|EmployeeDetails whereLastDate($value)
 * @method static Builder|EmployeeDetails whereLastUpdatedBy($value)
 * @method static Builder|EmployeeDetails whereSlackUsername($value)
 * @method static Builder|EmployeeDetails whereUpdatedAt($value)
 * @method static Builder|EmployeeDetails whereUserId($value)
 * @property string|null $attendance_reminder
 * @method static Builder|EmployeeDetails whereAttendanceReminder($value)
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @method static Builder|EmployeeDetails whereDateOfBirth($value)
 * @property int|null $company_id
 * @property string|null $calendar_view
 * @property string|null $about_me
 * @property int|null $reporting_to
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $upcoming_birthday
 * @property-read \App\Models\User|null $reportingTo
 * @method static Builder|EmployeeDetails whereAboutMe($value)
 * @method static Builder|EmployeeDetails whereCalendarView($value)
 * @method static Builder|EmployeeDetails whereCompanyId($value)
 * @method static Builder|EmployeeDetails whereReportingTo($value)
 * @property string|null $contract_end_date
 * @property string|null $internship_end_date
 * @property string|null $employment_type
 * @property string|null $marriage_anniversary_date
 * @property string|null $marital_status
 * @property string|null $notice_period_end_date
 * @property string|null $notice_period_start_date
 * @property string|null $probation_end_date
 * @property string|null $name
 * @property string|null $occassion
 * @method static Builder|EmployeeDetails whereContractEndDate($value)
 * @method static Builder|EmployeeDetails whereEmploymentType($value)
 * @method static Builder|EmployeeDetails whereInternshipEndDate($value)
 * @method static Builder|EmployeeDetails whereMaritalStatus($value)
 * @method static Builder|EmployeeDetails whereMarriageAnniversaryDate($value)
 * @method static Builder|EmployeeDetails whereNoticePeriodEndDate($value)
 * @method static Builder|EmployeeDetails whereNoticePeriodStartDate($value)
 * @method static Builder|EmployeeDetails whereProbationEndDate($value)
 * @mixin \Eloquent
 * @property int|null $company_address_id
 * @property float|null $overtime_hourly_rate This field is only for overtime calculation
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDetails whereCompanyAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDetails whereOvertimeHourlyRate($value)
 */
	class EmployeeDetails extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Holiday
 *
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $filename
 * @property string $hashname
 * @property string|null $size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $doc_url
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereCompanyId($value)
 * @mixin \Eloquent
 */
	class EmployeeDocument extends \Eloquent {}
}

namespace App\Models{
/**
 * Class EmployeeDocumentExpiry
 *
 * @package App\Models
 * @property int $id
 * @property int $company_id
 * @property int $user_id
 * @property string $document_name
 * @property string $document_number
 * @property \Carbon\Carbon $issue_date
 * @property \Carbon\Carbon $expiry_date
 * @property int $alert_before_days
 * @property boolean $alert_enabled
 * @property string|null $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $doc_url
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Company|null $company
 * @property-read boolean $is_expired
 * @property-read boolean $is_expiring_soon
 * @property-read int $days_until_expiry
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry expired()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry expiringSoon()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereAlertBeforeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereAlertEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereDocumentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocumentExpiry whereUserId($value)
 */
	class EmployeeDocumentExpiry extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeLeaveQuota
 *
 * @property int $id
 * @property int $user_id
 * @property int $leave_type_id
 * @property int $no_of_leaves
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\LeaveType $leaveType
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereLeaveTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereNoOfLeaves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereCompanyId($value)
 * @mixin \Eloquent
 * @property float $leaves_used
 * @property float $leaves_remaining
 * @property array|null $carry_forward_status
 * @property int $leave_type_impact
 * @property float $overutilised_leaves
 * @property float $unused_leaves
 * @property float $carry_forward_leaves
 * @property float $carry_forward_applied
 * @property int $leaves_to_reimburse
 * @property int $leaves_actually_reimbursed
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereCarryForwardApplied($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereCarryForwardLeaves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereCarryForwardStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereLeaveTypeImpact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereLeavesActuallyReimbursed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereLeavesRemaining($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereLeavesToReimburse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereLeavesUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereOverutilisedLeaves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuota whereUnusedLeaves($value)
 */
	class EmployeeLeaveQuota extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeLeaveQuotaHistory
 *
 * @property int $id
 * @property int $user_id
 * @property int $leave_type_id
 * @property float $no_of_leaves
 * @property float $leaves_used
 * @property float $leaves_remaining
 * @property \Illuminate\Support\Carbon $for_month
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float $overutilised_leaves
 * @property float $unused_leaves
 * @property float $carry_forward_leaves
 * @property int $carry_forward_applied
 * @property-read \App\Models\LeaveType $leaveType
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory whereCarryForwardApplied($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory whereCarryForwardLeaves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory whereForMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory whereLeaveTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory whereLeavesRemaining($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory whereLeavesUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory whereNoOfLeaves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory whereOverutilisedLeaves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory whereUnusedLeaves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeLeaveQuotaHistory whereUserId($value)
 */
	class EmployeeLeaveQuotaHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeShift
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $shift_name
 * @property string $shift_short_code
 * @property string $color
 * @property string $office_start_time
 * @property string $office_end_time
 * @property string $auto_clock_out_time
 * @property string|null $halfday_mark_time
 * @property int $late_mark_duration
 * @property int $clockin_in_day
 * @property string $office_open_days
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereClockinInDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereHalfdayMarkTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereLateMarkDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereOfficeEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereOfficeOpenDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereOfficeStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereShiftName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereShiftShortCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereUpdatedAt($value)
 * @property-read \App\Models\Company|null $company
 * @property string|null $early_clock_in
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereEarlyClockIn($value)
 * @mixin \Eloquent
 * @property string $shift_type
 * @property float|null $flexible_total_hours
 * @property float|null $flexible_auto_clockout
 * @property float|null $flexible_half_day_hours
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeShiftSchedule> $employeeShiftSchedules
 * @property-read int|null $employee_shift_schedules_count
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereAutoClockOutTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereFlexibleAutoClockout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereFlexibleHalfDayHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereFlexibleTotalHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShift whereShiftType($value)
 */
	class EmployeeShift extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeShiftChangeRequest
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $shift_schedule_id
 * @property int $employee_shift_id
 * @property string $status
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EmployeeShift $shift
 * @property-read \App\Models\EmployeeShiftSchedule $shiftSchedule
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereEmployeeShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereShiftScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereUpdatedAt($value)
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftChangeRequest whereCompanyId($value)
 * @mixin \Eloquent
 */
	class EmployeeShiftChangeRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeShiftSchedule
 *
 * @property string|null $color
 * @property-read \App\Models\EmployeeShift $shift
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $date
 * @property int $employee_shift_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $shift_start_time
 * @property \Illuminate\Support\Carbon|null $shift_end_time
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EmployeeShiftChangeRequest|null $pendingRequestChange
 * @property-read \App\Models\EmployeeShiftChangeRequest|null $requestChange
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereEmployeeShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereShiftEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereShiftStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereUserId($value)
 * @property string $file
 * @property-read mixed $download_file_url
 * @property-read mixed $file_url
 * @property-read \App\Models\EmployeeShiftSchedule|null $dates
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereFile($value)
 * @mixin \Eloquent
 * @property int|null $company_id
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeShiftSchedule whereCompanyId($value)
 */
	class EmployeeShiftSchedule extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeSkill
 *
 * @property int $id
 * @property int $user_id
 * @property int $skill_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\Skill $skill
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill whereSkillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill whereCompanyId($value)
 * @mixin \Eloquent
 */
	class EmployeeSkill extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeTeam
 *
 * @property int $id
 * @property int $team_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam whereCompanyId($value)
 * @mixin \Eloquent
 */
	class EmployeeTeam extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Estimate
 *
 * @property int $id
 * @property int $client_id
 * @property string|null $estimate_number
 * @property \Illuminate\Support\Carbon $valid_till
 * @property float $sub_total
 * @property float $discount
 * @property string $discount_type
 * @property float $total
 * @property int|null $currency_id
 * @property string $status
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $send_status
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User $client
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $total_amount
 * @property-read mixed $valid_date
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EstimateItem[] $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\AcceptEstimate|null $sign
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate query()
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereEstimateNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereSendStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereValidTill($value)
 * @property string|null $hash
 * @property int|null $unit_id
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereHash($value)
 * @property string $calculate_tax
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCalculateTax($value)
 * @property string|null $description
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereDescription($value)
 * @property int|null $company_id
 * @property-read \App\Models\ClientDetails $clientdetails
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCompanyId($value)
 * @property \Illuminate\Support\Carbon|null $last_viewed
 * @property string|null $ip_address
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereLastViewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereUnitId($value)
 * @property string|null $original_estimate_number
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereOriginalEstimateNumber($value)
 * @mixin \Eloquent
 * @property int|null $estimate_request_id
 * @property int|null $project_id
 * @property-read \App\Models\EstimateRequest|null $estimateRequest
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereEstimateRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereProjectId($value)
 */
	class Estimate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EstimateItem
 *
 * @property int $id
 * @property int $estimate_id
 * @property string $item_name
 * @property string|null $item_summary
 * @property string $type
 * @property float $quantity
 * @property float $unit_price
 * @property float $amount
 * @property string|null $taxes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $hsn_sac_code
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereUpdatedAt($value)
 * @property-read \App\Models\EstimateItemImage|null $estimateItemImage
 * @property-read mixed $tax_list
 * @property int|null $product_id
 * @property int|null $unit_id
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereUnitId($value)
 * @mixin \Eloquent
 * @property int $field_order
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItem whereFieldOrder($value)
 */
	class EstimateItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EstimateItemImage
 *
 * @property int $id
 * @property int $estimate_item_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereEstimateItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateItemImage whereUpdatedAt($value)
 * @property-read \App\Models\EstimateItem $item
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class EstimateItemImage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EstimateRequest
 *
 * @property int $id
 * @property string|null $original_request_number
 * @property string|null $estimate_request_number
 * @property int $client_id
 * @property int|null $company_id
 * @property int|null $estimate_id
 * @property string $description
 * @property float $estimated_budget
 * @property int|null $project_id
 * @property string|null $early_requirement
 * @property int|null $currency_id
 * @property string $status
 * @property string $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property-read \App\Models\User $client
 * @property-read \App\Models\ClientDetails $clientdetails
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Currency|null $currency
 * @property-read \App\Models\Estimate|null $estimate
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereEarlyRequirement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereEstimateRequestNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereEstimatedBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereOriginalRequestNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateRequest whereUpdatedAt($value)
 */
	class EstimateRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EstimateTemplate
 *
 * @property int|null $unit_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCompanyId($value)
 * @property int $id
 * @property int|null $company_id
 * @property string $name
 * @property float $sub_total
 * @property float $total
 * @property int|null $currency_id
 * @property string $discount_type
 * @property float $discount
 * @property int $invoice_convert
 * @property string $status
 * @property string|null $note
 * @property string|null $description
 * @property string $calculate_tax
 * @property string|null $client_comment
 * @property int $signature_approval
 * @property string|null $hash
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ClientDetails|null $clients
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplateItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\UnitType|null $units
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereCalculateTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereClientComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereInvoiceConvert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereSignatureApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class EstimateTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EstimateTemplateItem
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $estimate_template_id
 * @property string|null $hsn_sac_code
 * @property string $item_name
 * @property string $type
 * @property int $quantity
 * @property float $unit_price
 * @property float $amount
 * @property string|null $item_summary
 * @property string|null $taxes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EstimateTemplateItemImage|null $estimateTemplateItemImage
 * @property-read mixed $tax_list
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereEstimateTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereUpdatedAt($value)
 * @property int|null $product_id
 * @property int|null $unit_id
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItem whereUnitId($value)
 * @mixin \Eloquent
 */
	class EstimateTemplateItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EstimateTemplateItemImage
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $estimate_template_item_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereEstimateTemplateItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateTemplateItemImage whereUpdatedAt($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class EstimateTemplateItemImage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Event
 *
 * @property int $id
 * @property string $event_name
 * @property string $label_color
 * @property string $where
 * @property string $description
 * @property \Illuminate\Support\Carbon $start_date_time
 * @property \Illuminate\Support\Carbon $end_date_time
 * @property string $repeat
 * @property int|null $repeat_every
 * @property int|null $repeat_cycles
 * @property string $repeat_type
 * @property string $send_reminder
 * @property int|null $remind_time
 * @property string $remind_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EventAttendee[] $attendee
 * @property-read int|null $attendee_count
 * @property-read mixed $icon
 * @method static \Database\Factories\EventFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEndDateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEventName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRemindTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRemindType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRepeat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRepeatCycles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRepeatEvery($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRepeatType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereSendReminder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStartDateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereWhere($value)
 * @property string|null $event_id
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEventId($value)
 * @property int|null $company_id
 * @property string|null $event_link
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EventFile[] $files
 * @property-read int|null $files_count
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEventLink($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionEvent
 * @property-read int|null $mention_event_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property int|null $parent_id
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereParentId($value)
 * @mixin \Eloquent
 * @property int|null $host
 * @property string $status
 * @property string $note
 * @property string|null $departments
 * @property-read mixed $extras
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDepartments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStatus($value)
 */
	class Event extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EventAttendee
 *
 * @property int $id
 * @property int $user_id
 * @property int $event_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EventAttendee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventAttendee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventAttendee query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventAttendee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventAttendee whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventAttendee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventAttendee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventAttendee whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|EventAttendee whereCompanyId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Event $event
 */
	class EventAttendee extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EventFile
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $event_id
 * @property string|null $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class EventFile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Expense
 *
 * @property int $id
 * @property string $item_name
 * @property double $total
 * @property string $date
 * @property double $total
 * @property string $exchange_rate
 * @property \Illuminate\Support\Carbon $purchase_date
 * @property string|null $purchase_from
 * @property float $price
 * @property float $default_currency_price
 * @property int $currency_id
 * @property int|null $project_id
 * @property string|null $bill
 * @property int $user_id
 * @property int|null $approver_id
 * @property int|null $default_currency_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $can_claim
 * @property int|null $category_id
 * @property int|null $expenses_recurring_id
 * @property int|null $created_by
 * @property string|null $description
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Currency $currency
 * @property-read mixed $bill_url
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $purchase_on
 * @property-read mixed $total_amount
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection|Expense[] $recurrings
 * @property-read int|null $recurrings_count
 * @property-read \App\Models\User $user
 * @property-read \App\Models\User $approver
 * @method static \Database\Factories\ExpenseFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Expense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Expense query()
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereBill($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCanClaim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereExpensesRecurringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense wherePurchaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense wherePurchaseFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereUserId($value)
 * @property-read \App\Models\ExpensesCategory|null $category
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereApproverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCompanyId($value)
 * @property int|null $bank_account_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BankTransaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereDefaultCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereExchangeRate($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BankTransaction> $transactions
 * @mixin \Eloquent
 * @property-read \App\Models\BankAccount|null $bankAccount
 */
	class Expense extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ExpenseRecurring
 *
 * @property int $id
 * @property int|null $category_id
 * @property int|null $currency_id
 * @property int|null $project_id
 * @property int|null $user_id
 * @property int|null $created_by
 * @property string $item_name
 * @property int|null $day_of_month
 * @property int|null $day_of_week
 * @property string|null $payment_method
 * @property string $rotation
 * @property int|null $billing_cycle
 * @property \Illuminate\Support\Carbon $issue_date
 * @property int $unlimited_recurring
 * @property float $price
 * @property string|null $bill
 * @property int $immediate_expense
 * @property string $status
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $next_invoice_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ExpensesCategory|null $category
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $bill_url
 * @property-read mixed $created_on
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $total_amount
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Expense[] $recurrings
 * @property-read int|null $recurrings_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereBill($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereBillingCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereDayOfMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereRotation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereUnlimitedRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereUserId($value)
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereLastUpdatedBy($value)
 * @property string|null $purchase_from
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring wherePurchaseFrom($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereCompanyId($value)
 * @property \Illuminate\Support\Carbon|null $next_expense_date
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereImmediateExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereNextExpenseDate($value)
 * @property int|null $bank_account_id
 * @property-read \App\Models\BankAccount|null $bank
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseRecurring whereBankAccountId($value)
 * @mixin \Eloquent
 */
	class ExpenseRecurring extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ExpensesCategory
 *
 * @property int $id
 * @property string $category_name
 * @property bigint $category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Expense[] $expense
 * @property-read int|null $expense_count
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|ExpensesCategoryRole[] $roles
 * @property-read int|null $roles_count
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Expense|null $expenses
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategory whereCompanyId($value)
 * @property-read int|null $expenses_count
 * @mixin \Eloquent
 */
	class ExpensesCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ExpensesCategoryRole
 *
 * @property int $id
 * @property int|null $expenses_category_id
 * @property int $role_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ExpensesCategory $category
 * @property-read \App\Models\Role $role
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategoryRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategoryRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategoryRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategoryRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategoryRole whereExpensesCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategoryRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategoryRole whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategoryRole whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ExpensesCategoryRole whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ExpensesCategoryRole extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\FileStorage
 *
 * @property int $id
 * @property string $path
 * @property string $filename
 * @property string|null $type
 * @property int $size
 * @property string $storage_location
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage query()
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereStorageLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read mixed $size_format
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereFilename($value)
 * @mixin \Eloquent
 */
	class FileStorage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Flag
 *
 * @property int $id
 * @property string|null $capital
 * @property string|null $code
 * @property string|null $continent
 * @property string|null $name
 * @method static \Illuminate\Database\Eloquent\Builder|Flag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Flag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Flag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereCapital($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereContinent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereName($value)
 * @mixin \Eloquent
 */
	class Flag extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GanttLink
 *
 * @property int $id
 * @property string $type
 * @property int|null $project_id
 * @property int $source
 * @property int $target
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GanttLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GanttLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GanttLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|GanttLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GanttLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GanttLink whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GanttLink whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GanttLink whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GanttLink whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GanttLink whereUpdatedAt($value)
 */
	class GanttLink extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GdprSetting
 *
 * @property int $id
 * @property int $enable_gdpr
 * @property int $show_customer_area
 * @property int $show_customer_footer
 * @property string|null $top_information_block
 * @property int $enable_export
 * @property int $data_removal
 * @property int $lead_removal_public_form
 * @property int $terms_customer_footer
 * @property string|null $terms
 * @property string|null $policy
 * @property int $public_lead_edit
 * @property int $consent_customer
 * @property int $consent_leads
 * @property string|null $consent_block
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereConsentBlock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereConsentCustomer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereConsentLeads($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereDataRemoval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereEnableExport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereEnableGdpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereLeadRemovalPublicForm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting wherePolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting wherePublicLeadEdit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereShowCustomerArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereShowCustomerFooter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereTermsCustomerFooter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereTopInformationBlock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class GdprSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Company
 *
 * @property int $id
 * @property string $global_app_name
 * @property string $currency_key_version
 * @property string $license_type
 * @property string|null $logo
 * @property string|null $email
 * @property string|null $login_background
 * @property string $address
 * @property string|null $website
 * @property int|null $currency_id
 * @property int $rtl
 * @property int $show_update_popup
 * @property string $timezone
 * @property string $date_format
 * @property string|null $date_picker_format
 * @property string|null $moment_format
 * @property string $time_format
 * @property string $locale
 * @property string $latitude
 * @property string $longitude
 * @property string $leaves_start_from
 * @property string $active_theme
 * @property int|null $last_updated_by
 * @property string|null $currency_converter_key
 * @property string|null $google_map_key
 * @property string $task_self
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $purchase_code
 * @property string|null $supported_until
 * @property string $google_recaptcha_status
 * @property string $google_recaptcha_v2_status
 * @property string|null $google_recaptcha_v2_site_key
 * @property string|null $google_recaptcha_v2_secret_key
 * @property string $google_recaptcha_v3_status
 * @property string|null $google_recaptcha_v3_site_key
 * @property string|null $google_recaptcha_v3_secret_key
 * @property string|null $moment_format
 * @property int $app_debug
 * @property int $rounded_theme
 * @property int $hide_cron_message
 * @property int $system_update
 * @property string $logo_background_color
 * @property int $before_days
 * @property int $after_days
 * @property string $on_deadline
 * @property int $default_task_status
 * @property int $show_review_modal
 * @property int $dashboard_clock
 * @property int $taskboard_length
 * @property string|null $favicon
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $dark_logo_url
 * @property-read mixed $favicon_url
 * @property-read mixed $icon
 * @property-read mixed $light_logo_url
 * @property-read mixed $masked_default_logo
 * @property-read mixed $login_background_url
 * @property-read mixed $logo_url
 * @property-read mixed $moment_date_format
 * @property-read mixed $show_public_message
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereActiveTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAfterDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAppDebug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereBeforeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCompanyEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCompanyPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCurrencyConverterKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDashboardClock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDateFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDatePickerFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDefaultTaskStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereFavicon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleMapKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV2SecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV2SiteKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV2Status($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV3SecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV3SiteKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV3Status($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereHideCronMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLeavesStartFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLoginBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLogoBackgroundColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereMomentFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereOnDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting wherePurchaseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereRoundedTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereShowReviewModal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSupportedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSystemUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTaskSelf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTaskboardLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTimeFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereWeatherKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereWebsite($value)
 * @property int $ticket_form_google_captcha
 * @property int $lead_form_google_captcha
 * @property string|null $last_cron_run
 * @property string $auth_theme
 * @property string|null $light_logo
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAuthTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLastCronRun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLeadFormGoogleCaptcha($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLightLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTicketFormGoogleCaptcha($value)
 * @property string $sidebar_logo_style
 * @property string $session_driver
 * @property int $allow_client_signup
 * @property int $admin_client_signup_approval
 * @property string|null $allowed_file_types
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAdminClientSignupApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAllowClientSignup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAllowedFileTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSessionDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSidebarLogoStyle($value)
 * @property string $google_calendar_status
 * @property string|null $google_client_id
 * @property string|null $google_client_secret
 * @property string $google_calendar_verification_status
 * @property string|null $google_id
 * @property string|null $name
 * @property string|null $token
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleCalendarStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleCalendarVerificationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereToken($value)
 * @property int $allowed_file_size
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAllowedFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereCurrencyKeyVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereGlobalAppName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereLicenseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereMomentDateFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereRtl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereShowUpdatePopup($value)
 * @property string $header_color
 * @property string|null $hash
 * @property string|null $last_license_verified_at
 * @property int $datatable_row_limit
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereDatatableRowLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereHeaderColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereLastLicenseVerifiedAt($value)
 * @property string $auth_theme_text
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereAuthThemeText($value)
 * @property string $sign_up_terms
 * @property string|null $terms_link
 * @property int $allow_max_no_of_files
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereAllowMaxNoOfFiles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereSignUpTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereTermsLink($value)
 * @property string|null $purchased_on
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting wherePurchasedOn($value)
 * @mixin \Eloquent
 * @property string $company_email
 * @property string|null $company_phone
 * @property int $front_design
 * @property int $email_verification
 * @property string|null $logo_front
 * @property int $login_ui
 * @property string|null $auth_css
 * @property string|null $auth_css_theme_two
 * @property string|null $new_company_locale
 * @property int $frontend_disable
 * @property string $setup_homepage
 * @property string|null $custom_homepage_url
 * @property string|null $expired_message
 * @property int $enable_register
 * @property int $registration_open
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereAuthCss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereAuthCssThemeTwo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereCustomHomepageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereEmailVerification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereEnableRegister($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereExpiredMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereFrontDesign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereFrontendDisable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereLoginUi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereLogoFront($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereNewCompanyLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereRegistrationOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereSetupHomepage($value)
 * @property string|null $dedicated_subdomain
 * @property string $sign_up_phone_field
 * @property string $sign_up_phone_required
 * @property int $company_need_approval
 * @property-read mixed $logo_front_url
 * @property-read mixed $masked_dark_logo_url
 * @property-read mixed $masked_favicon_url
 * @property-read mixed $masked_light_logo_url
 * @property-read mixed $masked_login_background_url
 * @property-read mixed $masked_logo_front_url
 * @property-read mixed $masked_logo_url
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereCompanyNeedApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereDedicatedSubdomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereSignUpPhoneField($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSetting whereSignUpPhoneRequired($value)
 */
	class GlobalSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GoogleCalendarModule
 *
 * @property int $id
 * @property int $lead_status
 * @property int $leave_status
 * @property int $invoice_status
 * @property int $contract_status
 * @property int $task_status
 * @property int $event_status
 * @property int $holiday_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule query()
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereContractStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereEventStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereHolidayStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereInvoiceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereLeadStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereLeaveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereTaskStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|GoogleCalendarModule whereCompanyId($value)
 * @mixin \Eloquent
 */
	class GoogleCalendarModule extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Holiday
 *
 * @package App\Models
 * @property int $id
 * @property \Illuminate\Support\Carbon $date
 * @property string|null $occassion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User|null $addedBy
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday query()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereOccassion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereUpdatedAt($value)
 * @property string|null $event_id
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereEventId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereCompanyId($value)
 * @property-read \App\Models\Holiday|null $hdate
 * @property-read \App\Models\Leave|null $ldate
 * @mixin \Eloquent
 * @property string|null $department_id_json
 * @property string|null $designation_id_json
 * @property string|null $employment_type_json
 * @property string $notification_sent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeDetails> $employee
 * @property-read int|null $employee_count
 * @property-read \App\Models\EmployeeDetails|null $employeeDetails
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereDepartmentIdJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereDesignationIdJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereEmploymentTypeJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereNotificationSent($value)
 */
	class Holiday extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Invoice
 *
 * @property int $id
 * @property int|null $project_id
 * @property int|null $quickbooks_invoice_id
 * @property int|null $client_id
 * @property string $invoice_number
 * @property string $project_name
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon $due_date
 * @property float $sub_total
 * @property float $discount
 * @property string $discount_type
 * @property float $total
 * @property int|null $currency_id
 * @property int|null $default_currency_id
 * @property double|null $exchange_rate
 * @property string $status
 * @property string $recurring
 * @property int|null $billing_cycle
 * @property int|null $billing_interval
 * @property string|null $billing_frequency
 * @property string|null $file
 * @property string|null $file_original_name
 * @property string|null $note
 * @property int $credit_note
 * @property string $show_shipping_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $estimate_id
 * @property int $send_status
 * @property float $due_amount
 * @property float $amount
 * @property int|null $parent_id
 * @property int|null $invoice_recurring_id
 * @property int|null $created_by
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User|null $client
 * @property-read \App\Models\ClientDetails|null $clientdetails
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CreditNotes[] $creditNotes
 * @property-read int|null $credit_notes_count
 * @property-read \App\Models\Currency|null $currency
 * @property-read \App\Models\Estimate|null $estimate
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $issue_on
 * @property-read mixed $total_amount
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoiceItems[] $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[] $payment
 * @property-read int|null $payment_count
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection|Invoice[] $recurrings
 * @property-read int|null $recurrings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice pending()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereBillingCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereBillingFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereBillingInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereCreditNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereDueAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereFileOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereInvoiceRecurringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereSendStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereShowShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereUpdatedAt($value)
 * @property int|null $order_id
 * @property string|null $hash
 * @property-read \App\Models\Order|null $order
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereOrderId($value)
 * @property string $calculate_tax
 * @property int|null $company_address_id
 * @property-read \App\Models\CompanyAddress|null $address
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereCalculateTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereCompanyAddressId($value)
 * @property string|null $event_id
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereEventId($value)
 * @property int|null $company_id
 * @property string|null $custom_invoice_number
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereCustomInvoiceNumber($value)
 * @property int|null $bank_account_id
 * @property \Illuminate\Support\Carbon|null $last_viewed
 * @property string|null $ip_address
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereDefaultCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereLastViewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereQuickbooksInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereUnitId($value)
 * @property string $payment_status
 * @property string|null $downloadable_file
 * @property string|null $default_image
 * @property int|null $offline_method_id
 * @property string|null $transaction_id
 * @property string|null $gateway
 * @property-read \App\Models\BankAccount|null $bankAccount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceFiles> $files
 * @property-read int|null $files_count
 * @property-read mixed $download_file_url
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereOfflineMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereTransactionId($value)
 * @property string|null $original_invoice_number
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereOriginalInvoiceNumber($value)
 * @mixin \Eloquent
 * @property int|null $invoice_payment_id
 * @property string $is_timelog_invoice
 * @property-read \App\Models\InvoicePaymentDetail|null $invoicePaymentDetail
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereInvoicePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereIsTimelogInvoice($value)
 */
	class Invoice extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InvoiceFiles
 *
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read \App\Models\Invoice|null $invoice
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles query()
 * @property int $id
 * @property int $invoice_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property string|null $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceFiles whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class InvoiceFiles extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InvoiceItemImage
 *
 * @property int $id
 * @property int $invoice_item_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $image
 * @property string|null $size
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereInvoiceItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereUpdatedAt($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class InvoiceItemImage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InvoiceItems
 *
 * @property int $id
 * @property int $invoice_id
 * @property int|null $quickbooks_item_id
 * @property string $item_name
 * @property string|null $item_summary
 * @property string $type
 * @property float $quantity
 * @property float $unit_price
 * @property float $amount
 * @property string|null $taxes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $hsn_sac_code
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereUpdatedAt($value)
 * @property-read \App\Models\InvoiceItemImage|null $invoiceItemImage
 * @property-read mixed $tax_list
 * @property int|null $product_id
 * @property int|null $unit_id
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereUnitId($value)
 * @property-read \App\Models\Invoice $invoice
 * @mixin \Eloquent
 * @property int $field_order
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereFieldOrder($value)
 */
	class InvoiceItems extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InvoicePaymentDetail
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $title
 * @property string|null $image
 * @property string|null $payment_details
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $image_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read mixed $masked_image_url
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentDetail whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentDetail whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentDetail wherePaymentDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentDetail whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoicePaymentDetail whereUpdatedAt($value)
 */
	class InvoicePaymentDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InvoiceSetting
 *
 * @property int $id
 * @property string $invoice_prefix
 * @property int $invoice_digit
 * @property string $estimate_prefix
 * @property int $estimate_digit
 * @property string $credit_note_prefix
 * @property int $credit_note_digit
 * @property string $template
 * @property int $due_after
 * @property string $invoice_terms
 * @property string|null $other_info
 * @property string|null $gst_number
 * @property string|null $show_gst
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $logo
 * @property int $send_reminder
 * @property string|null $locale
 * @property int $hsn_sac_code_show
 * @property-read mixed $icon
 * @property-read mixed $logo_url
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereCreditNoteDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereCreditNotePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereDueAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereEstimateDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereEstimatePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereGstNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereHsnSacCodeShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereInvoiceDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereInvoicePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereInvoiceTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereSendReminder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowGst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereUpdatedAt($value)
 * @property string|null $estimate_terms
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereEstimateTerms($value)
 * @property int $tax_calculation_msg
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereTaxCalculationMsg($value)
 * @property int|null $company_id
 * @property string|null $reminder
 * @property int $send_reminder_after
 * @property int $show_project
 * @property string|null $show_client_name
 * @property string|null $show_client_email
 * @property string|null $show_client_phone
 * @property string|null $show_client_company_address
 * @property string|null $show_client_company_name
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereReminder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereSendReminderAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowClientCompanyAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowClientCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowClientEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowClientPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowProject($value)
 * @property string $invoice_number_separator
 * @property string $estimate_number_separator
 * @property string $credit_note_number_separator
 * @property string $contract_prefix
 * @property string $contract_number_separator
 * @property int $contract_digit
 * @property int $show_status
 * @property int $authorised_signatory
 * @property string|null $authorised_signatory_signature
 * @property-read mixed $authorised_signatory_signature_url
 * @property-read mixed $is_chinese_lang
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereAuthorisedSignatory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereAuthorisedSignatorySignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereContractDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereContractNumberSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereContractPrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereCreditNoteNumberSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereEstimateNumberSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereInvoiceNumberSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereShowStatus($value)
 * @property string $order_prefix
 * @property string $order_number_separator
 * @property int $order_digit
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereOrderDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereOrderNumberSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereOrderPrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereOtherInfo($value)
 * @mixin \Eloquent
 * @property string $estimate_request_prefix
 * @property string $estimate_request_number_separator
 * @property int $estimate_request_digit
 * @property string $proposal_prefix
 * @property string $proposal_number_separator
 * @property int $proposal_digit
 * @property-read mixed $masked_authorised_signatory_signature_url
 * @property-read mixed $masked_logo_url
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereEstimateRequestDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereEstimateRequestNumberSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereEstimateRequestPrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereProposalDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereProposalNumberSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceSetting whereProposalPrefix($value)
 */
	class InvoiceSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Issue
 *
 * @property int $id
 * @property string $description
 * @property int|null $user_id
 * @property int|null $project_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder|Issue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Issue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Issue query()
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereCompanyId($value)
 * @mixin \Eloquent
 */
	class Issue extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\KnowledgeBase
 *
 * @property protected $appends
 * @property int $id
 * @property string $to
 * @property string $heading
 * @property int|null $category_id
 * @property string|null $description
 * @property int $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\KnowledgeBaseCategory|null $knowledgebasecategory
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase query()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereHeading($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\KnowledgeBaseFile[] $files
 * @property-read int|null $files_count
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereCompanyId($value)
 * @mixin \Eloquent
 */
	class KnowledgeBase extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\KnowledgeBaseCategory
 *
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\KnowledgeBase[] $knowledgebase
 * @property-read int|null $knowledgebase_count
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereCompanyId($value)
 * @mixin \Eloquent
 */
	class KnowledgeBaseCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\KnowledgeBaseFile
 *
 * @property protected $appends
 * @property int $id
 * @property int|null $company_id
 * @property int $knowledge_base_id
 * @property string|null $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $external_link_name
 * @property string|null $external_link
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereKnowledgeBaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class KnowledgeBaseFile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LanguageSetting
 *
 * @property int $id
 * @property string $language_code
 * @property string $language_name
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereLanguageCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereLanguageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereUpdatedAt($value)
 * @property string|null $flag_code
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereFlagCode($value)
 * @property-read mixed $label
 * @property boolean $is_rtl
 * @mixin \Eloquent
 * @property-read mixed $label
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereIsRtl($value)
 */
	class LanguageSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Lead
 *
 * @property int $id
 * @property int|null $client_id
 * @property int|null $source_id
 * @property int|null $status_id
 * @property int $column_priority
 * @property int|null $agent_id
 * @property string|null $company_name
 * @property string|null $website
 * @property string|null $address
 * @property string|null $salutation
 * @property string $client_name
 * @property string $client_email
 * @property string|null $mobile
 * @property string|null $cell
 * @property string|null $office
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $postal_code
 * @property string|null $note
 * @property string $next_follow_up
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $value
 * @property float|null $total_value
 * @property int|null $currency_id
 * @property int|null $category_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User|null $client
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DealFile[] $files
 * @property-read int|null $files_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DealFollowUp[] $follow
 * @property-read int|null $follow_count
 * @property-read \App\Models\DealFollowUp|null $followup
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $image_url
 * @property-read \App\Models\LeadAgent|null $leadAgent
 * @property-read \App\Models\LeadSource|null $leadSource
 * @property-read \App\Models\LeadStatus|null $leadStatus
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\LeadFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lead newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lead query()
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCell($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereClientEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereColumnPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereNextFollowUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereSalutation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereWebsite($value)
 * @property string|null $hash
 * @property-read \App\Models\LeadCategory|null $category
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereHash($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read int|null $follow_up_date_next
 * @property-read int|null $follow_up_date_past
 * @mixin \Eloquent
 * @property int|null $lead_owner
 * @property-read \App\Models\User|null $addedBy
 * @property-read mixed $client_name_salutation
 * @property-read \App\Models\User|null $leadOwner
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereLeadOwner($value)
 */
	class Lead extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeadAgent
 *
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lead> $leads
 * @property-read int|null $leads_count
 * @mixin \Eloquent
 * @property int|null $lead_category_id
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereLeadCategoryId($value)
 */
	class LeadAgent extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeadCategory
 *
 * @property int $id
 * @property string $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereCompanyId($value)
 * @mixin \Eloquent
 * @property int $is_default
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LeadAgent> $enabledAgents
 * @property-read int|null $enabled_agents_count
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereIsDefault($value)
 */
	class LeadCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeadCustomForm
 *
 * @property int $id
 * @property string $field_display_name
 * @property string $field_name
 * @property int $field_order
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereFieldDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereFieldName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereFieldOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereUpdatedAt($value)
 * @property int $required
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereRequired($value)
 * @property int|null $company_id
 * @property int|null $custom_fields_id
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\CustomField|null $customField
 * @property-read mixed $extras
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereCustomFieldsId($value)
 * @mixin \Eloquent
 */
	class LeadCustomForm extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ClientNote
 *
 * @property int $id
 * @property int|null $client_id
 * @property string $title
 * @property int $type
 * @property int|null $member_id
 * @property int $is_client_show
 * @property int $ask_password
 * @property string $details
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereAskPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereIsClientShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientNote whereUpdatedAt($value)
 * @property-read \App\Models\User|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ClientUserNote[] $members
 * @property-read int|null $members_count
 * @property int|null $lead_id
 * @property int $is_lead_show
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote whereIsLeadShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote whereLeadId($value)
 * @mixin \Eloquent
 */
	class LeadNote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeadCategory
 *
 * @property int $id
 * @property string $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereCompanyId($value)
 * @property string|null $name
 * @property string|null $slug
 * @property int $priority
 * @property string $label_color
 * @property int $default
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deal> $deals
 * @property-read int|null $deals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PipelineStage> $stages
 * @property-read int|null $stages_count
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereSlug($value)
 * @mixin \Eloquent
 */
	class LeadPipeline extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeadStatus
 *
 * @property int $id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $priority
 * @property int $default
 * @property string $label_color
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lead[] $leads
 * @property-read int|null $leads_count
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereCompanyId($value)
 * @property int|null $lead_pipeline_id
 * @property int|null $pipeline_stages_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deal> $deals
 * @property-read int|null $deals_count
 * @property-read \App\Models\LeadPipeline|null $pipeline
 * @property-read \App\Models\PipelineStage $stage
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages whereLeadPipelineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages wherePipelineStagesId($value)
 * @mixin \Eloquent
 */
	class LeadPipelineStages extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeadProduct
 *
 * @property int $id
 * @property int $deal_id
 * @property int $product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereDealId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Deal|null $lead
 */
	class LeadProduct extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeadSetting
 *
 * @property int $id
 * @property int $status
 * @property int $user_id
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $ticket_round_robin_status
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSetting whereTicketRoundRobinStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSetting whereUserId($value)
 */
	class LeadSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeadSource
 *
 * @property int $id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lead[] $leads
 * @property-read int|null $leads_count
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereCompanyId($value)
 * @mixin \Eloquent
 */
	class LeadSource extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeadStatus
 *
 * @property int $id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $priority
 * @property int $default
 * @property string $label_color
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lead[] $leads
 * @property-read int|null $leads_count
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereCompanyId($value)
 * @mixin \Eloquent
 */
	class LeadStatus extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeadUserNote
 *
 * @property int $id
 * @property int $user_id
 * @property int $lead_note_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote whereLeadNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote whereUserId($value)
 * @mixin \Eloquent
 */
	class LeadUserNote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Leave
 *
 * @property int $id
 * @property int $user_id
 * @property int $leave_type_id
 * @property int $count
 * @property int $halfday
 * @property string $duration
 * @property \Illuminate\Support\Carbon $leave_date
 * @property string $reason
 * @property string $status
 * @property string|null $reject_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $paid
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $date
 * @property-read mixed $icon
 * @property-read mixed $leaves_taken_count
 * @property-read \App\Models\LeaveType $type
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\LeaveFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave query()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereLeaveDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereLeaveTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave wherePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereRejectReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereUserId($value)
 * @property string|null $event_id
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereEventId($value)
 * @property int|null $company_id
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $half_day_type
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereHalfDayType($value)
 * @property string|null $manager_status_permission
 * @property string|null $approve_reason
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereApproveReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereManagerStatusPermission($value)
 * @property string|null $unique_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LeaveFile> $files
 * @property-read int|null $files_count
 * @property-read \App\Models\Leave|null $ldate
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereUniqueId($value)
 * @mixin \Eloquent
 * @property int $over_utilized
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereOverUtilized($value)
 */
	class Leave extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeaveFile
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $user_id
 * @property int $leave_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read \App\Models\Leave $leave
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereLeaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereUserId($value)
 * @mixin \Eloquent
 */
	class LeaveFile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeaveSetting
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $manager_permission
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting whereManagerPermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class LeaveSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeaveType
 *
 * @property int $id
 * @property string $type_name
 * @property string $color
 * @property int $no_of_leaves
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $paid
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Leave[] $leaves
 * @property-read int|null $leaves_count
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereNoOfLeaves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType wherePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereTypeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereUpdatedAt($value)
 * @property int|null $company_id
 * @property int $monthly_limit
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Leave[] $leavesCount
 * @property-read int|null $leaves_count_count
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereMonthlyLimit($value)
 * @property int|null $effective_after
 * @property string|null $effective_type
 * @property string|null $unused_leave
 * @property int $encashed
 * @property int $allowed_probation
 * @property int $allowed_notice
 * @property string|null $gender
 * @property string|null $marital_status
 * @property string|null $department
 * @property string|null $designation
 * @property string|null $role
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereAllowedNotice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereAllowedProbation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereDesignation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereEffectiveAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereEffectiveType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereEncashed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereUnusedLeave($value)
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $leavetype
 * @property string $over_utilization
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereLeavetype($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereOverUtilization($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType withoutTrashed()
 */
	class LeaveType extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Holiday
 *
 * @package App\Models
 * @property int $id
 * @property string $log_time_for
 * @property string $auto_timer_stop
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $approval_required
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor query()
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereApprovalRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereAutoTimerStop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereLogTimeFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereCompanyId($value)
 * @property int $tracker_reminder
 * @property int $timelog_report
 * @property string|null $daily_report_roles
 * @property string|null $time
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereDailyReportRoles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereTimelogReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereTrackerReminder($value)
 * @mixin \Eloquent
 */
	class LogTimeFor extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskUser
 *
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereUserId($value)
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property int|null $task_comment_id
 * @property int|null $task_note_id
 * @property int|null $project_id
 * @property int|null $project_note_id
 * @property int|null $discussion_id
 * @property int|null $discussion_reply_id
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereDiscussionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereDiscussionReplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereProjectNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereTaskCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereTaskNoteId($value)
 * @property int|null $ticket_id
 * @property int|null $event_id
 * @property int|null $user_chat_id
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereUserChatId($value)
 * @mixin \Eloquent
 */
	class MentionUser extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Menu
 *
 * @property int $id
 * @property string $menu_name
 * @property string|null $translate_name
 * @property string|null $route
 * @property string|null $module
 * @property string|null $icon
 * @property int|null $setting_menu
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereMenuName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereSettingMenu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereTranslateName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereCompanyId($value)
 * @mixin \Eloquent
 */
	class Menu extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MessageSetting
 *
 * @property int $id
 * @property string $allow_client_admin
 * @property string $allow_client_employee
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereAllowClientAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereAllowClientEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereUpdatedAt($value)
 * @property int|null $company_id
 * @property string $restrict_client
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereRestrictClient($value)
 * @property int $send_sound_notification
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereSendSoundNotification($value)
 * @mixin \Eloquent
 */
	class MessageSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Module
 *
 * @property int $id
 * @property string $module_name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $customPermissions
 * @property-read int|null $custom_permissions_count
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Module newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Module newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Module query()
 * @method static \Illuminate\Database\Eloquent\Builder|Module whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Module whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Module whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Module whereModuleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Module whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissionsAll
 * @property-read int|null $permissions_all_count
 * @mixin \Eloquent
 * @property int $is_superadmin
 * @method static \Illuminate\Database\Eloquent\Builder|Module whereIsSuperadmin($value)
 */
	class Module extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ModuleSetting
 *
 * @property int $id
 * @property string $module_name
 * @property string $status
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereModuleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereCompanyId($value)
 * @mixin \Eloquent
 * @property int $is_allowed
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereIsAllowed($value)
 */
	class ModuleSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Notice
 *
 * @property int $id
 * @property string $to
 * @property string $heading
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $department_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Team|null $department
 * @property-read mixed $icon
 * @property-read mixed $notice_date
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NoticeView[] $member
 * @property-read int|null $member_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\NoticeFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notice query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereHeading($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereCompanyId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NoticeFile> $files
 * @property-read int|null $files_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NoticeBoardUser> $noticeClients
 * @property-read int|null $notice_clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NoticeBoardUser> $noticeEmployees
 * @property-read int|null $notice_employees_count
 */
	class Notice extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\NoticeBoardUser
 *
 * @property int $id
 * @property int $notice_id
 * @property string $type
 * @property int $user_id
 * @property-read \App\Models\Notice $clients
 * @property-read \App\Models\Notice $employees
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser whereNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser whereUserId($value)
 */
	class NoticeBoardUser extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\NoticeFile
 *
 * @property int $id
 * @property int $notice_id
 * @property string $filename
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $dropbox_link
 * @property string|null $external_link
 * @property string|null $external_link_name
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereUpdatedAt($value)
 */
	class NoticeFile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\NoticeView
 *
 * @property int $id
 * @property int $notice_id
 * @property int $user_id
 * @property int $read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereCompanyId($value)
 * @mixin \Eloquent
 */
	class NoticeView extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Notification
 *
 * @property int $id
 * @property string $type
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property string $data
 * @property string|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereNotifiableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereNotifiableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OfflinePaymentMethod
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod active()
 * @mixin \Eloquent
 * @property string|null $image
 * @property-read mixed $image_url
 * @property-read mixed $masked_image_url
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereImage($value)
 */
	class OfflinePaymentMethod extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Order
 *
 * @property int $id
 * @property int|null $client_id
 * @property string $order_date
 * @property float $sub_total
 * @property float $total
 * @property float $due_amount
 * @property string $status
 * @property int|null $currency_id
 * @property string $show_shipping_address
 * @property string|null $note
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $client
 * @property-read \App\Models\ClientDetails|null $clientdetails
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OrderItems[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoice
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[] $payment
 * @property-read int|null $payment_count
 * @property-read \App\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $recurrings
 * @property-read int|null $recurrings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDueAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShowShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @property mixed $order_number
 * @property float $discount
 * @property string $discount_type
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDiscountType($value)
 * @property int|null $company_id
 * @property int|null $company_address_id
 * @property-read \App\Models\CompanyAddress|null $address
 * @property-read \App\Models\Company|null $company
 * @property int|null $unit_id
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCompanyAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderNumber($value)
 * @property-read \App\Models\UnitType $unit
 * @property int|null $unit_id
 * @property string|null $custom_order_number
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCustomOrderNumber($value)
 * @property string|null $original_order_number
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOriginalOrderNumber($value)
 * @mixin \Eloquent
 * @property int|null $project_id
 * @property-read mixed $extras
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereProjectId($value)
 */
	class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InvoiceItems
 *
 * @property int $id
 * @property int $invoice_id
 * @property int|null $quickbooks_item_id
 * @property string $item_name
 * @property string|null $item_summary
 * @property string $type
 * @property float $quantity
 * @property float $unit_price
 * @property float $amount
 * @property string|null $taxes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $hsn_sac_code
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItems whereUpdatedAt($value)
 * @property-read \App\Models\InvoiceItemImage|null $invoiceItemImage
 * @property-read mixed $tax_list
 * @property int|null $product_id
 * @property int|null $unit_id
 * @property-read \App\Models\UnitType|null $unit
 * @property int|null $client_id
 * @property string|null $description
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCart whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCart whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCart whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderCart whereUnitId($value)
 * @mixin \Eloquent
 */
	class OrderCart extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OrderItemImage
 *
 * @property int $id
 * @property int|null $order_item_id
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage whereOrderItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItemImage whereUpdatedAt($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class OrderItemImage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OrderItems
 *
 * @property int $id
 * @property int $order_id
 * @property string $item_name
 * @property string|null $item_summary
 * @property string $type
 * @property int $quantity
 * @property int $unit_price
 * @property float $amount
 * @property string|null $hsn_sac_code
 * @property string|null $taxes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereUpdatedAt($value)
 * @property int|null $product_id
 * @property-read \App\Models\OrderItemImage|null $orderItemImage
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereProductId($value)
 * @property-read mixed $tax_list
 * @property int|null $product_id
 * @property int|null $unit_id
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereUnitId($value)
 * @property string|null $sku
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereSku($value)
 * @mixin \Eloquent
 * @property int $field_order
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereFieldOrder($value)
 */
	class OrderItems extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PackageUpdateNotify
 *
 * @property int $id
 * @property int $company_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify query()
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify whereUserId($value)
 */
	class PackageUpdateNotify extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Passport
 *
 * @property int $id
 * @property int|null $company_id
 * @property int|null $user_id
 * @property int|null $country_id
 * @property int|null $added_by
 * @property string $passport_number
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon $expiry_date
 * @property string|null $file
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Country|null $country
 * @property-read mixed $image_url
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Passport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Passport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Passport query()
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport wherePassportNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereUserId($value)
 * @mixin \Eloquent
 * @property int $alert_before_months
 * @method static \Illuminate\Database\Eloquent\Builder|Passport whereAlertBeforeMonths($value)
 */
	class Passport extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Payment
 *
 * @property int $id
 * @property string $date
 * @property string $exchange_rate
 * @property string $total
 * @property string $project_name
 * @property int|null $project_id
 * @property int|null $invoice_id
 * @property float $amount
 * @property string|null $gateway
 * @property string|null $transaction_id
 * @property string $exchange_rate
 * @property float $price
 * @property float $default_currency_price
 * @property int|null $currency_id
 * @property string|null $plan_id
 * @property string|null $customer_id
 * @property string|null $client_id
 * @property string|null $event_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $paid_on
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $offline_method_id
 * @property string|null $bill
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property int|null $default_currency_id
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read mixed $paid_date
 * @property-read mixed $total_amount
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \App\Models\OfflinePaymentMethod|null $offlineMethod
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereBill($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereOfflineMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePaidOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUpdatedAt($value)
 * @property int|null $order_id
 * @property string|null $payment_gateway_response null = success
 * @method static \Illuminate\Database\Eloquent\Builder|Payment completed()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePaymentGatewayResponse($value)
 * @property int|null $credit_notes_id
 * @property string|null $payload_id
 * @property-read \App\Models\CreditNotes|null $creditNote
 * @property-read \App\Models\Order|null $order
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreditNotesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePayloadId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCompanyId($value)
 * @property int|null $bank_account_id
 * @property int|null $quickbooks_payment_id
 * @property-read OfflinePaymentMethod|null $offlineMethods
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BankTransaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereDefaultCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereQuickbooksPaymentId($value)
 * @mixin \Eloquent
 */
	class Payment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PaymentGatewayCredentials
 *
 * @property int $id
 * @property string|null $paypal_client_id
 * @property string|null $paypal_secret
 * @property string $paypal_status
 * @property string|null $stripe_client_id
 * @property string|null $stripe_secret
 * @property string|null $stripe_webhook_secret
 * @property string $stripe_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $razorpay_key
 * @property string|null $razorpay_secret
 * @property string $razorpay_status
 * @property string $paypal_mode
 * @property string|null $sandbox_paypal_client_id
 * @property string|null $sandbox_paypal_secret
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePaypalClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePaypalMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePaypalSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePaypalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereRazorpayKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereRazorpaySecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereRazorpayStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereSandboxPaypalClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereSandboxPaypalSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereStripeClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereStripeSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereStripeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereStripeWebhookSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereUpdatedAt($value)
 * @property string|null $live_stripe_client_id
 * @property string|null $live_stripe_secret
 * @property string|null $live_stripe_webhook_secret
 * @property string|null $live_razorpay_key
 * @property string|null $live_razorpay_secret
 * @property string|null $test_stripe_client_id
 * @property string|null $test_stripe_secret
 * @property string|null $test_razorpay_key
 * @property string|null $test_razorpay_secret
 * @property string|null $test_stripe_webhook_secret
 * @property string $stripe_mode
 * @property string $razorpay_mode
 * @property string|null $paystack_key
 * @property string|null $paystack_secret
 * @property string|null $paystack_status
 * @property string|null $paystack_merchant_email
 * @property string|null $paystack_payment_url
 * @property string|null $mollie_api_key
 * @property string|null $mollie_status
 * @property string|null $payfast_merchant_id
 * @property string|null $payfast_merchant_key
 * @property string|null $payfast_passphrase
 * @property string $payfast_mode
 * @property string|null $payfast_status
 * @property string|null $authorize_api_login_id
 * @property string|null $authorize_transaction_key
 * @property string $authorize_environment
 * @property string $authorize_status
 * @property string|null $square_application_id
 * @property string|null $square_access_token
 * @property string|null $square_location_id
 * @property string $square_environment
 * @property string $square_status
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereAuthorizeApiLoginId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereAuthorizeEnvironment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereAuthorizeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereAuthorizeTransactionKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereLiveRazorpayKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereLiveRazorpaySecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereLiveStripeClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereLiveStripeSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereLiveStripeWebhookSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereMollieApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereMollieStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePayfastMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePayfastMerchantKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePayfastMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePayfastPassphrase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePayfastStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePaystackKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePaystackMerchantEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePaystackPaymentUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePaystackSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePaystackStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereRazorpayMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereSquareAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereSquareApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereSquareEnvironment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereSquareLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereSquareStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereStripeMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestRazorpayKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestRazorpaySecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestStripeClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestStripeSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestStripeWebhookSecret($value)
 * @property string $paystack_mode
 * @property string|null $test_paystack_key
 * @property string|null $test_paystack_secret
 * @property string|null $test_paystack_merchant_email
 * @property string $flutterwave_status
 * @property string $flutterwave_mode
 * @property string|null $test_flutterwave_key
 * @property string|null $test_flutterwave_secret
 * @property string|null $test_flutterwave_hash
 * @property string|null $live_flutterwave_key
 * @property string|null $live_flutterwave_secret
 * @property string|null $live_flutterwave_hash
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereFlutterwaveMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereFlutterwaveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereLiveFlutterwaveHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereLiveFlutterwaveKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereLiveFlutterwaveSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials wherePaystackMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestFlutterwaveHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestFlutterwaveKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestFlutterwaveSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestPaystackKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestPaystackMerchantEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestPaystackSecret($value)
 * @property int|null $company_id
 * @property string|null $flutterwave_webhook_secret_hash
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereFlutterwaveWebhookSecretHash($value)
 * @property string|null $test_payfast_merchant_id
 * @property string|null $test_payfast_merchant_key
 * @property string|null $test_payfast_passphrase
 * @property-read mixed $show_pay
 * @property-read bool $show_pay_webhook
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestPayfastMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestPayfastMerchantKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestPayfastPassphrase($value)
 * @mixin \Eloquent
 * @property string|null $live_razorpay_webhook_secret
 * @property string|null $test_razorpay_webhook_secret
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereLiveRazorpayWebhookSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGatewayCredentials whereTestRazorpayWebhookSecret($value)
 */
	class PaymentGatewayCredentials extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Permission
 *
 * @property int $id
 * @property string $name
 * @property string|null $display_name
 * @property string|null $description
 * @property int $module_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_custom
 * @property-read \App\Models\Module $module
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereIsCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereUpdatedAt($value)
 * @property string|null $allowed_permissions
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereAllowedPermissions($value)
 * @mixin \Eloquent
 */
	class Permission extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PermissionRole
 *
 * @property int $permission_id
 * @property int $role_id
 * @property int $permission_type_id
 * @property-read mixed $icon
 * @property-read \App\Models\PermissionType $permissionType
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole wherePermissionTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole whereRoleId($value)
 * @property-read \App\Models\Permission $permission
 * @mixin \Eloquent
 */
	class PermissionRole extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PermissionType
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType query()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType ofType($type)
 * @mixin \Eloquent
 */
	class PermissionType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Pinned
 *
 * @property int $id
 * @property int|null $project_id
 * @property int|null $task_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\Task|null $task
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereCompanyId($value)
 * @mixin \Eloquent
 * @property int|null $added_by
 * @method static \Illuminate\Database\Eloquent\Builder|Pinned whereAddedBy($value)
 */
	class Pinned extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PipelineStage
 *
 * @property int $id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $priority
 * @property int $default
 * @property string $label_color
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lead[] $leads
 * @property-read int|null $leads_count
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage query()
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereCompanyId($value)
 * @property int|null $lead_pipeline_id
 * @property string|null $name
 * @property string|null $slug
 * @property-read \App\Models\LeadPipeline|null $pipeline
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereLeadPipelineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PipelineStage whereSlug($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deal> $deals
 * @property-read int|null $deals_count
 */
	class PipelineStage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $name
 * @property string $price
 * @property string|null $taxes
 * @property int $allow_purchase
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $description
 * @property int|null $unit_id
 * @property int|null $category_id
 * @property int|null $sub_category_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property string|null $hsn_sac_code
 * @property string|null $sku
 * @property-read mixed $icon
 * @property-read mixed $total_amount
 * @property-read \App\Models\Tax $tax
 * @method static \Database\Factories\ProductFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereAllowPurchase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSubCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @property-read \App\Models\ProductCategory|null $category
 * @property string|null $image
 * @property-read mixed $image_url
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereImage($value)
 * @property int $downloadable
 * @property string|null $downloadable_file
 * @property string|null $default_image
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductFiles[] $files
 * @property-read int|null $files_count
 * @property-read mixed $download_file_url
 * @property-read mixed $extras
 * @property-read \App\Models\ProductSubCategory|null $subCategory
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDefaultImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDownloadable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDownloadableFile($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $tax_list
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lead> $leads
 * @property-read int|null $leads_count
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUnitId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItems> $orderItem
 * @property-read int|null $order_item_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lead> $leads
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItems> $orderItem
 * @property string|null $purchase_price
 * @property string $purchase_information
 * @property string $track_inventory
 * @property string|null $sales_description
 * @property string|null $purchase_description
 * @property int|null $opening_stock
 * @property float|null $rate_per_unit
 * @property string|null $sku
 * @property string|null $type
 * @property string $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lead> $leads
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItems> $orderItem
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereOpeningStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePurchaseDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePurchaseInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePurchasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereRatePerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSalesDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereTrackInventory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereType($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchaseStockAdjustment> $inventory
 * @property-read int|null $inventory_count
 * @mixin \Eloquent
 */
	class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProductCategory
 *
 * @property int $id
 * @property string $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ProductCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductCategory whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductSubCategory[] $subCategories
 * @property-read int|null $sub_categories_count
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ProductCategory whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ProductCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Holiday
 *
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property string $filename
 * @property string $hashname
 * @property string $size
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $dropbox_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereUserId($value)
 * @property int $product_id
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereProductId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereCompanyId($value)
 * @property int $default_status
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFiles whereDefaultStatus($value)
 * @mixin \Eloquent
 */
	class ProductFiles extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProductSubCategory
 *
 * @property int $id
 * @property int $category_id
 * @property string $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProductCategory $category
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSubCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSubCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSubCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSubCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSubCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSubCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSubCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSubCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSubCategory whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ProductSubCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Project
 *
 * @property int $id
 * @property string $project_name
 * @property string|null $project_summary
 * @property int|null $project_admin
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectNote[] $notes
 * @property int|null $category_id
 * @property int|null $client_id
 * @property int|null $team_id
 * @property string|null $feedback
 * @property string $manual_timelog
 * @property string $client_view_task
 * @property string $allow_client_notification
 * @property int $completion_percent
 * @property string $calculate_task_progress
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property float|null $project_budget
 * @property int|null $currency_id
 * @property float|null $hours_allocated
 * @property string $status
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\ProjectCategory|null $category
 * @property-read \App\Models\User|null $client
 * @property-read \App\Models\ClientDetails|null $clientdetails
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Discussion[] $discussions
 * @property-read int|null $discussions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Expense[] $expenses
 * @property-read int|null $expenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $is_project_admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Issue[] $issues
 * @property-read int|null $issues_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectMember[] $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $projectMembers
 * @property-read int|null $members_many_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectMilestone[] $milestones
 * @property-read int|null $milestones_count
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[] $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\ProjectRating|null $rating
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTimeLog[] $times
 * @property-read int|null $times_count
 * @method static \Illuminate\Database\Eloquent\Builder|Project canceled()
 * @method static \Illuminate\Database\Eloquent\Builder|Project completed()
 * @method static \Database\Factories\ProjectFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Project finished()
 * @method static \Illuminate\Database\Eloquent\Builder|Project inProcess()
 * @method static \Illuminate\Database\Eloquent\Builder|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project notStarted()
 * @method static \Illuminate\Database\Eloquent\Builder|Project onHold()
 * @method static \Illuminate\Database\Query\Builder|Project onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Project overdue()
 * @method static \Illuminate\Database\Eloquent\Builder|Project query()
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereAllowClientNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCalculateTaskProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereClientViewTask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCompletionPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereHoursAllocated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereManualTimelog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereProjectAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereProjectBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereProjectName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereProjectSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Project withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Project withoutTrashed()
 * @property string|null $hash
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereHash($value)
 * @property int $public
 * @method static \Illuminate\Database\Eloquent\Builder|Project wherePublic($value)
 * @property int|null $company_id
 * @property string|null $project_short_code
 * @property int $enable_miroboard
 * @property string|null $miro_board_id
 * @property int $client_access
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereClientAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereEnableMiroboard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereMiroBoardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereProjectShortCode($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Contract> $contracts
 * @property-read int|null $contracts_count
 * @property-read int|null $project_members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionNote
 * @property-read int|null $mention_note_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectMilestone> $incompleteMilestones
 * @property-read int|null $incomplete_milestones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionProject
 * @property-read int|null $mention_project_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read \App\Models\Project|null $due_date
 * @mixin \Eloquent
 * @property string $public_taskboard
 * @property string $public_gantt_chart
 * @property string $need_approval_by_admin
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectMilestone> $completedMilestones
 * @property-read int|null $completed_milestones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectDepartment> $departments
 * @property-read int|null $departments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectLabel> $label
 * @property-read int|null $label_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectLabelList> $labels
 * @property-read int|null $labels_count
 * @property-read \App\Models\User|null $projectAdmin
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $projectDepartments
 * @property-read int|null $project_departments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $projectMembersWithoutScope
 * @property-read int|null $project_members_without_scope_count
 * @method static \Illuminate\Database\Eloquent\Builder|Project notFinished()
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereNeedApprovalByAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project wherePublicGanttChart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project wherePublicTaskboard($value)
 */
	class Project extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectActivity
 *
 * @property int $id
 * @property int $project_id
 * @property string $activity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\Project $project
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity whereActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class ProjectActivity extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectCategory
 *
 * @property int $id
 * @property string $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $project
 * @property-read int|null $project_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ProjectCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectDepartment
 *
 * @property int $id
 * @property int $project_id
 * @property int $team_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team $department
 * @property-read \App\Models\Project $project
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment whereUpdatedAt($value)
 */
	class ProjectDepartment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectFile
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $dropbox_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $external_link_name
 * @property string|null $external_link
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read \App\Models\Project $project
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereUserId($value)
 * @property int|null $company_id
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ProjectFile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectLabel
 *
 * @property int $id
 * @property int $label_id
 * @property int $project_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\ProjectLabelList $label
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabel query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabel whereLabelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabel whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class ProjectLabel extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectLabelList
 *
 * @property int $id
 * @property string $label_name
 * @property string|null $color
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read mixed $label_color
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabelList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabelList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabelList query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabelList whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabelList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabelList whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabelList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabelList whereLabelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabelList whereUpdatedAt($value)
 * @property int|null $project_id
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabelList whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabelList whereCompanyId($value)
 * @mixin \Eloquent
 * @property int|null $added_by
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectLabelList whereAddedBy($value)
 */
	class ProjectLabelList extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectMember
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $hourly_rate
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereUserId($value)
 * @mixin \Eloquent
 */
	class ProjectMember extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectMilestone
 *
 * @property int $id
 * @property int|null $project_id
 * @property int|null $currency_id
 * @property string $milestone_title
 * @property string $summary
 * @property float $cost
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $invoice_created
 * @property int|null $invoice_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $icon
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereInvoiceCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereMilestoneTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereUpdatedAt($value)
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereStartDate($value)
 * @mixin \Eloquent
 * @property int|null $company_id
 * @property string $add_to_budget
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $completeTasks
 * @property-read int|null $complete_tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereAddToBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereCompanyId($value)
 */
	class ProjectMilestone extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectNote
 *
 * @property int $id
 * @property int|null $project_id
 * @property string $title
 * @property int $type
 * @property int|null $client_id
 * @property int $is_client_show
 * @property int $ask_password
 * @property string $details
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectUserNote[] $members
 * @property-read \App\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectUserNote[] $noteUsers
 * @property-read int|null $members_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote whereAskPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote whereIsClientShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectNote whereUpdatedAt($value)
 * @property-read int|null $note_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionNote
 * @property-read int|null $mention_note_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionNote
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @mixin \Eloquent
 */
	class ProjectNote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectRating
 *
 * @property int $id
 * @property int $project_id
 * @property float $rating
 * @property string|null $comment
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Project $project
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereUserId($value)
 * @mixin \Eloquent
 */
	class ProjectRating extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectSetting
 *
 * @property int $id
 * @property string $send_reminder
 * @property int $remind_time
 * @property string $remind_type
 * @property string $remind_to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereRemindTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereRemindTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereRemindType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereSendReminder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ProjectSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectStatusSetting
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $status_name
 * @property string $color
 * @property string $status
 * @property string $default_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereDefaultStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereStatusName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $alias
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereAlias($value)
 */
	class ProjectStatusSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectCategory
 *
 * @property int $id
 * @property string $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $project
 * @property-read int|null $project_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereCompanyId($value)
 * @mixin \Eloquent
 * @property int $category_id
 * @property-read \App\Models\ProjectCategory $projectCategory
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSubCategory whereCategoryId($value)
 */
	class ProjectSubCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectTemplate
 *
 * @property int $id
 * @property string $project_name
 * @property int|null $category_id
 * @property int|null $client_id
 * @property string|null $project_summary
 * @property string|null $notes
 * @property string|null $feedback
 * @property string $client_view_task
 * @property string $allow_client_notification
 * @property string $manual_timelog
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProjectCategory|null $category
 * @property-read \App\Models\User|null $client
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTemplateMember[] $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTemplateTask[] $tasks
 * @property-read int|null $tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereAllowClientNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereClientViewTask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereManualTimelog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereProjectName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereProjectSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $projectMembers
 * @property-read int|null $members_many_count
 * @property int|null $company_id
 * @property int $added_by
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereCompanyId($value)
 * @property-read int|null $project_members_count
 * @mixin \Eloquent
 * @property int|null $sub_category_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectTemplateMilestone> $milestones
 * @property-read int|null $milestones_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplate whereSubCategoryId($value)
 */
	class ProjectTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectTemplateMember
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_template_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\ProjectTemplate $projectTemplate
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateMember query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateMember whereProjectTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateMember whereUserId($value)
 * @mixin \Eloquent
 */
	class ProjectTemplateMember extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectMilestone
 *
 * @property int $id
 * @property int|null $project_id
 * @property int|null $currency_id
 * @property string $milestone_title
 * @property string $summary
 * @property float $cost
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $invoice_created
 * @property int|null $invoice_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $icon
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereInvoiceCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereMilestoneTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereUpdatedAt($value)
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMilestone whereStartDate($value)
 * @mixin \Eloquent
 * @property int|null $company_id
 * @property int|null $project_template_id
 * @property string $add_to_budget
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $completeTasks
 * @property-read int|null $complete_tasks_count
 * @property-read \App\Models\ProjectTemplate|null $projectTemplate
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateMilestone whereAddToBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateMilestone whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateMilestone whereProjectTemplateId($value)
 */
	class ProjectTemplateMilestone extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectTemplateSubTask
 *
 * @property int $id
 * @property int $project_template_task_id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\ProjectTemplateTask $task
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateSubTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateSubTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateSubTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateSubTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateSubTask whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateSubTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateSubTask whereProjectTemplateTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateSubTask whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateSubTask whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateSubTask whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateSubTask whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class ProjectTemplateSubTask extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectTemplateTask
 *
 * @property int $id
 * @property string $heading
 * @property string|null $description
 * @property int $project_template_id
 * @property string $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $project_template_task_category_id
 * @property-read mixed $icon
 * @property-read \App\Models\ProjectTemplate $projectTemplate
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTemplateSubTask[] $subtasks
 * @property-read int|null $subtasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTemplateTaskUser[] $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $usersMany
 * @property-read int|null $users_many_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTask whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTask whereHeading($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTask wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTask whereProjectTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTask whereProjectTemplateTaskCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTask whereUpdatedAt($value)
 * @property-read \App\Models\TaskCategory|null $category
 * @mixin \Eloquent
 * @property int|null $milestone_id
 * @property array|null $task_labels
 * @property-read \App\Models\ProjectTemplateMilestone|null $milestone
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTask whereMilestoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTask whereTaskLabels($value)
 */
	class ProjectTemplateTask extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectTemplateTaskUser
 *
 * @property int $id
 * @property int $project_template_task_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProjectTemplateTask $task
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser whereProjectTemplateTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser whereUserId($value)
 * @mixin \Eloquent
 */
	class ProjectTemplateTaskUser extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectTimeLog
 *
 * @property int $id
 * @property string $start
 * @property string $name
 * @property int|null $project_id
 * @property int|null $task_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property string $memo
 * @property string|null $total_hours
 * @property int|null $total_minutes
 * @property int|null $edited_by_user
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $hourly_rate
 * @property float $earnings
 * @property int $approved
 * @property int|null $approved_by
 * @property int|null $invoice_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User|null $editor
 * @property-read mixed $duration
 * @property-read mixed $hours
 * @property-read mixed $icon
 * @property-read mixed $timer
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\Task|null $task
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereEarnings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereEditedByUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereMemo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereTotalHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereTotalMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereUserId($value)
 * @property-read \App\Models\User $user
 * @property string|null $total_break_minutes
 * @property-read \App\Models\ProjectTimeLogBreak|null $activeBreak
 * @property-read Collection|\App\Models\ProjectTimeLogBreak[] $breaks
 * @property-read int|null $breaks_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereTotalBreakMinutes($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $extras
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereCompanyId($value)
 * @property-read mixed $hours_only
 * @mixin \Eloquent
 * @property int|null $weekly_timesheet_id
 * @property int $rejected
 * @property int|null $rejected_by
 * @property string|null $rejected_at
 * @property string|null $reject_reason
 * @property-read \App\Models\Task|null $tasksOnlyTrashed
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereRejectReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereRejected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereRejectedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLog whereWeeklyTimesheetId($value)
 */
	class ProjectTimeLog extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectTimeLogBreak
 *
 * @property int $id
 * @property int|null $project_time_log_id
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property string $reason
 * @property string|null $total_hours
 * @property string|null $total_minutes
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProjectTimeLog|null $timelog
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereProjectTimeLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereTotalHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereTotalMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereCompanyId($value)
 * @mixin \Eloquent
 */
	class ProjectTimeLogBreak extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectUserNote
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_note_id
 * @property int|null $client_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectMember[] $members
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote whereProjectNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote whereUserId($value)
 * @mixin \Eloquent
 */
	class ProjectUserNote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Promotion
 *
 * @property int $id
 * @property int|null $company_id
 * @property int|null $employee_id
 * @property string|null $date
 * @property int|null $previous_designation_id
 * @property int|null $current_designation_id
 * @property string $send_notification
 * @property int|null $previous_department_id
 * @property int|null $current_department_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_send
 * @property int $promotion
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Team|null $currentDepartment
 * @property-read \App\Models\Designation|null $currentDesignation
 * @property-read \App\Models\User|null $employee
 * @property-read \App\Models\Team|null $previousDepartment
 * @property-read \App\Models\Designation|null $previousDesignation
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereCurrentDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereCurrentDesignationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereIsSend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion wherePreviousDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion wherePreviousDesignationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion wherePromotion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereSendNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereUpdatedAt($value)
 */
	class Promotion extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Proposal
 *
 * @property int $id
 * @property int $deal_id
 * @property \Illuminate\Support\Carbon $valid_till
 * @property int|null $unit_id
 * @property float $sub_total
 * @property float $total
 * @property int|null $currency_id
 * @property string $discount_type
 * @property float $discount
 * @property int $invoice_convert
 * @property string $status
 * @property string $ip_address
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $last_viewed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $client_comment
 * @property int $signature_approval
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProposalItem[] $items
 * @property-read int|null $items_count
 * @property-read \App\Models\ProposalSign|null $signature
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereClientComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereInvoiceConvert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereSignatureApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereValidTill($value)
 * @property string|null $hash
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereHash($value)
 * @property string|null $description
 * @property string $calculate_tax
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereCalculateTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereDescription($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereCompanyId($value)
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereLastViewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereUnitId($value)
 * @property int $send_status
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereSendStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereDealId($value)
 * @mixin \Eloquent
 * @property string|null $proposal_number
 * @property string|null $original_proposal_number
 * @property-read \App\Models\Deal|null $deal
 * @property-read \App\Models\Deal|null $lead
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereOriginalProposalNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposal whereProposalNumber($value)
 */
	class Proposal extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProposalItem
 *
 * @property int $id
 * @property int $proposal_id
 * @property string $item_name
 * @property string $type
 * @property float $quantity
 * @property float $unit_price
 * @property float $amount
 * @property string|null $item_summary
 * @property string|null $taxes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $hsn_sac_code
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereProposalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereUpdatedAt($value)
 * @property-read \App\Models\ProposalItemImage|null $proposalItemImage
 * @property-read mixed $tax_list
 * @property int|null $product_id
 * @property int|null $unit_id
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereUnitId($value)
 * @mixin \Eloquent
 * @property int $field_order
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereFieldOrder($value)
 */
	class ProposalItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProposalItemImage
 *
 * @property int $id
 * @property int $proposal_item_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereProposalItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItemImage whereUpdatedAt($value)
 * @property-read \App\Models\ProposalItem $item
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class ProposalItemImage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProposalSign
 *
 * @property int $id
 * @property int $proposal_id
 * @property string $full_name
 * @property string $email
 * @property string $signature
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalSign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalSign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalSign query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalSign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalSign whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalSign whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalSign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalSign whereProposalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalSign whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalSign whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class ProposalSign extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProposalTemplate
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $name
 * @property int|null $unit_id
 * @property float $sub_total
 * @property float $total
 * @property int|null $currency_id
 * @property string $discount_type
 * @property float $discount
 * @property int $invoice_convert
 * @property string|null $description
 * @property string|null $client_comment
 * @property int $signature_approval
 * @property string|null $hash
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProposalTemplateItem[] $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereClientComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereInvoiceConvert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereSignatureApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereUpdatedAt($value)
 * @property-read UnitType|null $units
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplate whereUnitId($value)
 * @property-read \App\Models\Company|null $company
 * @mixin \Eloquent
 * @property-read \App\Models\Deal|null $lead
 */
	class ProposalTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProposalTemplateItem
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $proposal_template_id
 * @property string $hsn_sac_code
 * @property string $item_name
 * @property string $type
 * @property int $quantity
 * @property float $unit_price
 * @property float $amount
 * @property string|null $item_summary
 * @property string|null $taxes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProposalTemplateItemImage|null $proposalTemplateItemImage
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereProposalTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereUpdatedAt($value)
 * @property int|null $product_id
 * @property int|null $unit_id
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereUnitId($value)
 * @mixin \Eloquent
 */
	class ProposalTemplateItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProposalTemplateItemImage
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $proposal_template_item_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereProposalTemplateItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItemImage whereUpdatedAt($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class ProposalTemplateItemImage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurposeConsent
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\PurposeConsentLead|null $lead
 * @property-read \App\Models\PurposeConsentUser|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class PurposeConsent extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurposeConsentLead
 *
 * @property int $id
 * @property int $deal_id
 * @property int $purpose_consent_id
 * @property string $status
 * @property string|null $ip
 * @property int|null $updated_by_id
 * @property string|null $additional_description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentLead newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentLead newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentLead query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentLead whereAdditionalDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentLead whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentLead whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentLead whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentLead whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentLead wherePurposeConsentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentLead whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentLead whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentLead whereUpdatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentLead whereDealId($value)
 * @mixin \Eloquent
 */
	class PurposeConsentLead extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurposeConsentUser
 *
 * @property int $id
 * @property int $client_id
 * @property int $purpose_consent_id
 * @property string $status
 * @property string|null $ip
 * @property int $updated_by_id
 * @property string|null $additional_description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentUser whereAdditionalDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentUser whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentUser whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentUser wherePurposeConsentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentUser whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsentUser whereUpdatedById($value)
 * @mixin \Eloquent
 */
	class PurposeConsentUser extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PushNotificationSetting
 *
 * @property int $id
 * @property string|null $onesignal_app_id
 * @property string|null $onesignal_rest_api_key
 * @property string|null $notification_logo
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read mixed $notification_logo_url
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereNotificationLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereOnesignalAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereOnesignalRestApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $beams_push_status
 * @property string|null $instance_id
 * @property string|null $beam_secret
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereBeamSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereBeamsPushStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereInstanceId($value)
 */
	class PushNotificationSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PusherSetting
 *
 * @property int $id
 * @property string|null $pusher_app_id
 * @property string|null $pusher_app_key
 * @property string|null $pusher_app_secret
 * @property string|null $pusher_cluster
 * @property int $force_tls
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting whereForceTls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting wherePusherAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting wherePusherAppKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting wherePusherAppSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting wherePusherCluster($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting whereUpdatedAt($value)
 * @property int $taskboard
 * @property int $messages
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting whereMessages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PusherSetting whereTaskboard($value)
 * @mixin \Eloquent
 */
	class PusherSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Qrcodes
 *
 * @property int $id
 * @property string $qr_enable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Qrcodes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Qrcodes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Qrcodes query()
 * @method static \Illuminate\Database\Eloquent\Builder|Qrcodes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Qrcodes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Qrcodes whereQrEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Qrcodes whereUpdatedAt($value)
 */
	class Qrcodes extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\QuickBooksSetting
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $sandbox_client_id
 * @property string $sandbox_client_secret
 * @property string $client_id
 * @property string $client_secret
 * @property string $access_token
 * @property string $refresh_token
 * @property string $realmid
 * @property string $sync_type
 * @property string $environment
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereEnvironment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereRealmid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereSandboxClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereSandboxClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereSyncType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class QuickBooksSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\RecurringInvoice
 *
 * @property int $id
 * @property int|null $currency_id
 * @property int|null $project_id
 * @property int|null $client_id
 * @property int|null $user_id
 * @property int|null $created_by
 * @property int|null $unit_id
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon $next_invoice_date
 * @property \Illuminate\Support\Carbon $due_date
 * @property float $sub_total
 * @property float $total
 * @property float $discount
 * @property string $discount_type
 * @property string $status
 * @property string|null $file
 * @property string|null $file_original_name
 * @property string|null $note
 * @property string $show_shipping_address
 * @property int|null $day_of_month
 * @property int|null $day_of_week
 * @property string|null $payment_method
 * @property string $rotation
 * @property int|null $billing_cycle
 * @property int $client_can_stop
 * @property int $unlimited_recurring
 * @property string|null $deleted_at
 * @property string|null $shipping_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User|null $client
 * @property-read \App\Models\ClientDetails|null $clientdetails
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $icon
 * @property-read mixed $issue_on
 * @property-read mixed $total_amount
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RecurringInvoiceItems[] $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $recurrings
 * @property-read int|null $recurrings_count
 * @property-read \App\Models\User|null $withoutGlobalScopeCompanyClient
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereBillingCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereClientCanStop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereDayOfMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereFileOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereRotation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereShowShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereUnlimitedRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereUserId($value)
 * @property string $calculate_tax
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereCalculateTax($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereCompanyId($value)
 * @property int $immediate_invoice
 * @property-read \App\Models\UnitType|null $units
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereImmediateInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereNextInvoiceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereUnitId($value)
 * @property int|null $bank_account_id
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereBankAccountId($value)
 * @mixin \Eloquent
 */
	class RecurringInvoice extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InvoiceItemImage
 *
 * @property int $id
 * @property int $invoice_item_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $image
 * @property string|null $size
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereInvoiceItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItemImage whereUpdatedAt($value)
 * @property int $invoice_recurring_item_id
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItemImage whereInvoiceRecurringItemId($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class RecurringInvoiceItemImage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\RecurringInvoiceItems
 *
 * @property int $id
 * @property int $invoice_recurring_id
 * @property string $item_name
 * @property float $quantity
 * @property float $unit_price
 * @property float $amount
 * @property string|null $taxes
 * @property string $type
 * @property string|null $item_summary
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $hsn_sac_code
 * @property mixed $recurringInvoiceItemImage
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereInvoiceRecurringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereUpdatedAt($value)
 * @property int|null $product_id
 * @property int|null $unit_id
 * @property \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereUnitId($value)
 * @mixin \Eloquent
 */
	class RecurringInvoiceItems extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\RemovalRequest
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int|null $user_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereCompanyId($value)
 * @mixin \Eloquent
 */
	class RemovalRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\RemovalRequestLead
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int|null $lead_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\Lead|null $lead
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead query()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereCompanyId($value)
 * @mixin \Eloquent
 */
	class RemovalRequestLead extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property string|null $display_name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PermissionRole[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $perms
 * @property-read int|null $perms_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RoleUser[] $roleuser
 * @property-read int|null $roleuser_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $rolePermissions
 * @property-read int|null $role_permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $unsyncedUsers
 * @property-read int|null $unsynced_users_count
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCompanyId($value)
 * @mixin \Eloquent
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\RoleUser
 *
 * @property int $user_id
 * @property int $role_id
 * @property-read mixed $icon
 * @property-read \App\Models\Role $role
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser whereUserId($value)
 * @mixin \Eloquent
 */
	class RoleUser extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\RotationAutomateLog
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $user_id
 * @property int|null $employee_shift_rotation_id
 * @property int|null $employee_shift_id
 * @property int|null $sequence
 * @property string|null $cron_run_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|RotationAutomateLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RotationAutomateLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RotationAutomateLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|RotationAutomateLog whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RotationAutomateLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RotationAutomateLog whereCronRunDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RotationAutomateLog whereEmployeeShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RotationAutomateLog whereEmployeeShiftRotationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RotationAutomateLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RotationAutomateLog whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RotationAutomateLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RotationAutomateLog whereUserId($value)
 */
	class RotationAutomateLog extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Session
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $payload
 * @property int $last_activity
 * @method static \Illuminate\Database\Eloquent\Builder|Session newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Session newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Session query()
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereLastActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereUserId($value)
 * @mixin \Eloquent
 */
	class Session extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ShiftRotation
 *
 * @property int $id
 * @property int|null $company_id
 * @property string|null $rotation_name
 * @property string|null $rotation_frequency
 * @property string|null $schedule_on
 * @property int|null $rotation_date
 * @property string|null $color_code
 * @property string $override_shift
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $send_mail
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AutomateShift> $automateShifts
 * @property-read int|null $automate_shifts_count
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ShiftRotationSequence> $sequences
 * @property-read int|null $sequences_count
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation active()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation whereColorCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation whereOverrideShift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation whereRotationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation whereRotationFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation whereRotationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation whereScheduleOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation whereSendMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation whereUpdatedAt($value)
 */
	class ShiftRotation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ShiftRotationSequence
 *
 * @property int $id
 * @property int|null $employee_shift_rotation_id
 * @property int|null $employee_shift_id
 * @property int|null $sequence
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ShiftRotation|null $rotation
 * @property-read \App\Models\EmployeeShift|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotationSequence newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotationSequence newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotationSequence query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotationSequence whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotationSequence whereEmployeeShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotationSequence whereEmployeeShiftRotationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotationSequence whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotationSequence whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotationSequence whereUpdatedAt($value)
 */
	class ShiftRotationSequence extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SignUpSetting
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SignUpSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SignUpSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SignUpSetting query()
 * @mixin \Eloquent
 * @property int $id
 * @property int|null $language_setting_id
 * @property string|null $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SignUpSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SignUpSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SignUpSetting whereLanguageSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SignUpSetting whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SignUpSetting whereUpdatedAt($value)
 */
	class SignUpSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Skill
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|Skill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Skill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Skill query()
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereCompanyId($value)
 * @mixin \Eloquent
 */
	class Skill extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SlackSetting
 *
 * @property int $id
 * @property string|null $slack_webhook
 * @property string|null $slack_logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $status
 * @property-read mixed $icon
 * @property-read mixed $slack_logo_url
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereSlackLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereSlackWebhook($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereCompanyId($value)
 * @mixin \Eloquent
 */
	class SlackSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SmtpSetting
 *
 * @property int $id
 * @property string $mail_driver
 * @property string $mail_host
 * @property string $mail_port
 * @property string $mail_username
 * @property string $mail_password
 * @property string $mail_from_name
 * @property string $mail_from_email
 * @property string|null $mail_encryption
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $verified
 * @property int $email_verified
 * @property-read mixed $icon
 * @property-read mixed $set_smtp_message
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereMailDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereMailEncryption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereMailFromEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereMailFromName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereMailHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereMailPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereMailPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereMailUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereVerified($value)
 * @property string $mail_connection
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereMailConnection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmtpSetting whereEmailVerified($value)
 * @mixin \Eloquent
 */
	class SmtpSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Social
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $social_id
 * @property string $social_service
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Social newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Social newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Social query()
 * @method static \Illuminate\Database\Eloquent\Builder|Social whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Social whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Social whereSocialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Social whereSocialService($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Social whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Social whereUserId($value)
 * @mixin \Eloquent
 */
	class Social extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SocialAuthSetting
 *
 * @property int $id
 * @property string|null $facebook_client_id
 * @property string|null $facebook_secret_id
 * @property string $facebook_status
 * @property string|null $google_client_id
 * @property string|null $google_secret_id
 * @property string $google_status
 * @property string|null $twitter_client_id
 * @property string|null $twitter_secret_id
 * @property string $twitter_status
 * @property string|null $linkedin_client_id
 * @property string|null $linkedin_secret_id
 * @property string $linkedin_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $social_auth_enable
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereFacebookClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereFacebookSecretId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereFacebookStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereGoogleClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereGoogleSecretId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereGoogleStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereLinkedinClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereLinkedinSecretId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereLinkedinStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereTwitterClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereTwitterSecretId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereTwitterStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read mixed $social_auth_enable_count
 */
	class SocialAuthSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * Class StickyNote
 *
 * @package App
 * @property int $id
 * @property int $user_id
 * @property string $note_text
 * @property string $colour
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\User $userDetail
 * @method static \Illuminate\Database\Eloquent\Builder|StickyNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StickyNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StickyNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|StickyNote whereColour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickyNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickyNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickyNote whereNoteText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickyNote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickyNote whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|StickyNote whereCompanyId($value)
 * @mixin \Eloquent
 */
	class StickyNote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\StorageSetting
 *
 * @property int $id
 * @property string $filesystem
 * @property string|null $auth_keys
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|StorageSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StorageSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StorageSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|StorageSetting whereAuthKeys($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StorageSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StorageSetting whereFilesystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StorageSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StorageSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StorageSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class StorageSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SubTask
 *
 * @property int $id
 * @property int $task_id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property string|null $start_date
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read \App\Models\Task $task
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereUpdatedAt($value)
 * @property string|null $description
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SubTaskFile[] $files
 * @property-read int|null $files_count
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereDescription($value)
 * @property int|null $assigned_to
 * @property-read \App\Models\User|null $assignedTo
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereAssignedTo($value)
 * @mixin \Eloquent
 */
	class SubTask extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskFile
 *
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property string $filename
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $dropbox_link
 * @property string|null $external_link
 * @property string|null $external_link_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereUserId($value)
 * @property int $sub_task_id
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereSubTaskId($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class SubTaskFile extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\AuthorizationInvoice
 *
 * @property int $id
 * @property int $company_id
 * @property int $package_id
 * @property string|null $transaction_id
 * @property string|null $amount
 * @property \Illuminate\Support\Carbon|null $pay_date
 * @property \Illuminate\Support\Carbon|null $next_pay_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\SuperAdmin\Package $package
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizationInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizationInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizationInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizationInvoice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizationInvoice whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizationInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizationInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizationInvoice whereNextPayDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizationInvoice wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizationInvoice wherePayDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizationInvoice whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizationInvoice whereUpdatedAt($value)
 */
	class AuthorizationInvoice extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\AuthorizeInvoice
 *
 * @property int $id
 * @property int $company_id
 * @property int $package_id
 * @property string|null $transaction_id
 * @property string|null $amount
 * @property Carbon|null $pay_date
 * @property Carbon|null $next_pay_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read Package $package
 * @method static Builder|AuthorizeInvoice newModelQuery()
 * @method static Builder|AuthorizeInvoice newQuery()
 * @method static Builder|AuthorizeInvoice query()
 * @method static Builder|AuthorizeInvoice whereAmount($value)
 * @method static Builder|AuthorizeInvoice whereCompanyId($value)
 * @method static Builder|AuthorizeInvoice whereCreatedAt($value)
 * @method static Builder|AuthorizeInvoice whereId($value)
 * @method static Builder|AuthorizeInvoice whereNextPayDate($value)
 * @method static Builder|AuthorizeInvoice wherePackageId($value)
 * @method static Builder|AuthorizeInvoice wherePayDate($value)
 * @method static Builder|AuthorizeInvoice whereTransactionId($value)
 * @method static Builder|AuthorizeInvoice whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class AuthorizeInvoice extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\AuthorizeSubscription
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $subscription_id
 * @property int|null $plan_id
 * @property string|null $plan_type
 * @property string|null $ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizeSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizeSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizeSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizeSubscription whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizeSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizeSubscription whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizeSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizeSubscription wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizeSubscription wherePlanType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizeSubscription whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorizeSubscription whereUpdatedAt($value)
 */
	class AuthorizeSubscription extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\ClientPayment
 *
 * @property int $id
 * @property int|null $company_id
 * @property int|null $project_id
 * @property int|null $invoice_id
 * @property int|null $order_id
 * @property int|null $credit_notes_id
 * @property float $amount
 * @property string|null $gateway
 * @property string|null $transaction_id
 * @property int|null $currency_id
 * @property int|null $default_currency_id
 * @property float|null $exchange_rate
 * @property string|null $plan_id
 * @property string|null $customer_id
 * @property string|null $event_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $paid_on
 * @property string|null $remarks
 * @property int|null $offline_method_id
 * @property string|null $bill
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property string|null $payment_gateway_response null = success
 * @property string|null $payload_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $bank_account_id
 * @property int|null $quickbooks_payment_id
 * @property-read \App\Models\Invoice|null $invoice
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereBill($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereCreditNotesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereDefaultCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereOfflineMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment wherePaidOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment wherePayloadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment wherePaymentGatewayResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereQuickbooksPaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientPayment whereUpdatedAt($value)
 */
	class ClientPayment extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\Faq
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $faq_category_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read FaqCategory $category
 * @property-read Collection|FaqFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $image_url
 * @method static Builder|Faq newModelQuery()
 * @method static Builder|Faq newQuery()
 * @method static Builder|Faq query()
 * @method static Builder|Faq whereCreatedAt($value)
 * @method static Builder|Faq whereDescription($value)
 * @method static Builder|Faq whereFaqCategoryId($value)
 * @method static Builder|Faq whereId($value)
 * @method static Builder|Faq whereTitle($value)
 * @method static Builder|Faq whereUpdatedAt($value)
 * @mixin Eloquent
 * @property string|null $image
 * @method static Builder|Faq whereImage($value)
 */
	class Faq extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\FaqCategory
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Faq[] $faqs
 * @property-read int|null $faqs_count
 * @method static Builder|FaqCategory newModelQuery()
 * @method static Builder|FaqCategory newQuery()
 * @method static Builder|FaqCategory query()
 * @method static Builder|FaqCategory whereCreatedAt($value)
 * @method static Builder|FaqCategory whereId($value)
 * @method static Builder|FaqCategory whereName($value)
 * @method static Builder|FaqCategory whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class FaqCategory extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\FaqFile
 *
 * @property int $id
 * @property int $user_id
 * @property int $faq_id
 * @property string $filename
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $dropbox_link
 * @property string|null $external_link
 * @property string|null $external_link_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $file_url
 * @method static Builder|FaqFile newModelQuery()
 * @method static Builder|FaqFile newQuery()
 * @method static Builder|FaqFile query()
 * @method static Builder|FaqFile whereCreatedAt($value)
 * @method static Builder|FaqFile whereDescription($value)
 * @method static Builder|FaqFile whereDropboxLink($value)
 * @method static Builder|FaqFile whereExternalLink($value)
 * @method static Builder|FaqFile whereExternalLinkName($value)
 * @method static Builder|FaqFile whereFaqId($value)
 * @method static Builder|FaqFile whereFilename($value)
 * @method static Builder|FaqFile whereGoogleUrl($value)
 * @method static Builder|FaqFile whereHashname($value)
 * @method static Builder|FaqFile whereId($value)
 * @method static Builder|FaqFile whereSize($value)
 * @method static Builder|FaqFile whereUpdatedAt($value)
 * @method static Builder|FaqFile whereUserId($value)
 * @mixin Eloquent
 * @property-read mixed $icon
 */
	class FaqFile extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\Feature
 *
 * @property int $id
 * @property int|null $language_setting_id
 * @property string $title
 * @property string|null $description
 * @property string|null $image
 * @property string|null $icon
 * @property string $type
 * @property int|null $front_feature_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $image_url
 * @property-read LanguageSetting|null $language
 * @method static Builder|Feature newModelQuery()
 * @method static Builder|Feature newQuery()
 * @method static Builder|Feature query()
 * @method static Builder|Feature whereCreatedAt($value)
 * @method static Builder|Feature whereDescription($value)
 * @method static Builder|Feature whereFrontFeatureId($value)
 * @method static Builder|Feature whereIcon($value)
 * @method static Builder|Feature whereId($value)
 * @method static Builder|Feature whereImage($value)
 * @method static Builder|Feature whereLanguageSettingId($value)
 * @method static Builder|Feature whereTitle($value)
 * @method static Builder|Feature whereType($value)
 * @method static Builder|Feature whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class Feature extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\FooterMenu
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $video_link
 * @property string|null $video_embed
 * @property string|null $file_name
 * @property string|null $hash_name
 * @property string|null $external_link
 * @property string|null $type
 * @property string|null $status
 * @property int|null $language_setting_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $video_url
 * @property-read LanguageSetting|null $language
 * @method static Builder|FooterMenu newModelQuery()
 * @method static Builder|FooterMenu newQuery()
 * @method static Builder|FooterMenu query()
 * @method static Builder|FooterMenu whereCreatedAt($value)
 * @method static Builder|FooterMenu whereDescription($value)
 * @method static Builder|FooterMenu whereExternalLink($value)
 * @method static Builder|FooterMenu whereFileName($value)
 * @method static Builder|FooterMenu whereHashName($value)
 * @method static Builder|FooterMenu whereId($value)
 * @method static Builder|FooterMenu whereLanguageSettingId($value)
 * @method static Builder|FooterMenu whereName($value)
 * @method static Builder|FooterMenu whereSlug($value)
 * @method static Builder|FooterMenu whereStatus($value)
 * @method static Builder|FooterMenu whereType($value)
 * @method static Builder|FooterMenu whereUpdatedAt($value)
 * @method static Builder|FooterMenu whereVideoEmbed($value)
 * @method static Builder|FooterMenu whereVideoLink($value)
 * @mixin Eloquent
 * @property int $private
 * @method static \Illuminate\Database\Eloquent\Builder|FooterMenu wherePrivate($value)
 */
	class FooterMenu extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\FrontClients
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $image
 * @property int|null $language_setting_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $image_url
 * @property-read LanguageSetting|null $language
 * @method static Builder|FrontClients newModelQuery()
 * @method static Builder|FrontClients newQuery()
 * @method static Builder|FrontClients query()
 * @method static Builder|FrontClients whereCreatedAt($value)
 * @method static Builder|FrontClients whereId($value)
 * @method static Builder|FrontClients whereImage($value)
 * @method static Builder|FrontClients whereLanguageSettingId($value)
 * @method static Builder|FrontClients whereTitle($value)
 * @method static Builder|FrontClients whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class FrontClients extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\FrontDetail
 *
 * @property int $id
 * @property string $get_started_show
 * @property string $sign_in_show
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $image
 * @property string|null $social_links
 * @property string|null $primary_color
 * @property string|null $custom_css
 * @property string|null $custom_css_theme_two
 * @property string|null $locale
 * @property string|null $contact_html
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $image_url
 * @property-read mixed $light_color
 * @method static Builder|FrontDetail newModelQuery()
 * @method static Builder|FrontDetail newQuery()
 * @method static Builder|FrontDetail query()
 * @method static Builder|FrontDetail whereAddress($value)
 * @method static Builder|FrontDetail whereContactHtml($value)
 * @method static Builder|FrontDetail whereCreatedAt($value)
 * @method static Builder|FrontDetail whereCustomCss($value)
 * @method static Builder|FrontDetail whereCustomCssThemeTwo($value)
 * @method static Builder|FrontDetail whereEmail($value)
 * @method static Builder|FrontDetail whereGetStartedShow($value)
 * @method static Builder|FrontDetail whereId($value)
 * @method static Builder|FrontDetail whereLocale($value)
 * @method static Builder|FrontDetail wherePhone($value)
 * @method static Builder|FrontDetail wherePrimaryColor($value)
 * @method static Builder|FrontDetail whereSignInShow($value)
 * @method static Builder|FrontDetail whereSocialLinks($value)
 * @method static Builder|FrontDetail whereUpdatedAt($value)
 * @mixin Eloquent
 * @property string $homepage_background
 * @property string|null $background_color
 * @property string|null $background_image
 * @property-read mixed $background_image_url
 * @method static \Illuminate\Database\Eloquent\Builder|FrontDetail whereBackgroundColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontDetail whereBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontDetail whereHomepageBackground($value)
 */
	class FrontDetail extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\FrontFaq
 *
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property int|null $language_setting_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read LanguageSetting|null $language
 * @method static Builder|FrontFaq newModelQuery()
 * @method static Builder|FrontFaq newQuery()
 * @method static Builder|FrontFaq query()
 * @method static Builder|FrontFaq whereAnswer($value)
 * @method static Builder|FrontFaq whereCreatedAt($value)
 * @method static Builder|FrontFaq whereId($value)
 * @method static Builder|FrontFaq whereLanguageSettingId($value)
 * @method static Builder|FrontFaq whereQuestion($value)
 * @method static Builder|FrontFaq whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class FrontFaq extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\FrontFeature
 *
 * @property int $id
 * @property int|null $language_setting_id
 * @property string|null $title
 * @property string|null $description
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Feature[] $features
 * @property-read int|null $features_count
 * @method static Builder|FrontFeature newModelQuery()
 * @method static Builder|FrontFeature newQuery()
 * @method static Builder|FrontFeature query()
 * @method static Builder|FrontFeature whereCreatedAt($value)
 * @method static Builder|FrontFeature whereDescription($value)
 * @method static Builder|FrontFeature whereId($value)
 * @method static Builder|FrontFeature whereLanguageSettingId($value)
 * @method static Builder|FrontFeature whereStatus($value)
 * @method static Builder|FrontFeature whereTitle($value)
 * @method static Builder|FrontFeature whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read LanguageSetting|null $language
 */
	class FrontFeature extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\FrontMenu
 *
 * @property int $id
 * @property string|null $home
 * @property string|null $feature
 * @property string|null $price
 * @property string|null $contact
 * @property string|null $get_start
 * @property string|null $login
 * @property string|null $contact_submit
 * @property int|null $language_setting_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\LanguageSetting|null $language
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu query()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu whereContactSubmit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu whereFeature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu whereGetStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu whereHome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu whereLanguageSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontMenu whereUpdatedAt($value)
 */
	class FrontMenu extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\FrontWidget
 *
 * @property int $id
 * @property string $name
 * @property string $widget_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|FrontWidget newModelQuery()
 * @method static Builder|FrontWidget newQuery()
 * @method static Builder|FrontWidget query()
 * @method static Builder|FrontWidget whereCreatedAt($value)
 * @method static Builder|FrontWidget whereId($value)
 * @method static Builder|FrontWidget whereName($value)
 * @method static Builder|FrontWidget whereUpdatedAt($value)
 * @method static Builder|FrontWidget whereWidgetCode($value)
 * @mixin Eloquent
 * @property string|null $footer_script
 * @property string|null $header_script
 * @method static \Illuminate\Database\Eloquent\Builder|FrontWidget whereFooterScript($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontWidget whereHeaderScript($value)
 */
	class FrontWidget extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\GlobalCurrency
 *
 * @property int $id
 * @property string $currency_name
 * @property string $currency_symbol
 * @property string $currency_code
 * @property float|null $exchange_rate
 * @property float|null $usd_price
 * @property string $is_cryptocurrency
 * @property string $currency_position
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency query()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereCurrencyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereCurrencyPosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereCurrencySymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereIsCryptocurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereUsdPrice($value)
 * @mixin Eloquent
 * @method static Builder|GlobalCurrency onlyTrashed()
 * @method static Builder|GlobalCurrency withTrashed()
 * @method static Builder|GlobalCurrency withoutTrashed()
 * @property int $no_of_decimal
 * @property string|null $thousand_separator
 * @property string|null $decimal_separator
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereDecimalSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereNoOfDecimal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalCurrency whereThousandSeparator($value)
 */
	class GlobalCurrency extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\GlobalInvoice
 *
 * @property int $id
 * @property int|null $company_id
 * @property int|null $currency_id
 * @property int|null $package_id
 * @property int|null $global_subscription_id
 * @property int|null $offline_method_id
 * @property string|null $m_payment_id
 * @property string|null $pf_payment_id
 * @property string|null $payfast_plan
 * @property string|null $signature
 * @property string|null $token
 * @property string|null $transaction_id
 * @property string|null $package_type
 * @property float|null $sub_total
 * @property float|null $total
 * @property string|null $billing_frequency
 * @property string|null $billing_interval
 * @property string|null $recurring
 * @property string|null $plan_id
 * @property string|null $event_id
 * @property string|null $order_id
 * @property string|null $subscription_id
 * @property string|null $invoice_id
 * @property float|null $amount
 * @property string|null $stripe_invoice_number
 * @property \Illuminate\Support\Carbon|null $pay_date
 * @property \Illuminate\Support\Carbon|null $next_pay_date
 * @property string|null $gateway_name
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\SuperAdmin\GlobalCurrency|null $currency
 * @property-read mixed $invoice_number
 * @property-read \App\Models\OfflinePaymentMethod|null $offlinePaymentMethod
 * @property-read \App\Models\SuperAdmin\Package|null $package
 * @property-read \App\Models\SuperAdmin\GlobalSubscription|null $subscription
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice paid()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereBillingFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereBillingInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereGatewayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereGlobalSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereMPaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereNextPayDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereOfflineMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice wherePackageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice wherePayDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice wherePayfastPlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice wherePfPaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereStripeInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoice whereUpdatedAt($value)
 */
	class GlobalInvoice extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\GlobalInvoiceSetting
 *
 * @property int $id
 * @property string|null $billing_name
 * @property string|null $billing_address
 * @property string|null $billing_tax_name
 * @property string|null $billing_tax_id
 * @property string|null $logo
 * @property string $template
 * @property string|null $locale
 * @property int $authorised_signatory
 * @property string|null $authorised_signatory_signature
 * @property string $invoice_terms
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $authorised_signatory_signature_url
 * @property-read mixed $is_chinese_lang
 * @property-read mixed $logo_url
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting whereAuthorisedSignatory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting whereAuthorisedSignatorySignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting whereBillingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting whereBillingTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting whereBillingTaxName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting whereInvoiceTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalInvoiceSetting whereUpdatedAt($value)
 */
	class GlobalInvoiceSetting extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\StripeSetting
 *
 * @property int $id
 * @property string|null $api_key
 * @property string|null $api_secret
 * @property string|null $webhook_key
 * @property string|null $paypal_client_id
 * @property string|null $paypal_secret
 * @property string $paypal_status
 * @property string $stripe_status
 * @property string|null $razorpay_key
 * @property string|null $razorpay_secret
 * @property string|null $razorpay_webhook_secret
 * @property string $razorpay_status
 * @property string $paypal_mode
 * @property string|null $paystack_client_id
 * @property string|null $paystack_secret
 * @property string|null $paystack_status
 * @property string|null $paystack_merchant_email
 * @property string|null $paystack_payment_url
 * @property string $mollie_api_key
 * @property string $mollie_status
 * @property string|null $authorize_api_login_id
 * @property string|null $authorize_transaction_key
 * @property string|null $authorize_signature_key
 * @property string|null $authorize_environment
 * @property string $authorize_status
 * @property string|null $payfast_key
 * @property string|null $payfast_secret
 * @property string $payfast_status
 * @property string|null $payfast_salt_passphrase
 * @property string $payfast_mode
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $show_pay
 * @method static Builder|StripeSetting newModelQuery()
 * @method static Builder|StripeSetting newQuery()
 * @method static Builder|StripeSetting query()
 * @method static Builder|StripeSetting whereApiKey($value)
 * @method static Builder|StripeSetting whereApiSecret($value)
 * @method static Builder|StripeSetting whereAuthorizeApiLoginId($value)
 * @method static Builder|StripeSetting whereAuthorizeEnvironment($value)
 * @method static Builder|StripeSetting whereAuthorizeSignatureKey($value)
 * @method static Builder|StripeSetting whereAuthorizeStatus($value)
 * @method static Builder|StripeSetting whereAuthorizeTransactionKey($value)
 * @method static Builder|StripeSetting whereCreatedAt($value)
 * @method static Builder|StripeSetting whereId($value)
 * @method static Builder|StripeSetting whereMollieApiKey($value)
 * @method static Builder|StripeSetting whereMollieStatus($value)
 * @method static Builder|StripeSetting wherePayfastKey($value)
 * @method static Builder|StripeSetting wherePayfastMode($value)
 * @method static Builder|StripeSetting wherePayfastSaltPassphrase($value)
 * @method static Builder|StripeSetting wherePayfastSecret($value)
 * @method static Builder|StripeSetting wherePayfastStatus($value)
 * @method static Builder|StripeSetting wherePaypalClientId($value)
 * @method static Builder|StripeSetting wherePaypalMode($value)
 * @method static Builder|StripeSetting wherePaypalSecret($value)
 * @method static Builder|StripeSetting wherePaypalStatus($value)
 * @method static Builder|StripeSetting wherePaystackClientId($value)
 * @method static Builder|StripeSetting wherePaystackMerchantEmail($value)
 * @method static Builder|StripeSetting wherePaystackPaymentUrl($value)
 * @method static Builder|StripeSetting wherePaystackSecret($value)
 * @method static Builder|StripeSetting wherePaystackStatus($value)
 * @method static Builder|StripeSetting whereRazorpayKey($value)
 * @method static Builder|StripeSetting whereRazorpaySecret($value)
 * @method static Builder|StripeSetting whereRazorpayStatus($value)
 * @method static Builder|StripeSetting whereRazorpayWebhookSecret($value)
 * @method static Builder|StripeSetting whereStripeStatus($value)
 * @method static Builder|StripeSetting whereUpdatedAt($value)
 * @method static Builder|StripeSetting whereWebhookKey($value)
 * @mixin Eloquent
 * @property string|null $sandbox_paypal_client_id
 * @property string|null $sandbox_paypal_secret
 * @property string|null $live_stripe_client_id
 * @property string|null $live_stripe_secret
 * @property string|null $live_stripe_webhook_secret
 * @property string|null $test_stripe_client_id
 * @property string|null $test_stripe_secret
 * @property string|null $test_stripe_webhook_secret
 * @property string $stripe_mode
 * @property string|null $live_razorpay_key
 * @property string|null $live_razorpay_secret
 * @property string|null $test_razorpay_key
 * @property string|null $test_razorpay_secret
 * @property string|null $live_razorpay_webhook_secret
 * @property string|null $test_razorpay_webhook_secret
 * @property string $razorpay_mode
 * @property string|null $paystack_key
 * @property string|null $test_paystack_key
 * @property string|null $test_paystack_secret
 * @property string|null $test_paystack_merchant_email
 * @property string $paystack_mode
 * @property string|null $payfast_merchant_id
 * @property string|null $payfast_merchant_key
 * @property string|null $payfast_passphrase
 * @property string|null $test_payfast_merchant_id
 * @property string|null $test_payfast_merchant_key
 * @property string|null $test_payfast_passphrase
 * @property string|null $square_application_id
 * @property string|null $square_access_token
 * @property string|null $square_location_id
 * @property string $square_environment
 * @property string $square_status
 * @property string|null $test_flutterwave_key
 * @property string|null $test_flutterwave_secret
 * @property string|null $test_flutterwave_hash
 * @property string|null $live_flutterwave_key
 * @property string|null $live_flutterwave_secret
 * @property string|null $live_flutterwave_hash
 * @property string|null $flutterwave_webhook_secret_hash
 * @property string $flutterwave_status
 * @property string $flutterwave_mode
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereFlutterwaveMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereFlutterwaveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereFlutterwaveWebhookSecretHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereLiveFlutterwaveHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereLiveFlutterwaveKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereLiveFlutterwaveSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereLiveRazorpayKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereLiveRazorpaySecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereLiveRazorpayWebhookSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereLiveStripeClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereLiveStripeSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereLiveStripeWebhookSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials wherePayfastMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials wherePayfastMerchantKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials wherePayfastPassphrase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials wherePaystackKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials wherePaystackMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereRazorpayMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereSandboxPaypalClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereSandboxPaypalSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereSquareAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereSquareApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereSquareEnvironment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereSquareLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereSquareStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereStripeMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestFlutterwaveHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestFlutterwaveKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestFlutterwaveSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestPayfastMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestPayfastMerchantKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestPayfastPassphrase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestPaystackKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestPaystackMerchantEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestPaystackSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestRazorpayKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestRazorpaySecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestRazorpayWebhookSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestStripeClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestStripeSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalPaymentGatewayCredentials whereTestStripeWebhookSecret($value)
 */
	class GlobalPaymentGatewayCredentials extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\GlobalSubscription
 *
 * @property int $id
 * @property int|null $company_id
 * @property int|null $package_id
 * @property int|null $currency_id
 * @property string|null $package_type
 * @property string|null $plan_type
 * @property string|null $transaction_id
 * @property string|null $name
 * @property string|null $customer_id
 * @property string|null $user_id
 * @property string|null $payfast_plan
 * @property string|null $payfast_status
 * @property string|null $quantity
 * @property string|null $token
 * @property string|null $razorpay_id
 * @property string|null $razorpay_plan
 * @property string|null $stripe_id
 * @property string|null $stripe_status
 * @property string|null $stripe_price
 * @property string|null $gateway_name
 * @property string|null $trial_ends_at
 * @property string|null $subscription_status
 * @property string|null $ends_at
 * @property \Illuminate\Support\Carbon|null $subscribed_on_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property mixed $0
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription active()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereGatewayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription wherePackageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription wherePayfastPlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription wherePayfastStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription wherePlanType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereRazorpayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereRazorpayPlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereStripePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereStripeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereSubscribedOnDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereSubscriptionStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSubscription whereUserId($value)
 */
	class GlobalSubscription extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\MollieInvoice
 *
 * @property int $id
 * @property int $company_id
 * @property int $package_id
 * @property string|null $transaction_id
 * @property string|null $amount
 * @property string|null $package_type
 * @property Carbon|null $pay_date
 * @property Carbon|null $next_pay_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read Package $package
 * @method static Builder|MollieInvoice newModelQuery()
 * @method static Builder|MollieInvoice newQuery()
 * @method static Builder|MollieInvoice query()
 * @method static Builder|MollieInvoice whereAmount($value)
 * @method static Builder|MollieInvoice whereCompanyId($value)
 * @method static Builder|MollieInvoice whereCreatedAt($value)
 * @method static Builder|MollieInvoice whereId($value)
 * @method static Builder|MollieInvoice whereNextPayDate($value)
 * @method static Builder|MollieInvoice wherePackageId($value)
 * @method static Builder|MollieInvoice wherePackageType($value)
 * @method static Builder|MollieInvoice wherePayDate($value)
 * @method static Builder|MollieInvoice whereTransactionId($value)
 * @method static Builder|MollieInvoice whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class MollieInvoice extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\MollieSubscription
 *
 * @property int $id
 * @property int|null $company_id
 * @property string|null $customer_id
 * @property string|null $subscription_id
 * @property string|null $ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MollieSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MollieSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MollieSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|MollieSubscription whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MollieSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MollieSubscription whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MollieSubscription whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MollieSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MollieSubscription whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MollieSubscription whereUpdatedAt($value)
 */
	class MollieSubscription extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\OfflineInvoice
 *
 * @property int $id
 * @property int $company_id
 * @property int $package_id
 * @property string|null $package_type
 * @property int|null $offline_method_id
 * @property string|null $transaction_id
 * @property string $amount
 * @property Carbon $pay_date
 * @property Carbon|null $next_pay_date
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read OfflinePaymentMethod|null $offlinePaymentMethod
 * @property-read OfflinePlanChange|null $offlinePlanChangeRequest
 * @property-read Package $package
 * @method static Builder|OfflineInvoice newModelQuery()
 * @method static Builder|OfflineInvoice newQuery()
 * @method static Builder|OfflineInvoice query()
 * @method static Builder|OfflineInvoice whereAmount($value)
 * @method static Builder|OfflineInvoice whereCompanyId($value)
 * @method static Builder|OfflineInvoice whereCreatedAt($value)
 * @method static Builder|OfflineInvoice whereId($value)
 * @method static Builder|OfflineInvoice whereNextPayDate($value)
 * @method static Builder|OfflineInvoice whereOfflineMethodId($value)
 * @method static Builder|OfflineInvoice wherePackageId($value)
 * @method static Builder|OfflineInvoice wherePackageType($value)
 * @method static Builder|OfflineInvoice wherePayDate($value)
 * @method static Builder|OfflineInvoice whereStatus($value)
 * @method static Builder|OfflineInvoice whereTransactionId($value)
 * @method static Builder|OfflineInvoice whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class OfflineInvoice extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\OfflinePlanChange
 *
 * @property int $id
 * @property int $company_id
 * @property int $package_id
 * @property string $package_type
 * @property int $invoice_id
 * @property int $offline_method_id
 * @property string|null $file_name
 * @property string $status
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read mixed $file
 * @property-read OfflinePaymentMethod $offlineMethod
 * @property-read Package $package
 * @method static Builder|OfflinePlanChange newModelQuery()
 * @method static Builder|OfflinePlanChange newQuery()
 * @method static Builder|OfflinePlanChange query()
 * @method static Builder|OfflinePlanChange whereCompanyId($value)
 * @method static Builder|OfflinePlanChange whereCreatedAt($value)
 * @method static Builder|OfflinePlanChange whereDescription($value)
 * @method static Builder|OfflinePlanChange whereFileName($value)
 * @method static Builder|OfflinePlanChange whereId($value)
 * @method static Builder|OfflinePlanChange whereInvoiceId($value)
 * @method static Builder|OfflinePlanChange whereOfflineMethodId($value)
 * @method static Builder|OfflinePlanChange wherePackageId($value)
 * @method static Builder|OfflinePlanChange wherePackageType($value)
 * @method static Builder|OfflinePlanChange whereStatus($value)
 * @method static Builder|OfflinePlanChange whereUpdatedAt($value)
 * @mixin Eloquent
 * @property float|null $amount
 * @property \Illuminate\Support\Carbon|null $pay_date
 * @property \Illuminate\Support\Carbon|null $next_pay_date
 * @property string|null $remark
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePlanChange whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePlanChange whereNextPayDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePlanChange wherePayDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePlanChange whereRemark($value)
 */
	class OfflinePlanChange extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\Package
 *
 * @property int $id
 * @property int|null $currency_id
 * @property string $name
 * @property string|null $description
 * @property int $max_storage_size
 * @property int $max_file_size
 * @property string $annual_price
 * @property string $monthly_price
 * @property int $billing_cycle
 * @property int $max_employees
 * @property string $sort
 * @property string $module_in_package
 * @property string $stripe_annual_plan_id
 * @property string $stripe_monthly_plan_id
 * @property string|null $razorpay_annual_plan_id
 * @property string|null $razorpay_monthly_plan_id
 * @property string|null $default
 * @property string|null $paystack_monthly_plan_id
 * @property string|null $paystack_annual_plan_id
 * @property int $is_private
 * @property string $storage_unit
 * @property int $is_recommended
 * @property int $is_free
 * @property int $contact_to_buy
 * @property int $is_auto_renew
 * @property string|null $monthly_status
 * @property string|null $annual_status
 * @property string|null $contact_button_text
 * @property string|null $contact_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Package newModelQuery()
 * @method static Builder|Package newQuery()
 * @method static Builder|Package query()
 * @method static Builder|Package whereAnnualPrice($value)
 * @method static Builder|Package whereAnnualStatus($value)
 * @method static Builder|Package whereBillingCycle($value)
 * @method static Builder|Package whereCreatedAt($value)
 * @method static Builder|Package whereCurrencyId($value)
 * @method static Builder|Package whereDefault($value)
 * @method static Builder|Package whereDescription($value)
 * @method static Builder|Package whereId($value)
 * @method static Builder|Package whereIsAutoRenew($value)
 * @method static Builder|Package whereIsFree($value)
 * @method static Builder|Package whereIsPrivate($value)
 * @method static Builder|Package whereIsRecommended($value)
 * @method static Builder|Package whereMaxEmployees($value)
 * @method static Builder|Package whereMaxFileSize($value)
 * @method static Builder|Package whereMaxStorageSize($value)
 * @method static Builder|Package whereModuleInPackage($value)
 * @method static Builder|Package whereMonthlyPrice($value)
 * @method static Builder|Package whereMonthlyStatus($value)
 * @method static Builder|Package whereName($value)
 * @method static Builder|Package wherePaystackAnnualPlanId($value)
 * @method static Builder|Package wherePaystackMonthlyPlanId($value)
 * @method static Builder|Package whereRazorpayAnnualPlanId($value)
 * @method static Builder|Package whereRazorpayMonthlyPlanId($value)
 * @method static Builder|Package whereSort($value)
 * @method static Builder|Package whereStorageUnit($value)
 * @method static Builder|Package whereStripeAnnualPlanId($value)
 * @method static Builder|Package whereStripeMonthlyPlanId($value)
 * @method static Builder|Package whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|Company[] $companies
 * @property-read int|null $companies_count
 * @property-read \App\Models\SuperAdmin\GlobalCurrency|null $currency
 * @property-read mixed $formatted_annual_price
 * @property-read mixed $formatted_monthly_price
 * @property string|null $price
 * @property string|null $package
 * @method static \Illuminate\Database\Eloquent\Builder|Package wherePackage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package wherePrice($value)
 */
	class Package extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\PackageSetting
 *
 * @property int $id
 * @property string $status
 * @property int|null $no_of_days
 * @property string|null $modules
 * @property string|null $trial_message
 * @property int|null $notification_before
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $all_packages
 * @method static Builder|PackageSetting newModelQuery()
 * @method static Builder|PackageSetting newQuery()
 * @method static Builder|PackageSetting query()
 * @method static Builder|PackageSetting whereCreatedAt($value)
 * @method static Builder|PackageSetting whereId($value)
 * @method static Builder|PackageSetting whereModules($value)
 * @method static Builder|PackageSetting whereNoOfDays($value)
 * @method static Builder|PackageSetting whereNotificationBefore($value)
 * @method static Builder|PackageSetting whereStatus($value)
 * @method static Builder|PackageSetting whereTrialMessage($value)
 * @method static Builder|PackageSetting whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class PackageSetting extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\PayfastInvoice
 *
 * @property int $id
 * @property int|null $company_id
 * @property int|null $package_id
 * @property string|null $m_payment_id
 * @property string|null $pf_payment_id
 * @property string|null $payfast_plan
 * @property string|null $amount
 * @property Carbon|null $pay_date
 * @property Carbon|null $next_pay_date
 * @property string|null $signature
 * @property string|null $token
 * @property string|null $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company|null $company
 * @property-read Package|null $package
 * @method static Builder|PayfastInvoice newModelQuery()
 * @method static Builder|PayfastInvoice newQuery()
 * @method static Builder|PayfastInvoice query()
 * @method static Builder|PayfastInvoice whereAmount($value)
 * @method static Builder|PayfastInvoice whereCompanyId($value)
 * @method static Builder|PayfastInvoice whereCreatedAt($value)
 * @method static Builder|PayfastInvoice whereId($value)
 * @method static Builder|PayfastInvoice whereMPaymentId($value)
 * @method static Builder|PayfastInvoice whereNextPayDate($value)
 * @method static Builder|PayfastInvoice wherePackageId($value)
 * @method static Builder|PayfastInvoice wherePayDate($value)
 * @method static Builder|PayfastInvoice wherePayfastPlan($value)
 * @method static Builder|PayfastInvoice wherePfPaymentId($value)
 * @method static Builder|PayfastInvoice whereSignature($value)
 * @method static Builder|PayfastInvoice whereStatus($value)
 * @method static Builder|PayfastInvoice whereToken($value)
 * @method static Builder|PayfastInvoice whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class PayfastInvoice extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\PayfastSubscription
 *
 * @property int $id
 * @property int|null $company_id
 * @property string|null $payfast_plan
 * @property int|null $quantity
 * @property string $payfast_status
 * @property string|null $ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PayfastSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayfastSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayfastSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|PayfastSubscription whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayfastSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayfastSubscription whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayfastSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayfastSubscription wherePayfastPlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayfastSubscription wherePayfastStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayfastSubscription whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayfastSubscription whereUpdatedAt($value)
 */
	class PayfastSubscription extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\PaypalInvoice
 *
 * @property int $id
 * @property int|null $company_id
 * @property int|null $currency_id
 * @property int|null $package_id
 * @property float|null $sub_total
 * @property float|null $total
 * @property string|null $transaction_id
 * @property string|null $remarks
 * @property string|null $billing_frequency
 * @property int|null $billing_interval
 * @property Carbon|null $pay_date
 * @property Carbon|null $next_pay_date
 * @property string|null $recurring
 * @property string|null $status
 * @property string|null $plan_id
 * @property string|null $event_id
 * @property string|null $end_on
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company|null $company
 * @property-read Package|null $package
 * @method static Builder|PaypalInvoice newModelQuery()
 * @method static Builder|PaypalInvoice newQuery()
 * @method static Builder|PaypalInvoice query()
 * @method static Builder|PaypalInvoice whereBillingFrequency($value)
 * @method static Builder|PaypalInvoice whereBillingInterval($value)
 * @method static Builder|PaypalInvoice whereCompanyId($value)
 * @method static Builder|PaypalInvoice whereCreatedAt($value)
 * @method static Builder|PaypalInvoice whereCurrencyId($value)
 * @method static Builder|PaypalInvoice whereEndOn($value)
 * @method static Builder|PaypalInvoice whereEventId($value)
 * @method static Builder|PaypalInvoice whereId($value)
 * @method static Builder|PaypalInvoice whereNextPayDate($value)
 * @method static Builder|PaypalInvoice wherePackageId($value)
 * @method static Builder|PaypalInvoice wherePaidOn($value)
 * @method static Builder|PaypalInvoice wherePlanId($value)
 * @method static Builder|PaypalInvoice whereRecurring($value)
 * @method static Builder|PaypalInvoice whereRemarks($value)
 * @method static Builder|PaypalInvoice whereStatus($value)
 * @method static Builder|PaypalInvoice whereSubTotal($value)
 * @method static Builder|PaypalInvoice whereTotal($value)
 * @method static Builder|PaypalInvoice whereTransactionId($value)
 * @method static Builder|PaypalInvoice whereUpdatedAt($value)
 * @mixin Eloquent
 * @property Carbon|null $paid_on
 * @property-read \App\Models\SuperAdmin\GlobalCurrency|null $currency
 */
	class PaypalInvoice extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\PaystackInvoice
 *
 * @property int $id
 * @property int $company_id
 * @property int $package_id
 * @property string|null $transaction_id
 * @property string|null $amount
 * @property Carbon $pay_date
 * @property Carbon|null $next_pay_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read Package $package
 * @method static Builder|PaystackInvoice newModelQuery()
 * @method static Builder|PaystackInvoice newQuery()
 * @method static Builder|PaystackInvoice query()
 * @method static Builder|PaystackInvoice whereAmount($value)
 * @method static Builder|PaystackInvoice whereCompanyId($value)
 * @method static Builder|PaystackInvoice whereCreatedAt($value)
 * @method static Builder|PaystackInvoice whereId($value)
 * @method static Builder|PaystackInvoice whereNextPayDate($value)
 * @method static Builder|PaystackInvoice wherePackageId($value)
 * @method static Builder|PaystackInvoice wherePayDate($value)
 * @method static Builder|PaystackInvoice whereTransactionId($value)
 * @method static Builder|PaystackInvoice whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class PaystackInvoice extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\PaystackSubscription
 *
 * @property int $id
 * @property int $company_id
 * @property string|null $subscription_id
 * @property string|null $customer_id
 * @property string $token
 * @property string $plan_id
 * @property string $status
 * @property string|null $ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property mixed $0
 * @property-read \App\Models\Company $company
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackSubscription whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackSubscription whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackSubscription whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackSubscription wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackSubscription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackSubscription whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackSubscription whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackSubscription whereUpdatedAt($value)
 */
	class PaystackSubscription extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\RazorpayInvoice
 *
 * @property int $id
 * @property int $company_id
 * @property int|null $currency_id
 * @property string $invoice_id
 * @property string $subscription_id
 * @property string|null $order_id
 * @property int $package_id
 * @property string $transaction_id
 * @property string $amount
 * @property Carbon|null $pay_date
 * @property Carbon|null $next_pay_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read Package $package
 * @method static Builder|RazorpayInvoice newModelQuery()
 * @method static Builder|RazorpayInvoice newQuery()
 * @method static Builder|RazorpayInvoice query()
 * @method static Builder|RazorpayInvoice whereAmount($value)
 * @method static Builder|RazorpayInvoice whereCompanyId($value)
 * @method static Builder|RazorpayInvoice whereCreatedAt($value)
 * @method static Builder|RazorpayInvoice whereCurrencyId($value)
 * @method static Builder|RazorpayInvoice whereId($value)
 * @method static Builder|RazorpayInvoice whereInvoiceId($value)
 * @method static Builder|RazorpayInvoice whereNextPayDate($value)
 * @method static Builder|RazorpayInvoice whereOrderId($value)
 * @method static Builder|RazorpayInvoice wherePackageId($value)
 * @method static Builder|RazorpayInvoice wherePayDate($value)
 * @method static Builder|RazorpayInvoice whereSubscriptionId($value)
 * @method static Builder|RazorpayInvoice whereTransactionId($value)
 * @method static Builder|RazorpayInvoice whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read \App\Models\SuperAdmin\GlobalCurrency|null $currency
 */
	class RazorpayInvoice extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\RazorpaySubscription
 *
 * @property int $id
 * @property int $company_id
 * @property string|null $subscription_id
 * @property string|null $customer_id
 * @property string $name
 * @property string $razorpay_id
 * @property string $razorpay_plan
 * @property int $quantity
 * @property string|null $trial_ends_at
 * @property string|null $ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property mixed $0
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Currency|null $currency
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription whereRazorpayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription whereRazorpayPlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RazorpaySubscription whereUpdatedAt($value)
 */
	class RazorpaySubscription extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\SeoDetail
 *
 * @property int $id
 * @property string $page_name
 * @property string|null $seo_title
 * @property string|null $seo_keywords
 * @property string|null $seo_description
 * @property string|null $seo_author
 * @property int|null $language_setting_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $og_image_url
 * @method static Builder|SeoDetail newModelQuery()
 * @method static Builder|SeoDetail newQuery()
 * @method static Builder|SeoDetail query()
 * @method static Builder|SeoDetail whereCreatedAt($value)
 * @method static Builder|SeoDetail whereId($value)
 * @method static Builder|SeoDetail whereLanguageSettingId($value)
 * @method static Builder|SeoDetail wherePageName($value)
 * @method static Builder|SeoDetail whereSeoAuthor($value)
 * @method static Builder|SeoDetail whereSeoDescription($value)
 * @method static Builder|SeoDetail whereSeoKeywords($value)
 * @method static Builder|SeoDetail whereSeoTitle($value)
 * @method static Builder|SeoDetail whereUpdatedAt($value)
 * @mixin Eloquent
 * @property string|null $og_image
 * @method static Builder|SeoDetail whereOgImage($value)
 * @property-read \App\Models\LanguageSetting|null $language
 * @property-read mixed $masked_og_image_url
 */
	class SeoDetail extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\SignUpSetting
 *
 * @property int $id
 * @property int|null $language_setting_id
 * @property string|null $message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read LanguageSetting|null $language
 * @method static Builder|SignUpSetting newModelQuery()
 * @method static Builder|SignUpSetting newQuery()
 * @method static Builder|SignUpSetting query()
 * @method static Builder|SignUpSetting whereCreatedAt($value)
 * @method static Builder|SignUpSetting whereId($value)
 * @method static Builder|SignUpSetting whereLanguageSettingId($value)
 * @method static Builder|SignUpSetting whereMessage($value)
 * @method static Builder|SignUpSetting whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class SignUpSetting extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\StripeInvoice
 *
 * @property int $id
 * @property int $company_id
 * @property string|null $invoice_id
 * @property int $package_id
 * @property string|null $transaction_id
 * @property string $amount
 * @property Carbon|null $pay_date
 * @property Carbon|null $next_pay_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read Package $package
 * @method static Builder|StripeInvoice newModelQuery()
 * @method static Builder|StripeInvoice newQuery()
 * @method static Builder|StripeInvoice query()
 * @method static Builder|StripeInvoice whereAmount($value)
 * @method static Builder|StripeInvoice whereCompanyId($value)
 * @method static Builder|StripeInvoice whereCreatedAt($value)
 * @method static Builder|StripeInvoice whereId($value)
 * @method static Builder|StripeInvoice whereInvoiceId($value)
 * @method static Builder|StripeInvoice whereNextPayDate($value)
 * @method static Builder|StripeInvoice wherePackageId($value)
 * @method static Builder|StripeInvoice wherePayDate($value)
 * @method static Builder|StripeInvoice whereTransactionId($value)
 * @method static Builder|StripeInvoice whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class StripeInvoice extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\StripeSetting
 *
 * @property int $id
 * @property string|null $api_key
 * @property string|null $api_secret
 * @property string|null $webhook_key
 * @property string|null $paypal_client_id
 * @property string|null $paypal_secret
 * @property string $paypal_status
 * @property string $stripe_status
 * @property string|null $razorpay_key
 * @property string|null $razorpay_secret
 * @property string|null $razorpay_webhook_secret
 * @property string $razorpay_status
 * @property string $paypal_mode
 * @property string|null $paystack_client_id
 * @property string|null $paystack_secret
 * @property string|null $paystack_status
 * @property string|null $paystack_merchant_email
 * @property string|null $paystack_payment_url
 * @property string $mollie_api_key
 * @property string $mollie_status
 * @property string|null $authorize_api_login_id
 * @property string|null $authorize_transaction_key
 * @property string|null $authorize_signature_key
 * @property string|null $authorize_environment
 * @property string $authorize_status
 * @property string|null $payfast_key
 * @property string|null $payfast_secret
 * @property string $payfast_status
 * @property string|null $payfast_salt_passphrase
 * @property string $payfast_mode
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $show_pay
 * @method static Builder|StripeSetting newModelQuery()
 * @method static Builder|StripeSetting newQuery()
 * @method static Builder|StripeSetting query()
 * @method static Builder|StripeSetting whereApiKey($value)
 * @method static Builder|StripeSetting whereApiSecret($value)
 * @method static Builder|StripeSetting whereAuthorizeApiLoginId($value)
 * @method static Builder|StripeSetting whereAuthorizeEnvironment($value)
 * @method static Builder|StripeSetting whereAuthorizeSignatureKey($value)
 * @method static Builder|StripeSetting whereAuthorizeStatus($value)
 * @method static Builder|StripeSetting whereAuthorizeTransactionKey($value)
 * @method static Builder|StripeSetting whereCreatedAt($value)
 * @method static Builder|StripeSetting whereId($value)
 * @method static Builder|StripeSetting whereMollieApiKey($value)
 * @method static Builder|StripeSetting whereMollieStatus($value)
 * @method static Builder|StripeSetting wherePayfastKey($value)
 * @method static Builder|StripeSetting wherePayfastMode($value)
 * @method static Builder|StripeSetting wherePayfastSaltPassphrase($value)
 * @method static Builder|StripeSetting wherePayfastSecret($value)
 * @method static Builder|StripeSetting wherePayfastStatus($value)
 * @method static Builder|StripeSetting wherePaypalClientId($value)
 * @method static Builder|StripeSetting wherePaypalMode($value)
 * @method static Builder|StripeSetting wherePaypalSecret($value)
 * @method static Builder|StripeSetting wherePaypalStatus($value)
 * @method static Builder|StripeSetting wherePaystackClientId($value)
 * @method static Builder|StripeSetting wherePaystackMerchantEmail($value)
 * @method static Builder|StripeSetting wherePaystackPaymentUrl($value)
 * @method static Builder|StripeSetting wherePaystackSecret($value)
 * @method static Builder|StripeSetting wherePaystackStatus($value)
 * @method static Builder|StripeSetting whereRazorpayKey($value)
 * @method static Builder|StripeSetting whereRazorpaySecret($value)
 * @method static Builder|StripeSetting whereRazorpayStatus($value)
 * @method static Builder|StripeSetting whereRazorpayWebhookSecret($value)
 * @method static Builder|StripeSetting whereStripeStatus($value)
 * @method static Builder|StripeSetting whereUpdatedAt($value)
 * @method static Builder|StripeSetting whereWebhookKey($value)
 * @mixin Eloquent
 */
	class StripeSetting extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\Subscription
 *
 * @property int $id
 * @property int|null $company_id
 * @property int|null $user_id
 * @property string $name
 * @property string $stripe_id
 * @property string $stripe_status
 * @property string|null $stripe_price
 * @property int|null $quantity
 * @property string|null $trial_ends_at
 * @property string|null $ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property mixed $0
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereStripePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereStripeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscription whereUserId($value)
 */
	class Subscription extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\SupportTicket
 *
 * @property-read User $agent
 * @property-read User $client
 * @property-read mixed $created_on
 * @property-read mixed $updated_on
 * @property-read Collection|SupportTicketReply[] $reply
 * @property-read int|null $reply_count
 * @property-read User $requester
 * @method static Builder|SupportTicket newModelQuery()
 * @method static Builder|SupportTicket newQuery()
 * @method static \Illuminate\Database\Query\Builder|SupportTicket onlyTrashed()
 * @method static Builder|SupportTicket query()
 * @method static \Illuminate\Database\Query\Builder|SupportTicket withTrashed()
 * @method static \Illuminate\Database\Query\Builder|SupportTicket withoutTrashed()
 * @mixin Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $created_by
 * @property string $subject
 * @property string $description
 * @property string $status
 * @property string $priority
 * @property int|null $agent_id
 * @property int|null $support_ticket_type_id
 * @property int|null $company_id
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|SupportTicket whereAgentId($value)
 * @method static Builder|SupportTicket whereCreatedAt($value)
 * @method static Builder|SupportTicket whereCreatedBy($value)
 * @method static Builder|SupportTicket whereDeletedAt($value)
 * @method static Builder|SupportTicket whereDescription($value)
 * @method static Builder|SupportTicket whereId($value)
 * @method static Builder|SupportTicket wherePriority($value)
 * @method static Builder|SupportTicket whereStatus($value)
 * @method static Builder|SupportTicket whereSubject($value)
 * @method static Builder|SupportTicket whereSupportTicketTypeId($value)
 * @method static Builder|SupportTicket whereUpdatedAt($value)
 * @method static Builder|SupportTicket whereUserId($value)
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\SuperAdmin\SupportTicketReply|null $latestReply
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket whereCompanyId($value)
 */
	class SupportTicket extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\SupportTicketFile
 *
 * @property-read mixed $file_url
 * @method static Builder|SupportTicketFile newModelQuery()
 * @method static Builder|SupportTicketFile newQuery()
 * @method static Builder|SupportTicketFile query()
 * @mixin Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $support_ticket_reply_id
 * @property string $filename
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $dropbox_link
 * @property string|null $external_link
 * @property string|null $external_link_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static Builder|SupportTicketFile whereCreatedAt($value)
 * @method static Builder|SupportTicketFile whereDescription($value)
 * @method static Builder|SupportTicketFile whereDropboxLink($value)
 * @method static Builder|SupportTicketFile whereExternalLink($value)
 * @method static Builder|SupportTicketFile whereExternalLinkName($value)
 * @method static Builder|SupportTicketFile whereFilename($value)
 * @method static Builder|SupportTicketFile whereGoogleUrl($value)
 * @method static Builder|SupportTicketFile whereHashname($value)
 * @method static Builder|SupportTicketFile whereId($value)
 * @method static Builder|SupportTicketFile whereSize($value)
 * @method static Builder|SupportTicketFile whereSupportTicketReplyId($value)
 * @method static Builder|SupportTicketFile whereUpdatedAt($value)
 * @method static Builder|SupportTicketFile whereUserId($value)
 */
	class SupportTicketFile extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\SupportTicketReply
 *
 * @property-read Collection|SupportTicketFile[] $files
 * @property-read int|null $files_count
 * @property-read SupportTicket|null $ticket
 * @property-read User $user
 * @method static Builder|SupportTicketReply newModelQuery()
 * @method static Builder|SupportTicketReply newQuery()
 * @method static \Illuminate\Database\Query\Builder|SupportTicketReply onlyTrashed()
 * @method static Builder|SupportTicketReply query()
 * @method static \Illuminate\Database\Query\Builder|SupportTicketReply withTrashed()
 * @method static \Illuminate\Database\Query\Builder|SupportTicketReply withoutTrashed()
 * @mixin Eloquent
 * @property int $id
 * @property int $support_ticket_id
 * @property int $user_id
 * @property string $message
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|SupportTicketReply whereCreatedAt($value)
 * @method static Builder|SupportTicketReply whereDeletedAt($value)
 * @method static Builder|SupportTicketReply whereId($value)
 * @method static Builder|SupportTicketReply whereMessage($value)
 * @method static Builder|SupportTicketReply whereSupportTicketId($value)
 * @method static Builder|SupportTicketReply whereUpdatedAt($value)
 * @method static Builder|SupportTicketReply whereUserId($value)
 * @property mixed $0
 */
	class SupportTicketReply extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\SupportTicketType
 *
 * @property int $id
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|SupportTicketType newModelQuery()
 * @method static Builder|SupportTicketType newQuery()
 * @method static Builder|SupportTicketType query()
 * @method static Builder|SupportTicketType whereCreatedAt($value)
 * @method static Builder|SupportTicketType whereId($value)
 * @method static Builder|SupportTicketType whereType($value)
 * @method static Builder|SupportTicketType whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|SupportTicket[] $tickets
 * @property-read int|null $tickets_count
 */
	class SupportTicketType extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\Testimonials
 *
 * @property int $id
 * @property string $name
 * @property string|null $comment
 * @property float|null $rating
 * @property int|null $language_setting_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read LanguageSetting|null $language
 * @method static Builder|Testimonials newModelQuery()
 * @method static Builder|Testimonials newQuery()
 * @method static Builder|Testimonials query()
 * @method static Builder|Testimonials whereComment($value)
 * @method static Builder|Testimonials whereCreatedAt($value)
 * @method static Builder|Testimonials whereId($value)
 * @method static Builder|Testimonials whereLanguageSettingId($value)
 * @method static Builder|Testimonials whereName($value)
 * @method static Builder|Testimonials whereRating($value)
 * @method static Builder|Testimonials whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class Testimonials extends \Eloquent {}
}

namespace App\Models\SuperAdmin{
/**
 * App\Models\SuperAdmin\TrFrontDetail
 *
 * @property int $id
 * @property int|null $language_setting_id
 * @property string $header_title
 * @property string $header_description
 * @property string $image
 * @property string|null $feature_title
 * @property string|null $feature_description
 * @property string|null $price_title
 * @property string|null $price_description
 * @property string|null $task_management_title
 * @property string|null $task_management_detail
 * @property string|null $manage_bills_title
 * @property string|null $manage_bills_detail
 * @property string|null $teamates_title
 * @property string|null $teamates_detail
 * @property string|null $favourite_apps_title
 * @property string|null $favourite_apps_detail
 * @property string|null $cta_title
 * @property string|null $cta_detail
 * @property string|null $client_title
 * @property string|null $client_detail
 * @property string|null $testimonial_title
 * @property string|null $testimonial_detail
 * @property string|null $faq_title
 * @property string|null $faq_detail
 * @property string|null $footer_copyright_text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $image_url
 * @method static Builder|TrFrontDetail newModelQuery()
 * @method static Builder|TrFrontDetail newQuery()
 * @method static Builder|TrFrontDetail query()
 * @method static Builder|TrFrontDetail whereClientDetail($value)
 * @method static Builder|TrFrontDetail whereClientTitle($value)
 * @method static Builder|TrFrontDetail whereCreatedAt($value)
 * @method static Builder|TrFrontDetail whereCtaDetail($value)
 * @method static Builder|TrFrontDetail whereCtaTitle($value)
 * @method static Builder|TrFrontDetail whereFaqDetail($value)
 * @method static Builder|TrFrontDetail whereFaqTitle($value)
 * @method static Builder|TrFrontDetail whereFavouriteAppsDetail($value)
 * @method static Builder|TrFrontDetail whereFavouriteAppsTitle($value)
 * @method static Builder|TrFrontDetail whereFeatureDescription($value)
 * @method static Builder|TrFrontDetail whereFeatureTitle($value)
 * @method static Builder|TrFrontDetail whereFooterCopyrightText($value)
 * @method static Builder|TrFrontDetail whereHeaderDescription($value)
 * @method static Builder|TrFrontDetail whereHeaderTitle($value)
 * @method static Builder|TrFrontDetail whereId($value)
 * @method static Builder|TrFrontDetail whereImage($value)
 * @method static Builder|TrFrontDetail whereLanguageSettingId($value)
 * @method static Builder|TrFrontDetail whereManageBillsDetail($value)
 * @method static Builder|TrFrontDetail whereManageBillsTitle($value)
 * @method static Builder|TrFrontDetail wherePriceDescription($value)
 * @method static Builder|TrFrontDetail wherePriceTitle($value)
 * @method static Builder|TrFrontDetail whereTaskManagementDetail($value)
 * @method static Builder|TrFrontDetail whereTaskManagementTitle($value)
 * @method static Builder|TrFrontDetail whereTeamatesDetail($value)
 * @method static Builder|TrFrontDetail whereTeamatesTitle($value)
 * @method static Builder|TrFrontDetail whereTestimonialDetail($value)
 * @method static Builder|TrFrontDetail whereTestimonialTitle($value)
 * @method static Builder|TrFrontDetail whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read LanguageSetting|null $language
 */
	class TrFrontDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Task
 *
 * @property int $id
 * @property string $heading
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property int|null $project_id
 * @property int|null $task_category_id
 * @property string $priority
 * @property string $status
 * @property int|null $board_column_id
 * @property int $column_priority
 * @property \Illuminate\Support\Carbon|null $completed_on
 * @property int|null $created_by
 * @property int|null $recurring_task_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Task[] $recurrings
 * @property-read int|null $recurrings_count
 * @property int|null $dependent_task_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $milestone_id
 * @property int $is_private
 * @property int $billable
 * @property int $estimate_hours
 * @property int $estimate_minutes
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTimeLog[] $activeTimerAll
 * @property-read int|null $active_timer_all_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTimeLog[] $approvedTimeLogs
 * @property-read int|null $approved_time_logs_count
 * @property-read \App\Models\TaskboardColumn|null $boardColumn
 * @property-read \App\Models\TaskCategory|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskComment[] $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SubTask[] $completedSubtasks
 * @property-read int|null $completed_subtasks_count
 * @property-read \App\Models\User|null $createBy
 * @property-read \App\Models\User|null $addedByUser
 * @property-read \App\Models\ProjectMilestone|null $milestone
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $create_on
 * @property-read string $due_on
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $is_task_user
 * @property-read mixed $total_estimated_minutes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskHistory[] $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SubTask[] $incompleteSubtasks
 * @property-read int|null $incomplete_subtasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskLabel[] $label
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskUser[] $taskUsers
 * @property-read int|null $label_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskLabelList[] $labels
 * @property-read int|null $labels_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskNote[] $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SubTask[] $subtasks
 * @property-read int|null $subtasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTimeLog[] $timeLogged
 * @property-read int|null $time_logged_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task pending()
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereBillable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereBoardColumnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereColumnPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCompletedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDependentTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereEstimateHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereEstimateMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereHeading($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereIsPrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereMilestoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereRecurringTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereTaskCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUpdatedAt($value)
 * @property string|null $hash
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereHash($value)
 * @property int $repeat
 * @property int $repeat_complete
 * @property int|null $repeat_count
 * @property string $repeat_type
 * @property int|null $repeat_cycles
 * @property-read \App\Models\ProjectTimeLog|null $activeTimer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $activeUsers
 * @property-read int|null $active_users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereRepeat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereRepeatComplete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereRepeatCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereRepeatCycles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereRepeatType($value)
 * @property string|null $event_id
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereEventId($value)
 * @property int|null $company_id
 * @property string|null $task_short_code
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Project|null $activeProject
 * @property-read \App\Models\Company|null $company
 * @property-read int|null $task_users_count
 * @method static \Illuminate\Database\Query\Builder|Task onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereTaskShortCode($value)
 * @method static \Illuminate\Database\Query\Builder|Task withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Task withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionTask
 * @property-read int|null $mention_task_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionTask
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @mixin \Eloquent
 * @property int $approval_send
 * @property-read Task|null $dependentTask
 * @property-read Task|null $recurringTask
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereApprovalSend($value)
 */
	class Task extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskCategory
 *
 * @property int $id
 * @property string $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCategory whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCategory whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCategory whereCompanyId($value)
 * @mixin \Eloquent
 */
	class TaskCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskComment
 *
 * @property int $id
 * @property string $comment
 * @property int $user_id
 * @property int $task_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $commentEmoji
 * @property-read int|null $comment_emoji_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $dislike
 * @property-read int|null $dislike_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $dislikeUsers
 * @property-read int|null $dislike_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $like
 * @property-read int|null $like_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $likeUsers
 * @property-read int|null $like_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $commentEmoji
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $dislike
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $dislikeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $like
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $likeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $commentEmoji
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $dislike
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $dislikeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $like
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $likeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $commentEmoji
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $dislike
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $dislikeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $like
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $likeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $commentEmoji
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $dislike
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $dislikeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $like
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $likeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $commentEmoji
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $dislike
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $dislikeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $like
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $likeUsers
 * @property string|null $mention_user_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $commentEmoji
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $dislike
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $dislikeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $like
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $likeUsers
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereMentionUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $commentEmoji
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $dislike
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $dislikeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $like
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $likeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionComment
 * @property-read int|null $mention_comment_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $commentEmoji
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $dislike
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $dislikeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $like
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $likeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionComment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @mixin \Eloquent
 */
	class TaskComment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskCommentEmoji
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $comment_id
 * @property string|null $emoji_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TaskComment|null $taskComment
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji whereEmojiName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskCommentEmoji whereUserId($value)
 * @mixin \Eloquent
 */
	class TaskCommentEmoji extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskFile
 *
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property string $filename
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $dropbox_link
 * @property string|null $external_link
 * @property string|null $external_link_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereUserId($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class TaskFile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskHistory
 *
 * @property int $id
 * @property int $task_id
 * @property int|null $sub_task_id
 * @property int $user_id
 * @property string $details
 * @property int|null $board_column_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TaskboardColumn|null $boardColumn
 * @property-read mixed $icon
 * @property-read \App\Models\SubTask|null $subTask
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereBoardColumnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereSubTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereUserId($value)
 * @mixin \Eloquent
 */
	class TaskHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskLabel
 *
 * @property int $id
 * @property int $label_id
 * @property int $task_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\TaskLabelList $label
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabel query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabel whereLabelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabel whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class TaskLabel extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskLabelList
 *
 * @property int $id
 * @property string $label_name
 * @property string|null $color
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read mixed $label_color
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereLabelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereUpdatedAt($value)
 * @property int|null $project_id
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskLabelList whereCompanyId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Company $company
 */
	class TaskLabelList extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskNote
 *
 * @property int $id
 * @property int $task_id
 * @property int|null $user_id
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskNote whereUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionNote
 * @property-read int|null $mention_note_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionNote
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @mixin \Eloquent
 */
	class TaskNote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskSetting
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $task_category
 * @property string $project
 * @property string $start_date
 * @property string $due_date
 * @property string $assigned_to
 * @property string $assigned_by
 * @property string $description
 * @property string $label
 * @property string $status
 * @property string $priority
 * @property string $make_private
 * @property string $time_estimate
 * @property string $hours_logged
 * @property string $custom_fields
 * @property string $copy_task_link
 * @property string $files
 * @property string $sub_task
 * @property string $comments
 * @property string $time_logs
 * @property string $notes
 * @property string $history
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereAssignedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereCopyTaskLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereCustomFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereFiles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereHoursLogged($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereMakePrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereSubTask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereTaskCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereTimeEstimate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereTimeLogs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereUpdatedAt($value)
 * @property-read \App\Models\Company|null $company
 * @mixin \Eloquent
 * @property string $project_required
 * @method static \Illuminate\Database\Eloquent\Builder|TaskSetting whereProjectRequired($value)
 */
	class TaskSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskTag
 *
 * @property-read mixed $icon
 * @property-read \App\Models\TaskTagList $tag
 * @method static \Illuminate\Database\Eloquent\Builder|TaskTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskTag query()
 * @mixin \Eloquent
 */
	class TaskTag extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskTagList
 *
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|TaskTagList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskTagList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskTagList query()
 * @mixin \Eloquent
 */
	class TaskTagList extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskUser
 *
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskUser whereUserId($value)
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @mixin \Eloquent
 */
	class TaskUser extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TaskboardColumn
 *
 * @property int $id
 * @property string $column_name
 * @property string|null $slug
 * @property string $label_color
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereColumnName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereCompanyId($value)
 * @mixin \Eloquent
 */
	class TaskboardColumn extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Tax
 *
 * @property int $id
 * @property string $tax_name
 * @property string $rate_percent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|Tax newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tax newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tax query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereRatePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereTaxName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereUpdatedAt($value)
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Query\Builder|Tax onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Tax withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Tax withoutTrashed()
 * @mixin \Eloquent
 */
	class Tax extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Team
 *
 * @property int $id
 * @property string $team_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmployeeDetails[] $teamMembers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Team[] $childs
 * @property-read int|null $team_members_count
 * @method static \Illuminate\Database\Eloquent\Builder|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereTeamName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmployeeTeam[] $members
 * @property int|null $company_id
 * @property int|null $parent_id
 * @property-read int|null $childs_count
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereParentId($value)
 * @mixin \Eloquent
 */
	class Team extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ThemeSetting
 *
 * @property int $id
 * @property string $panel
 * @property string $header_color
 * @property string $sidebar_color
 * @property string $sidebar_text_color
 * @property int $restrict_admin_theme_change
 * @property string $link_color
 * @property string|null $user_css
 * @property string $sidebar_theme
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereHeaderColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereLinkColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting wherePanel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereSidebarColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereSidebarTextColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereSidebarTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereUserCss($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereCompanyId($value)
 * @mixin \Eloquent
 * @property int $enable_rounded_theme
 * @property string|null $login_background
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereEnableRoundedTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereLoginBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereRestrictAdminThemeChange($value)
 */
	class ThemeSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Ticket
 *
 * @property int $id
 * @property int $user_id
 * @property string $subject
 * @property string $status
 * @property string $priority
 * @property int|null $agent_id
 * @property int|null $channel_id
 * @property int|null $type_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User|null $agent
 * @property-read \App\Models\User $client
 * @property-read mixed $created_on
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketReply[] $reply
 * @property-read int|null $reply_count
 * @property-read \App\Models\User $requester
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketTag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketTagList[] $ticketTags
 * @property-read int|null $ticket_tags_count
 * @method static \Database\Factories\TicketFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newQuery()
 * @method static \Illuminate\Database\Query\Builder|Ticket onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Ticket withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Ticket withoutTrashed()
 * @property string|null $mobile
 * @property int|null $country_id
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereMobile($value)
 * @property string|null $close_date
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCloseDate($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $extras
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCompanyId($value)
 * @property int|null $ticket_number
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTicketNumber($value)
 * @property int|null $group_id
 * @property-read \App\Models\TicketReply|null $latestReply
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereGroupId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $ticketMention
 * @property-read int|null $ticket_mention_count
 * @property int|null $project_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TicketActivity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereProjectId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\TicketGroup|null $group
 */
	class Ticket extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketActivity
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property int|null $assigned_to
 * @property int|null $channel_id
 * @property int|null $group_id
 * @property int|null $type_id
 * @property string $status
 * @property string $priority
 * @property string $type
 * @property string|null $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $assignedTo
 * @property-read \App\Models\TicketChannel|null $channel
 * @property-read \App\Models\TicketGroup|null $group
 * @property-read \App\Models\Ticket $ticket
 * @property-read \App\Models\TicketType|null $ticketType
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketActivity whereUserId($value)
 * @mixin \Eloquent
 * @property-read mixed $details
 */
	class TicketActivity extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketAgentGroups
 *
 * @property int $id
 * @property int $agent_id
 * @property int|null $group_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\TicketGroup|null $group
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereUpdatedAt($value)
 * @property int|null $company_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketAgentGroups whereLastUpdatedBy($value)
 * @mixin \Eloquent
 */
	class TicketAgentGroups extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketChannel
 *
 * @property int $id
 * @property string $channel_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $tickets
 * @property-read int|null $tickets_count
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel whereChannelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TicketChannel whereCompanyId($value)
 * @mixin \Eloquent
 */
	class TicketChannel extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketCustomForm
 *
 * @property int $id
 * @property string $field_display_name
 * @property string $field_name
 * @property string $field_type
 * @property int $field_order
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm whereFieldDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm whereFieldName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm whereFieldOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm whereFieldType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm whereUpdatedAt($value)
 * @property int $required
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm whereRequired($value)
 * @property int|null $company_id
 * @property int|null $custom_fields_id
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\CustomField|null $customField
 * @property-read mixed $extras
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketCustomForm whereCustomFieldsId($value)
 * @mixin \Eloquent
 */
	class TicketCustomForm extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketEmailSetting
 *
 * @property int $id
 * @property int|null $company_id
 * @property string|null $mail_username
 * @property string|null $mail_password
 * @property string|null $mail_from_name
 * @property string|null $mail_from_email
 * @property string|null $imap_host
 * @property string|null $imap_port
 * @property string|null $imap_encryption
 * @property int $status
 * @property int $verified
 * @property int $sync_interval
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereImapEncryption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereImapHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereImapPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereMailFromEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereMailFromName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereMailPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereMailUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereSyncInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketEmailSetting whereVerified($value)
 * @property-read \App\Models\Company|null $company
 * @mixin \Eloquent
 */
	class TicketEmailSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketFile
 *
 * @property int $id
 * @property int $user_id
 * @property int $ticket_reply_id
 * @property string $filename
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $dropbox_link
 * @property string|null $external_link
 * @property string|null $external_link_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read \App\Models\TicketReply $reply
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile whereTicketReplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketFile whereUserId($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class TicketFile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketGroup
 *
 * @property int $id
 * @property string $group_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketAgentGroups[] $agents
 * @property-read int|null $agents_count
 * @property-read mixed $icon
 * @property-read mixed $enabledAgents
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereGroupName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read int|null $enabled_agents_count
 * @method static \Database\Factories\TicketGroupFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereCompanyId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticket> $tickets
 * @property-read int|null $tickets_count
 */
	class TicketGroup extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketReply
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property string|null $message
 * @property int|null $added_by
 * @property int|null $agent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $icon
 * @property-read \App\Models\Ticket $ticket
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply newQuery()
 * @method static \Illuminate\Database\Query\Builder|TicketReply onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|TicketReply withTrashed()
 * @method static \Illuminate\Database\Query\Builder|TicketReply withoutTrashed()
 * @property int|null $company_id
 * @property string|null $imap_message_id
 * @property string|null $imap_message_uid
 * @property string|null $imap_in_reply_to
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereImapInReplyTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereImapMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereImapMessageUid($value)
 * @mixin \Eloquent
 * @property string $type
 * @property int $is_description
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TicketReplyUser> $ticketReplyUsers
 * @property-read int|null $ticket_reply_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereIsDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereType($value)
 */
	class TicketReply extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketReplyTemplate
 *
 * @property int $id
 * @property string $reply_heading
 * @property string $reply_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyTemplate whereReplyHeading($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyTemplate whereReplyText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyTemplate whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyTemplate whereCompanyId($value)
 * @mixin \Eloquent
 */
	class TicketReplyTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketReplyUser
 *
 * @property int $id
 * @property int $ticket_reply_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TicketReply $ticketReply
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyUser whereTicketReplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReplyUser whereUserId($value)
 */
	class TicketReplyUser extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketSettingForAgents
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $company_id
 * @property string|null $ticket_scope
 * @property array|null $group_id
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TicketSettingForAgents newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketSettingForAgents newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketSettingForAgents query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketSettingForAgents whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketSettingForAgents whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketSettingForAgents whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketSettingForAgents whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketSettingForAgents whereTicketScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketSettingForAgents whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketSettingForAgents whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketSettingForAgents whereUserId($value)
 */
	class TicketSettingForAgents extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketTag
 *
 * @property int $id
 * @property int $tag_id
 * @property int $ticket_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\TicketTagList $tag
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereCompanyId($value)
 * @mixin \Eloquent
 */
	class TicketTag extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketTagList
 *
 * @property int $id
 * @property string $tag_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList whereTagName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList whereCompanyId($value)
 * @mixin \Eloquent
 */
	class TicketTagList extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketType
 *
 * @property int $id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $tickets
 * @property-read int|null $tickets_count
 * @method static \Illuminate\Database\Eloquent\Builder|TicketType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketType query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketType whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketType whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|TicketType whereCompanyId($value)
 * @mixin \Eloquent
 */
	class TicketType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TranslateSetting
 *
 * @property int $id
 * @property string|null $google_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting whereGoogleKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class TranslateSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UnitType
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $unit_type
 * @property int $default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read int|null $creditnoteitems_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read int|null $estimateitems_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read int|null $estimatetemplate_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read int|null $invoices_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read int|null $proposalitems_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read int|null $proposaltemplate_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read int|null $recurring_invoice_count
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType query()
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereUnitType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 * @mixin \Eloquent
 */
	class UnitType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UniversalSearch
 *
 * @property int $id
 * @property int $searchable_id
 * @property string|null $module_type
 * @property string $title
 * @property string $route_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch query()
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereModuleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereRouteName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereSearchableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereCompanyId($value)
 * @mixin \Eloquent
 */
	class UniversalSearch extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property int|null $telegram_user_id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $remember_token
 * @property string|null $image
 * @property string|null $mobile
 * @property string $gender
 * @property string $locale
 * @property string $status
 * @property string $login
 * @property string|null $onesignal_player_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property int $email_notifications
 * @property int|null $country_id
 * @property int $dark_theme
 * @property int $rtl
 * @property-read Collection|\App\Models\TicketAgentGroups[] $agent
 * @property-read int|null $agent_count
 * @property-read Collection|\App\Models\Ticket[] $agents
 * @property-read int|null $agents_count
 * @property-read Collection|\App\Models\Attendance[] $attendance
 * @property-read Collection|\App\Models\Leave[] $leaves
 * @property-read int|null $attendance_count
 * @property-read Collection|\App\Models\EventAttendee[] $attendee
 * @property-read int|null $attendee_count
 * @property-read \App\Models\ClientDetails|null $clientDetails
 * @property-read Collection|\App\Models\Contract[] $contracts
 * @property-read int|null $contracts_count
 * @property-read \App\Models\Country|null $country
 * @property-read Collection|\App\Models\EmployeeDocument[] $documents
 * @property-read int|null $documents_count
 * @property-read Collection|\App\Models\EmployeeDetails[] $employee
 * @property-read int|null $employee_count
 * @property-read \App\Models\EmployeeDetails|null $employeeDetail
 * @property-read \App\Models\EmployeeDetails|null $employeeDetails
 * @property-read mixed $icon
 * @property-read mixed $image_url
 * @property-read mixed $modules
 * @property-read mixed $unread_notifications
 * @property-read mixed $user_other_role
 * @property-read Collection|\App\Models\EmployeeTeam[] $group
 * @property-read int|null $group_count
 * @property-read \App\Models\Lead|null $lead
 * @property-read Collection|\App\Models\LeadAgent[] $leadAgent
 * @property-read int|null $lead_agent_count
 * @property-read Collection|\App\Models\EmployeeLeaveQuota[] $leaveTypes
 * @property-read int|null $leave_types_count
 * @property-read Collection|\App\Models\ProjectMember[] $member
 * @property-read int|null $member_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|\App\Models\Permission[] $permissionTypes
 * @property-read int|null $permission_types_count
 * @property-read Collection|\App\Models\Project[] $projects
 * @property-read int|null $projects_count
 * @property-read Collection|\App\Models\Estimate[] $estimates
 * @property-read int|null $estimates_count
 * @property-read Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read Collection|\App\Models\RoleUser[] $role
 * @property-read int|null $role_count
 * @property-read Collection|\App\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\Session|null $session
 * @property-read Collection|\App\Models\StickyNote[] $sticky
 * @property-read int|null $sticky_count
 * @property-read Collection|\App\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @property-read Collection|\App\Models\Ticket[] $tickets
 * @property-read int|null $tickets_count
 * @property-read Collection|\App\Models\UserChat[] $userChat
 * @property-read int|null $user_chat_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCountryId($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDarkTheme($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailNotifications($value)
 * @method static Builder|User whereGender($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereImage($value)
 * @method static Builder|User whereLastLogin($value)
 * @method static Builder|User whereLocale($value)
 * @method static Builder|User whereLogin($value)
 * @method static Builder|User whereMobile($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User whereOnesignalPlayerId($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereRtl($value)
 * @method static Builder|User whereStatus($value)
 * @method static Builder|User whereTwoFactorRecoveryCodes($value)
 * @method static Builder|User whereTwoFactorSecret($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User withRole(string $role)
 * @property int $two_factor_confirmed
 * @property int $two_factor_email_confirmed
 * @property string|null $salutation
 * @property string|null $two_fa_verify_via
 * @property string|null $two_factor_code when authenticator is email
 * @property \Illuminate\Support\Carbon|null $two_factor_expires_at
 * @property int $admin_approval
 * @property int $permission_sync
 * @property-read int|null $leaves_count
 * @method static Builder|User whereAdminApproval($value)
 * @method static Builder|User wherePermissionSync($value)
 * @method static Builder|User whereSalutation($value)
 * @method static Builder|User whereTwoFaVerifyVia($value)
 * @method static Builder|User whereTwoFactorCode($value)
 * @method static Builder|User whereTwoFactorConfirmed($value)
 * @method static Builder|User whereTwoFactorEmailConfirmed($value)
 * @method static Builder|User whereTwoFactorExpiresAt($value)
 * @property-read Collection|\App\Models\ClientDocument[] $clientDocuments
 * @property-read Collection|\App\Models\EmployeeShiftSchedule[] $shifts
 * @property-read int|null $client_documents_count
 * @property-read Collection|\App\Models\Task[] $openTasks
 * @property-read int|null $open_tasks_count
 * @property-read Collection|\App\Models\EmergencyContact[] $emergencyContacts
 * @property-read int|null $emergency_contacts_count
 * @property int|null $company_id
 * @property int $google_calendar_status
 * @property int $customised_permissions
 * @property-read \App\Models\Company|null $company
 * @property-read Collection|\App\Models\EmployeeShift[] $employeeShift
 * @property-read int|null $employee_shift_count
 * @property-read Collection|\App\Models\EmployeeDetails[] $reportingTeam
 * @property-read int|null $reporting_team_count
 * @property-read int|null $shifts_count
 * @property-read Collection|\App\Models\ProjectTemplateMember[] $templateMember
 * @property-read int|null $template_member_count
 * @method static Builder|User whereCompanyId($value)
 * @method static Builder|User whereCustomisedPermissions($value)
 * @method static Builder|User whereGoogleCalendarStatus($value)
 * @property-read Collection|\App\Models\Appreciation[] $appreciations
 * @property-read int|null $appreciations_count
 * @property-read Collection|\App\Models\Appreciation[] $appreciationsGrouped
 * @property-read int|null $appreciations_grouped_count
 * @property-read Collection|SupportTicket[] $supportTickets
 * @property-read int|null $support_tickets_count
 * @property-read \App\Models\UserAuth|null $userAuth
 * @property-read Collection|\App\Models\ProjectTimeLog[] $projectTimeLog
 * @property string|null $stripe_id
 * @property string|null $pm_type
 * @property string|null $pm_last_four
 * @property string|null $trial_ends_at
 * @property-read Collection<int, \App\Models\ProjectTimeLog> $timeLogs
 * @property-read int|null $time_logs_count
 * @property-read Collection<int, \App\Models\VisaDetail> $visa
 * @property-read int|null $visa_count
 * @method static Builder|User onlyEmployee()
 * @method static Builder|User wherePmLastFour($value)
 * @method static Builder|User wherePmType($value)
 * @method static Builder|User whereStripeId($value)
 * @method static Builder|User whereTelegramUserId($value)
 * @method static Builder|User whereTrialEndsAt($value)
 * @property int|null $country_phonecode
 * @property-read Collection<int, \App\Models\TicketGroup> $agentGroup
 * @property-read int|null $agent_group_count
 * @property-read mixed $mobile_with_phone_code
 * @method static Builder|User whereCountryPhonecode($value)
 * @mixin \Eloquent
 * @property int|null $user_auth_id
 * @property int $is_superadmin
 * @property string|null $last_activity
 * @property string|null $headers
 * @property string|null $register_ip
 * @property string|null $location_details
 * @property string|null $inactive_date
 * @property int|null $is_client_contact
 * @property-read \App\Models\Company|null $approvedCompany
 * @property-read \App\Models\ClientContact|null $clientContact
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeDocumentExpiry> $documentExpiries
 * @property-read int|null $document_expiries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LeaveType> $employeeLeaveTypes
 * @property-read int|null $employee_leave_types_count
 * @property-read mixed $name_salutation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LeadCategory> $leadAgentCategory
 * @property-read int|null $lead_agent_category_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeLeaveQuotaHistory> $leaveQuotaHistory
 * @property-read int|null $leave_quota_history_count
 * @property-read mixed $masked_image_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TicketReply> $ticketReply
 * @property-read int|null $ticket_reply_count
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHeaders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInactiveDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsClientContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsSuperadmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLocationDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRegisterIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserAuthId($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserActivity
 *
 * @property int $id
 * @property int $user_id
 * @property string $activity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereCompanyId($value)
 * @mixin \Eloquent
 */
	class UserActivity extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserAuth
 *
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 * @property int $two_factor_confirmed
 * @property int $two_factor_email_confirmed
 * @property string|null $salutation
 * @property string|null $two_fa_verify_via
 * @property string|null $two_factor_code when authenticator is email
 * @property \Illuminate\Support\Carbon|null $two_factor_expires_at
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\User|null $userWithoutCompany
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth query()
 * @property int $id
 * @property string|null $email_verified_at
 * @property string|null $email_verification_code
 * @property \Illuminate\Support\Carbon|null $email_code_expires_at
 * @property string|null $twitter_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \IvanoMatteo\LaravelDeviceTracking\Models\Device> $device
 * @property-read int|null $device_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereEmailCodeExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereEmailVerificationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereTwitterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereTwoFaVerifyVia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereTwoFactorCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereTwoFactorConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereTwoFactorEmailConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereTwoFactorExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth whereUpdatedAt($value)
 */
	class UserAuth extends \Eloquent implements \Illuminate\Contracts\Auth\Authenticatable, \Illuminate\Contracts\Auth\Access\Authorizable, \Illuminate\Contracts\Auth\CanResetPassword, \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

namespace App\Models{
/**
 * App\Models\UserChat
 *
 * @property int $id
 * @property int $user_one
 * @property int $user_id
 * @property int $notification_sent
 * @property string $message
 * @property int|null $from
 * @property int|null $to
 * @property string $message_seen
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $fromUser
 * @property-read mixed $icon
 * @property-read \App\Models\User|null $toUser
 * @method static Builder|UserChat lastPerGroup(?array $fields = null)
 * @method static Builder|UserChat newModelQuery()
 * @method static Builder|UserChat newQuery()
 * @method static Builder|UserChat query()
 * @method static Builder|UserChat whereCreatedAt($value)
 * @method static Builder|UserChat whereFrom($value)
 * @method static Builder|UserChat whereId($value)
 * @method static Builder|UserChat whereMessage($value)
 * @method static Builder|UserChat whereMessageSeen($value)
 * @method static Builder|UserChat whereTo($value)
 * @method static Builder|UserChat whereUpdatedAt($value)
 * @method static Builder|UserChat whereUserId($value)
 * @method static Builder|UserChat whereUserOne($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserchatFile[] $files
 * @property-read int|null $files_count
 * @method static \Database\Factories\UserChatFactory factory(...$parameters)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static Builder|UserChat whereCompanyId($value)
 * @method static Builder|UserChat whereNotificationSent($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionProject
 * @property-read int|null $mention_project_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @mixin \Eloquent
 */
	class UserChat extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserInvitation
 *
 * @property int $id
 * @property int $user_id
 * @property string $invitation_type
 * @property string|null $email
 * @property string $invitation_code
 * @property string $status
 * @property string|null $email_restriction
 * @property string|null $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereEmailRestriction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereInvitationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereInvitationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|UserInvitation whereCompanyId($value)
 * @mixin \Eloquent
 */
	class UserInvitation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserLeadboardSetting
 *
 * @property int $id
 * @property int $user_id
 * @property int $board_column_id
 * @property int $collapsed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereBoardColumnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereCollapsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereCompanyId($value)
 * @property int|null $pipeline_stage_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting wherePipelineStageId($value)
 * @mixin \Eloquent
 */
	class UserLeadboardSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserPermission
 *
 * @property int $id
 * @property int $user_id
 * @property int $permission_id
 * @property int $permission_type_id
 * @property int $customised_permissions
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Permission $permission
 * @property-read \App\Models\PermissionType $type
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission wherePermissionTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereUserId($value)
 * @property-read \App\Models\User $user
 * @mixin \Eloquent
 */
	class UserPermission extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserTaskboardSetting
 *
 * @property int $id
 * @property int $user_id
 * @property int $board_column_id
 * @property int $collapsed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereBoardColumnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereCollapsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereCompanyId($value)
 * @mixin \Eloquent
 */
	class UserTaskboardSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserchatFile
 *
 * @property int $id
 * @property int $user_id
 * @property int $users_chat_id
 * @property string $filename
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $external_link
 * @property string|null $external_link_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\UserChat $chat
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereUsersChatId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereCompanyId($value)
 * @property-read mixed $file
 * @mixin \Eloquent
 */
	class UserchatFile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VisaDetail
 *
 * @property string|null $image
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCompanyId($value)
 * @property int $id
 * @property int|null $company_id
 * @property int|null $user_id
 * @property int|null $country_id
 * @property int|null $added_by
 * @property string $visa_number
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon $expiry_date
 * @property string|null $file
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Country|null $country
 * @property-read mixed $image_url
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereVisaNumber($value)
 * @mixin \Eloquent
 * @property int $alert_before_months
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereAlertBeforeMonths($value)
 */
	class VisaDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\WeeklyTimesheet
 *
 * @property int $id
 * @property int $company_id
 * @property int $user_id
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon $week_start_date
 * @property string $status
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\Company $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WeeklyTimesheetEntries> $entries
 * @property-read int|null $entries_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheet query()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheet whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheet whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheet whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheet whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheet whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheet whereWeekStartDate($value)
 */
	class WeeklyTimesheet extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\WeeklyTimesheetEntries
 *
 * @property int $id
 * @property int $company_id
 * @property int $weekly_timesheet_id
 * @property int|null $task_id
 * @property \Illuminate\Support\Carbon $date
 * @property string $hours
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\Task|null $task
 * @property-read \App\Models\WeeklyTimesheet $weeklyTimesheet
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheetEntries newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheetEntries newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheetEntries query()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheetEntries whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheetEntries whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheetEntries whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheetEntries whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheetEntries whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheetEntries whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheetEntries whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyTimesheetEntries whereWeeklyTimesheetId($value)
 */
	class WeeklyTimesheetEntries extends \Eloquent {}
}

