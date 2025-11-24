<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScannedCardDetail extends Model
{
    use HasFactory;

    protected $table = 'scanned_card_details';

    protected $guarded = [];
}
