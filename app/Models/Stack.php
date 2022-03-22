<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stack extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'player_id',
        'table_id'
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}
