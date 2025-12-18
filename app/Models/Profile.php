<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $table = 'profile'; // table name

    protected $fillable = [
        'u_id',
        'age',
        'gender',
        'state',
        'qualification',
        'income',
    ];

    /**
     * Profile belongs to User
     * profile.u_id -> users.id
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'u_id', 'id');
    }
}
