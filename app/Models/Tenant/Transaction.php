<?php

namespace App\Models\Tenant;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;

class Transaction extends Model
{
    use HasDynamicTable;
    use SoftDeletes;

    protected $table = '';

    public function __construct(array $attributes = [], ?string $year = null)
    {
        $this->syncOriginal();
        $this->fill($attributes);
        $this->setDynamicTable($year);
    }

    protected function baseTableName()
    {
        return 'transactions';
    }

    protected function defineSchema(Blueprint $table, $year)
    {
        $table->id();
        $table->morphs('accountable');
        // Leave Account
        $table->morphs('referenceable');
        // credit, debit, adjustment
        $table->morphs('authable');
        // User
        $table->datetime('trx_datetime')->nullable();
        $table->foreignId('trx_user_id')->nullable()->constrained('users')->noActionOnDelete();
        $table->foreignId('leave_type_id')->nullable()->constrained('leave_types')->noActionOnDelete();
        $table->decimal('opening_balance', 10, 2);
        $table->decimal('credit', 10, 2);
        $table->decimal('debit', 10, 2);
        $table->decimal('balance', 10, 2);
        $table->string('remarks')->nullable();
        $table->tinyInteger('status')->default(1);
        $table->decimal('lapse', 10, 2)->nullable()->default('0.0');
        $table->decimal('encashment', 10, 2)->nullable()->default('0.0');
        $table->foreignId('created_by')->nullable()->constrained('users')->noActionOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->noActionOnDelete();
        $table->foreignId('deleted_by')->nullable()->constrained('users')->noActionOnDelete();
        $table->softDeletes();
        $table->timestamps();
    }

    protected $fillable = [
        'accountable_type',
        'accountable_id',
        'referenceable_type',
        'referenceable_id',
        'authable_type',
        'authable_id',
        'trx_datetime',
        'trx_user_id',
        'leave_type_id',
        'opening_balance',
        'credit',
        'debit',
        'balance',
        'remarks',
        'status',
        'lapse',
        'encashment',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'trx_user_id' => 'int',
        'trx_datetime' => 'datetime',
        'accountable_id' => 'int',
        'referenceable_id' => 'int',
        'authable_id' => 'int',
        'leave_type_id' => 'int',
        'opening_balance' => 'decimal:2',
        'credit' => 'decimal:2',
        'debit' => 'decimal:2',
        'balance' => 'decimal:2',
        'status' => 'int',
        'lapse' => 'decimal:2',
        'encashment' => 'decimal:2',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    /**
     * Scope a query to only include transactions from a specific year.
     *
     * @param  string|int  $year
     * @return Builder
     */
    public static function scopeYear(Builder $query, $year)
    {
        $instance = new static([], $year);

        return $query->setModel($instance);
    }

    public function trx_user()
    {
        return $this->belongsTo(User::class);
    }

    public function accountable()
    {
        return $this->morphTo();
    }

    public function referenceable()
    {
        return $this->morphTo();
    }

    public function authable()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'authable_id');
    }

    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
