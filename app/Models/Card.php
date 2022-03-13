<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function rank()
    {
        return $this->hasOne(Rank::class, 'id', 'rank_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function suit()
    {
        return $this->hasOne(Suit::class, 'id', 'suit_id');
    }

    public function getRankingAttribute()
    {
        return $this->rank->ranking;
    }


}
