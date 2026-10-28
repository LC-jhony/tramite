<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    protected $fillable = [
        'customer_id',
        'document_number',
        'case_number',
        'subject',
        'origen',
        'document_type_id',
        'current_office_id',
        'gestion_id',
        'user_id',
        'folio',
        'reception_date',
        'response_deadline',
        'condition',
        'status',
        'priority_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            related: Customer::class,
            foreignKey: 'customer_id',
        );
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(
            related: DocumentType::class,
            foreignKey: 'document_type_id'
        );
    }

    public function currentOffice(): BelongsTo
    {
        return $this->belongsTo(
            related: Office::class,
            foreignKey: 'current_office_id'
        );
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(
            related: Administration::class,
            foreignKey: 'gestion_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: User::class,
            foreignKey: 'user_id'
        );
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function documentFiles(): HasMany
    {
        return $this->hasMany(DocumentFile::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function administration(): BelongsTo
    {
        return $this->belongsTo(Administration::class, 'gestion_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    public function latestMovement(): HasOne
    {
        return $this->hasOne(Movement::class)->latestOfMany();
    }

    public function receptions(): HasMany
    {
        return $this->hasMany(DocumentReception::class);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['finalizado', 'cancelado', 'rechazado']);
    }

    public function wasDerivedBy(int $userId): bool
    {
        return $this->movements()
            ->where('user_id', $userId)
            ->where('action', 'derivado')
            ->exists();
    }

    public function wasReceived(): bool
    {
        return $this->receptions()->exists();
    }

    public function hasActionByCurrentUser(): bool
    {
        return $this->movements()
            ->where('user_id', auth()->id())
            ->exists();
    }
}
