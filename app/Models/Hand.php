<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hand extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'game_type_id'
    ];

    public function handTable()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    /*
     * Through relations not working well when running full test suite
     * and possibly when using the app in browser.
     */
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

    public function pot()
    {
        return $this->hasOne(Pot::class);
    }

    public function complete()
    {
        $this->completed_on = now();
        $this->save();
    }
}
