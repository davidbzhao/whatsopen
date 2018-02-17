<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>What's Open</title>
        <link rel="stylesheet" href="css/styles.css">
    </head>
    <body>
        <?php
            class POI {
                public $name;
                public $hours;
            }

            function decode_time_string($time, $is_end = False) {
                $ampm = substr($time, -2);
                $time_digits = substr($time, 0, strlen($time) - 2);
                $colon_pos = strrpos($time_digits, ":");
                $time_hours = -1;
                $time_minutes -1;

                if ($colon_pos === false) {
                    $time_hours = (int) $time_digits;
                    $time_minutes = 0;
                } else {
                    $time_hours = (int) substr($time_digits, 0, $colon_pos);
                    $time_minutes = (int) substr($time_digits, $colon_pos + 1);
                }

                if ($time_hours == 12 && $ampm == "AM") {
                    $time_hours = 0;
                } else if ($time_hours != 12 && $ampm == "PM") {
                    $time_hours += 12;
                }
                if ($ampm == "AM" && $is_end) { 
                    $time_hours += 24;
                }
                return array($time_hours, $time_minutes);
            }

            date_default_timezone_set('America/New_York');
            $day_of_week = (int) date("N");  // 1 for Monday to 7 for Sunday
            $current_hour = (int) date("G");
            $current_minute = (int) date("i");
            $current_time = $current_hour + $current_minute / 60;

            $pois = array();
            if (($handle = fopen("data/hours.csv", "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $cols = count($data);
                    $poi = new POI();
                    $poi->name = $data[0];
                    
                    $cur_hours_arr = array();
                    for ($c = 1; $c < $cols; $c++) {
                        $times = explode("-", $data[$c]);
                        if (count($times) != 2) {
                            continue;
                        }
                        
                        $open_time_arr = decode_time_string($times[0]);
                        $close_time_arr = decode_time_string($times[1], True);
                        $hours = [
                            "open_time_hours" => $open_time_arr[0],
                            "open_time_minutes" => $open_time_arr[1],
                            "close_time_hours" => $close_time_arr[0],
                            "close_time_minutes" => $close_time_arr[1]
                        ];

                        array_push($cur_hours_arr, $hours);
                    } 
                    if (count($cur_hours_arr) == 0) {
                        continue;
                    }
                    $poi->hours = $cur_hours_arr;
                    array_push($pois, $poi);
                }
            }
            fclose($handle);

            $num_pois = count($pois);
            foreach ($pois as $poi) {
                $poi_open = "";
                $hours_today = $poi->hours[$day_of_week - 1];
                $hours_yesterday = $poi->hours[($day_of_week - 2 + 7) % 7];
                $open_time_today = $hours_today["open_time_hours"] + $hours_today["open_time_minutes"] / 60;
                $close_time_today = $hours_today["close_time_hours"] + $hours_today["close_time_minutes"] / 60;
                $open_time_yesterday = $hours_yesterday["open_time_hours"] + $hours_yesterday["open_time_minutes"] / 60;
                $close_time_yesterday = $hours_yesterday["open_time_hours"] + $hours_yesterday["close_time_minutes"] / 60;
                if ($current_time > $open_time_today && $current_time < $close_time_today) {
                    $poi_open = "poi-open";
                } else if ($current_time + 24 > $open_time_yesterday && $current_time + 24 < $close_time_yesterday) {
                    $poi_open = "poi-open";
                }
                echo "<div class='poi-name " . $poi_open . "'>" . $poi->name . "</div>";
                echo "<div class='poi-hours-container'>";
                foreach ($poi->hours as $cur_hours) {
                    echo "<div class='poi-hours'>";
                    echo $cur_hours["open_time_hours"] . ":" . $cur_hours["open_time_minutes"];
                    echo "->";
                    echo $cur_hours["close_time_hours"] . ":" . $cur_hours["close_time_minutes"];
                    echo "</div>";
                }
                echo "</div>";
            }
        ?>
        </body>
</html>
