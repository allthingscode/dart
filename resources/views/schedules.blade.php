<!DOCTYPE html>
<html>
<head>
    <title>Scheduled Stops</title>
</head>
<body>
<div>
    <div>
        @if( isset( $reportName ) )
            <h1>{{$reportName}}</h1>
        @else
            <h1>Schedule Items</h1>
        @endif

        @if( isset( $scheduleProgram ) )
            <h2>
                Schedule Program Name:  {{$scheduleProgram->name}}
            </h2>
        @endif
        @if( isset( $trainTripProgram ) )
            <h2>
                Train Trip Program Name:  {{$trainTripProgram->name}}
            </h2>
        @endif

        <h3>
            Stops:  {{number_format(count($schedules))}}
        </h3>

        <table border="1">
            <tr>
                <th>Time</th>
                <th>Station Name</th>
                <th>Trip Program Name</th>
            </tr>
            @each( 'schedule', $schedules, 'schedule' )
        </table>

    </div>
</div>
</body>
</html>
