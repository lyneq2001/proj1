<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Apartments - Apartment Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            600: '#A0D9A0',
                            700: '#8CCF83',
                        },
                        secondary: {
                            500: '#6B7280',
                            600: '#4B5563',
                        },
                        accent: {
                            500: '#A0D9A0',
                            600: '#8CCF83',
                        },
                        dark: '#111827',
                        light: '#F9FAFB',
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                    boxShadow: {
                        'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                        'card-hover': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
                    },
                },
            },
        }
    </script>
    <style>
        .offer-card {
            transition: all 0.3s ease;
        }
        .offer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .filter-section {
            transition: all 0.3s ease;
        }
        .filter-section:hover {
            transform: translateY(-2px);
        }
        .checkbox-label {
            transition: all 0.2s ease;
        }
        .checkbox-label:hover {
            background-color: #F3F4F6;
        }
        .toggle-filters {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 dark:text-white min-h-screen font-sans">
    <?php include 'header.php'; ?>
    <main class="container mx-auto px-4 py-8">
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
            <div class="mb-6 p-4 rounded-lg shadow <?php echo $flash['type'] === 'error' ? 'bg-red-100 text-red-700 border-l-4 border-red-500' : 'bg-green-100 text-green-700 border-l-4 border-green-500'; ?> flex items-start">
                <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $flash['type'] === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M5 13l4 4L19 7'; ?>"></path>
                </svg>
                <div><?php echo htmlspecialchars($flash['message']); ?></div>
            </div>
        <?php endif; ?>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filters Sidebar -->
            <aside class="w-full lg:w-80 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-card p-6 sticky top-4">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-dark">Filtry</h2>
                        <button id="toggle-filters" class="lg:hidden text-primary-600 hover:text-primary-700 toggle-filters">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    <form method="GET" action="index.php" id="filters-form" class="space-y-6">
                        <input type="hidden" name="action" value="search">

                        <!-- Location Filters -->
                        <div class="filter-section">
                            <h3 class="font-medium text-dark mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Lokalizacja
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-secondary-500 text-sm font-medium mb-1">Miasto</label>
                                    <input type="text" name="city" value="<?php echo htmlspecialchars($_GET['city'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition">
                                </div>
                                <div>
                                    <label class="block text-secondary-500 text-sm font-medium mb-1">Ulica</label>
                                    <input type="text" name="street" value="<?php echo htmlspecialchars($_GET['street'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition">
                                </div>
                                <div>
                                    <label class="block text-secondary-500 text-sm font-medium mb-1">Maksymalna odległość (km)</label>
                                    <input type="number" step="1" name="distance_km" value="<?php echo htmlspecialchars($_GET['distance_km'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition">
                                </div>
                            </div>
                        </div>

                        <!-- Price & Size -->
                        <div class="filter-section">
                            <h3 class="font-medium text-dark mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Cena i metraż
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-secondary-500 text-sm font-medium mb-1">Cena od (PLN)</label>
                                    <input type="number" step="1" name="min_price" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition">
                                </div>
                                <div>
                                    <label class="block text-secondary-500 text-sm font-medium mb-1">Cena do (PLN)</label>
                                    <input type="number" step="1" name="max_price" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition">
                                </div>
                                <div>
                                    <label class="block text-secondary-500 text-sm font-medium mb-1">Min. powierzchnia (m²)</label>
                                    <input type="number" name="min_size" value="<?php echo htmlspecialchars($_GET['min_size'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition">
                                </div>
                            </div>
                        </div>

                        <!-- Property Type -->
                        <div class="filter-section">
                            <h3 class="font-medium text-dark mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Typ nieruchomości
                            </h3>
                            <div>
                                <label class="block text-secondary-500 text-sm font-medium mb-1">Rodzaj budynku</label>
                                <select name="building_type" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition">
                                    <option value="">Dowolny</option>
                                    <option value="apartment" <?php echo ($_GET['building_type'] ?? '') == 'apartment' ? 'selected' : ''; ?>>Mieszkanie</option>
                                    <option value="block" <?php echo ($_GET['building_type'] ?? '') == 'block' ? 'selected' : ''; ?>>Blok mieszkalny</option>
                                    <option value="house" <?php echo ($_GET['building_type'] ?? '') == 'house' ? 'selected' : ''; ?>>Dom</option>
                                </select>
                            </div>
                        </div>

                        <!-- Rooms & Floors -->
                        <div class="filter-section">
                            <h3 class="font-medium text-dark mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Pokoje i piętra
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-secondary-500 text-sm font-medium mb-1">Min. liczba pokoi</label>
                                    <input type="number" name="min_rooms" value="<?php echo htmlspecialchars($_GET['min_rooms'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition">
                                </div>
                                <div>
                                    <label class="block text-secondary-500 text-sm font-medium mb-1">Min. liczba łazienek</label>
                                    <input type="number" name="min_bathrooms" value="<?php echo htmlspecialchars($_GET['min_bathrooms'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition">
                                </div>
                                <div>
                                    <label class="block text-secondary-500 text-sm font-medium mb-1">Min. piętro</label>
                                    <input type="number" name="min_floor" value="<?php echo htmlspecialchars($_GET['min_floor'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition">
                                </div>
                                <div>
                                    <label class="block text-secondary-500 text-sm font-medium mb-1">Maks. piętro</label>
                                    <input type="number" name="max_floor" value="<?php echo htmlspecialchars($_GET['max_floor'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition">
                                </div>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <div class="filter-section">
                            <h3 class="font-medium text-dark mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Udogodnienia
                            </h3>
                            <div class="space-y-2">
                                <label class="checkbox-label flex items-center p-2 border border-gray-200 rounded-lg cursor-pointer">
                                    <input type="checkbox" name="has_balcony" value="1" <?php echo isset($_GET['has_balcony']) ? 'checked' : ''; ?> class="h-4 w-4 text-primary-600 focus:ring-primary-600 border-gray-300 rounded">
                                    <span class="ml-2 text-secondary-500 text-sm">Balkon</span>
                                </label>
                                <label class="checkbox-label flex items-center p-2 border border-gray-200 rounded-lg cursor-pointer">
                                    <input type="checkbox" name="has_elevator" value="1" <?php echo isset($_GET['has_elevator']) ? 'checked' : ''; ?> class="h-4 w-4 text-primary-600 focus:ring-primary-600 border-gray-300 rounded">
                                    <span class="ml-2 text-secondary-500 text-sm">Winda</span>
                                </label>
                                <label class="checkbox-label flex items-center p-2 border border-gray-200 rounded-lg cursor-pointer">
                                    <input type="checkbox" name="parking" value="1" <?php echo isset($_GET['parking']) ? 'checked' : ''; ?> class="h-4 w-4 text-primary-600 focus:ring-primary-600 border-gray-300 rounded">
                                    <span class="ml-2 text-secondary-500 text-sm">Parking</span>
                                </label>
                                <label class="checkbox-label flex items-center p-2 border border-gray-200 rounded-lg cursor-pointer">
                                    <input type="checkbox" name="furnished" value="1" <?php echo isset($_GET['furnished']) ? 'checked' : ''; ?> class="h-4 w-4 text-primary-600 focus:ring-primary-600 border-gray-300 rounded">
                                    <span class="ml-2 text-secondary-500 text-sm">Umeblowane</span>
                                </label>
                            </div>
                        </div>

                        <!-- Sort & Submit -->
                        <div class="filter-section">
                            <div class="mb-3">
                                <label class="block text-secondary-500 text-sm font-medium mb-1">Sortuj według</label>
                                <select name="sort" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition">
                                    <option value="price_asc" <?php echo ($_GET['sort'] ?? '') == 'price_asc' ? 'selected' : ''; ?>>Cena: rosnąco</option>
                                    <option value="price_desc" <?php echo ($_GET['sort'] ?? '') == 'price_desc' ? 'selected' : ''; ?>>Cena: malejąco</option>
                                    <option value="date_asc" <?php echo ($_GET['sort'] ?? '') == 'date_asc' ? 'selected' : ''; ?>>Data: od najstarszych</option>
                                    <option value="date_desc" <?php echo ($_GET['sort'] ?? '') == 'date_desc' ? 'selected' : ''; ?>>Data: od najnowszych</option>
                                    <option value="size_asc" <?php echo ($_GET['sort'] ?? '') == 'size_asc' ? 'selected' : ''; ?>>Powierzchnia: rosnąco</option>
                                    <option value="size_desc" <?php echo ($_GET['sort'] ?? '') == 'size_desc' ? 'selected' : ''; ?>>Powierzchnia: malejąco</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white p-2 rounded-lg transition font-medium">
                                Zastosuj filtry
                            </button>
                            <?php if (!empty($_GET)): ?>
                                <a href="index.php?action=search" class="block w-full text-center mt-2 text-secondary-500 hover:text-dark text-sm">Wyczyść filtry</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex-1">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-dark">Dostępne oferty</h1>
                    <p class="text-secondary-500">
                        Znaleziono <?php echo count($offers); ?> ofert
                    </p>
                </div>

                <?php if (empty($offers)): ?>
                    <div class="bg-white rounded-xl shadow-card p-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-secondary-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-dark mb-2">Brak wyników</h3>
                        <p class="text-secondary-500 mb-4">Spróbuj zmienić filtry wyszukiwania</p>
                        <button id="show-filters" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition font-medium lg:hidden">
                            Pokaż filtry
                        </button>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php foreach ($offers as $offer): ?>
                            <div class="bg-white rounded-xl shadow-card overflow-hidden offer-card">
                                <a href="index.php?action=view_offer&offer_id=<?php echo $offer['id']; ?>" class="block">
                                    <?php if (!empty($offer['primary_image'])): ?>
                                        <div class="w-full h-48 overflow-hidden relative">
                                            <img src="<?php echo htmlspecialchars($offer['primary_image']); ?>" alt="Property image" class="w-full h-full object-cover">
                                            <div class="absolute top-3 right-3 bg-white/90 rounded-full p-1.5 shadow">
                                                <span class="text-sm font-medium px-2"><?php echo htmlspecialchars($offer['visits']); ?> views</span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-full h-48 bg-gray-100 flex items-center justify-center relative">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <div class="absolute top-3 right-3 bg-white/90 rounded-full p-1.5 shadow">
                                                <span class="text-sm font-medium px-2"><?php echo htmlspecialchars($offer['visits']); ?> views</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="p-5">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="text-lg font-semibold text-dark truncate"><?php echo htmlspecialchars($offer['title']); ?></h3>
                                            <span class="text-lg font-bold text-primary-600 whitespace-nowrap"><?php echo htmlspecialchars($offer['price']); ?> PLN</span>
                                        </div>
                                        <p class="text-secondary-500 text-sm mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <?php echo htmlspecialchars($offer['city']); ?>, <?php echo htmlspecialchars($offer['street']); ?>
                                        </p>
                                        <div class="flex flex-wrap gap-3 mb-3">
                                            <span class="text-xs bg-gray-100 text-secondary-600 px-2 py-1 rounded">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                                </svg>
                                                <?php echo htmlspecialchars($offer['size']); ?> m²
                                            </span>
                                            <span class="text-xs bg-gray-100 text-secondary-600 px-2 py-1 rounded">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                <?php echo htmlspecialchars($offer['rooms']); ?> rooms
                                            </span>
                                            <?php if ($offer['has_balcony']): ?>
                                                <span class="text-xs bg-gray-100 text-secondary-600 px-2 py-1 rounded">
                                                    Balcony
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($offer['has_elevator']): ?>
                                                <span class="text-xs bg-gray-100 text-secondary-600 px-2 py-1 rounded">
                                                    Elevator
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-secondary-500 text-sm line-clamp-2"><?php echo htmlspecialchars($offer['description']); ?></p>
                                    </div>
                                </a>
                                <?php if (isLoggedIn() && $offer['user_id'] != $_SESSION['user_id']): ?>
                                    <div class="px-5 pb-5 flex justify-between items-center">
                                        <a href="index.php?action=dashboard&offer_id=<?php echo $offer['id']; ?>&receiver_id=<?php echo $offer['user_id']; ?>" class="text-sm text-white bg-primary-600 hover:bg-primary-700 px-4 py-2 rounded-lg transition font-medium">
                                            Kontakt z właścicielem
                                        </a>
                                        <?php if (isFavorite($_SESSION['user_id'], $offer['id'])): ?>
                                            <a href="index.php?action=toggle_favorite&offer_id=<?php echo $offer['id']; ?>" class="text-sm text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition font-medium flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Usuń
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?action=toggle_favorite&offer_id=<?php echo $offer['id']; ?>" class="text-sm text-white bg-accent-500 hover:bg-accent-600 px-4 py-2 rounded-lg transition font-medium flex items-center">
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
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
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