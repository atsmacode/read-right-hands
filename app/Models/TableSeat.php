<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'player_id',
        'can_continue'
    ];

    public function player()
    {
        return $this->hasOne(Player::class, 'id', 'player_id');
    }
}
