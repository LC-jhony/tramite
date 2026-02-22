<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'filename',
        'path',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    public function document()
    {
        return $this->belongsTo(
            related: Document::class,
            foreignKey: 'document_id'
        );
    }
}
