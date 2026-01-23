<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyQualificationQuestion extends Model
{
     use SoftDeletes;
    protected $table = 'survey_qualification_questions';
    protected $primaryKey = 'qs_id';

    protected $fillable = [
        'question',
        'label',
        'type',
        'status'
    ];

    public function options()
    {
        return $this->hasMany(
            SurveyQualificationQuestionOption::class,
            'qs_id',
            'qs_id'
        );  
    }
}
