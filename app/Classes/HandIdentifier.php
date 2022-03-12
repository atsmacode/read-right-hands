<?php

namespace App\Classes;

class HandIdentifier
{
    public function identify($wholeCards, $communityCards)
    {
        return [
            'wholeCards' => $wholeCards,
            'communityCards' => $communityCards
        ];
    }
}
