<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'name',
        'parent_office_id',
        'level',
        'manager',
        'status',
    ];
    public function parentOffice(): BelongsTo
    {
        return $this->belongsTo(
            related: Office::class,
            foreignKey: 'parent_office_id'
        );
    }
    public function childOffice(): HasMany
    {
        return $this->hasMany(
            related: Office::class,
            foreignKey: 'parent_office_id'
        );
    }
    public function users(): HasMany
    {
        return $this->hasMany(
            related: User::class,
            foreignKey: 'office_id'
        );
    }
}
