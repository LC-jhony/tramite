<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function type(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function currentOffice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Office::class, 'current_office_id');
    }

    public function administration(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Administration::class, 'gestion_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Movement::class);
    }

    public function latestMovement(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Movement::class)->latestOfMany();
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
}
