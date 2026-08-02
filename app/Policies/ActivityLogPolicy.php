<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Admin;
use Spatie\Activitylog\Models\Activity;

class ActivityLogPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo(Permission::ActivityLogsViewAny);
    }

    public function view(Admin $admin, Activity $activity): bool
    {
        return $admin->hasPermissionTo(Permission::ActivityLogsView);
    }
}
