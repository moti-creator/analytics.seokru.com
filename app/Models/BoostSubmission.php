<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoostSubmission extends Model
{
    protected $guarded = [];
    protected $casts = [
        'indexnow_result' => 'array',
        'indexing_api_result' => 'array',
        'llms_txt_result' => 'array',
        'reddit_result' => 'array',
        'wayback_result' => 'array',
        'archive_today_result' => 'array',
        'websub_result' => 'array',
        'inspection_24h' => 'array',
        'inspection_72h' => 'array',
        'inspection_7d' => 'array',
        'indexed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function connection() { return $this->belongsTo(Connection::class); }
}
