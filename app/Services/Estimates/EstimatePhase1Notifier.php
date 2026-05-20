<?php

declare(strict_types=1);

namespace App\Services\Estimates;

use App\Models\Estimate;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\RoleUser;
use App\Models\User;
use App\Models\UserPermission;
use App\Notifications\EstimatePhase1ReviewNotification;
use App\Scopes\CompanyScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

final class EstimatePhase1Notifier
{
    public function notifySubmitted(Estimate $estimate): void
    {
        $recipients = $this->usersWithPermission((int) $estimate->company_id, 'approve_estimate_president');

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new EstimatePhase1ReviewNotification($estimate, 'submitted'));
    }

    public function notifyPresidentDecision(Estimate $estimate, string $decision): void
    {
        if ($decision === 'approved') {
            $recipients = $this->usersWithPermission((int) $estimate->company_id, 'approve_estimate_vp_pricing');

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new EstimatePhase1ReviewNotification($estimate, 'president_approved'));
            }

            return;
        }

        $this->notifySalesOwner($estimate, 'president_rejected');
    }

    public function notifyVpDecision(Estimate $estimate, string $decision): void
    {
        if ($decision === 'approved') {
            $this->notifySalesOwner($estimate, 'vp_approved');

            return;
        }

        $this->notifySalesOwner($estimate, 'vp_rejected');
    }

    private function notifySalesOwner(Estimate $estimate, string $event): void
    {
        if ($estimate->added_by === null) {
            return;
        }

        $sales = User::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $estimate->company_id)
            ->where('id', $estimate->added_by)
            ->first();

        if ($sales instanceof User) {
            Notification::send($sales, new EstimatePhase1ReviewNotification($estimate, $event));
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function usersWithPermission(int $companyId, string $permissionName): Collection
    {
        $permission = Permission::where('name', $permissionName)->first();

        if ($permission === null || $companyId <= 0) {
            return collect();
        }

        $roleIds = PermissionRole::where('permission_id', $permission->id)
            ->whereIn('permission_type_id', [3, 4])
            ->pluck('role_id');

        $userIdsFromRoles = RoleUser::whereIn('role_id', $roleIds)
            ->pluck('user_id');

        $userIdsDirect = UserPermission::where('permission_id', $permission->id)
            ->whereIn('permission_type_id', [3, 4])
            ->pluck('user_id');

        $userIds = $userIdsFromRoles->merge($userIdsDirect)->unique()->filter();

        if ($userIds->isEmpty()) {
            return collect();
        }

        return User::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->whereIn('id', $userIds->all())
            ->get();
    }
}
