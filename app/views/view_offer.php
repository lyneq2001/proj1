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
        #map { height: 300px; border-radius: 0.5rem; }
        .gallery-thumbnail {
            transition: all 0.3s ease;
        }
        .gallery-thumbnail:hover {
            transform: scale(1.02);
        }
        .feature-badge {
            transition: all 0.2s ease;
        }
        .feature-badge:hover {
            transform: translateY(-2px);
        }
        .offer-section {
            transition: all 0.3s ease;
        }
        .offer-section:hover {
            transform: translateY(-2px);
        }
        @media (min-width: 640px) {
            #map { height: 400px; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen font-roboto">
    <?php include 'header.php'; ?>
    <main class="container mx-auto px-4 py-8">
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
        } else {
        ?>
            <div class="bg-white rounded-xl shadow-card p-8 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h2 class="text-2xl font-bold text-dark-blue mb-2">Ogłoszenie nie znalezione</h2>
                <p class="text-secondary-500 mb-6">Wybrana oferta nie istnieje lub została usunięta.</p>
                <a href="index.php?action=search" class="inline-block px-6 py-2 bg-gold hover:bg-accent-600 hover:text-white text-dark-blue rounded-lg transition-colors duration-300 font-medium">
                    Przeglądaj dostępne oferty
                </a>
            </div>
        <?php } ?>

        <?php if ($offer): ?>
            <!-- Breadcrumbs -->
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="index.php?action=home" class="inline-flex items-center text-sm font-medium text-secondary-500 hover:text-dark-blue">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Home
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <a href="index.php?action=search" class="ml-1 text-sm font-medium text-secondary-500 hover:text-dark-blue md:ml-2">Przeglądaj oferty</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span class="ml-1 text-sm font-medium text-secondary-400 md:ml-2"><?php echo htmlspecialchars($offer['title']); ?></span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                <!-- Left Column - Images & Map -->
                <div class="space-y-6">
                    <!-- Gallery -->
                    <div class="offer-section bg-white rounded-xl shadow-card overflow-hidden">
                        <?php if (!empty($offer['images']) && $offer['primary_image']): ?>
                            <div id="lightgallery" class="grid grid-cols-1 gap-4">
                                <!-- Main Image -->
                                <div class="relative">
                                    <a href="<?php echo htmlspecialchars($offer['primary_image']); ?>" class="block w-full h-64 sm:h-80 md:h-96 overflow-hidden">
                                        <img src="<?php echo htmlspecialchars($offer['primary_image']); ?>" alt="Main property image" class="w-full h-full object-cover">
                                    </a>
                                    <div class="absolute top-3 right-3 bg-white/90 rounded-full p-1.5 shadow">
                                        <span class="text-sm font-medium px-2">
                                            <?php echo htmlspecialchars($offer['visits']); ?> wizyt • 24h: <?php echo htmlspecialchars($offer['views_last_24h']); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Thumbnails -->
                                <div class="grid grid-cols-3 gap-2 px-4 pb-4">
                                    <?php foreach ($offer['images'] as $image): ?>
                                        <?php if ($image['file_path'] !== $offer['primary_image']): ?>
                                            <a href="<?php echo htmlspecialchars($image['file_path']); ?>" class="gallery-thumbnail block h-20 sm:h-24 overflow-hidden rounded-lg">
                                                <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="Property image" class="w-full h-full object-cover">
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="w-full h-64 sm:h-80 md:h-96 bg-gray-100 flex items-center justify-center rounded-t-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="p-4 bg-gray-100 rounded-b-xl">
                                <p class="text-secondary-500 text-center">No images available for this property</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Map -->
                    <?php if ($offer['lat'] && $offer['lng']): ?>
                        <div class="offer-section bg-white rounded-xl shadow-card overflow-hidden">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="font-semibold text-dark-blue flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Location
                                </h3>
                            </div>
                            <div id="map"></div>
                            <div class="p-4 text-sm text-secondary-500">
                                <p><?php echo htmlspecialchars($offer['city'] . ', ' . $offer['street']); ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="offer-section bg-white rounded-xl shadow-card p-4">
                            <div class="flex items-center text-secondary-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Map unavailable: Location not specified</span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($poiList)): ?>
                        <div class="offer-section bg-white rounded-xl shadow-card p-6">
                            <h3 class="text-lg font-semibold text-dark-blue mb-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Nearby points of interest
                            </h3>
                            <?php if ($nearestPoi): ?>
                                <div class="mb-4 p-4 bg-primary-50 border border-primary-100 rounded-lg text-sm text-primary-900">
                                    <p class="font-medium">Najbliżej: <?php echo htmlspecialchars($nearestPoi['name']); ?></p>
                                    <p><?php echo number_format($nearestPoi['distance'], 1, ',', ' '); ?> km od nieruchomości</p>
                                </div>
                            <?php endif; ?>
                            <ul class="space-y-3">
                                <?php foreach ($poiList as $poi): ?>
                                    <li class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                                        <div>
                                            <p class="font-medium text-dark-blue"><?php echo htmlspecialchars($poi['name']); ?></p>
                                            <p class="text-xs text-secondary-500"><?php echo htmlspecialchars($offer['city']); ?></p>
                                        </div>
                                        <span class="text-sm font-semibold text-secondary-500"><?php echo number_format($poi['distance'], 1, ',', ' '); ?> km</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column - Details -->
                <div class="space-y-6">
                    <!-- Title & Price -->
                    <div class="offer-section bg-white rounded-xl shadow-card p-6">
                        <h1 class="text-xl sm:text-2xl font-playfair font-bold text-dark-blue mb-2"><?php echo htmlspecialchars($offer['title']); ?></h1>
                        <div class="flex items-center text-secondary-500 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span><?php echo htmlspecialchars($offer['city'] . ', ' . $offer['street']); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-2xl sm:text-3xl font-bold text-gold"><?php echo htmlspecialchars($offer['price']); ?> PLN</span>
                            <span class="text-secondary-500 text-sm">
                                Łącznie: <?php echo htmlspecialchars($offer['visits']); ?> • 24h: <?php echo htmlspecialchars($offer['views_last_24h']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Key Features -->
                    <div class="offer-section bg-white rounded-xl shadow-card p-6">
                        <h3 class="text-lg font-semibold text-dark-blue mb-4">Key Features</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div class="feature-badge bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-secondary-500">Size</p>
                                <p class="font-medium"><?php echo htmlspecialchars($offer['size']); ?> m²</p>
                            </div>
                            <div class="feature-badge bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-secondary-500">Rooms</p>
                                <p class="font-medium"><?php echo htmlspecialchars($offer['rooms']); ?></p>
                            </div>
                            <div class="feature-badge bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-secondary-500">Bathrooms</p>
                                <p class="font-medium"><?php echo htmlspecialchars($offer['bathrooms']); ?></p>
                            </div>
                            <div class="feature-badge bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-secondary-500">Floor</p>
                                <p class="font-medium"><?php echo $offer['floor'] ?? 'N/A'; ?></p>
                            </div>
                            <div class="feature-badge bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-secondary-500">Building Type</p>
                                <p class="font-medium"><?php echo ucfirst($offer['building_type']); ?></p>
                            </div>
                            <div class="feature-badge bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-secondary-500">Condition</p>
                                <p class="font-medium"><?php echo ucfirst(str_replace('_', ' ', $offer['condition_type'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="offer-section bg-white rounded-xl shadow-card p-6">
                        <h3 class="text-lg font-semibold text-dark-blue mb-4">Amenities</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <?php if ($offer['has_balcony']): ?>
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gold mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                    </svg>
                                    <span>Balcony</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($offer['has_elevator']): ?>
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gold mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                    </svg>
                                    <span>Elevator</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($offer['parking']): ?>
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gold mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                    </svg>
                                    <span>Parking</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($offer['garage']): ?>
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gold mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <span>Garage</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($offer['garden']): ?>
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gold mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <span>Garden</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($offer['furnished']): ?>
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gold mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    <span>Furnished</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($offer['pets_allowed']): ?>
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gold mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <span>Pets Allowed</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="offer-section bg-white rounded-xl shadow-card p-6">
                        <h3 class="text-lg font-semibold text-dark-blue mb-4">Description</h3>
                        <div class="prose max-w-none text-gray-600">
                            <?php echo nl2br(htmlspecialchars($offer['description'])); ?>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="offer-section bg-white rounded-xl shadow-card p-6">
                        <h3 class="text-lg font-semibold text-dark-blue mb-4">Additional Details</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-secondary-500">Heating Type</p>
                                <p class="font-medium"><?php echo ucfirst($offer['heating_type']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-secondary-500">Year Built</p>
                                <p class="font-medium"><?php echo $offer['year_built'] ?? 'N/A'; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-secondary-500">Available From</p>
                                <p class="font-medium"><?php echo $offer['available_from'] ?? 'N/A'; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-secondary-500">Listed By</p>
                                <p class="font-medium"><?php echo htmlspecialchars($offer['owner_username']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <?php if (isLoggedIn() && $offer['user_id'] != $_SESSION['user_id']): ?>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="index.php?action=dashboard&offer_id=<?php echo $offer['id']; ?>&receiver_id=<?php echo $offer['user_id']; ?>" class="flex-1 bg-gold hover:bg-accent-600 hover:text-white text-dark-blue px-6 py-3 rounded-lg transition-colors duration-300 font-medium flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Contact Owner
                            </a>
                            <?php if (isFavorite($_SESSION['user_id'], $offer['id'])): ?>
                                <a href="index.php?action=toggle_favorite&offer_id=<?php echo $offer['id']; ?>" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition font-medium flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Remove from Favorites
                                </a>
                            <?php else: ?>
                                <a href="index.php?action=toggle_favorite&offer_id=<?php echo $offer['id']; ?>" class="flex-1 bg-accent-500 hover:bg-accent-600 text-white px-6 py-3 rounded-lg transition font-medium flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                    Add to Favorites
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="offer-section bg-white rounded-xl shadow-card p-6">
                            <h3 class="text-lg font-semibold text-dark-blue mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.64 5.64l12.72 12.72M5.64 18.36L18.36 5.64" />
                                </svg>
                                Zgłoś naruszenie ogłoszenia
                            </h3>
                            <p class="text-sm text-secondary-500 mb-4">Jeśli ogłoszenie narusza regulamin lub budzi Twoje wątpliwości, poinformuj nas, a zespół moderatorów je zweryfikuje.</p>
                            <form action="index.php?action=report_offer" method="POST" class="space-y-3">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                <input type="hidden" name="offer_id" value="<?php echo (int)$offer['id']; ?>">
                                <label class="block text-sm font-medium text-secondary-500" for="report-reason">Powód zgłoszenia</label>
                                <textarea id="report-reason" name="reason" rows="3" required minlength="10" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-red-400 focus:border-red-400 transition" placeholder="Opisz krótko problem, np. podejrzenie oszustwa lub treści niezgodne z regulaminem"></textarea>
                                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition font-medium">Wyślij zgłoszenie</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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