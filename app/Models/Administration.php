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
 * @property string $name
 * @property string $start_period
 * @property string $end_period
 * @property string $mayor
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Document> $documents
 * @property-read int|null $documents_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereEndPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereMayor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereStartPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Administration whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Administration extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'start_period',
        'end_period',
        'mayor',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'gestion_id');
    }
}
