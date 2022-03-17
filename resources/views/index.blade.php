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
            <p>@{{count}}</p>
        </div>
    
    </body>
    <script src="{{asset('js/app.js')}}"></script>

</html>
