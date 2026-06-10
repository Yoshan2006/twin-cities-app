<?php
require_once 'config.php';

// set the correct header so the browser reads this as an xml file, not html
header("Content-Type: text/xml; charset=UTF-8");
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0">
    <channel>
        <title>Twin &amp; Sister Cities Data Feed</title>
        <link>http://localhost/twin_cities/index.php</link>
        <description>Latest news, places of interest, and updates from <?php echo LIVERPOOL['NAME']; ?> and <?php echo DUBLIN['NAME']; ?>.</description>
        <language>en-us</language>

        <?php
        try {
            // fetch recent news articles with their respective city names
            $newsSql = "SELECT n.*, c.name as city_name 
                        FROM news n 
                        JOIN city c ON n.city_id = c.city_id 
                        ORDER BY n.publish_date DESC";
            $newsStmt = $pdo->query($newsSql);

            // xml requirement: use a php while-loop to wrap database records in well-formed xml tags
            while ($row = $newsStmt->fetch()) {
                // escape output to prevent xml parsing errors
                $title = htmlspecialchars($row['title'] . " (" . $row['city_name'] . ")", ENT_QUOTES, 'UTF-8');
                $desc = htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8');
                $link = "http://localhost/twin_cities/index.php"; 
                $pubDate = date(DATE_RSS, strtotime($row['publish_date']));
                $guid = "news-" . $row['news_id']; 
                
                echo "        <item>\n";
                echo "            <title>$title</title>\n";
                echo "            <description>$desc</description>\n";
                echo "            <link>$link</link>\n";
                echo "            <pubDate>$pubDate</pubDate>\n";
                echo "            <guid isPermaLink=\"false\">$guid</guid>\n";
                echo "            <category>City News</category>\n";
                echo "        </item>\n";
            }

            // fetch landmarks and places of interest to include in the feed
            $placesSql = "SELECT p.*, c.name as city_name 
                          FROM place_of_interest p 
                          JOIN city c ON p.city_id = c.city_id 
                          ORDER BY p.place_id DESC";
            $placesStmt = $pdo->query($placesSql);

            // iterate through places and output as rss items
            while ($row = $placesStmt->fetch()) {
                $title = htmlspecialchars($row['place_name'] . " - " . $row['type'] . " (" . $row['city_name'] . ")", ENT_QUOTES, 'UTF-8');
                $desc = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
                $link = "http://localhost/twin_cities/place_details.php?id=" . $row['place_id']; 
                
                // fallback date since places don't have a specific publish timestamp
                $pubDate = date(DATE_RSS); 
                $guid = "place-" . $row['place_id']; 
                
                echo "        <item>\n";
                echo "            <title>$title</title>\n";
                echo "            <description>$desc</description>\n";
                echo "            <link>$link</link>\n";
                echo "            <pubDate>$pubDate</pubDate>\n";
                echo "            <guid isPermaLink=\"false\">$guid</guid>\n";
                echo "            <category>Places of Interest</category>\n";
                echo "        </item>\n";
            }

        } catch(PDOException $e) {
            // gracefully handle database errors in the feed without breaking the xml structure
            $error_msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            echo "        <item>\n";
            echo "            <title>System Error</title>\n";
            echo "            <description>Unable to fetch data at this time.</description>\n";
            echo "        </item>\n";
        }
        ?>
    </channel>
</rss>