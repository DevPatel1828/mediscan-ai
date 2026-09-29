<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    protected $fillable = [
        'medicine_name',
        'generic_name',
        'brand_name',
        'composition',
        'uses',
        'dosage',
        'side_effects',
        'warnings',
        'pregnancy_safety',
        'storage_conditions',
        'manufacturer',
        'price',
        'category',
        'food_interactions',
        'alcohol_interactions'
    ];

    /**
     * Relationship: Users who bookmarked this medicine
     */
    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_medicines')
                    ->withPivot('notes')
                    ->withTimestamps();
    }

    /**
     * Relationship: Interactions on medicine_id_1
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(MedicineInteraction::class, 'medicine_id_1');
    }
}
