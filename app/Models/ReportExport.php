<?php

namespace App\Models;

use App\Enums\ReportType;
use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExport extends Model
{
    protected $table = 'report_exports';

    protected $fillable = [
        'user_id',
        'report_name',
        'type',
        'export_type',
        'campaign_id',
        'status',
        'progress',
        'file_path',
        'completed_at',
    ];

    protected $casts = [
        'type'         => ReportType::class,
        'status'       => ReportStatus::class,
        'progress'     => 'integer',
        'completed_at' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }
}
