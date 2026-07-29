<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportExamResult extends Model
{
    protected $table = 'import_exam_results';

    protected $fillable = [
        'import_file_id',
        'campaign_id',
        'subject_id',
        'imported_by',
        'total_rows',
        'imported_rows',
        'errored_rows',
        'column_mapping',
        'status',
        'error_message',
    ];

    protected $casts = [
        'total_rows'     => 'integer',
        'imported_rows'  => 'integer',
        'errored_rows'   => 'integer',
        'column_mapping' => 'array',
    ];

    public function importFile(): BelongsTo
    {
        return $this->belongsTo(ImportFile::class, 'import_file_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SelectCampaing::class, 'campaign_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class, 'subject_id');
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
