<!DOCTYPE html>
<html>
    <head>
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
            </div>
        </section>


        <div class="poi-container table-responsive-lg">
            <table class="table table-hover">
        <?php
            date_default_timezone_set('America/New_York');
            $current_day_of_week = (int) date("N") - 1; // 0 for Monday to 6 for Sunday
            $current_hour = (int) date("G");
            $current_minute = (int) date("i");
            $current_time = $current_hour + $current_minute / 60;

            $days_of_week = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');          
                        
            $json_file = file_get_contents('data/hours.json');
            $json_string = json_decode($json_file, true);
            foreach($json_string as $group) {
                echo '<thead><tr>';
                echo '<th>' . $group['groupName'] . '</th>';
                    for($cnt = 0; $cnt < 7; $cnt++) {
                        echo '<th>';
                            if($cnt == 0) {
                                echo 'Today';
                            } else if($cnt == 1){
                                echo 'Tomorrow';
                            } else {
                                echo $days_of_week[($cnt + $current_day_of_week) % 7];
                        }
                        echo '</th>';
                    }
                echo '</tr></thead>';
                echo '<tbody>';
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
                        $open_now_class = 'table-success';
                    }

                    echo '<tr class="' . $open_now_class . '">';
                        echo '<td>' . $location['locationName'] . '</td>';
                        for($cnt = 0; $cnt < 7; $cnt++) {
                            $day = $location['locationHours'][($cnt + $current_day_of_week) % 7];
                            echo '<td>';
                            if($day['open']) {
                                echo date("g:ia", mktime($day['startTime'][0], $day['startTime'][1]));
                                echo '-';
                                echo date("g:ia", mktime($day['stopTime'][0], $day['stopTime'][1]));
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
        <script src="js/search.js"></script>
    </body>
</html>
