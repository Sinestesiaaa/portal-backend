<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'department_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* ============================
       RELATIONSHIPS
    ============================= */

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /* ============================
       ROLE HELPERS
    ============================= */

    public function isAdmin()
    {
        return $this->role_id == 1;
    }

    public function isSuperUser()
    {
        return $this->role_id == 2;
    }

    public function isUser()
    {
        return $this->role_id == 3;
    }
}
