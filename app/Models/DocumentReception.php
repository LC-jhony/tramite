<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DocumentReceptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentReception extends Model
{
    /** @use HasFactory<DocumentReceptionFactory> */
    use HasFactory;

    protected $fillable = [
        'document_id',
        'movement_id',
        'user_id',
        'office_id',
        'reception_date',
        'movement_Action',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(Movement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
