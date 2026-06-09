<?php
require_once 'config.php';
require_once 'functions.php';

// retrieve and validate the place ID from the URL parameters
$place_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($place_id <= 0) {
    header("Location: index.php");
    exit;
}

// perform an INNER JOIN to fetch comprehensive place and associated city data
$sql = "SELECT p.*, c.name as city_name, c.country 
        FROM place_of_interest p 
        JOIN city c ON p.city_id = c.city_id 
        WHERE p.place_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$place_id]);
$place = $stmt->fetch();

if (!$place) {
    echo "Sector data not found.";
    exit;
}

// xml requirement: fetch high-res contextual photos from flickr
// to improve accuracy, we concatenate the place name with the host city name
$search_query = $place['place_name'] . " " . $place['city_name'];
$photos = get_flickr_photos($search_query, 12);

// fallback mechanism: if no specific photos are found, display general city photos
if (empty($photos)) {
    $photos = get_flickr_photos($place['city_name'], 12);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $place['place_name']; ?> | Detailed Analysis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0b1120;
            --bg-card: rgba(30, 41, 59, 0.7);
            --text-main: #f8fafc;
            --text-readable: #cbd5e1;
            --accent-glow: #38bdf8;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            background-image: radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.1) 0px, transparent 40%);
            color: var(--text-main);
            min-height: 100vh;
            padding-bottom: 50px;
        }
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 30px;
            height: 100%;
        }
        .text-gradient {
            background: linear-gradient(135deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }
        .data-label { color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
        .data-value { color: #fff; font-size: 1.1rem; font-weight: 500; }
        
        #map { height: 350px; width: 100%; border-radius: 15px; border: 1px solid rgba(255,255,255,0.1); }
        
        /* xml requirement: responsive css masonry-style layout for dynamic visual feeds */
        .photo-grid { column-count: 3; column-gap: 15px; }
        @media (max-width: 768px) { .photo-grid { column-count: 2; } }
        .photo-item { 
            break-inside: avoid; margin-bottom: 15px; border-radius: 10px; overflow: hidden; 
            border: 1px solid rgba(255,255,255,0.1); transition: transform 0.3s;
        }
        .photo-item:hover { transform: scale(1.03); border-color: var(--accent-glow); }
        .photo-item img { width: 100%; display: block; }

        .btn-back {
            background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s;
        }
        .btn-back:hover { background: rgba(56, 189, 248, 0.2); box-shadow: 0 0 15px rgba(56, 189, 248, 0.3); color: #fff; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="fw-bold m-0"><span class="text-gradient">Sector Analysis:</span> <?php echo $place['place_name']; ?></h1>
        <a href="index.php" class="btn-back"><i class="fa-solid fa-arrow-left me-2"></i>Dashboard</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="glass-card">
                <h4 class="fw-bold mb-4 text-white">Geospatial & Structural Data</h4>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="data-label">Category</div>
                        <div class="data-value"><?php echo $place['type']; ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="data-label">Host City</div>
                        <div class="data-value"><?php echo $place['city_name']; ?>, <?php echo $place['country']; ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="data-label">Max Capacity</div>
                        <div class="data-value"><?php echo number_format($place['capacity']); ?> Units</div>
                    </div>
                    <div class="col-12">
                        <div class="data-label">Field Intelligence / Description</div>
                        <p class="text-readable mt-2"><?php echo $place['description']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <div class="data-label">Coordinates</div>
                        <div class="data-value text-info"><?php echo $place['latitude']; ?>, <?php echo $place['longitude']; ?></div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="<?php echo $place['website_link']; ?>" target="_blank" class="btn btn-outline-info rounded-pill px-4 mt-2">External Uplink <i class="fa-solid fa-up-right-from-square ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="glass-card">
                <h4 class="fw-bold mb-4 text-white">Localization Map</h4>
                <div id="map"></div>
            </div>
        </div>

        <div class="col-12 mt-4">
            <div class="glass-card">
                <h4 class="fw-bold mb-4 text-white"><i class="fa-solid fa-camera-retro me-2 text-info"></i>Live Visual Reconnaissance</h4>
                <?php if (empty($photos)): ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-image-slash mb-3" style="font-size: 3rem; color: #334155;"></i>
                        <p class="text-readable">No live visual data streams available for this sector.</p>
                    </div>
                <?php else: ?>
                    <div class="photo-grid">
                        <?php foreach ($photos as $p): ?>
                            <div class="photo-item">
                                <img src="<?php echo $p['url']; ?>" alt="Live Stream" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // initialize the tactical map with dark tiles to match the UI theme
    var map = L.map('map').setView([<?php echo $place['latitude']; ?>, <?php echo $place['longitude']; ?>], 15);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; CARTO'
    }).addTo(map);

    // drop a marker at the exact sector coordinates
    L.marker([<?php echo $place['latitude']; ?>, <?php echo $place['longitude']; ?>])
        .addTo(map)
        .bindPopup("<b><?php echo $place['place_name']; ?></b><br>Sector Target Established.")
        .openPopup();
</script>

</body>
</html>