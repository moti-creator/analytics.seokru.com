<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $guarded = [];
    protected $casts = ['metrics' => 'array'];

    public function connection() { return $this->belongsTo(Connection::class); }
}
