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

    /*
     * Through relations not working well when running full test suite
     * and possibly when using the app in browser.
     */
    public function players()
    {
        return $this->hasManyThrough(
            Player::class,
            TableSeat::class,
            'table_id',
            'id'
        );
    }

    public function hands()
    {
        return $this->hasMany(Hand::class);
    }
}
