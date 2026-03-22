<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Administration $administration
 * @property-read Office $currentOffice
 * @property-read Customer|null $customer
 * @property-read Movement|null $latestMovement
 * @property-read Collection<int, Movement> $movements
 * @property-read int|null $movements_count
 * @property-read DocumentType $type
 * @property-read User|null $user
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
class Document extends Model implements AuditableContract
{
    use Auditable;

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

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function documentFiles(): HasMany
    {
        return $this->hasMany(DocumentFile::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function currentOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'current_office_id');
    }

    public function administration(): BelongsTo
    {
        return $this->belongsTo(Administration::class, 'gestion_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
