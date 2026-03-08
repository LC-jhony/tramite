<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $representation
 * @property string|null $full_name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $dni
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $ruc
 * @property string|null $company
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 *
 * @method static \Database\Factories\CustomerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDni($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereRepresentation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereRuc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'representation',
        'full_name',
        'first_name',
        'last_name',
        'dni',
        'phone',
        'email',
        'address',
        'ruc',
        'company',
    ];

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Document::class);
    }
}
