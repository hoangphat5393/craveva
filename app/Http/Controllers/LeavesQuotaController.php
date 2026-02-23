<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Exports\LeaveQuotaReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Helper\Reply;
use App\Models\LeaveType;
use App\Scopes\ActiveScope;
use Illuminate\Http\Request;
use App\Models\EmployeeLeaveQuota;
use Illuminate\Support\Facades\Artisan;

class LeavesQuotaController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.leaves';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leaves', $this->user->modules));
            return $next($request);
        });
    }

    public function update(Request $request, $id)
    {
        $type = EmployeeLeaveQuota::findOrFail($id);

        if ($request->leaves < 0 || $request->leaves < $type->leaves_used) {
            return Reply::error('messages.employeeLeaveQuota');
        }

        $remainingLeaves = ($request->leaves - $type->leaves_used - $type->unused_leaves);
        $overutilisedLeaves = ($type->overutilised_leaves - $request->leaves);
        $unusedLeaves = ($type->unused_leaves - $request->leaves);

        $type->no_of_leaves = $request->leaves;
        $type->leave_type_impact = $request->leaveimpact;
        $type->leaves_remaining = ($remainingLeaves > 0) ? $remainingLeaves : 0;
        $type->overutilised_leaves = ($overutilisedLeaves > 0) ? $overutilisedLeaves : 0;
        $type->unused_leaves = ($unusedLeaves > 0) ? $unusedLeaves : 0;
        $type->save();

        session()->forget('user');

        return Reply::success(__('messages.leaveTypeAdded'));
    }

    public function employeeLeaveTypes($userId)
    {
        if ($userId != 0) {
            $employee = User::withoutGlobalScope(ActiveScope::class)->with(['roles', 'leaveTypes', 'employeeDetail'])->findOrFail($userId);
            $options = '';
            $eligible = false;
            $message = null;
            $profileMissing = false;
            $missingFields = [];

            $detail = $employee->employeeDetail;
            if (!$detail) {
                $missingFields = ['joining_date', 'department', 'designation', 'marital_status'];
            } else {
                if (empty($detail->joining_date)) {
                    $missingFields[] = 'joining_date';
                }
                if (empty($detail->department_id)) {
                    $missingFields[] = 'department';
                }
                if (empty($detail->designation_id)) {
                    $missingFields[] = 'designation';
                }
                if (is_null($detail->marital_status)) {
                    $missingFields[] = 'marital_status';
                }
            }
            if (empty($employee->gender)) {
                $missingFields[] = 'gender';
            }
            $profileMissing = count($missingFields) > 0;

            foreach ($employee->leaveTypes as $leavesQuota) {
                $hasLeave = ($leavesQuota->leaveType && $leavesQuota->leaveType->deleted_at == null) ? $leavesQuota->leaveType->leaveTypeCondition($leavesQuota->leaveType, $employee) : false;

                if ($hasLeave) {
                    $options .= '<option value="' . $leavesQuota->leave_type_id . '"> ' .  $leavesQuota->leaveType->type_name . ' (' . $leavesQuota->leaves_remaining . ') </option>';
                    /** @phpstan-ignore-line */
                    $eligible = true;
                }
            }

            if ($options === '') {
                $leaveTypes = LeaveType::all();
                foreach ($leaveTypes as $leaveType) {
                    $hasLeave = ($leaveType->deleted_at == null) ? $leaveType->leaveTypeCondition($leaveType, $employee) : false;
                    if ($hasLeave) {
                        $options .= '<option value="' . $leaveType->id . '"> ' .  $leaveType->type_name . ' (' . $leaveType->no_of_leaves . ') </option>';
                        /** @phpstan-ignore-line */
                        $eligible = true;
                    }
                }
            }

            if ($eligible === false) {
                if ($profileMissing) {
                    $labels = [
                        'joining_date' => __('modules.employees.joiningDate'),
                        'department' => __('modules.employees.department'),
                        'designation' => __('modules.employees.designation'),
                        'marital_status' => __('modules.employees.maritalStatus'),
                        'gender' => __('modules.employees.gender'),
                    ];
                    $translated = array_map(function ($f) use ($labels) {
                        return $labels[$f] ?? $f;
                    }, $missingFields);
                    $message = __('messages.employeeProfileIncomplete', ['fields' => implode(', ', $translated)]);
                } else {
                    $message = __('messages.leaveTypeNotAllowed');
                }
            }
        } else {
            $leaveQuotas = LeaveType::all();

            $options = '';

            foreach ($leaveQuotas as $leaveQuota) {
                $options .= '<option value="' . $leaveQuota->id . '"> ' .  $leaveQuota->type_name . ' (' . $leaveQuota->no_of_leaves . ') </option>';
                /** @phpstan-ignore-line */
            }
        }

        $resp = [
            'status' => 'success',
            'data' => $options,
            'eligible' => isset($eligible) ? $eligible : true,
            'profile_missing' => isset($profileMissing) ? $profileMissing : false,
            'profile_missing_fields' => isset($missingFields) ? $missingFields : []
        ];

        if (!empty($message)) {
            $resp['message'] = $message;
        }

        return Reply::dataOnly($resp);
    }

    public function exportAllLeaveQuota($id, $year, $month)
    {
        abort_403(!canDataTableExport());
        $name = __('app.leaveQuotaReport') . '-' . Carbon::createFromDate($year, $month, 1)->startOfDay()->translatedFormat('F-Y');
        return Excel::download(new LeaveQuotaReportExport($id, $year, $month), $name . '.xlsx');
    }
}
