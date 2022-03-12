<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'player_id',
        'table_seat_id',
        'hand_street_id',
        'action_id',
        'bet_amount',
        'hand_id'
    ];

    public function hand()
    {
        return $this->hasOneThrough(
            Hand::class,
            HandStreet::class,
            'hand_street_id',
            'hand_id',
            'hand_street_id',
            'hand_id'
        );
    }

    public function action()
    {
        return $this->hasOne(Action::class, 'id', 'action_id');
    }

    public function tableSeat()
    {
        return $this->hasOne(TableSeat::class, 'id', 'table_seat_id');
    }
}
