<?php
require_once 'config.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// prevent searching with very short strings to reduce server load
if (strlen($q) < 3) {
    exit;
}

// xml requirement: setup local file-based cache directory
$cache_dir = 'cache/';
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0777, true); 
}

// xml requirement: hash unique search queries using MD5 and cache for 60 seconds
$cache_file = $cache_dir . md5($q) . '.json';
$cache_time = 60; 

// check if a valid cache file exists and is less than 60 seconds old
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
    // load instantly from cache to reduce database load during peak usage
    $results = json_decode(file_get_contents($cache_file), true);
    $is_cached = true;
} else {
    // xml requirement: refactored query using INNER JOIN to pull landmark names
    $sql = "SELECT c.*, p.place_name 
            FROM comment c 
            INNER JOIN place_of_interest p ON c.place_id = p.place_id 
            WHERE p.place_name LIKE ? OR c.author_name LIKE ? OR c.comment_text LIKE ?
            ORDER BY c.comment_id DESC";
            
    $stmt = $pdo->prepare($sql);
    $searchTerm = "%{$q}%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // xml requirement: use file_put_contents and json_encode to store the new cache
    file_put_contents($cache_file, json_encode($results));
    $is_cached = false;
}

// format and send results back to the presentation tier (dark theme styling)
if (empty($results)) {
    echo "<div class='p-3 text-center' style='color: #64748b;'>No reviews found for '{$q}'</div>";
} else {
    // indicate to the user if the result was fetched from the md5 cache
    if ($is_cached) {
        echo "<div class='badge bg-success mb-2 w-100 text-center' style='opacity: 0.8;'>⚡ Loaded instantly from MD5 Cache</div>";
    }
    
    foreach ($results as $row) {
        // generate star rating display
        $stars = str_repeat('<i class="fa-solid fa-star text-warning"></i>', $row['rating']) . 
                 str_repeat('<i class="fa-regular fa-star text-secondary"></i>', 5 - $row['rating']);
        
        echo "
        <div class='p-3 mb-3' style='background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px;'>
            <div class='d-flex justify-content-between align-items-start mb-2'>
                <div>
                    <strong class='text-white'>{$row['author_name']}</strong><br>
                    <small style='color: #38bdf8;'><i class='fa-solid fa-location-dot me-1'></i> {$row['place_name']}</small>
                </div>
                <div>{$stars}</div>
            </div>
            <p class='m-0' style='color: #cbd5e1; font-size: 0.9rem;'>\"{$row['comment_text']}\"</p>
        </div>";
    }
}
?>