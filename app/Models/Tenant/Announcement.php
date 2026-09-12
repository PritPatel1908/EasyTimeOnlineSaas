<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $table = 'announcements';

    protected $casts = [
        'from_date' => 'datetime',
        'to_date' => 'datetime',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'from_date',
        'to_date',
        'message',
        'attachment',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'announcement_department')
            ->withPivot('id')
            ->withTimestamps();
    }
}
