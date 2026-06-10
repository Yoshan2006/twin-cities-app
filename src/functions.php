<?php
require_once 'config.php';

// fetch current weather from openweather and save it to the database for history
function get_weather_data($city_name, $city_id) {
    global $pdo; 
    
    $api_key = OPENWEATHER_API_KEY; 
    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city_name) . "&appid=" . $api_key . "&units=metric";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // bypass ssl errors on local server
    $response = curl_exec($ch);
    curl_close($ch);

    $weather_data = json_decode($response, true);

    // if api call is successful, insert the details into our database securely
    if ($weather_data && $weather_data['cod'] == 200) {
        try {
            $temp = $weather_data['main']['temp'];
            $desc = $weather_data['weather'][0]['description'];
            $humidity = $weather_data['main']['humidity'];
            $wind = $weather_data['wind']['speed'];

            if ($pdo) {
                $sql = "INSERT INTO weather (city_id, temperature, description, humidity, wind_speed) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$city_id, $temp, $desc, $humidity, $wind]);
            }
        } catch(PDOException $e) {
            error_log("failed to save weather history: " . $e->getMessage());
        }
    }

    return $weather_data;
}

// get the 3-day weather forecast to display on the dashboard cards
function get_weather_forecast($city_name) {
    $api_key = OPENWEATHER_API_KEY; 
    $url = "https://api.openweathermap.org/data/2.5/forecast?q=" . urlencode($city_name) . "&appid=" . $api_key . "&units=metric";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $forecast = [];

    // the api sends data every 3 hours. we just need one record per day (around noon) for the next 3 days
    if ($data && $data['cod'] == "200") {
        $count = 0;
        foreach ($data['list'] as $item) {
            if (strpos($item['dt_txt'], '12:00:00') !== false) {
                $forecast[] = $item;
                $count++;
                if ($count == 3) break; // stop loop after grabbing 3 days
            }
        }
    }
    return $forecast;
}

// fetch photos from flickr public feed (bypassing auth key limits)
function get_flickr_photos($city_name, $count = 20) {
    $url = "https://www.flickr.com/services/feeds/photos_public.gne?tags=" . urlencode($city_name) . ",landmark&format=json&nojsoncallback=1";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $photos = [];

    if (isset($data['items'])) {
        $i = 0;
        foreach ($data['items'] as $item) {
            if ($i >= $count) break; 
            
            // xml requirement: replace _m.jpg with _b.jpg to get high resolution images for the masonry layout
            $small_photo_url = $item['media']['m'];
            $high_res_photo_url = str_replace('_m.', '_b.', $small_photo_url);

            $photos[] = [
                'url' => $high_res_photo_url,
                'title' => $item['title']
            ];
            $i++;
        }
    }
    return $photos;
}
?>d