<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HandStreetCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'hand_street_id'
    ];
}
