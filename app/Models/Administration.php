<?php

namespace App\Models;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Administration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_period',
        'end_period',
        'mayor',
        'status',
    ];

    protected $casts = [
        'start_period' => 'integer',
        'end_period' => 'integer',
        'status' => 'boolean',
    ];
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'gestion_id');
    }
}
