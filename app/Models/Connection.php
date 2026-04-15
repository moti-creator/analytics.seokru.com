<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    protected $guarded = [];
    protected $casts = ['expires_at' => 'datetime'];

    public function reports() { return $this->hasMany(Report::class); }
}
