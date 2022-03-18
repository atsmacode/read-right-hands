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

                    <a class="navbar-brand" href="#"><strong><span class="text-danger">Read</span></strong> Right Hands</a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">

                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="/play">Play</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Hand History</a>
                            </li>
                        </ul>

                    </div>

                </div>
            </nav>

            <h1>Welcome</h1>

            <p>Read Right Hands is a simple poker game developed in Laravel.</p>

            <div class="ms-1 mb-3">
                <a class="btn btn-primary" href="/play">Play Now!</a>
            </div>

            <div class="bg-secondary p-3 rounded m-1">

                <div class="row">

                    <h2>Players</h2>

                    <p>Players will be displayed here.</p>

                </div>

            </div>

            <div class="bg-success p-3 rounded m-1">
                <div class="row">
                    <div class="col">
                        <h2>Community Cards</h2>
                        <p>Community cards will be dealt here.</p>
                    </div>
                </div>
            </div>

            <div class="bg-info p-3 rounded m-1">
                <h2>Winner</h2>
                <p>The winner of the hand will be shown here.</p>
            </div>

        </div>


    </body>
    <script src="{{asset('js/app.js')}}"></script>
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
</html>
