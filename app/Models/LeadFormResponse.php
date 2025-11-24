<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property array fields
 * @property string fingerprint
 * @property string ip
 * @property string user_agent
 * @property int lead_form_id
 * @property LeadForm lead_form
 */

class LeadFormResponse extends Model
{
    use HasFactory;

    protected $casts = [
        'fields' => 'array'
    ];

    public function lead_form()
    {
        return $this->belongsTo(LeadForm::class);
    }

    public function findAnswer($question)
    {
        $answers = collect($this->fields);

        $answer = $answers->first(function ($answer) use ($question) {
            return trim($answer['question']) === trim($question['text']);
        });

        return isset($answer['value']) ? $answer['value'] : null;
    }
}
