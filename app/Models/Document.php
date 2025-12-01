<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_number',
        'tanggal',
        'kategori',
        'title',
        'description',
        'department_id',
        'file_path',
        'created_by',
    ];

    /* ============================
       RELATIONSHIPS
    ============================= */

    // Relasi ke departemen
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Relasi ke user yang membuat dokumen
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ============================
       Helpers
    ============================= */

    // Helper untuk mendapatkan URL file
    public function fileUrl()
    {
        return Storage::url($this->file_path);
    }
}
