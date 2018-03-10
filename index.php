<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>What's Open</title>
        <link rel="stylesheet" href="css/styles.css">
    </head>
    <body>
        <div class="poi-container">
        <?php
            date_default_timezone_set('America/New_York');
            $current_day_of_week = (int) date("N") - 1; // 0 for Monday to 6 for Sunday
            $current_hour = (int) date("G");
            $current_minute = (int) date("i");
            $current_time = $current_hour + $current_minute / 60;

            $days_of_week = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
            echo '<div class="poi poi-header">';
                echo '<div></div>';
                echo '<div class="poi-week">';
                for($cnt = 0; $cnt < 7; $cnt++) {
                    echo '<div class="poi-hours">';
                        if($cnt == 0) {
                            echo 'Today';
                        } else if($cnt == 1){
                            echo 'Tomorrow';
                        } else {
                            echo $days_of_week[($cnt + $current_day_of_week) % 7];
                        }
                    echo '</div>';
                }
                echo '</div>';
            echo '</div>';

            $json_file = file_get_contents('data/hours.json');
            $json_string = json_decode($json_file, true);
            foreach($json_string as $group) {
                echo $group['groupName'] . '<br>';
                foreach($group['groupLocations'] as $location) {

                    $hours_today = $location['locationHours'][$current_day_of_week];
                    $start_time_today = $hours_today['startTime'][0] + $hours_today['startTime'][1] / 60;
                    $stop_time_today = $hours_today['stopTime'][0] + $hours_today['stopTime'][1] / 60;
                    $open_right_now = False;
                    if($current_time > $start_time_today && $current_time < $stop_time_today) {
                        $open_right_now = True;
                    }

                    $open_now_class = '';
                    if($open_right_now) {
                        $open_now_class = 'poi-open';
                    }

                    echo '<div class="poi">';
                        echo '<div class="poi-name ' . $open_now_class . '">' . $location['locationName'] . '</div>';
                        echo '<div class="poi-week">';
                        for($cnt = 0; $cnt < 7; $cnt++) {
                            $day = $location['locationHours'][($cnt + $current_day_of_week) % 7];
                            if($cnt == 0) {
                                echo '<div class="poi-hours ' . $open_now_class . '">';
                            } else {
                                echo '<div class="poi-hours">';
                            }
                            if($day['open']) {
                                echo date("g:iA", mktime($day['startTime'][0], $day['startTime'][1]));
                                echo '-';
                                echo date("g:iA", mktime($day['stopTime'][0], $day['stopTime'][1]));
                            }
                            echo '</div>';
                        }
                        echo '</div>';
                    echo '</div>';
                }
            }
        ?>
        </div>
    </body>
</html>
