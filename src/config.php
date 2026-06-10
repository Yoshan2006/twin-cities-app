<?php

// global exception handler to catch fatal errors gracefully
// this prevents the 'white screen of death' or leaking sensitive server paths
set_exception_handler(function ($exception) {
    // log the actual error for debugging
    error_log($exception->getMessage());

    // show a user-friendly error box
    echo "<div style='border: 1px solid red; padding: 15px; margin: 20px; background: #ffeeee; border-radius: 8px; font-family: sans-serif;'>
            <h3 style='color: red; margin-top: 0;'>System Error</h3>
            <p>Something went wrong on our end. Please try refreshing the page later.</p>
          </div>";
    exit();
});

// custom error handler to catch minor warnings silently
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    error_log("Minor Error [$errno]: $errstr in $errfile on line $errline");
    return true; 
});


// --- database configuration ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); 
define('DB_NAME', 'twin_cities_db');

// --- external api keys ---
// GITHUB PUSH PROTECTION: Real API keys removed for security. 
// Replace these with your actual keys when testing locally.
define('OPENWEATHER_API_KEY', 'YOUR_OPENWEATHER_API_KEY_HERE'); 
define('FLICKR_API_KEY', 'YOUR_FLICKR_API_KEY_HERE');


// lecturer requirement: DBMS config array
define('DBMS', [
    'HOST' => 'localhost',
    'DB' => 'twin_cities_db',
    'UN' => 'root',
    'PW' => ''
]);

// lecturer requirement: city data constants
define('LIVERPOOL', [
    'ID' => 1,
    'NAME' => 'Liverpool',
    'LAT' => 53.4084,
    'LNG' => -2.9916
]);

define('DUBLIN', [
    'ID' => 2,
    'NAME' => 'Dublin',
    'LAT' => 53.3498,
    'LNG' => -6.2603
]);


// --- establish secure pdo connection ---
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    
    // enable exceptions for database errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // fetch results as associative arrays by default
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    // throw to the global exception handler instead of using die()
    throw new Exception("DB connection failed: " . $e->getMessage());
}
?>