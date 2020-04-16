<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Kalkulator odsetkowy</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
        </style>
    </head>
    <body>
        <div class="content">
            <form method="post" action="{!! route('result') !!}">
            @csrf
            <p>Należność główna: <input type="text" name="main"></p>
            <p>Koszty: <input type="text" name="cost"></p>
            <p>Odsetki za opóźnienie:<input type="checkbox" name="interest1"></p>
            <p>Odsetki kapitałowe:<input type="checkbox" name="interest2"></p>
            <p>Termin płatność: <input type="date" name="start"></p>
            <p>Data płatności: <input type="date" name="end"></p>
            <p><input type="submit" value="Oblicz"></p>
            </form>
        </div>
    </body>
</html>
