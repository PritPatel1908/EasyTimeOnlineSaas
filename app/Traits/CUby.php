<?php

namespace App\Traits;

use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait CUby
{
    public static function bootCUby()
    {
        static::creating(function ($model) {
            if (! $model->isDirty('created_by')) {
                $model->created_by = auth()->user()?->id;
            }
            if (! $model->isDirty('updated_by')) {
                $model->updated_by = auth()->user()?->id;
            }
        });

        static::updating(function ($model) {
            if (! $model->isDirty('updated_by')) {
                $model->updated_by = auth()->user()?->id;
            }
        });
    }

    /**
     * @return BelongsTo
     */
    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
