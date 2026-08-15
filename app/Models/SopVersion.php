<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SopVersion extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'sop_id',
        'version_number',
        'document_code',
        'purpose',
        'scope',
        'procedure_content',
        'quality_control',
        'safety_information',
        'references',
        'content',
        'change_summary',
        'created_by',
    ];

    public function sop(): BelongsTo
    {
        return $this->belongsTo(Sop::class, 'sop_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
