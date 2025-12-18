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
        'education',
        'income',
        'zip_code',
        'city',
        'country',

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
