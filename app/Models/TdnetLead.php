<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TdnetLead extends Model
{
    protected $table = 'tdnet_leads';

    protected $guarded = [];

    protected $casts = [
        'source_meta' => 'array',
        'subject_variants' => 'array',
        'sent_at' => 'datetime',
    ];

    public function fullName(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}
