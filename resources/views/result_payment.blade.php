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

     
        <table style='text-align:center; border:1px solid;'>
            <tr>
              <th></th>
              <th>Main</th>
              <th>Cost</th>
              <th>kze</th>
              <th>Total interest</th>
              <th>Date-range interest</th>
              <th>Days</th>
              <th>Period interest</th>
              <th>Payment amount</th>
              <th>Payment date</th>
            </tr>
            @foreach($befores as $key =>$before)
           
            <tr>
              <td>Before payment</td>
              <td>{{$before['main']}}</td>
              <td>{{$before['cost']}}</td>
              <td>{{$before['executivecost']}}</td>
              <td>{{$before['interesttotal']}}</td>
              <td>{{$before['datefrom'].' to '.$before['dateto']}}</td>
              <td>{{$before['days']}}</td>
              <td>{{$before['interestperiod']}}</td>
              <td>--</td>
              <td>--</td>
            </tr>
            <tr>
                <td>Payment allocation</td>
                <td>{{$allocations[$key]['main']}}</td>
                <td>{{$allocations[$key]['cost']}}</td>
                <td>{{$allocations[$key]['executivecost']}}</td>
                <td>{{$allocations[$key]['interest']}}</td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
                <td>{{$before['payment']}}</td>
                <td>{{$before['dateto']}}</td>
              </tr>
              <tr>
                <td>After payment</td>
                <td>{{$after[$key]['main']}}</td>
                <td>{{$after[$key]['cost']}}</td>
                <td>{{$after[$key]['executivecost']}}</td>
                <td>{{$after[$key]['interest']}}</td>
                @if($after[$key]['overbalance'] != '0')
                <td col='6' style='color:red'>Overbalance: {{$after[$key]['overbalance']}} </td>
                @else
                <td>--</td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
                @endif
              </tr>
            @endforeach
        </table>
    </body>
</html>
