<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Document $document
 * @property-read \App\Models\Office|null $fromOffice
 * @property-read \App\Models\Office|null $toOffice
 * @property-read \App\Models\User $user
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
class Movement extends Model
{
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

    public function document(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromOffice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Office::class, 'from_office_id');
    }

    public function toOffice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Office::class, 'to_office_id');
    }
}
