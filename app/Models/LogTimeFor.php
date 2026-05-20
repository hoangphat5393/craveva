<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Support\Carbon;

/**
 * Class Holiday
 *
 * @property int $id
 * @property string $log_time_for
 * @property string $auto_timer_stop
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $approval_required
 * @property-read mixed $icon
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor query()
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereApprovalRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereAutoTimerStop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereLogTimeFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereUpdatedAt($value)
 *
 * @property int|null $company_id
 * @property-read Company|null $company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereCompanyId($value)
 *
 * @property int $tracker_reminder
 * @property int $timelog_report
 * @property string|null $daily_report_roles
 * @property string|null $time
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereDailyReportRoles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereTimelogReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereTrackerReminder($value)
 *
 * @mixin \Eloquent
 */
class LogTimeFor extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'log_time_for',
        'auto_timer_stop',
        'approval_required',
        'tracker_reminder',
        'timelog_report',
        'daily_report_roles',
        'time',
    ];

    protected $guarded = ['id'];

    protected $table = 'log_time_for';
}
