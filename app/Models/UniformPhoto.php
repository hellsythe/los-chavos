<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniformPhoto extends Model
{
    public const STATUS_DELETED = 0;

    public const STATUS_ACTIVE = 30;

    protected $fillable = [
        'uniform_id',
        'photo',
        'order',
        'status',
    ];

    public function uniform()
    {
        return $this->belongsTo(Uniform::class);
    }
}
