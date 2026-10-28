<?php

namespace App\Models;

use App\Models\Movement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'status',
    ];

    #[Override]
    protected function casts()
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function user(): HasMany
    {
        return $this->hasMany(
            related: User::class,
            foreignKey: 'office_id'
        );
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'current_office_id');
    }

    public function movementsFrom(): HasMany
    {
        return $this->hasMany(Movement::class, 'from_office_id');
    }

    public function movementsTo(): HasMany
    {
        return $this->hasMany(Movement::class, 'to_office_id');
    }
}
