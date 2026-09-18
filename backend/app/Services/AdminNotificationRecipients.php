<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;

class AdminNotificationRecipients
{
    /** @return Collection<int, User> */
    public function forProject(Project $project, ?User $exclude = null): Collection
    {
        $allAdmins = User::query()
            ->where('role', 'admin')
            ->whereNull('disabled_at')
            ->get();
        $admins = $allAdmins;

        if ($project->reviewer_id) {
            $routedAdmins = $admins->filter(
                fn (User $admin): bool => $admin->id === $project->reviewer_id
                    || $admin->monitorsAllProjects(),
            );

            // A reviewer may have been deleted or demoted after project assignment.
            $admins = $routedAdmins->isNotEmpty() ? $routedAdmins : $allAdmins;
        }

        if ($exclude) {
            $admins = $admins->reject(fn (User $admin): bool => $admin->is($exclude));
        }

        return $admins->values();
    }
}
