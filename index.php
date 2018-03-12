<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="utf-8">
        <title>What's Open</title>
        <link rel="stylesheet" href="css/styles.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
    </head>
    <body onload="initialize()">
        <section id="header-section">
            <div>
                <h1>What's Open @ UVA</h1>
                <input id="search-input" placeholder="Search..." onkeyup="searchTable()" autofocus>
                <div id="search-options">
                    <div class="bg-success text-white" onclick="toggleChildInput(this)">
                        <div>
                            <input id="search-open-only" type="checkbox" name="search-options" value="open-only" onchange='searchTable()' onclick='ensureOneToggle(this)'>
                            <label for="open-only">Open places only?</label>
                        </div>
                    </div>
                    <div class="bg-warning text-white" onclick="toggleChildInput(this)">
                        <div>
                            <input id="search-include-closing-soon" type="checkbox" name="search-options" value="include-closing-soon" onchange='searchTable()' onclick='ensureOneToggle(this)' checked>
                            <label for="include-closing-soon">Include places closing soon?</label>
                        </div>
                    </div>
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
            $current_hour = 23;
            $current_minute = (int) date("i");
            $current_time = $current_hour + $current_minute / 60;
            $before_3_am = $current_hour < 3;

            // If before 3am, the previous day will be the first column,
            //      so we need to adjust the highlighted column to the right 
            echo '<colgroup><col>';
            if($before_3_am) {
                echo '<col>';
            }
            echo '<col id="today-column" class="bg-faded"></colgroup>';

            $days_of_week = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
            $json_file = file_get_contents('data/hours.json');
            $json_string = json_decode($json_file, true);
            foreach($json_string as $group) {
                echo '<thead><tr>';
                echo '<th class="group-name">' . $group['groupName'] . '</th>';
                    for($cnt = 0; $cnt < 7; $cnt++) {
                        echo '<th>' . $days_of_week[($cnt + $current_day_of_week - $before_3_am + 7) % 7] . '</th>';
                    }
                echo '</tr></thead>';
                echo '<tbody>';
                foreach($group['groupLocations'] as $location) {
                    $hours_today = $location['locationHours'][$current_day_of_week];
                    $hours_yesterday = $location['locationHours'][($current_day_of_week - 1 + 7) % 7];
                    $hours_tomorrow = $location['locationHours'][($current_day_of_week + 1) % 7];
                    $start_time_today = $hours_today['startTime'][0] + $hours_today['startTime'][1] / 60;
                    $stop_time_today = $hours_today['stopTime'][0] + $hours_today['stopTime'][1] / 60;
                    $stop_time_yesterday = $hours_yesterday['stopTime'][0] + $hours_yesterday['stopTime'][1] / 60;
                    $start_time_tomorrow = $hours_tomorrow['startTime'][0] + $hours_tomorrow['startTime'][1] / 60;

                    $open_from_today = False;
                    if($current_time > $start_time_today && $current_time < $stop_time_today) {
                        $open_from_today = True;
                    }

                    $open_from_yesterday = False;
                    if($current_time + 24 < $stop_time_yesterday) {
                        $open_from_yesterday = True;
                    }

                    $within_one_hour_of_stop_time_yesterday = ($stop_time_yesterday - $current_time - 24 < 1 && $stop_time_yesterday - $current_time - 24 > 0);
                    $within_one_hour_of_stop_time_today = ($stop_time_today - $current_time < 1 && $stop_time_today - $current_time > 0);
                    $within_one_hour_of_start_time_tomorrow = ($start_time_tomorrow - $current_time + 24 < 1 && $start_time_tomorrow - $current_time + 24 > 0);
                    $closing_soon = False;
                    if(($within_one_hour_of_stop_time_yesterday) || ($within_one_hour_of_stop_time_today && !$within_one_hour_of_start_time_tomorrow)) {
                        $closing_soon = True;
                    }

                    if($closing_soon) {
                        echo '<tr class="table-warning">';
                    } else if($open_from_today || $open_from_yesterday) {
                        echo '<tr class="table-success">';
                    } else {
                        echo '<tr>';
                    }
                        echo '<td data-alt="' . $group['groupName'] . '">' . $location['locationName'] . '</td>';
                        for($cnt = 0; $cnt < 7; $cnt++) {
                            $day = $location['locationHours'][($cnt + $current_day_of_week - $before_3_am + 7) % 7];
                            if(($cnt == 0 && $before_3_am && $open_from_yesterday) ||
                                ($cnt == 1 && $before_3_am && $open_from_today) ||
                                ($cnt == 0 && !$before_3_am && $open_from_today)) {
                                if($closing_soon) { 
                                    echo '<td class="bg-warning text-white">';
                                } else {
                                    echo '<td class="bg-success text-white">';
                                }
                            } else {
                                echo '<td>';
                            }
                            if($day['open']) {
                                // Ok this is wild, you need to define seconds as 0 and then month as 0 
                                //      because Daylight Savings Time
                                $start_time_epoch = mktime($day['startTime'][0], $day['startTime'][1], 0, 0);
                                if($day['startTime'][1] == 0){
                                    echo date("ga", $start_time_epoch);
                                } else {
                                    echo date("g:ia", $start_time_epoch);
                                }
                                echo '-';
                                $stop_time_epoch = mktime($day['stopTime'][0], $day['stopTime'][1], 0, 0);
                                if($day['stopTime'][1] == 0){
                                    echo date("ga", $stop_time_epoch);
                                } else {
                                    echo date("g:ia", $stop_time_epoch);
                                }
                            } else {
                                echo 'Closed';
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
