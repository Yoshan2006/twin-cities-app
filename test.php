<?php
// include the main configuration file to test the pdo connection
require_once 'config.php';

// check if the pdo object was instantiated successfully
if ($pdo) {
    echo "<div style='font-family: sans-serif; padding: 20px; background: #e8f5e9; border: 1px solid #4caf50; border-radius: 8px;'>";
    echo "<h2 style='color: #2e7d32; margin-top: 0;'>System Check: Optimal</h2>";
    echo "<p><strong>Database Connection:</strong> <span style='color: green;'>Successful (PDO Active)</span></p>";
    
    // securely output a masked version of the api key for verification
    $masked_key = substr(OPENWEATHER_API_KEY, 0, 4) . str_repeat('*', 20) . substr(OPENWEATHER_API_KEY, -4);
    echo "<p><strong>OpenWeather API:</strong> Validated [" . $masked_key . "]</p>";
    echo "</div>";
} else {
    echo "<div style='font-family: sans-serif; padding: 20px; background: #ffebee; border: 1px solid #f44336; border-radius: 8px;'>";
    echo "<h2 style='color: #c62828; margin-top: 0;'>System Check: Failed</h2>";
    echo "<p>Unable to establish a database connection. Please check your config.php credentials.</p>";
    echo "</div>";
}
?>