<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineInteraction extends Model
{
    protected $fillable = [
        'medicine_id_1',
        'medicine_id_2',
        'risk_level',
        'severity',
        'description',
        'recommended_action'
    ];

    /**
     * Relationship: First medicine
     */
    public function medicine1(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id_1');
    }

    /**
     * Relationship: Second medicine
     */
    public function medicine2(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id_2');
    }
}
