<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'seats'
    ];

    public function tableSeats()
    {
        return $this->hasMany(TableSeat::class, 'table_id', 'id');
    }

    public function players()
    {
        return $this->hasManyThrough(
            Player::class,
            TableSeat::class,
            'table_id',
            'player_id',
            'table_id',
            'player_id'
        );
    }
}
