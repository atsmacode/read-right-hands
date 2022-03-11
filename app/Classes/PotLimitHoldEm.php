<?php

namespace App\Classes;

class PotLimitHoldEm implements Game
{

    public array $streets;
    public string $limit;

    public function __construct()
    {
        $this->streets = [
            'pre_flop' => [
                'whole_cards' => 2,
                'community_cards' => 0
            ],
            'flop' => [
                'whole_cards' => 0,
                'community_cards' => 3
            ],
            'turn' => [
                'whole_cards' => 0,
                'community_cards' => 1
            ],
            'river' => [
                'whole_cards' => 0,
                'community_cards' => 1
            ]
        ];
        $this->limit = 'pot';
    }

}
