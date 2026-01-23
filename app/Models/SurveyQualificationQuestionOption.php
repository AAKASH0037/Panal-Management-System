<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class SurveyQualificationQuestionOption extends Model
{
      use SoftDeletes;
    protected $table = 'survey_qualification_question_options';
    protected $primaryKey = 'opt_id';

    protected $fillable = [
        'qs_id',
        'option_value'
    ];

    public function question()
    {
        return $this->belongsTo(
            SurveyQualificationQuestion::class,
            'qs_id',
            'qs_id'
        );
    }
}