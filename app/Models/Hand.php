<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hand extends Model
{
    use HasFactory;

    public function handTable()
    {
        return $this->belongsTo(Table::class);
    }

    public function actions()
    {
        return $this->hasManyThrough(
            PlayerAction::class,
            HandStreet::class,
            'hand_id',
            'id',
            'hand_id',
            'id'
        );
    }
}
