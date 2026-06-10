<?php
require_once 'config.php';

// check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $place_id = intval($_POST['place_id']);
    
    // xml requirement: sanitize user inputs using htmlspecialchars to prevent cross-site scripting (XSS) attacks
    $author = htmlspecialchars(trim($_POST['author_name']), ENT_QUOTES, 'UTF-8');
    $comment = htmlspecialchars(trim($_POST['comment_text']), ENT_QUOTES, 'UTF-8');
    
    // ensure the rating stays within the 1-5 star boundary
    $rating = intval($_POST['rating']);
    if ($rating < 1) $rating = 1;
    if ($rating > 5) $rating = 5;

    // xml requirement: use secure PDO prepared statements to block SQL injection attempts
    if (!empty($place_id) && !empty($author) && !empty($comment)) {
        $sql = "INSERT INTO comment (place_id, author_name, comment_text, rating) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$place_id, $author, $comment, $rating]);
    }
    
    // redirect back to the main dashboard after submitting the review
    header("Location: index.php");
    exit;
}
?>