<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ProcessTag extends Model
{
    protected $fillable = ['name', 'color', 'icon'];

    public function taggable()
    {
        return $this->morphTo();
    }
}
