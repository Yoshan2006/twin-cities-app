<?php
require_once 'config.php';
require_once 'functions.php';

// fetch city details from the database
$stmtLiv = $pdo->prepare("SELECT * FROM city WHERE city_id = ?");
$stmtLiv->execute([LIVERPOOL['ID']]);
$livCity = $stmtLiv->fetch();

$stmtDub = $pdo->prepare("SELECT * FROM city WHERE city_id = ?");
$stmtDub->execute([DUBLIN['ID']]);
$dubCity = $stmtDub->fetch();

// fetch current weather and 3-day forecast
$livWeather = get_weather_data(LIVERPOOL['NAME'], LIVERPOOL['ID']);
$dubWeather = get_weather_data(DUBLIN['NAME'], DUBLIN['ID']);
$livForecast = get_weather_forecast(LIVERPOOL['NAME']);
$dubForecast = get_weather_forecast(DUBLIN['NAME']);

// fetch all places of interest and filter them by city
$placesStmt = $pdo->query("SELECT * FROM place_of_interest");
$allPlaces = $placesStmt->fetchAll();
$livPlaces = array_filter($allPlaces, function($p) { return $p['city_id'] == LIVERPOOL['ID']; });
$dubPlaces = array_filter($allPlaces, function($p) { return $p['city_id'] == DUBLIN['ID']; });
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twin Cities | Dark Premium</title>
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
            background-image: 
                radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.1) 0px, transparent 40%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.1) 0px, transparent 40%);
            color: var(--text-main);
            min-height: 100vh;
        }
        
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 28px;
            transition: all 0.4s ease;
            height: 100%;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            border-color: rgba(56, 189, 248, 0.4);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5), 0 0 15px rgba(56, 189, 248, 0.15);
        }

        .text-readable { color: var(--text-readable) !important; font-weight: 400; font-size: 0.95rem; line-height: 1.6; }
        .text-gradient { background: linear-gradient(135deg, #38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800; }
        
        .hero-section { padding: 60px 0 40px 0; text-align: center; }
        
        .data-pill {
            background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.15);
            color: #e2e8f0; padding: 8px 16px; border-radius: 8px; font-weight: 500; font-size: 0.9rem;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .data-pill i { color: var(--accent-glow); }
        
        .forecast-grid { display: flex; justify-content: space-between; gap: 12px; margin-top: 25px; }
        .forecast-card { background: rgba(15, 23, 42, 0.5); border-radius: 12px; padding: 15px 10px; text-align: center; flex: 1; border: 1px solid rgba(255, 255, 255, 0.08); }
        .forecast-date { font-size: 0.85rem; color: #94a3b8; font-weight: 500; }
        .forecast-temp { font-size: 1.2rem; font-weight: 600; color: #fff; margin: 5px 0; }
        
        .map-container { height: 380px; width: 100%; border-radius: 20px; z-index: 1; border: 1px solid rgba(255,255,255,0.1); }
        .places-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; }
        
        .btn-glow {
            background: linear-gradient(135deg, #0284c7, #4f46e5); color: white; border: none; border-radius: 8px;
            padding: 12px 24px; font-weight: 600; transition: all 0.3s; text-decoration: none; display: inline-block; letter-spacing: 0.5px;
        }
        .btn-glow:hover { box-shadow: 0 0 20px rgba(56, 189, 248, 0.4); color: white; transform: scale(1.02); }
        
        .leaflet-popup-content-wrapper { background: #1e293b; color: #f8fafc; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); }
        .leaflet-popup-tip { background: #1e293b; }

        .top-nav { padding: 25px 0; display: flex; justify-content: space-between; align-items: center; }
        .btn-rss {
            background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 50px; padding: 10px 24px; font-weight: 600; transition: all 0.3s; text-decoration: none; display: flex; align-items: center; gap: 10px;
        }
        .btn-rss:hover { background: rgba(245, 158, 11, 0.2); color: #fcd34d; box-shadow: 0 0 20px rgba(245, 158, 11, 0.4); transform: translateY(-2px); }

        .dark-input {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #f8fafc !important;
        }
        .dark-input:focus {
            background: rgba(15, 23, 42, 0.9) !important;
            border-color: var(--accent-glow) !important;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.2) !important;
        }
        .dark-input::placeholder { color: #64748b !important; }

        /* custom dark theme for leaflet tooltips */
        .leaflet-tooltip { background: #0f172a !important; color: #f8fafc !important; border: 1px solid rgba(255,255,255,0.1) !important; font-family: 'Outfit', sans-serif; font-weight: 600; border-radius: 6px; }
        .leaflet-tooltip-top:before { border-top-color: #0f172a !important; }
    </style>
</head>
<body>

<div class="container top-nav">
    <div class="fw-bold fs-4 text-white">
        <i class="fa-solid fa-earth-americas text-gradient me-2"></i>TwinCities
    </div>
    
    <div class="d-flex gap-3">
        <a href="gallery.php" class="btn-rss" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8; border-color: rgba(56, 189, 248, 0.3);">
            <i class="fa-solid fa-images"></i> Photo Gallery
        </a>

        <a href="rss.php" target="_blank" class="btn-rss">
            <i class="fa-solid fa-rss"></i> Live RSS Feed
        </a>
    </div>
</div>

<div class="hero-section container">
    <h1 class="display-4 mb-3"><span class="text-gradient">Discover Dimensions:</span> Twin Cities</h1>
    <p class="text-readable w-75 mx-auto" style="font-size: 1.1rem;">An advanced topological and meteorological analysis mapping <?php echo LIVERPOOL['NAME']; ?> and <?php echo DUBLIN['NAME']; ?> in real-time.</p>
</div>

<div class="container mb-5">
    
    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold m-0 text-white"><?php echo $livCity['name']; ?></h2>
                    <span class="badge border border-info text-info px-3 py-2 bg-transparent"><?php echo $livCity['country']; ?></span>
                </div>
                <div class="d-flex flex-wrap gap-3">
                    <div class="data-pill"><i class="fa-solid fa-users"></i> <?php echo number_format($livCity['population']); ?> Residents</div>
                    <div class="data-pill"><i class="fa-solid fa-coins"></i> <?php echo $livCity['currency']; ?></div>
                    <div class="data-pill"><i class="fa-regular fa-clock"></i> <?php echo $livCity['timezone']; ?></div>
                    <div class="data-pill"><i class="fa-solid fa-satellite"></i> <?php echo $livCity['latitude'] . ", " . $livCity['longitude']; ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold m-0 text-white"><?php echo $dubCity['name']; ?></h2>
                    <span class="badge border border-info text-info px-3 py-2 bg-transparent"><?php echo $dubCity['country']; ?></span>
                </div>
                <div class="d-flex flex-wrap gap-3">
                    <div class="data-pill"><i class="fa-solid fa-users"></i> <?php echo number_format($dubCity['population']); ?> Residents</div>
                    <div class="data-pill"><i class="fa-solid fa-coins"></i> <?php echo $dubCity['currency']; ?></div>
                    <div class="data-pill"><i class="fa-regular fa-clock"></i> <?php echo $dubCity['timezone']; ?></div>
                    <div class="data-pill"><i class="fa-solid fa-satellite"></i> <?php echo $dubCity['latitude'] . ", " . $dubCity['longitude']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="glass-card">
                <h4 class="fw-bold text-gradient mb-4">Atmospheric Conditions</h4>
                <div class="d-flex align-items-center gap-4">
                    <?php if($livWeather): ?>
                        <h1 class="display-3 fw-bold m-0"><?php echo round($livWeather['main']['temp']); ?>°</h1>
                        <div>
                            <h5 class="m-0 text-capitalize text-white"><?php echo $livWeather['weather'][0]['description']; ?></h5>
                            <span class="text-readable"><i class="fa-solid fa-wind me-1"></i><?php echo $livWeather['wind']['speed']; ?> m/s | <i class="fa-solid fa-droplet me-1"></i><?php echo $livWeather['main']['humidity']; ?>%</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if(!empty($livForecast)): ?>
                <div class="forecast-grid">
                    <?php foreach($livForecast as $f): ?>
                        <div class="forecast-card">
                            <div class="forecast-date"><?php echo date('D, M d', strtotime($f['dt_txt'])); ?></div>
                            <img src="http://openweathermap.org/img/wn/<?php echo $f['weather'][0]['icon']; ?>.png" width="40" alt="icon">
                            <div class="forecast-temp"><?php echo round($f['main']['temp']); ?>°C</div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="glass-card">
                <h4 class="fw-bold text-gradient mb-4">Atmospheric Conditions</h4>
                <div class="d-flex align-items-center gap-4">
                    <?php if($dubWeather): ?>
                        <h1 class="display-3 fw-bold m-0"><?php echo round($dubWeather['main']['temp']); ?>°</h1>
                        <div>
                            <h5 class="m-0 text-capitalize text-white"><?php echo $dubWeather['weather'][0]['description']; ?></h5>
                            <span class="text-readable"><i class="fa-solid fa-wind me-1"></i><?php echo $dubWeather['wind']['speed']; ?> m/s | <i class="fa-solid fa-droplet me-1"></i><?php echo $dubWeather['main']['humidity']; ?>%</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if(!empty($dubForecast)): ?>
                <div class="forecast-grid">
                    <?php foreach($dubForecast as $f): ?>
                        <div class="forecast-card">
                            <div class="forecast-date"><?php echo date('D, M d', strtotime($f['dt_txt'])); ?></div>
                            <img src="http://openweathermap.org/img/wn/<?php echo $f['weather'][0]['icon']; ?>.png" width="40" alt="icon">
                            <div class="forecast-temp"><?php echo round($f['main']['temp']); ?>°C</div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <h3 class="fw-bold text-white mb-4">Geospatial Mapping</h3>
    <div class="row g-4 mb-5">
        <div class="col-lg-6"><div id="map-liverpool" class="map-container shadow-lg"></div></div>
        <div class="col-lg-6"><div id="map-dublin" class="map-container shadow-lg"></div></div>
    </div>

    <h3 class="fw-bold text-white mb-4"><?php echo $livCity['name']; ?> Coordinates</h3>
    <div class="places-grid mb-5">
        <?php foreach($livPlaces as $place): ?>
            <div class="glass-card d-flex flex-column">
                <h4 class="fw-bold text-white mb-2"><?php echo htmlspecialchars($place['place_name']); ?></h4>
                <p class="text-info mb-3 text-uppercase" style="font-size: 0.8rem; font-weight: 600; letter-spacing: 1px;"><i class="fa-solid fa-tag me-2"></i><?php echo htmlspecialchars($place['type']); ?></p>
                <p class="text-readable mb-4 flex-grow-1"><?php echo htmlspecialchars($place['description']); ?></p>
                <a href="place_details.php?id=<?php echo $place['place_id']; ?>" class="btn-glow text-center">Analyze Sector <i class="fa-solid fa-radar mx-1"></i></a>
            </div>
        <?php endforeach; ?>
    </div>

    <h3 class="fw-bold text-white mb-4"><?php echo $dubCity['name']; ?> Coordinates</h3>
    <div class="places-grid mb-5">
        <?php foreach($dubPlaces as $place): ?>
            <div class="glass-card d-flex flex-column">
                <h4 class="fw-bold text-white mb-2"><?php echo htmlspecialchars($place['place_name']); ?></h4>
                <p class="text-info mb-3 text-uppercase" style="font-size: 0.8rem; font-weight: 600; letter-spacing: 1px;"><i class="fa-solid fa-tag me-2"></i><?php echo htmlspecialchars($place['type']); ?></p>
                <p class="text-readable mb-4 flex-grow-1"><?php echo htmlspecialchars($place['description']); ?></p>
                <a href="place_details.php?id=<?php echo $place['place_id']; ?>" class="btn-glow text-center">Analyze Sector <i class="fa-solid fa-radar mx-1"></i></a>
            </div>
        <?php endforeach; ?>
    </div>

    <h3 class="fw-bold text-white mb-4 mt-5">Community & Reviews</h3>
    <div class="row g-4 mb-5">
        
        <div class="col-lg-5">
            <div class="glass-card">
                <h4 class="fw-bold text-white mb-3"><i class="fa-solid fa-magnifying-glass me-2 text-info"></i>Search Reviews</h4>
                <p class="text-readable small mb-4">Find reviews by typing a city (e.g., Liverpool) or a place name.</p>
                <input type="text" id="searchInput" class="form-control dark-input mb-4" placeholder="Type here to search...">
                
                <div id="searchResults" style="max-height: 350px; overflow-y: auto; padding-right: 10px;"></div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="glass-card">
                <h4 class="fw-bold text-white mb-4"><i class="fa-regular fa-comments me-2 text-info"></i>Share Your Experience</h4>
                <form action="save_comment.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-readable mb-1">Select Place</label>
                            <select name="place_id" class="form-select dark-input" required>
                                <option value="" disabled selected>-- Choose a location --</option>
                                <?php foreach($allPlaces as $p): ?>
                                    <option value="<?php echo $p['place_id']; ?>">
                                        <?php echo htmlspecialchars($p['place_name']); ?> 
                                        (<?php echo $p['city_id'] == LIVERPOOL['ID'] ? 'Liverpool' : 'Dublin'; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="text-readable mb-1">Your Name</label>
                            <input type="text" name="author_name" class="form-control dark-input" required placeholder="John Doe">
                        </div>
                        <div class="col-12">
                            <label class="text-readable mb-1">Your Review</label>
                            <textarea name="comment_text" class="form-control dark-input" rows="3" required placeholder="Write your thoughts here..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="text-readable mb-1">Rating (1 to 5 Stars)</label>
                            <input type="number" name="rating" class="form-control dark-input" min="1" max="5" value="5" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn-glow w-100"><i class="fa-solid fa-paper-plane me-2"></i>Post Comment</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-card" style="border-color: rgba(56, 189, 248, 0.4);">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white fw-bold" id="modalPlaceName">Place Detail</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid rounded-3 mb-4 shadow-lg" style="max-height: 450px; width: 100%; object-fit: cover;">
                <p id="modalDesc" class="text-readable px-3" style="font-size: 1.05rem;"></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // configure dark themed map tiles
    var mapStyle = 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
    var mapAttrib = '&copy; <a href="https://carto.com/">CARTO</a>';

    // initialize maps
    var mapL = L.map('map-liverpool').setView([<?php echo LIVERPOOL['LAT']; ?>, <?php echo LIVERPOOL['LNG']; ?>], 12);
    L.tileLayer(mapStyle, { attribution: mapAttrib, subdomains: 'abcd', maxZoom: 20 }).addTo(mapL);

    var mapD = L.map('map-dublin').setView([<?php echo DUBLIN['LAT']; ?>, <?php echo DUBLIN['LNG']; ?>], 12);
    L.tileLayer(mapStyle, { attribution: mapAttrib, subdomains: 'abcd', maxZoom: 20 }).addTo(mapD);

    var places = <?php echo json_encode($allPlaces); ?>;
    
    // xml requirement: implement a client-side cache array to minimize redundant api calls
    var photoCache = {}; 

    // plot markers for all places
    places.forEach(function(place) {
        var marker;
        if (place.city_id == <?php echo LIVERPOOL['ID']; ?>) {
            marker = L.marker([place.latitude, place.longitude]).addTo(mapL);
        } else {
            marker = L.marker([place.latitude, place.longitude]).addTo(mapD);
        }
        
        // display place name on hover
        marker.bindTooltip(place.place_name, {direction: 'top', offset: [0, -10]});
        
        // popup html template with loading spinner
        var popupHTML = `
            <div style="width: 220px; text-align: center; font-family: 'Outfit', sans-serif;">
                <h6 style="color: #38bdf8; font-weight: 700; margin-bottom: 5px; font-size: 16px;">${place.place_name}</h6>
                <span class="badge" style="background: rgba(255,255,255,0.1); color: #cbd5e1; font-size: 0.75rem; margin-bottom: 10px;">${place.type}</span>
                
                <div id="img-box-${place.place_id}" style="height: 130px; background: #0b1120; border-radius: 8px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
                    <i class="fa-solid fa-spinner fa-spin" style="color: #38bdf8; font-size: 24px;"></i>
                </div>
                
                <a href="place_details.php?id=${place.place_id}" style="display: block; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); font-weight: 600; border-radius: 6px; padding: 8px; text-decoration: none;">Full Details <i class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>
        `;
        
        marker.bindPopup(popupHTML, {className: 'leaflet-popup-content-wrapper'});

        // handle asynchronous photo fetching when popup opens
        marker.on('popupopen', function() {
            var imgBox = document.getElementById('img-box-' + place.place_id);
            var cacheKey = place.place_name;

            function updateUI(imgUrl, desc) {
                imgBox.innerHTML = `<img src="${imgUrl}" style="width:100%; height:100%; object-fit:cover; border-radius: 8px; cursor: pointer;" onclick="openPhotoModal('${place.place_name}', '${imgUrl}', '${desc.replace(/'/g, "\\'")}')">`;
            }

            // load from cache if available, otherwise fetch from api proxy
            if (photoCache[cacheKey]) {
                updateUI(photoCache[cacheKey].image, photoCache[cacheKey].desc);
            } else {
                fetch('get_place_photo.php?place=' + encodeURIComponent(place.place_name))
                    .then(response => response.json())
                    .then(data => {
                        if(data.image) {
                            photoCache[cacheKey] = { image: data.image, desc: data.desc }; 
                            updateUI(data.image, data.desc);
                        }
                    })
                    .catch(err => {
                        imgBox.innerHTML = `<span style="color:#ef4444; font-size:0.8rem;">Image Load Failed</span>`;
                    });
            }
        });
    });

    // function to trigger the fullscreen image modal
    function openPhotoModal(name, img, desc) {
        document.getElementById('modalPlaceName').innerText = name;
        document.getElementById('modalImage').src = img;
        document.getElementById('modalDesc').innerText = desc;
        var myModal = new bootstrap.Modal(document.getElementById('photoModal'));
        myModal.show();
    }

    // handle asynchronous live search for user reviews
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let query = this.value;
        if(query.length > 2) {
            fetch('search_comments.php?q=' + query)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('searchResults').innerHTML = data;
                });
        } else {
            // clear results if query is too short
            document.getElementById('searchResults').innerHTML = ''; 
        }
    });
</script>

</body>
</html>