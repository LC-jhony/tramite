<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $requires_response
 * @property int|null $response_days
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Document> $documents
 * @property-read int|null $documents_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereRequiresResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereResponseDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class DocumentType extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'code',
        'name',
        'requires_response',
        'response_days',
        'status',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
