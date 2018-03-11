<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="utf-8">
        <title>What's Open</title>
        <link rel="stylesheet" href="css/styles.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
    </head>
    <body>
        <section id="header-section">
            <div>
                <h1>What's Open?</h1>
                <input id="search-input" placeholder="Search..." onkeyup="searchTable()" autofocus>
                <div>
                    <input id="search-open-only" type="checkbox" name="search-options" value="open-only" onchange='searchTable()'>
                    <label for="open-only">Open locations only?</label>
                </div>
            </div>
        </section>

        <section id="content-section">
        <div class="poi-container table-responsive">
            <table class="table table-hover">
        <?php
            date_default_timezone_set('America/New_York');
            $current_day_of_week = (int) date("N") - 1; // 0 for Monday to 6 for Sunday
            $current_hour = (int) date("G");
            $current_minute = (int) date("i");
            $current_time = $current_hour + $current_minute / 60;
            $before_3_am = $current_hour < 3;


            // If before 3am, the previous day will be the first column,
            //      so we need to adjust the highlighted column to the right 
            echo '<colgroup><col>';
            if($before_3_am) {
                echo '<col>';
            }
            echo '<col id="today-column"></colgroup>';

            $days_of_week = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');          
                        
            $json_file = file_get_contents('data/hours.json');
            $json_string = json_decode($json_file, true);
            foreach($json_string as $group) {
                echo '<thead><tr>';
                echo '<th class="group-name">' . $group['groupName'] . '</th>';
                    for($cnt = 0; $cnt < 7; $cnt++) {
                        echo '<th>' . $days_of_week[($cnt + $current_day_of_week - $before_3_am) % 7] . '</th>';
                    }
                echo '</tr></thead>';
                echo '<tbody>';
                foreach($group['groupLocations'] as $location) {
                    $hours_today = $location['locationHours'][$current_day_of_week];
                    $hours_yesterday = $location['locationHours'][($current_day_of_week + 6) % 7];
                    $start_time_today = $hours_today['startTime'][0] + $hours_today['startTime'][1] / 60;
                    $stop_time_today = $hours_today['stopTime'][0] + $hours_today['stopTime'][1] / 60;
                    $stop_time_yesterday = $hours_yesterday['stopTime'][0] + $hours_yesterday['stopTime'][1] / 60;
                    $open_right_now = False;
                    if(($current_time > $start_time_today && $current_time < $stop_time_today) ||
                            ($current_time + 24 < $stop_time_yesterday)) {
                        $open_right_now = True;
                    }

                    $open_now_class = '';
                    if($open_right_now) {
                        $open_now_class = 'table-success';
                    }

                    echo '<tr class="' . $open_now_class . '">';
                        echo '<td>' . $location['locationName'] . '</td>';
                        for($cnt = 0; $cnt < 7; $cnt++) {
                            $day = $location['locationHours'][($cnt + $current_day_of_week) % 7];
                            echo '<td>';
                            if($day['open']) {
                                // Ok this is wild, you need to define seconds as 0 and then month as 0 
                                //      because Daylight Savings Time
                                echo date("g:ia", mktime($day['startTime'][0], $day['startTime'][1], 0, 0));
                                echo '-';
                                echo date("g:ia", mktime($day['stopTime'][0], $day['stopTime'][1], 0, 0));
                            }
                            echo '</td>';
                        }
                    echo '</tr>';
                }
                echo '</tbody>';
            }

        ?>
            </table>
        </div>
        </section>
        <script src="js/search.js"></script>
    </body>
</html>
