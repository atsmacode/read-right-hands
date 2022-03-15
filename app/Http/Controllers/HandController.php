<?php

namespace App\Http\Controllers;

use App\Classes\GamePlay;
use App\Models\Hand;
use Illuminate\Http\Request;

class HandController extends Controller
{
    public function new(Request $request)
    {
        $hand = Hand::create();

        return response((new GamePlay($hand))->start());
    }
}
