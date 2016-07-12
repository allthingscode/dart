<!DOCTYPE html>
<html>
<head>
    <title>Schedules</title>
</head>
<body>
<div>
    <div>
        <h1>Where's My Train</h1>

        <h2>
            As Of:
        </h2>
        <h2>
            Station:  {{$railLine->rail_line}}
        </h2>

        <h3>
            Stops:  {{number_format(count($schedules))}}
        </h3>

        <table border="1">
            <tr>
                <th>Trip Number</th>
                <th>Station Name</th>
                <th>Time</th>
            </tr>
            @each('schedule', $schedules, 'schedule' )
        </table>

    </div>
</div>
</body>
</html>
