<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'revision_number',
        'revision_note',
        'revised_by',
        'revised_at',
    ];

    protected $casts = [
        'revised_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function revisedBy()
    {
        return $this->belongsTo(User::class, 'revised_by');
    }
}
