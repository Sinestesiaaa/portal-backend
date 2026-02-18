<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'document_number',
        'title',
        'kategori',
        'department_id',
        'site_id',
        'published_at',
        'review_date',
        'file_path',
        'form_description_path',
        'revision_number',
        'last_revision_at',
        'previous_file_path',
        'revision_note',
        'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'last_revision_at' => 'datetime',
        'review_date' => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function revisions()
    {
        return $this->hasMany(DocumentRevision::class)->orderBy('revision_number', 'desc');
    }

    public function audits()
    {
        return $this->hasMany(DocumentAudit::class)->orderBy('created_at', 'desc');
    }

    public function relatedDocuments()
    {
        return $this->belongsToMany(
            self::class,
            'document_relations',
            'document_id',
            'related_document_id'
        );
    }
}
