<?php
require_once 'config.php';

// set response header to json since this acts as an api endpoint for the frontend
header('Content-Type: application/json');

try {
    // fetch all places from the database
    $stmt = $pdo->query("SELECT * FROM place_of_interest");
    $places = $stmt->fetchAll();
    
    // output the result as a json string
    echo json_encode($places);
    
} catch(PDOException $e) {
    // return errors in json format to prevent breaking frontend javascript
    echo json_encode(['error' => $e->getMessage()]);
}
?>