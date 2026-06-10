<?php
require_once 'config.php';
require_once 'functions.php';

// fetch 20 photos for each city to display in the gallery
// note: the high-res image conversion (_m to _b) is handled inside functions.php
$liverpool_photos = get_flickr_photos(LIVERPOOL['NAME'], 20);
$dublin_photos = get_flickr_photos(DUBLIN['NAME'], 20);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twin Cities | Photo Gallery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0b1120;
            --bg-card: rgba(30, 41, 59, 0.7);
            --text-main: #f8fafc;
            --text-readable: #cbd5e1;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            background-image: radial-gradient(at 50% 0%, rgba(56, 189, 248, 0.1) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            padding-bottom: 50px;
        }
        
        .top-nav { padding: 25px 0; display: flex; justify-content: space-between; align-items: center; }
        .text-gradient { background: linear-gradient(135deg, #38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800; }
        .btn-back { background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 50px; padding: 10px 24px; font-weight: 600; text-decoration: none; transition: all 0.3s; }
        .btn-back:hover { background: rgba(56, 189, 248, 0.2); color: #fff; box-shadow: 0 0 15px rgba(56, 189, 248, 0.4); transform: translateY(-2px); }

        .glass-card { background: var(--bg-card); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 20px; padding: 30px; margin-bottom: 40px; }

        /* responsive css masonry layout for the photo grid */
        .gallery-container { column-count: 3; column-gap: 20px; }
        @media (max-width: 992px) { .gallery-container { column-count: 2; } }
        @media (max-width: 576px) { .gallery-container { column-count: 1; } }

        .gallery-item { break-inside: avoid; margin-bottom: 20px; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); transition: all 0.4s ease; background: #000; cursor: zoom-in; }
        .gallery-item:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5), 0 0 15px rgba(56, 189, 248, 0.3); border-color: rgba(56, 189, 248, 0.5); }
        .gallery-img { width: 100%; height: auto; display: block; opacity: 0.9; transition: opacity 0.3s; }
        .gallery-item:hover .gallery-img { opacity: 1; }
    </style>
</head>
<body>

<div class="container top-nav">
    <div class="fw-bold fs-4 text-white">
        <i class="fa-solid fa-earth-americas text-gradient me-2"></i>TwinCities
    </div>
    <a href="index.php" class="btn-back"><i class="fa-solid fa-arrow-left me-2"></i>Back to Dashboard</a>
</div>

<div class="container">
    <div class="row mb-5 text-center">
        <div class="col-12">
            <h1 class="display-4 fw-bold text-white"><i class="fa-solid fa-camera-retro text-info me-2"></i>City Galleries</h1>
            <p class="lead" style="color: var(--text-readable);">Live high-resolution photo feeds from Flickr for <?php echo LIVERPOOL['NAME']; ?> and <?php echo DUBLIN['NAME']; ?></p>
        </div>
    </div>

    <div class="glass-card">
        <h3 class="fw-bold text-white mb-4"><i class="fa-solid fa-flag-checkered me-2 text-info"></i><?php echo LIVERPOOL['NAME']; ?> Highlights</h3>
        <div class="gallery-container">
            <?php if (empty($liverpool_photos)): ?>
                <div class="alert w-100 p-4 text-center" style="grid-column: 1 / -1; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5;">
                    <h5><i class="fa-solid fa-link-slash me-2"></i>Connection Error</h5>
                    <p class="mb-0">Unable to load photos from Flickr right now. Please try again later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($liverpool_photos as $photo): ?>
                    <div class="gallery-item" onclick="openGalleryModal('<?php echo $photo['url']; ?>', '<?php echo addslashes(htmlspecialchars($photo['title'])); ?>')">
                        <img src="<?php echo $photo['url']; ?>" class="gallery-img" alt="<?php echo htmlspecialchars($photo['title']); ?>" loading="lazy">
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="glass-card">
        <h3 class="fw-bold text-white mb-4"><i class="fa-solid fa-clover me-2 text-success"></i><?php echo DUBLIN['NAME']; ?> Highlights</h3>
        <div class="gallery-container">
            <?php if (empty($dublin_photos)): ?>
                <div class="alert w-100 p-4 text-center" style="grid-column: 1 / -1; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5;">
                    <h5><i class="fa-solid fa-link-slash me-2"></i>Connection Error</h5>
                    <p class="mb-0">Unable to load photos from Flickr right now. Please try again later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($dublin_photos as $photo): ?>
                    <div class="gallery-item" onclick="openGalleryModal('<?php echo $photo['url']; ?>', '<?php echo addslashes(htmlspecialchars($photo['title'])); ?>')">
                        <img src="<?php echo $photo['url']; ?>" class="gallery-img" alt="<?php echo htmlspecialchars($photo['title']); ?>" loading="lazy">
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-card" style="background: rgba(15, 23, 42, 0.9); border-color: rgba(56, 189, 248, 0.4); backdrop-filter: blur(20px);">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white fw-bold" id="modalTitle">Photo Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="fullImage" src="" class="img-fluid rounded-3 mb-3 shadow-lg" style="max-height: 70vh; width: 100%; object-fit: contain;">
                <p id="fullDesc" class="text-readable px-3" style="font-size: 1.1rem; color: #38bdf8;"></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// function to open the image preview modal when a gallery item is clicked
function openGalleryModal(imgUrl, title) {
    document.getElementById('fullImage').src = imgUrl;
    document.getElementById('modalTitle').innerText = "Visual Capture";
    document.getElementById('fullDesc').innerText = title ? title : "A capture from the city.";
    
    // initialize and show the bootstrap modal
    var myModal = new bootstrap.Modal(document.getElementById('galleryModal'));
    myModal.show();
}
</script>

</body>
</html>