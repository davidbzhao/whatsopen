<!DOCTYPE html>
<html>
    <head>
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
                echo "<div class='poi-name'>" . $poi->name . "</div>";
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
