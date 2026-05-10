<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RememberToken extends Model
{
    protected $guarded = [];
    protected $casts = ['expires_at' => 'datetime', 'last_used_at' => 'datetime'];

    public function connection() { return $this->belongsTo(Connection::class); }

    public static function hash(string $raw): string
    {
        return hash('sha256', $raw);
    }
}
