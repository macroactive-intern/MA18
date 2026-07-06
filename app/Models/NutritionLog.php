<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'logged_at',
        'meal_name',
        'protein_g',
        'carbs_g',
        'fat_g',
        'calories',
    ];

    protected $casts = [
        'logged_at' => 'date',
        'protein_g' => 'decimal:1',
        'carbs_g'   => 'decimal:1',
        'fat_g'     => 'decimal:1',
        'calories'  => 'decimal:1',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
