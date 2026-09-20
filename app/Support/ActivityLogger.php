<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

final class ActivityLogger
{
    public static function log(?Model $authUser, Model $user, array $old, array $new, string $event): void
    {
        activity()
            ->causedBy($authUser)
            ->performedOn($user)
            ->withProperties([
                'old' => $old,
                'attributes' => $new,
            ])
            ->event($event)
            ->log($event);
    }
}
