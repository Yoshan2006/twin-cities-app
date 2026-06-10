<?php
// Output JSON as required by the client-side Fetch API
header('Content-Type: application/json');

// Check if a place name was provided in the GET request
if (!isset($_GET['place'])) {
    echo json_encode(['error' => 'No place specified', 'image' => '', 'desc' => '']);
    exit;
}

// Sanitize and format the input for the URL
$place_name = trim($_GET['place']);
$safe_name = urlencode($place_name);

// --- 1. SERVER-SIDE CACHING LOGIC ---
$cache_dir = 'cache/';
// Generate a unique MD5 hash filename for this specific place
$cache_file = $cache_dir . md5($place_name) . '_wiki.json';
// Set cache expiration time (3600 seconds = 1 hour)
$cache_time = 3600; 

// Check if a valid cache file already exists
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
    // If valid cache exists, bypass the API call and return the cached data immediately
    echo file_get_contents($cache_file);
    exit;
}


// --- 2. WIKIPEDIA API INTEGRATION ---
// Use 'generator=search' for fuzzy searching to fix naming discrepancies
$url = "https://en.wikipedia.org/w/api.php?action=query&generator=search&gsrsearch={$safe_name}&gsrlimit=1&prop=pageimages|extracts&pithumbsize=800&exintro&explaintext&exsentences=2&format=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// Wikipedia API strictly requires a valid User-Agent header
curl_setopt($ch, CURLOPT_USERAGENT, 'TwinCitiesApp/1.0 (University Project)'); 
// Bypass SSL verification for local server environments
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

// Parse the JSON response from Wikipedia
$data = json_decode($response, true);
$imageUrl = '';
$description = 'No additional information available from Wikipedia.';

// Extract the required data from the complex Wikipedia JSON structure
if (isset($data['query']['pages'])) {
    $pages = $data['query']['pages'];
    $firstPage = reset($pages); // Grab the top search result
    
    // Extract the thumbnail image URL
    if (isset($firstPage['thumbnail']['source'])) {
        $imageUrl = $firstPage['thumbnail']['source'];
    }
    // Extract the short text description
    if (isset($firstPage['extract'])) {
        $description = $firstPage['extract'];
    }
}

// Fallback placeholder matching the UI dark theme if Wikipedia doesn't have an image
if (empty($imageUrl)) {
    $imageUrl = 'https://via.placeholder.com/800x600/0f172a/38bdf8?text=Image+Not+Found';
}

// Prepare the final output array matching the frontend requirements
$final_output = json_encode([
    'image' => $imageUrl,
    'desc' => $description
]);


// --- 3. SAVE DATA TO CACHE ---
// Create the cache directory if it does not exist
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0777, true); 
}
// Save the newly fetched JSON data into the cache file for future requests
file_put_contents($cache_file, $final_output);

// Output the final JSON data to the frontend
echo $final_output;
?>