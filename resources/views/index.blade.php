<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Read Right Hands</title>

    </head>
    
    <body>
        <div id="app">
            <h1>Read Right Hands</h1>
            <div class="row">

                <div v-for="player in players" :key="player.table_seat_id" class="col">
                    Player @{{player.player_id}}: @{{player.action_name}}
                    <div v-for="card in player.whole_cards" class="card">
                        @{{card.rank}} @{{card.suit}}
                    </div>
                    
                    <button v-if="player.action_on" v-on:click="action(option.id, player)" class="btn btn-primary" v-for="option in player.availableOptions" :key="option.name" v-bind:data-action-id="option.id">
                        @{{option.name}}
                    </button>
                </div>

                <div v-if="communityCards.length > 0">
                    <h2>Community Cards</h2>
                    <div v-for="card in communityCards" class="card">
                        @{{card.rank}} @{{card.suit}}   
                    </div>
                </div>

                <div v-if="winner">
                    <h2>Winner</h2>
                    <p>Player @{{winner.player.id}} with @{{winner.handType.name}}
                </div>

            </div>
        </div>
    
    </body>
    <script src="{{asset('js/app.js')}}"></script>
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
</html>
