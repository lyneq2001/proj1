<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'auth.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'offers.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View details of <?php echo htmlspecialchars($offer['title'] ?? 'a premium apartment'); ?> for rent on Luxury Apartments.">
    <meta name="keywords" content="luksusowe apartamenty, wynajem apartamentów, premium nieruchomości, <?php echo htmlspecialchars($offer['city'] ?? 'mieszkania'); ?>">
    <title><?php echo htmlspecialchars($offer['title'] ?? 'Offer Details'); ?> - Luxury Apartments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery.min.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>

    <style>
        #map { 
            height: 400px; 
            border-radius: 1.25rem; 
        }
        .gallery-thumbnail {
            transition: all 0.3s ease;
        }
        .gallery-thumbnail:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .feature-badge {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        .feature-badge:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px -10px rgba(30, 64, 175, 0.2);
        }
        .offer-section {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 30px 60px -45px rgba(15, 23, 42, 0.3);
        }
        .offer-section:hover {
            transform: translateY(-4px);
            box-shadow: 0 35px 70px -40px rgba(30, 64, 175, 0.25);
        }
        .page-heading {
            text-align: center;
            margin-bottom: 3rem;
        }
        .page-heading__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            background: rgba(30, 64, 175, 0.12);
            color: var(--primary-700);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 1rem;
        }
        .page-heading__eyebrow::before {
            content: '';
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, var(--accent-500), var(--accent-600));
        }
        .page-heading__title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-700);
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        .page-heading__subtitle {
            color: var(--secondary-500);
            font-size: 1.25rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 30px 60px -45px rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(10px);
        }
        .amenity-badge {
            background: rgba(30, 64, 175, 0.08);
            color: var(--primary-700);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border: 1px solid rgba(30, 64, 175, 0.15);
            transition: all 0.3s ease;
        }
        .amenity-badge:hover {
            background: rgba(30, 64, 175, 0.12);
            transform: translateY(-2px);
        }
        .breadcrumb-item {
            transition: all 0.2s ease;
        }
        .breadcrumb-item:hover {
            color: var(--primary-600);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen font-roboto">
    <?php include 'header.php'; ?>
    
    <main class="page-shell py-12">
        <div class="container mx-auto px-4 sm:px-6">
        <?php
        $offer = getOfferDetails($_GET['offer_id']);
        if ($offer) {
            recordOfferView((int)$offer['id']);
            $offer['visits'] = (int)($offer['visits'] ?? 0) + 1;
            $offer['views_last_24h'] = (int)($offer['views_last_24h'] ?? 0) + 1;
            // Set primary_image if not set
            $offer['primary_image'] = null;
            foreach ($offer['images'] as $image) {
                if ($image['is_primary']) {
                    $offer['primary_image'] = $image['file_path'];
                    break;
                }
            }
            if (!$offer['primary_image'] && !empty($offer['images'])) {
                $offer['primary_image'] = $offer['images'][0]['file_path'];
            }

            if (!function_exists('normalizeCityName')) {
                function normalizeCityName(?string $city): string
                {
                    $city = trim((string)$city);
                    if ($city === '') {
                        return '';
                    }
                    $lower = mb_strtolower($city, 'UTF-8');
                    $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $lower);
                    if ($normalized === false) {
                        $normalized = $lower;
                    }
                    return preg_replace('/[^a-z0-9]+/', '', $normalized);
                }
            }

            if (!function_exists('getPointsOfInterestForCity')) {
                function getPointsOfInterestForCity(?string $city, ?float $lat = null, ?float $lng = null): array
                {
                    $points = [
                        'warszawa' => [
                            ['name' => 'Pałac Kultury i Nauki', 'lat' => 52.2318381, 'lng' => 21.0067249],
                            ['name' => 'Dworzec Centralny', 'lat' => 52.2296756, 'lng' => 21.0012705],
                            ['name' => 'Park Łazienkowski', 'lat' => 52.210278, 'lng' => 21.036667],
                        ],
                        'krakow' => [
                            ['name' => 'Rynek Główny', 'lat' => 50.0619474, 'lng' => 19.9368564],
                            ['name' => 'Zamek Królewski na Wawelu', 'lat' => 50.0544302, 'lng' => 19.9352533],
                            ['name' => 'Dworzec Główny', 'lat' => 50.0665205, 'lng' => 19.9466149],
                        ],
                        'wroclaw' => [
                            ['name' => 'Rynek we Wrocławiu', 'lat' => 51.109407, 'lng' => 17.032601],
                            ['name' => 'Hala Stulecia', 'lat' => 51.106944, 'lng' => 17.076667],
                            ['name' => 'Dworzec Główny', 'lat' => 51.098218, 'lng' => 17.036541],
                        ],
                        'gdansk' => [
                            ['name' => 'Długi Targ', 'lat' => 54.348036, 'lng' => 18.653639],
                            ['name' => 'Stocznia Gdańska', 'lat' => 54.360458, 'lng' => 18.647354],
                            ['name' => 'Dworzec Główny', 'lat' => 54.355297, 'lng' => 18.645271],
                        ],
                        'poznan' => [
                            ['name' => 'Stary Rynek', 'lat' => 52.407, 'lng' => 16.932],
                            ['name' => 'Dworzec Poznań Główny', 'lat' => 52.402793, 'lng' => 16.910944],
                            ['name' => 'Międzynarodowe Targi Poznańskie', 'lat' => 52.406376, 'lng' => 16.909793],
                        ],
                    ];

                    $key = normalizeCityName($city);
                    if (isset($points[$key])) {
                        return $points[$key];
                    }

                    if ($lat !== null && $lng !== null) {
                        return [
                            ['name' => 'Centrum miasta', 'lat' => $lat, 'lng' => $lng],
                            ['name' => 'Park miejski', 'lat' => $lat + 0.01, 'lng' => $lng + 0.01],
                        ];
                    }

                    return [];
                }

                function calculateDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
                {
                    $earthRadius = 6371;
                    $dLat = deg2rad($lat2 - $lat1);
                    $dLng = deg2rad($lng2 - $lng1);
                    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
                    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                    return $earthRadius * $c;
                }

                function findNearestPointOfInterest(?string $city, float $lat, float $lng): ?array
                {
                    $pois = getPointsOfInterestForCity($city, $lat, $lng);
                    $closest = null;
                    $distance = PHP_FLOAT_MAX;
                    foreach ($pois as $poi) {
                        if (!isset($poi['lat'], $poi['lng'])) {
                            continue;
                        }
                        $currentDistance = calculateDistanceKm($lat, $lng, (float)$poi['lat'], (float)$poi['lng']);
                        if ($currentDistance < $distance) {
                            $distance = $currentDistance;
                            $closest = $poi + ['distance' => $currentDistance];
                        }
                    }
                    return $closest;
                }
            }

            $nearestPoi = null;
            $cityPois = [];
            $poiList = [];
            if (!empty($offer['lat']) && !empty($offer['lng'])) {
                $lat = (float)$offer['lat'];
                $lng = (float)$offer['lng'];
                $cityPois = getPointsOfInterestForCity($offer['city'] ?? '', $lat, $lng);
                $nearestPoi = findNearestPointOfInterest($offer['city'] ?? '', $lat, $lng);
                foreach ($cityPois as $poi) {
                    if (!isset($poi['lat'], $poi['lng'])) {
                        continue;
                    }
                    $poiList[] = $poi + [
                        'distance' => calculateDistanceKm($lat, $lng, (float)$poi['lat'], (float)$poi['lng'])
                    ];
                }
                usort($poiList, function ($a, $b) {
                    return ($a['distance'] ?? 0) <=> ($b['distance'] ?? 0);
                });
                $poiList = array_slice($poiList, 0, 3);
            }

            $aiRecommendations = [];
            if (isLoggedIn()) {
                $aiRecommendations = getAiRecommendedOffers((int)$_SESSION['user_id'], (int)$offer['id'], 3);
            }
        } else {
        ?>
            <div class="glass-panel p-12 text-center max-w-2xl mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-red-500 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h2 class="text-3xl font-playfair font-bold text-slate-800 mb-4">Ogłoszenie nie znalezione</h2>
                <p class="text-slate-600 text-lg mb-8">Wybrana oferta nie istnieje lub została usunięta.</p>
                <a href="index.php?action=search" class="inline-block bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white px-8 py-4 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105">
                    Przeglądaj dostępne oferty
                </a>
            </div>
        <?php } ?>

