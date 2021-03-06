<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pot extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'hand_id'
    ];

    public function hand()
    {
        return $this->belongsTo(Hand::class);
    }

    public function handTable()
    {
        return $this->hasOneThrough(
            Table::class,
            Hand::class,
            'table_id',
            'id'
        );
    }
}
