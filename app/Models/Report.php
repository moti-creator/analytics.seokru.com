<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Report extends Model
{
    protected $guarded = [];
    protected $casts = ['metrics' => 'array'];

    protected static function booted(): void
    {
        static::creating(function ($r) {
            if (empty($r->slug)) $r->slug = Str::random(16);
        });
    }

    public function getRouteKeyName(): string { return 'slug'; }

    public function connection() { return $this->belongsTo(Connection::class); }
}
