<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hand extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_type_id'
    ];

    public function handTable()
    {
        return $this->belongsTo(Table::class, '');
    }

    public function playerActions()
    {
        // This hasManyThrough returns empty collection, I've added hand_id directly to player_actions for now
        return $this->hasManyThrough(
            PlayerAction::class,
            HandStreet::class,
            'hand_id',
            'hand_street_id'
        );
    }

    public function streets()
    {
        return $this->hasMany(HandStreet::class, 'hand_id', 'id');
    }

    public function wholeCards()
    {
        return $this->hasMany(WholeCard::class, 'hand_id', 'id');
    }
}
