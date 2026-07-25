<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportFile extends Model
{
    protected $fillable = [
        'campaign_id',
        'province_id',
        'ngo_id',
        'original_filename',
        'stored_path',
        'file_type',
        'file_size',
        'detected_columns',
        'sample_rows',
        'row_count',
        'status',
        'error_message',
        'imported_by',
    ];

    protected $casts = [
        'file_size'        => 'integer',
        'row_count'        => 'integer',
        'detected_columns' => 'array',
        'sample_rows'      => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function ngo(): BelongsTo
    {
        return $this->belongsTo(NgoPartner::class, 'ngo_id');
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
