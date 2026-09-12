<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DocumentTypeMaster
 *
 * @property int $id
 * @property string|null $type
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class DocumentTypeMaster extends Model
{
    use SoftDeletes;

    // use CUDby;
    protected $table = 'document_type_masters';

    protected $casts = [
        // 'created_by' => 'int',
        // 'updated_by' => 'int',
        // 'deleted_by' => 'int'
    ];

    protected $fillable = [
        'type',
        // 'created_by',
        // 'updated_by',
        // 'deleted_by'
    ];
}
