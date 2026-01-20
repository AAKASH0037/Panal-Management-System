<?php

// app/Models/Country.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
      protected $table = 'countries';
    protected $fillable = ['name', 'code'];
    public function campaigns()
    {
        return $this->hasMany(SurveyCampaign::class, 'country_id');
    }
}
