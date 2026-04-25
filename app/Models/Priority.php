<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Priority extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'name',
        'color',
        'status',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
