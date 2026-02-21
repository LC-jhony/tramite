<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, SoftDeletes;
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

    public function document(): MorphOne
    {
        return $this->morphOne(
            Document::class,
            'sender'
        );
    }
}
