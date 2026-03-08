<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $requires_response
 * @property int|null $response_days
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
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

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Document::class);
    }
}
