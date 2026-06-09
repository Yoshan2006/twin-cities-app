<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twin Cities | Discover Liverpool & Dublin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            color: #2c3e50;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: 1px;
        }
        
        /* මෙනු ලින්ක් සඳහා (Menu Links styling) */
        .nav-link {
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            color: #ff9a9e !important;
            transform: translateY(-2px);
        }

        .pro-card {
            background: #ffffff;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .pro-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        
        .weather-widget {
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
        }
        .weather-temp {
            font-size: 3rem;
            font-weight: 700;
            color: #ff6b6b;
            line-height: 1;
        }
        
        .map-container {
            height: 400px;
            width: 100%;
            border-radius: 15px;
            border: 3px solid #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .btn-custom {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%);
            border: none;
            color: #333;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background: linear-gradient(135deg, #fecfef 0%, #ff9a9e 100%);
            transform: scale(1.02);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #e1e8ed;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.2);
            border-color: #2a5298;
        }
        
        .section-title {
            font-weight: 700;
            margin-bottom: 30px;
            color: #1e3c72;
            position: relative;
            padding-bottom: 10px;
        }
        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 4px;
            width: 50px;
            background: #ff6b6b;
            border-radius: 2px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-5">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fa-solid fa-earth-europe me-2"></i>Twin Cities</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                   

                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link text-light me-3" href="index.php"><i class="fa-solid fa-house me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light fw-bold me-3" href="gallery.php"><i class="fa-solid fa-images me-1"></i> Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-warning fw-bold" href="rss.php" target="_blank"><i class="fa-solid fa-rss me-1"></i> RSS</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">