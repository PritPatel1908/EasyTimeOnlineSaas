<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StatusMaster
 *
 * @property int $id
 * @property string $code
 * @property string $alias
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|StatusMuster[] $status_musters
 * @property Collection|Attendance[] $attendance
 */
class StatusMaster extends Model
{
    protected $table = 'status_masters';

    protected $fillable = [
        'code',
        'alias',
        'description',
    ];

    public function status_musters()
    {
        return $this->hasMany(StatusMuster::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
}
