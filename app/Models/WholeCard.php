<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WholeCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'player_id',
        'hand_id'
    ];
}
