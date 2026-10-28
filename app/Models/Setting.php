<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'name',
        'locked',
        'payload',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'locked' => 'boolean',
        ];
    }
}
