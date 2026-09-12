<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Document
 *
 * @property int $id
 * @property string|null $document_type
 * @property string|null $remarks
 * @property string|null $document_file_path
 * @property int $user_id
 * @property int $document_type_master_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 * @property DocumentTypeMaster $document_type_master
 */
class Document extends Model
{
    protected $table = 'documents';

    protected $casts = [
        'user_id' => 'int',
        'document_type_master_id' => 'int',
    ];

    protected $fillable = [
        'document_file_path',
        'user_id',
        'document_type_master_id',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function document_type_master()
    {
        return $this->belongsTo(DocumentTypeMaster::class);
    }
}
