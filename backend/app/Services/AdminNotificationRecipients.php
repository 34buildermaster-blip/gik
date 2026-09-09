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
        $admins = User::query()->where('role', 'admin')->get();

        if ($project->reviewer_id) {
            $admins = $admins->filter(
                fn (User $admin): bool => $admin->id === $project->reviewer_id
                    || $admin->monitorsAllProjects(),
            );
        }

        if ($exclude) {
            $admins = $admins->reject(fn (User $admin): bool => $admin->is($exclude));
        }

        return $admins->values();
    }
}
