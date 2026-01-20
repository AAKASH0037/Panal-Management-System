<?php

// app/Models/Language.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
     protected $table = 'languages';
    protected $fillable = ['name', 'code'];
     public function campaigns()
    {
        return $this->hasMany(SurveyCampaign::class, 'language_id');
    }
}