<?php if ($offer): ?>
    <!-- Page Heading -->
    <div class="page-heading">
        <span class="page-heading__eyebrow">Ekskluzywna oferta</span>
        <h1 class="page-heading__title"><?php echo htmlspecialchars($offer['title']); ?></h1>
        <p class="page-heading__subtitle"><?php echo htmlspecialchars($offer['city']); ?> • <?php echo htmlspecialchars($offer['size']); ?> m² • <?php echo htmlspecialchars(number_format((float)$offer['price'], 0, ',', ' ')); ?> PLN</p>
    </div>

    <!-- Breadcrumbs -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-2 md:space-x-4">
            <li class="inline-flex items-center">
                <a href="index.php?action=home" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-blue-600 breadcrumb-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Strona główna
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="index.php?action=search" class="ml-2 text-sm font-medium text-slate-600 hover:text-blue-600 breadcrumb-item">Przeglądaj oferty</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="ml-2 text-sm font-medium text-slate-400"><?php echo htmlspecialchars($offer['title']); ?></span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column - Images & Map -->
        <div class="space-y-8">
            <!-- Gallery -->
            <div class="offer-section overflow-hidden p-6">
                <?php if (!empty($offer['images']) && $offer['primary_image']): ?>
                    <div id="lightgallery" class="space-y-4">
                        <!-- Main Image -->
                        <div class="relative rounded-2xl overflow-hidden">
                            <a href="<?php echo htmlspecialchars($offer['primary_image']); ?>" class="block w-full h-64 sm:h-80 md:h-96 overflow-hidden">
                                <img src="<?php echo htmlspecialchars($offer['primary_image']); ?>" alt="Main property image" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                            </a>
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-full p-2 shadow-lg">
                                <span class="text-sm font-semibold px-2 text-slate-700">
                                    <?php echo htmlspecialchars($offer['visits']); ?> wizyt • 24h: <?php echo htmlspecialchars($offer['views_last_24h']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Thumbnails -->
                        <div class="grid grid-cols-3 gap-3">
                            <?php foreach ($offer['images'] as $image): ?>
                                <?php if ($image['file_path'] !== $offer['primary_image']): ?>
                                    <a href="<?php echo htmlspecialchars($image['file_path']); ?>" class="gallery-thumbnail block h-24 sm:h-28 overflow-hidden rounded-xl">
                                        <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="Property image" class="w-full h-full object-cover">
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="w-full h-64 sm:h-80 md:h-96 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center rounded-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="p-6 bg-slate-100 rounded-b-2xl">
                        <p class="text-slate-600 text-center font-medium">Brak dostępnych zdjęć dla tej nieruchomości</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Map -->
            <?php if ($offer['lat'] && $offer['lng']): ?>
                <div class="offer-section overflow-hidden p-6">
                    <div class="flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h3 class="text-xl font-playfair font-bold text-slate-800">Lokalizacja</h3>
                    </div>
                    <div id="map"></div>
                    <div class="mt-4 p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <p class="text-blue-800 font-medium"><?php echo htmlspecialchars($offer['city'] . ', ' . $offer['street']); ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="offer-section p-6">
                    <div class="flex items-center text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="font-medium">Mapa niedostępna: Lokalizacja nie została określona</span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Points of Interest -->
            <?php if (!empty($poiList)): ?>
                <div class="offer-section p-6">
                    <div class="flex items-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-xl font-playfair font-bold text-slate-800">Pobliskie miejsca zainteresowania</h3>
                    </div>
                    
                    <?php if ($nearestPoi): ?>
                        <div class="mb-6 p-4 bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-100 rounded-xl">
                            <p class="font-semibold text-emerald-800 text-lg">Najbliżej: <?php echo htmlspecialchars($nearestPoi['name']); ?></p>
                            <p class="text-emerald-700"><?php echo number_format($nearestPoi['distance'], 1, ',', ' '); ?> km od nieruchomości</p>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-4">
                        <?php foreach ($poiList as $poi): ?>
                            <div class="flex items-center justify-between bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                                <div>
                                    <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($poi['name']); ?></p>
                                    <p class="text-sm text-slate-600"><?php echo htmlspecialchars($offer['city']); ?></p>
                                </div>
                                <span class="text-lg font-bold text-blue-600"><?php echo number_format($poi['distance'], 1, ',', ' '); ?> km</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column - Details -->
        <div class="space-y-8">
            <!-- Title & Price -->
            <div class="offer-section p-6">
                <h1 class="text-2xl sm:text-3xl font-playfair font-bold text-slate-800 mb-3"><?php echo htmlspecialchars($offer['title']); ?></h1>
                <div class="flex items-center text-slate-600 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-lg"><?php echo htmlspecialchars($offer['city'] . ', ' . $offer['street']); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-3xl sm:text-4xl font-bold text-blue-600"><?php echo htmlspecialchars(number_format((float)$offer['price'], 0, ',', ' ')); ?> PLN</span>
                    <div class="text-right">
                        <p class="text-sm text-slate-600 font-medium">Wyświetlenia</p>
                        <p class="text-slate-700">Łącznie: <?php echo htmlspecialchars($offer['visits']); ?> • 24h: <?php echo htmlspecialchars($offer['views_last_24h']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Key Features -->
            <div class="offer-section p-6">
                <h3 class="text-xl font-playfair font-bold text-slate-800 mb-6">Podstawowe informacje</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="feature-badge p-4 rounded-xl text-center">
                        <p class="text-sm text-slate-600 mb-1">Powierzchnia</p>
                        <p class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($offer['size']); ?> m²</p>
                    </div>
                    <div class="feature-badge p-4 rounded-xl text-center">
                        <p class="text-sm text-slate-600 mb-1">Pokoje</p>
                        <p class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($offer['rooms']); ?></p>
                    </div>
                    <div class="feature-badge p-4 rounded-xl text-center">
                        <p class="text-sm text-slate-600 mb-1">Łazienki</p>
                        <p class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($offer['bathrooms']); ?></p>
                    </div>
                    <div class="feature-badge p-4 rounded-xl text-center">
                        <p class="text-sm text-slate-600 mb-1">Piętro</p>
                        <p class="text-lg font-bold text-slate-800"><?php echo $offer['floor'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="feature-badge p-4 rounded-xl text-center">
                        <p class="text-sm text-slate-600 mb-1">Typ budynku</p>
                        <p class="text-lg font-bold text-slate-800"><?php echo ucfirst($offer['building_type']); ?></p>
                    </div>
                    <div class="feature-badge p-4 rounded-xl text-center">
                        <p class="text-sm text-slate-600 mb-1">Stan</p>
                        <p class="text-lg font-bold text-slate-800"><?php echo ucfirst(str_replace('_', ' ', $offer['condition_type'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Amenities -->
            <div class="offer-section p-6">
                <h3 class="text-xl font-playfair font-bold text-slate-800 mb-6">Udogodnienia</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <?php if ($offer['has_balcony']): ?>
                        <div class="amenity-badge flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                            </svg>
                            <span class="font-medium">Balkon</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($offer['has_elevator']): ?>
                        <div class="amenity-badge flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                            </svg>
                            <span class="font-medium">Winda</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($offer['parking']): ?>
                        <div class="amenity-badge flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                            </svg>
                            <span class="font-medium">Parking</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($offer['garage']): ?>
                        <div class="amenity-badge flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="font-medium">Garaż</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($offer['garden']): ?>
                        <div class="amenity-badge flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span class="font-medium">Ogród</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($offer['furnished']): ?>
                        <div class="amenity-badge flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span class="font-medium">Umeblowane</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($offer['pets_allowed']): ?>
                        <div class="amenity-badge flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span class="font-medium">Zwierzęta dozwolone</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <div class="offer-section p-6">
                <h3 class="text-xl font-playfair font-bold text-slate-800 mb-6">Opis nieruchomości</h3>
                <div class="prose max-w-none text-slate-700 leading-relaxed text-lg">
                    <?php echo nl2br(htmlspecialchars($offer['description'])); ?>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="offer-section p-6">
                <h3 class="text-xl font-playfair font-bold text-slate-800 mb-6">Dodatkowe informacje</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="bg-slate-50 p-4 rounded-xl">
                        <p class="text-sm text-slate-600 mb-1">Typ ogrzewania</p>
                        <p class="font-semibold text-slate-800"><?php echo ucfirst($offer['heating_type']); ?></p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl">
                        <p class="text-sm text-slate-600 mb-1">Rok budowy</p>
                        <p class="font-semibold text-slate-800"><?php echo $offer['year_built'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl">
                        <p class="text-sm text-slate-600 mb-1">Dostępne od</p>
                        <p class="font-semibold text-slate-800"><?php echo $offer['available_from'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl">
                        <p class="text-sm text-slate-600 mb-1">Wystawione przez</p>
                        <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($offer['owner_username']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <?php if (isLoggedIn() && $offer['user_id'] != $_SESSION['user_id']): ?>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="index.php?action=dashboard&offer_id=<?php echo $offer['id']; ?>&receiver_id=<?php echo $offer['user_id']; ?>" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white px-6 py-4 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Skontaktuj się z właścicielem
                    </a>
                    <?php if (isFavorite($_SESSION['user_id'], $offer['id'])): ?>
                        <a href="index.php?action=toggle_favorite&offer_id=<?php echo $offer['id']; ?>" class="flex-1 bg-gradient-to-r from-red-600 to-pink-700 hover:from-red-700 hover:to-pink-800 text-white px-6 py-4 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Usuń z ulubionych
                        </a>
                    <?php else: ?>
                        <a href="index.php?action=toggle_favorite&offer_id=<?php echo $offer['id']; ?>" class="flex-1 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white px-6 py-4 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            Dodaj do ulubionych
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Report Section -->
                <div class="offer-section p-6">
                    <div class="flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.64 5.64l12.72 12.72M5.64 18.36L18.36 5.64" />
                        </svg>
                        <h3 class="text-xl font-playfair font-bold text-slate-800">Zgłoś naruszenie ogłoszenia</h3>
                    </div>
                    <p class="text-slate-600 mb-6">Jeśli ogłoszenie narusza regulamin lub budzi Twoje wątpliwości, poinformuj nas, a zespół moderatorów je zweryfikuje.</p>
                    <form action="index.php?action=report_offer" method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                        <input type="hidden" name="offer_id" value="<?php echo (int)$offer['id']; ?>">
                        <label class="block text-sm font-medium text-slate-700 mb-2" for="report-reason">Powód zgłoszenia</label>
                        <textarea id="report-reason" name="reason" rows="4" required minlength="10" class="w-full border border-slate-300 rounded-xl p-4 focus:ring-2 focus:ring-red-400 focus:border-red-400 transition bg-white/80" placeholder="Opisz krótko problem, np. podejrzenie oszustwa lub treści niezgodne z regulaminem"></textarea>
                        <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-pink-700 hover:from-red-700 hover:to-pink-800 text-white px-6 py-3 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105">
                            Wyślij zgłoszenie
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($aiRecommendations)): ?>
        <section class="mt-16">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-500 font-semibold">Rekomendacje AI</p>
                    <h2 class="text-3xl font-playfair font-bold text-slate-800">Oferty dopasowane do Twoich wyszukiwań</h2>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-indigo-600/10 text-indigo-700 text-xs font-semibold px-3 py-1">
                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                    AI
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($aiRecommendations as $recommendation): ?>
                    <div class="offer-card overflow-hidden">
                        <a href="index.php?action=view_offer&offer_id=<?php echo $recommendation['id']; ?>" class="block">
                            <div class="relative h-48">
                                <?php if (!empty($recommendation['primary_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($recommendation['primary_image']); ?>" alt="Offer Image" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-slate-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <span class="absolute top-3 left-3 rounded-full bg-white/90 text-indigo-700 text-xs font-semibold px-2 py-1 shadow">
                                    AI
                                </span>
                            </div>
                            <div class="p-4 space-y-2">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-lg font-semibold text-slate-800"><?php echo htmlspecialchars($recommendation['title']); ?></h3>
                                    <span class="text-lg font-bold text-blue-600 whitespace-nowrap"><?php echo htmlspecialchars(number_format((float)$recommendation['price'], 0, ',', ' ')); ?> PLN</span>
                                </div>
                                <p class="text-sm text-slate-600"><?php echo htmlspecialchars($recommendation['city']); ?>, <?php echo htmlspecialchars($recommendation['street']); ?></p>
                                <div class="flex items-center gap-3 text-sm text-slate-600">
                                    <span><?php echo htmlspecialchars($recommendation['size']); ?> m²</span>
                                    <span>•</span>
                                    <span><?php echo htmlspecialchars($recommendation['rooms']); ?> pokoi</span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
        <?php endif; ?>
        </div>
    </main>

    <script>
        <?php if ($offer && $offer['lat'] && $offer['lng']): ?>
            // Initialize map
            var map = L.map('map').setView([<?php echo $offer['lat']; ?>, <?php echo $offer['lng']; ?>], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Add marker
            var marker = L.marker([<?php echo $offer['lat']; ?>, <?php echo $offer['lng']; ?>]).addTo(map)
                .bindPopup('<?php echo htmlspecialchars($offer['title']); ?>')
                .openPopup();
        <?php endif; ?>

        <?php if (!empty($offer['images']) && $offer['primary_image']): ?>
            // Initialize lightgallery
            document.addEventListener('DOMContentLoaded', () => {
                lightGallery(document.getElementById('lightgallery'), {
                    selector: 'a',
                    download: false,
                    counter: false
                });
            });
        <?php endif; ?>
    </script>

</body>
</html>
