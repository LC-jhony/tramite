<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Movement> $movementsFrom
 * @property-read int|null $movements_from_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Movement> $movementsTo
 * @property-read int|null $movements_to_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Office whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Office extends Model
{
    protected $fillable = [
        'code',
        'name',
        'status',
    ];

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Document::class, 'current_office_id');
    }

    public function movementsFrom(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Movement::class, 'from_office_id');
    }

    public function movementsTo(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Movement::class, 'to_office_id');
    }
}
