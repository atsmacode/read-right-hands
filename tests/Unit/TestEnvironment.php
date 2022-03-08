<?php

namespace Tests\Unit;

use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\CreatesApplication;
use Tests\TestCase;

abstract class TestEnvironment extends TestCase
{

    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('create:ranks');
        Artisan::call('create:suits');
        Artisan::call('create:deck');

        $this->deck = Card::all();
    }
}
