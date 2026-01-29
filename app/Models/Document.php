<?php

namespace App\Models;

use App\Enum\DocumentStatus;
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
        'area_origen_id',
        'gestion_id',
        'user_id',
        'folio',
        'reception_date',
        'response_deadline',
        'condition',
        'status',
        'priority_id',
        'id_office_destination',
    ];

    protected $casts = [
        'reception_date' => 'date',
        'response_deadline' => 'date',
        'condition' => 'boolean',
        'status' => DocumentStatus::class,
    ];

    public function files(): HasMany
    {
        return $this->hasMany(DocumentFile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function areaOrigen(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'area_origen_id');
    }

    public function officeDestination(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'id_office_destination');
    }

    public function gestion(): BelongsTo
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
}
