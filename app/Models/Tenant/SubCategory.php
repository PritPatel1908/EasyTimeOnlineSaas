<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SubCategory
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $email
 * @property int|null $location_id
 * @property int $category_id
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Category $category
 * @property User|null $user
 * @property Location|null $location
 * @property Collection|DataPolicy[] $data_policies
 * @property Collection|User[] $users
 */
class SubCategory extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'sub_categories';

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);
    }

    protected $casts = [
        'location_id' => 'array',
        'category_id' => 'array',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'name',
        'code',
        'email',
        'location_id',
        'category_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function data_policies()
    {
        return $this->belongsToMany(DataPolicy::class)
            ->withPivot('id')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
