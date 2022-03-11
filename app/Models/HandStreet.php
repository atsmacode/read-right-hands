<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HandStreet extends Model
{
    use HasFactory;

    protected $fillable = [
        'street_id',
        'hand_id'
    ];

    public function cards()
    {
        return $this->hasMany(HandStreetCard::class, 'hand_street_id', 'id');
    }
}
