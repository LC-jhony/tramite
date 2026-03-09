<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $customer_id
 * @property string $document_number
 * @property string $case_number
 * @property string $subject
 * @property string $origen
 * @property int $document_type_id
 * @property int $current_office_id
 * @property int $gestion_id
 * @property int|null $user_id
 * @property string|null $folio
 * @property string $reception_date
 * @property string|null $response_deadline
 * @property string|null $condition
 * @property string $status
 * @property int|null $priority_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Administration $administration
 * @property-read \App\Models\Office $currentOffice
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\Movement|null $latestMovement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Movement> $movements
 * @property-read int|null $movements_count
 * @property-read \App\Models\DocumentType $type
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCaseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCurrentOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDocumentTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFolio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereGestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereOrigen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document wherePriorityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereReceptionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereResponseDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUserId($value)
 *
 * @mixin \Eloquent
 */
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

    public function receptions(): \Illuminate\Database\Eloquent\Relations\HasMany
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
}
