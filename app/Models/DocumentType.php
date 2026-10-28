<?php

namespace App\Models;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'requires_response',
        'response_days',
        'status',
    ];

    #[Override]
    protected function casts()
    {
        return [
            'status' => 'boolean',
        ];
    }
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
