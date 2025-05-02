<?php

namespace App\Policies;

use App\Models\Timeline;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TimelinePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Timeline $timeline): bool
    {
        return $user->id === $timeline->user_id;
    }

    public function update(User $user, Timeline $timeline): bool
    {
        return $user->id === $timeline->user_id;
    }

    public function delete(User $user, Timeline $timeline): bool
    {
        return $user->id === $timeline->user_id;
    }
}
