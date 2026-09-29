<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PrescriptionResult extends Model
{
    protected $fillable = [
        'prescription_id',
        'raw_ocr_text',
        'extracted_json',
        'confidence_score',
        'language'
    ];

    /**
     * Relationship: Prescription
     */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Relationship: Translations (Polymorphic)
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }
}
