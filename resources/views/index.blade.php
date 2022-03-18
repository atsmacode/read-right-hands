<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Zilla+Slab:ital,wght@0,300;0,500;0,600;0,700;1,300;1,400&display=swap" rel="stylesheet">

        <title>Read Right Hands</title>

    </head>

    <body class="bg-dark text-white">

        <div id="app" class="container-sm">

            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <div class="container-fluid">

                    <a class="navbar-brand" href="/"><strong><span class="text-danger">Read</span></strong> Right Hands</a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">

                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="/play">Play</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Hand History</a>
                            </li>
                        </ul>

                    </div>

                </div>
            </nav>

            <div class="bg-secondary p-3 rounded m-1">

                <div class="row">

                    <h1>Players</h1>

                    <div v-for="player in players" :key="player.name" class="col-3 mb-3">

                        <div class="m-1 p-3 bg-dark rounded">

                            <p>
                                Player @{{player.player_id}}: <span v-if="player.action_id" v-bind:class="actionColours[player.action_name]" class="d-inline rounded p-1"><strong>@{{player.action_name}}</strong></span>
                            </p>

                            <div class="row mb-2 m-0 p-0">
                                <div v-for="card in player.whole_cards" class="m-0 me-1 bg-white" v-bind:class="suitColours[card.suit]" style="width:100px;height:130px;">
                                    <div class="card-body ps-1 pe-0">
                                        <p class="fs-2"><strong>@{{card.rank}}</strong> @{{card.suitAbbreviation}}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-show="showOptions(player.action_on)">
                                <button v-on:click="action(option.id, player)" class="btn btn-primary me-1" v-for="option in player.availableOptions" :key="option.name" v-bind:data-action-id="option.id">
                                    @{{option.name}}
                                </button>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="bg-success p-3 rounded m-1">
                <div class="row">
                    <div class="col">
                        <h2>Community Cards</h2>
                        <div v-if="communityCards.length > 0">
                            <div class="row mb-2 ms-0">
                                <div v-for="card in communityCards" class="m-0 bg-white ms-1" v-bind:class="suitColours[card.suit]" style="width:100px;height:130px">
                                    <div class="card-body ps-1 pe-0">
                                        <p class="fs-2"><strong>@{{card.rank}}</strong> @{{card.suitAbbreviation}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div v-if="winner">
                <div class="bg-info p-3 rounded m-1">
                    <h2>Winner</h2>
                    <p>Player @{{winner.player.id}} with @{{winner.handType.name}}</p>
                    <button v-on:click="gameData" class="btn btn-primary">
                        Play Again
                    </button>
                </div>
            </div>

        </div>


    </body>
    <script src="{{asset('js/app.js')}}"></script>
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
</html>
