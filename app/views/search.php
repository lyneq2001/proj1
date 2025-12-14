<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Apartments - Apartment Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        .offer-card {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 30px 60px -45px rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(10px);
        }
        .offer-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 35px 70px -40px rgba(30, 64, 175, 0.35);
        }
        .filter-section {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.15);
        }
        .filter-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -30px rgba(15, 23, 42, 0.2);
        }
        .checkbox-label {
            transition: all 0.2s ease;
            border-radius: 0.75rem;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }
        .checkbox-label:hover {
            background-color: #f8fafc;
            border-color: rgba(30, 64, 175, 0.3);
        }
        .toggle-filters {
            transition: all 0.3s ease;
        }
        #offers-map {
            height: 520px;
            border-radius: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.2);
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
            color: #1e3a8a;
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
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .page-heading__title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        .page-heading__subtitle {
            color: #64748b;
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
        .form-input {
            border-radius: 0.75rem;
            border: 1px solid rgba(148, 163, 184, 0.3);
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }
        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: rgba(255, 255, 255, 0.95);
        }

        .ai-status-row {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #1d4ed8;
            background: #e0e7ff;
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: 12px;
            padding: 10px 12px;
        }

        .ai-status-dot {
            position: relative;
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #4338ca;
            animation: ai-bounce 1.2s ease-in-out infinite;
        }

        .ai-status-dot::before,
        .ai-status-dot::after {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #4338ca;
            opacity: 0.75;
        }

        .ai-status-dot::before {
            left: -14px;
            animation: ai-bounce 1.2s ease-in-out infinite;
            animation-delay: -0.25s;
        }

        .ai-status-dot::after {
            right: -14px;
            animation: ai-bounce 1.2s ease-in-out infinite;
            animation-delay: 0.25s;
        }

        @keyframes ai-bounce {
            0%,
            80%,
            100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1.1);
                opacity: 1;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen font-roboto">
    <?php include 'header.php'; ?>
    
    <main class="page-shell py-12">
        <div class="container mx-auto px-4 sm:px-6">
            <!-- Page Heading -->
            <div class="page-heading">
                <span class="page-heading__eyebrow">Przeglądaj oferty</span>
                <h1 class="page-heading__title">Znajdź idealny apartament</h1>
                <p class="page-heading__subtitle">Filtruj i porównuj luksusowe mieszkania dopasowane do Twojego stylu życia.</p>
            </div>

            <?php
            $flash = getFlashMessage();
            if ($flash):
            ?>
                <div class="glass-panel mb-8 p-6 flex items-start gap-4 <?php echo $flash['type'] === 'error' ? 'flash-error' : 'flash-success'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $flash['type'] === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M5 13l4 4L19 7'; ?>"></path>
                    </svg>
                    <div class="font-medium leading-relaxed text-lg"><?php echo htmlspecialchars($flash['message']); ?></div>
                </div>
            <?php endif; ?>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Filters Sidebar -->
                <aside class="w-full lg:w-80 flex-shrink-0">
                    <div class="glass-panel p-6 sticky top-4">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-playfair font-bold text-slate-800">Filtry</h2>
                            <button id="toggle-filters" class="lg:hidden text-blue-600 hover:text-blue-700 toggle-filters p-2 rounded-lg hover:bg-blue-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>

                        <div class="filter-section p-4 mb-6">
                            <h3 class="font-semibold text-slate-800 mb-3">Asystent AI</h3>
                            <p class="text-sm text-slate-600 mb-3">Opisz wymagania, a AI zaproponuje filtry i wagi wyszukiwania.</p>
                            <textarea id="ai-message" class="w-full p-3 form-input mb-3" rows="3" placeholder="Np. Szukam 3 pokoi na Mokotowie do 800000 zł"></textarea>
                            <button id="ai-send" type="button" class="w-full bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-lg font-semibold">Wyślij do AI</button>
                            <div id="ai-status" class="mt-3 hidden" aria-live="polite">
                                <span class="ai-status-row">
                                    <span class="ai-status-dot" aria-hidden="true"></span>
                                    <span id="ai-status-text" class="font-medium">Analizuję Twój opis...</span>
                                </span>
                            </div>
                            <div id="ai-response" class="mt-3 text-sm text-slate-700 hidden"></div>
                        </div>

                        <form method="GET" action="index.php" id="filters-form" class="space-y-6">
                            <input type="hidden" name="action" value="search">

                            <!-- Location Filters -->
                            <div class="filter-section p-4">
                                <h3 class="font-semibold text-slate-800 mb-4 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Lokalizacja
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-slate-700 text-sm font-semibold mb-2">Miasto</label>
                                        <input type="text" name="city" value="<?php echo htmlspecialchars($_GET['city'] ?? ''); ?>" 
                                               class="w-full p-3 form-input" placeholder="Wpisz miasto">
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 text-sm font-semibold mb-2">Ulica</label>
                                        <input type="text" name="street" value="<?php echo htmlspecialchars($_GET['street'] ?? ''); ?>" 
                                               class="w-full p-3 form-input" placeholder="Wpisz ulicę">
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 text-sm font-semibold mb-2">Maksymalna odległość (km)</label>
                                        <input type="number" step="1" name="distance_km" value="<?php echo htmlspecialchars($_GET['distance_km'] ?? ''); ?>" 
                                               class="w-full p-3 form-input" placeholder="np. 5">
                                    </div>
                                </div>
                            </div>

                            <!-- Price & Size -->
                            <div class="filter-section p-4">
                                <h3 class="font-semibold text-slate-800 mb-4 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Cena i metraż
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-slate-700 text-sm font-semibold mb-2">Cena od (PLN)</label>
                                        <input type="number" step="1" name="min_price" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>" 
                                               class="w-full p-3 form-input" placeholder="Minimalna cena">
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 text-sm font-semibold mb-2">Cena do (PLN)</label>
                                        <input type="number" step="1" name="max_price" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>" 
                                               class="w-full p-3 form-input" placeholder="Maksymalna cena">
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 text-sm font-semibold mb-2">Min. powierzchnia (m²)</label>
                                        <input type="number" name="min_size" value="<?php echo htmlspecialchars($_GET['min_size'] ?? ''); ?>" 
                                               class="w-full p-3 form-input" placeholder="Minimalny metraż">
                                    </div>
                                </div>
                            </div>

                            <!-- Property Type -->
                            <div class="filter-section p-4">
                                <h3 class="font-semibold text-slate-800 mb-4 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    Typ nieruchomości
                                </h3>
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-2">Rodzaj budynku</label>
                                    <select name="building_type" class="w-full p-3 form-input">
                                        <option value="">Dowolny</option>
                                        <option value="apartment" <?php echo ($_GET['building_type'] ?? '') == 'apartment' ? 'selected' : ''; ?>>Mieszkanie</option>
                                        <option value="block" <?php echo ($_GET['building_type'] ?? '') == 'block' ? 'selected' : ''; ?>>Blok mieszkalny</option>
                                        <option value="house" <?php echo ($_GET['building_type'] ?? '') == 'house' ? 'selected' : ''; ?>>Dom</option>
                                        <option value="studio" <?php echo ($_GET['building_type'] ?? '') == 'studio' ? 'selected' : ''; ?>>Kawalerka</option>
                                        <option value="loft" <?php echo ($_GET['building_type'] ?? '') == 'loft' ? 'selected' : ''; ?>>Loft</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Rooms & Floors -->
                            <div class="filter-section p-4">
                                <h3 class="font-semibold text-slate-800 mb-4 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Pokoje i piętra
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-slate-700 text-sm font-semibold mb-2">Min. pokoje</label>
                                        <input type="number" name="min_rooms" value="<?php echo htmlspecialchars($_GET['min_rooms'] ?? ''); ?>" 
                                               class="w-full p-3 form-input" placeholder="Min">
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 text-sm font-semibold mb-2">Min. łazienki</label>
                                        <input type="number" name="min_bathrooms" value="<?php echo htmlspecialchars($_GET['min_bathrooms'] ?? ''); ?>" 
                                               class="w-full p-3 form-input" placeholder="Min">
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 text-sm font-semibold mb-2">Min. piętro</label>
                                        <input type="number" name="min_floor" value="<?php echo htmlspecialchars($_GET['min_floor'] ?? ''); ?>" 
                                               class="w-full p-3 form-input" placeholder="Min">
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 text-sm font-semibold mb-2">Maks. piętro</label>
                                        <input type="number" name="max_floor" value="<?php echo htmlspecialchars($_GET['max_floor'] ?? ''); ?>" 
                                               class="w-full p-3 form-input" placeholder="Maks">
                                    </div>
                                </div>
                            </div>

                            <!-- Amenities -->
                            <div class="filter-section p-4">
                                <h3 class="font-semibold text-slate-800 mb-4 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Udogodnienia
                                </h3>
                                <div class="space-y-3">
                                    <label class="checkbox-label flex items-center p-3 cursor-pointer">
                                        <input type="checkbox" name="has_balcony" value="1" <?php echo isset($_GET['has_balcony']) ? 'checked' : ''; ?> 
                                               class="h-5 w-5 text-blue-600 focus:ring-blue-600 border-slate-300 rounded">
                                        <span class="ml-3 text-slate-700 text-sm font-semibold">Balkon</span>
                                    </label>
                                    <label class="checkbox-label flex items-center p-3 cursor-pointer">
                                        <input type="checkbox" name="has_elevator" value="1" <?php echo isset($_GET['has_elevator']) ? 'checked' : ''; ?> 
                                               class="h-5 w-5 text-blue-600 focus:ring-blue-600 border-slate-300 rounded">
                                        <span class="ml-3 text-slate-700 text-sm font-semibold">Winda</span>
                                    </label>
                                    <label class="checkbox-label flex items-center p-3 cursor-pointer">
                                        <input type="checkbox" name="parking" value="1" <?php echo isset($_GET['parking']) ? 'checked' : ''; ?> 
                                               class="h-5 w-5 text-blue-600 focus:ring-blue-600 border-slate-300 rounded">
                                        <span class="ml-3 text-slate-700 text-sm font-semibold">Parking</span>
                                    </label>
                                    <label class="checkbox-label flex items-center p-3 cursor-pointer">
                                        <input type="checkbox" name="furnished" value="1" <?php echo isset($_GET['furnished']) ? 'checked' : ''; ?> 
                                               class="h-5 w-5 text-blue-600 focus:ring-blue-600 border-slate-300 rounded">
                                        <span class="ml-3 text-slate-700 text-sm font-semibold">Umeblowane</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Sort & Submit -->
                            <div class="filter-section p-4">
                                <div class="mb-4">
                                    <label class="block text-slate-700 text-sm font-semibold mb-2">Sortuj według</label>
                                    <select name="sort" class="w-full p-3 form-input">
                                        <option value="popularity_desc" <?php echo ($_GET['sort'] ?? '') == 'popularity_desc' ? 'selected' : ''; ?>>Popularność: ostatnie 24h</option>
                                        <option value="popularity_asc" <?php echo ($_GET['sort'] ?? '') == 'popularity_asc' ? 'selected' : ''; ?>>Popularność: od najmniejszej (24h)</option>
                                        <option value="price_asc" <?php echo ($_GET['sort'] ?? '') == 'price_asc' ? 'selected' : ''; ?>>Cena: rosnąco</option>
                                        <option value="price_desc" <?php echo ($_GET['sort'] ?? '') == 'price_desc' ? 'selected' : ''; ?>>Cena: malejąco</option>
                                        <option value="date_asc" <?php echo ($_GET['sort'] ?? '') == 'date_asc' ? 'selected' : ''; ?>>Data: od najstarszych</option>
                                        <option value="date_desc" <?php echo ($_GET['sort'] ?? '') == 'date_desc' ? 'selected' : ''; ?>>Data: od najnowszych</option>
                                        <option value="size_asc" <?php echo ($_GET['sort'] ?? '') == 'size_asc' ? 'selected' : ''; ?>>Powierzchnia: rosnąco</option>
                                        <option value="size_desc" <?php echo ($_GET['sort'] ?? '') == 'size_desc' ? 'selected' : ''; ?>>Powierzchnia: malejąco</option>
                                        <option value="ai" <?php echo ($_GET['sort'] ?? '') == 'ai' ? 'selected' : ''; ?>>Inteligentne (AI)</option>
                                        <option value="ai_personalized" <?php echo ($_GET['sort'] ?? '') == 'ai_personalized' ? 'selected' : ''; ?>>Personalizowane (AI + historia)</option>
                                    </select>
                                </div>
                                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white p-4 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl">
                                    Zastosuj filtry
                                </button>
                                <?php if (!empty($_GET)): ?>
                                    <a href="index.php?action=search" class="block w-full text-center mt-3 text-slate-600 hover:text-slate-800 text-sm font-medium transition-colors">
                                        Wyczyść filtry
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="flex-1">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-8">
                    <div>
                        <h1 class="text-3xl font-playfair font-bold text-slate-800">Dostępne oferty</h1>
                        <p class="text-slate-600 text-lg font-semibold">
                            Znaleziono <span class="text-blue-600"><?php echo count($offers); ?></span> ofert
                        </p>
                    </div>
                    <p class="text-slate-500 text-sm md:text-base max-w-xl">Mapa i lista ofert są widoczne jednocześnie, aby łatwo porównywać lokalizacje i szczegóły mieszkań.</p>
                </div>

                    <?php if (empty($offers)): ?>
                        <div class="glass-panel p-12 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-slate-400 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-2xl font-playfair font-bold text-slate-800 mb-4">Brak wyników</h3>
                            <p class="text-slate-600 text-lg mb-8">Spróbuj zmienić filtry wyszukiwania</p>
                            <button id="show-filters" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl lg:hidden">
                                Pokaż filtry
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="grid xl:grid-cols-3 gap-6 items-start">
                            <div class="xl:col-span-2 space-y-6">
                                <div id="list-view" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-6">
                                    <?php foreach ($offers as $offer): ?>
                                        <div class="offer-card overflow-hidden">
                                            <a href="index.php?action=view_offer&offer_id=<?php echo $offer['id']; ?>" class="block">
                                                <?php if (!empty($offer['primary_image'])): ?>
                                                    <div class="w-full h-48 overflow-hidden relative">
                                                        <img src="<?php echo htmlspecialchars($offer['primary_image']); ?>" alt="Property image" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                                                        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-full p-2 shadow-lg">
                                                            <span class="text-sm font-semibold px-2 text-slate-700">
                                                                Wyświetlenia: <?php echo htmlspecialchars($offer['visits']); ?> • 24h: <?php echo htmlspecialchars($offer['views_last_24h'] ?? 0); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="w-full h-48 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center relative">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-full p-2 shadow-lg">
                                                            <span class="text-sm font-semibold px-2 text-slate-700">
                                                                Wyświetlenia: <?php echo htmlspecialchars($offer['visits']); ?> • 24h: <?php echo htmlspecialchars($offer['views_last_24h'] ?? 0); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="p-6">
                                                    <div class="flex justify-between items-start mb-3">
                                                        <h3 class="text-xl font-semibold text-slate-800 truncate"><?php echo htmlspecialchars($offer['title']); ?></h3>
                                                        <span class="text-2xl font-bold text-blue-600 whitespace-nowrap ml-4"><?php echo htmlspecialchars(number_format((float)$offer['price'], 0, ',', ' ')); ?> PLN</span>
                                                    </div>
                                                    <p class="text-slate-600 text-base mb-3 flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        <?php echo htmlspecialchars($offer['city']); ?>, <?php echo htmlspecialchars($offer['street']); ?>
                                                    </p>
                                                    <div class="flex flex-wrap gap-2 mb-4">
                                                        <span class="text-sm bg-slate-100 text-slate-700 px-3 py-1.5 rounded-lg font-medium">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                                            </svg>
                                                            <?php echo htmlspecialchars($offer['size']); ?> m²
                                                        </span>
                                                        <span class="text-sm bg-slate-100 text-slate-700 px-3 py-1.5 rounded-lg font-medium">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                            </svg>
                                                            <?php echo htmlspecialchars($offer['rooms']); ?> pokoi
                                                        </span>
                                                        <?php if ($offer['has_balcony']): ?>
                                                            <span class="text-sm bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-lg font-medium">
                                                                Balkon
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if ($offer['has_elevator']): ?>
                                                            <span class="text-sm bg-purple-100 text-purple-700 px-3 py-1.5 rounded-lg font-medium">
                                                                Winda
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="text-slate-600 text-sm line-clamp-2 leading-relaxed mb-4"><?php echo htmlspecialchars($offer['description']); ?></p>
                                                    <?php if (!empty($offer['lat']) && !empty($offer['lng'])): ?>
                                                        <p class="text-slate-500 text-sm font-medium js-poi" data-lat="<?php echo htmlspecialchars($offer['lat']); ?>" data-lng="<?php echo htmlspecialchars($offer['lng']); ?>" data-city="<?php echo htmlspecialchars($offer['city']); ?>">
                                                            Analizuję pobliskie miejsca zainteresowania...
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </a>
                                            <?php if (isLoggedIn() && $offer['user_id'] != $_SESSION['user_id']): ?>
                                                <div class="px-6 pb-6 flex justify-between items-center">
                                                    <a href="index.php?action=dashboard&offer_id=<?php echo $offer['id']; ?>&receiver_id=<?php echo $offer['user_id']; ?>" class="text-sm text-white bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 px-4 py-2 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl">
                                                        Kontakt z właścicielem
                                                    </a>
                                                    <?php if (isFavorite($_SESSION['user_id'], $offer['id'])): ?>
                                                        <a href="index.php?action=toggle_favorite&offer_id=<?php echo $offer['id']; ?>" class="text-sm text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 px-4 py-2 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                            Usuń
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="index.php?action=toggle_favorite&offer_id=<?php echo $offer['id']; ?>" class="text-sm text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 px-4 py-2 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                            </svg>
                                                            Zapisz
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <?php if (!empty($mapOffers ?? [])): ?>
                                <div id="map-view" class="glass-panel p-6 sticky top-4">
                                    <div class="flex justify-between items-center mb-6">
                                        <h2 class="text-2xl font-playfair font-bold text-slate-800">Mapa aktualnych ofert</h2>
                                        <span class="text-slate-600 text-sm font-medium">Przeciągnij mapę, aby zobaczyć więcej lokalizacji</span>
                                    </div>
                                    <div id="offers-map" class="w-full"></div>
                                    <p class="text-slate-500 text-sm mt-4 font-medium">Kliknij pinezkę, aby zobaczyć szczegóły oferty i najbliższy punkt zainteresowania.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const mapOffersData = <?php echo json_encode($mapOffers ?? []); ?>;
        let offersMap;
        const CITY_POIS = {
            'warszawa': [
                { name: 'Pałac Kultury i Nauki', lat: 52.2318381, lng: 21.0067249 },
                { name: 'Dworzec Centralny', lat: 52.2296756, lng: 21.0012705 },
                { name: 'Park Łazienkowski', lat: 52.210278, lng: 21.036667 }
            ],
            'krakow': [
                { name: 'Rynek Główny', lat: 50.0619474, lng: 19.9368564 },
                { name: 'Zamek Królewski na Wawelu', lat: 50.0544302, lng: 19.9352533 },
                { name: 'Dworzec Główny', lat: 50.0665205, lng: 19.9466149 }
            ],
            'wroclaw': [
                { name: 'Rynek we Wrocławiu', lat: 51.109407, lng: 17.032601 },
                { name: 'Hala Stulecia', lat: 51.106944, lng: 17.076667 },
                { name: 'Dworzec Główny', lat: 51.098218, lng: 17.036541 }
            ],
            'gdansk': [
                { name: 'Długi Targ', lat: 54.348036, lng: 18.653639 },
                { name: 'Stocznia Gdańska', lat: 54.360458, lng: 18.647354 },
                { name: 'Dworzec Główny', lat: 54.355297, lng: 18.645271 }
            ],
            'poznan': [
                { name: 'Stary Rynek', lat: 52.407, lng: 16.932 },
                { name: 'Dworzec Poznań Główny', lat: 52.402793, lng: 16.910944 },
                { name: 'Międzynarodowe Targi Poznańskie', lat: 52.406376, lng: 16.909793 }
            ]
        };

        const FALLBACK_POIS = [
            { name: 'Centrum miasta', offsetLat: 0, offsetLng: 0 },
            { name: 'Park miejski', offsetLat: 0.01, offsetLng: 0.01 }
        ];

        function normalizeCity(city) {
            return (city || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }

        function toRad(value) {
            return (value * Math.PI) / 180;
        }

        function distanceKm(lat1, lng1, lat2, lng2) {
            const R = 6371;
            const dLat = toRad(lat2 - lat1);
            const dLng = toRad(lng2 - lng1);
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        function getCityPois(city, lat, lng) {
            const normalized = normalizeCity(city);
            if (CITY_POIS[normalized]) {
                return CITY_POIS[normalized];
            }
            if (typeof lat === 'number' && typeof lng === 'number') {
                return FALLBACK_POIS.map(poi => ({
                    name: poi.name,
                    lat: lat + (poi.offsetLat || 0),
                    lng: lng + (poi.offsetLng || 0)
                }));
            }
            return [];
        }

        function findNearestPoi(city, lat, lng) {
            const pois = getCityPois(city, lat, lng);
            if (!pois.length) {
                return null;
            }
            let nearest = null;
            let bestDistance = Infinity;
            pois.forEach(poi => {
                if (typeof poi.lat !== 'number' || typeof poi.lng !== 'number') {
                    return;
                }
                const distance = distanceKm(lat, lng, poi.lat, poi.lng);
                if (distance < bestDistance) {
                    bestDistance = distance;
                    nearest = { ...poi, distance };
                }
            });
            return nearest;
        }

        function escapeHtml(text) {
            return String(text).replace(/[&<>"]+/g, (match) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;'
            })[match] || match);
        }

        const aiMessage = document.getElementById('ai-message');
        const aiSend = document.getElementById('ai-send');
        const aiResponse = document.getElementById('ai-response');
        const aiStatus = document.getElementById('ai-status');
        const aiStatusText = document.getElementById('ai-status-text');
        const sortSelect = document.querySelector('select[name="sort"]');

        function showAiStatus(message) {
            if (aiStatus && aiStatusText) {
                aiStatusText.textContent = message;
                aiStatus.classList.remove('hidden');
            }
        }

        function hideAiStatus() {
            if (aiStatus) {
                aiStatus.classList.add('hidden');
            }
        }

        function applyAiFilters(filters) {
            if (!filters || typeof filters !== 'object') return;
            const mapping = {
                max_price: 'max_price',
                min_price: 'min_price',
                min_rooms: 'min_rooms',
                max_rooms: 'max_rooms',
                min_floor: 'min_floor',
                max_floor: 'max_floor'
            };

            Object.entries(mapping).forEach(([key, inputName]) => {
                if (filters[key] !== undefined) {
                    const input = document.querySelector(`[name="${inputName}"]`);
                    if (input) {
                        input.value = filters[key];
                    }
                }
            });

            if (Array.isArray(filters.preferred_districts) && filters.preferred_districts.length) {
                const cityInput = document.querySelector('[name="city"]');
                if (cityInput) {
                    cityInput.value = filters.preferred_districts[0];
                }
            }

            if (sortSelect) {
                sortSelect.value = 'ai';
            }
        }

        if (aiSend && aiMessage && aiResponse) {
            aiSend.addEventListener('click', async () => {
                aiResponse.classList.add('hidden');
                aiResponse.textContent = '';
                hideAiStatus();

                aiSend.disabled = true;
                aiSend.textContent = 'Wysyłanie...';
                showAiStatus('Analizuję Twój opis i dobieram filtry...');

                const payload = new URLSearchParams({ message: aiMessage.value });

                try {
                    const aiEndpoint = 'ai-chat.php';
                    const res = await fetch(aiEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: payload,
                    });

                    const rawResponse = await res.text();
                    let data;

                    try {
                        data = JSON.parse(rawResponse);
                    } catch (parseError) {
                        throw new Error('Nie udało się odczytać odpowiedzi AI.');
                    }

                    if (!res.ok || data.error) {
                        throw new Error(data?.error || 'AI niedostępne. Spróbuj ponownie.');
                    }

                    aiResponse.innerHTML = '';
                    if (data.reply) {
                        aiResponse.innerHTML += `<div class="font-semibold mb-1">${escapeHtml(data.reply)}</div>`;
                    }
                    if (data.filters) {
                        aiResponse.innerHTML += `<div class="mt-1 text-slate-600">Filtry: ${escapeHtml(JSON.stringify(data.filters))}</div>`;
                        applyAiFilters(data.filters);
                    }
                    if (data.weights) {
                        aiResponse.innerHTML += `<div class="mt-1 text-slate-600">Wagi: ${escapeHtml(JSON.stringify(data.weights))}</div>`;
                    }
                } catch (error) {
                    aiResponse.textContent = error?.message || 'Nie udało się połączyć z AI.';
                }

                aiResponse.classList.remove('hidden');
                hideAiStatus();
                aiSend.disabled = false;
                aiSend.textContent = 'Wyślij do AI';
            });
        }

        if (Array.isArray(mapOffersData) && mapOffersData.length && typeof L !== 'undefined') {
            const validOffers = mapOffersData.filter(offer => typeof offer.lat === 'number' && typeof offer.lng === 'number');
            if (validOffers.length) {
                offersMap = L.map('offers-map');
                const [first] = validOffers;
                offersMap.setView([first.lat, first.lng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
                }).addTo(offersMap);

                const bounds = [];
                validOffers.forEach(offer => {
                    const marker = L.marker([offer.lat, offer.lng]).addTo(offersMap);
                    const nearest = findNearestPoi(offer.city, offer.lat, offer.lng);
                    const priceText = offer.price ? new Intl.NumberFormat('pl-PL').format(offer.price) + ' PLN' : 'Cena dostępna w ogłoszeniu';
                    const poiText = nearest ? `<br><em>Najbliżej:</em> ${escapeHtml(nearest.name)} (${nearest.distance.toFixed(1)} km)` : '';
                    marker.bindPopup(
                        `<strong>${escapeHtml(offer.title)}</strong><br>${escapeHtml(offer.city)}, ${escapeHtml(offer.street)}<br>${priceText}${poiText}`
                    );
                    bounds.push([offer.lat, offer.lng]);
                });

                if (bounds.length > 1) {
                    offersMap.fitBounds(bounds, { padding: [32, 32] });
                }
            }
        }

        document.querySelectorAll('.js-poi').forEach(node => {
            const lat = parseFloat(node.dataset.lat);
            const lng = parseFloat(node.dataset.lng);
            const city = node.dataset.city;
            if (Number.isNaN(lat) || Number.isNaN(lng)) {
                node.textContent = 'Brak danych lokalizacyjnych.';
                return;
            }
            const nearest = findNearestPoi(city, lat, lng);
            if (nearest) {
                node.textContent = `Najbliżej: ${nearest.name} (${nearest.distance.toFixed(1)} km)`;
            } else {
                node.textContent = 'Brak danych o pobliskich punktach zainteresowania.';
            }
        });

        // Toggle filters on mobile
        const toggleFilters = document.getElementById('toggle-filters');
        const showFilters = document.getElementById('show-filters');
        const filtersForm = document.getElementById('filters-form');

        if (toggleFilters && filtersForm) {
            toggleFilters.addEventListener('click', () => {
                filtersForm.classList.toggle('hidden');
                toggleFilters.classList.toggle('rotate-180');
            });
        }

        if (showFilters && filtersForm) {
            showFilters.addEventListener('click', () => {
                filtersForm.classList.toggle('hidden');
                // Scroll to filters
                document.querySelector('aside').scrollIntoView({ behavior: 'smooth' });
            });
        }

        // Hide filters on mobile by default
        if (window.innerWidth < 1024) {
            if (filtersForm) filtersForm.classList.add('hidden');
        }
    </script>

</body>
</html>
