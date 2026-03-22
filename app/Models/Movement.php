<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property int $document_id
 * @property int $user_id
 * @property int|null $from_office_id
 * @property int|null $to_office_id
 * @property string $action
 * @property string|null $indication
 * @property string|null $observation
 * @property string $receipt_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Document $document
 * @property-read Office|null $fromOffice
 * @property-read Office|null $toOffice
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereFromOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereIndication($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereObservation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereReceiptDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereToOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movement whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Movement extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'document_id',
        'user_id',
        'from_office_id',
        'to_office_id',
        'action',
        'indication',
        'observation',
        'receipt_date',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'from_office_id');
    }

    public function toOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'to_office_id');
    }
}
