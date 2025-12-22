<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Clicks extends Model
{
    use HasFactory;

    protected $table = 'clicks';

    protected $fillable = [
        'survey_id',
        'quota_id',
        'u_id',
        'uu_id',
    ];

    
    public function survey()
    {
        return $this->belongsTo(Survey::class, 'survey_id');
    }

    public function quota()
    {
        return $this->belongsTo(Quota::class, 'quota_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'u_id');
    }
}
