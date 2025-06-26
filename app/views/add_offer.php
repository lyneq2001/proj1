<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Offer - Apartment Rental</title>
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
                            600: '#1D4ED8',
                            700: '#1E40AF',
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
                    },
                },
            },
        }
    </script>
    <style>
        .form-section {
            transition: all 0.3s ease;
        }
        .form-section:hover {
            transform: translateY(-2px);
        }
        .checkbox-label {
            transition: all 0.2s ease;
        }
        .checkbox-label:hover {
            background-color: #F3F4F6;
        }
        .image-preview-container {
            transition: all 0.2s ease;
        }
        .image-preview-container:hover {
            transform: scale(1.02);
        }
        .image-preview-container.primary {
            box-shadow: 0 0 0 3px #1D4ED8;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 dark:text-white min-h-screen font-sans">
    <?php include 'header.php'; ?>
    <main class="container mx-auto px-4 py-8">
        <?php
        $flash = getFlashMessage();
        $errors = getFormErrors();
        clearOldInput();
        if ($flash):
        ?>
            <div class="mb-6 p-4 rounded-lg shadow <?php echo $flash['type'] === 'error' ? 'bg-red-100 text-red-700 border-l-4 border-red-500' : 'bg-green-100 text-green-700 border-l-4 border-green-500'; ?> flex items-start">
                <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $flash['type'] === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M5 13l4 4L19 7'; ?>"></path>
                </svg>
                <div><?php echo htmlspecialchars($flash['message']); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!isLoggedIn()): ?>
            <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-card text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h2 class="text-2xl font-bold text-dark mb-2">Musisz być zalogowany</h2>
                <p class="text-secondary-500 mb-6">Zaloguj się, aby dodać nowe ogłoszenie.</p>
                <div class="flex justify-center space-x-4">
                    <a href="index.php?action=login" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition font-medium">
                        Zaloguj się
                    </a>
                    <a href="index.php?action=register" class="px-6 py-2 border border-gray-300 hover:bg-gray-50 rounded-lg transition font-medium">
                        Zarejestruj
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="max-w-4xl mx-auto">
                <div class="mb-8 text-center">
                    <h1 class="text-3xl font-bold text-dark mb-2">Dodaj nowe ogłoszenie</h1>
                    <p class="text-secondary-500">Uzupełnij dane nieruchomości, aby dodać ogłoszenie</p>
                </div>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <input type="hidden" name="primary_image" id="primary_image" value="0">

                    <!-- Basic Information Section -->
                    <div class="bg-white rounded-xl shadow-card p-6 form-section">
                        <h2 class="text-xl font-semibold text-dark mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Informacje podstawowe
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Tytuł*</label>
                                <input type="text" name="title" placeholder="np. Nowoczesne mieszkanie w centrum"
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition <?php echo isset($errors['title']) ? 'border-red-500' : ''; ?>"
                                       value="<?php echo htmlspecialchars(getOldInput('title')); ?>" required>
                                <?php if (isset($errors['title'])): ?>
                                    <p class="text-red-500 text-sm mt-1"><?php echo htmlspecialchars($errors['title']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Typ nieruchomości*</label>
                                <select name="building_type"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition <?php echo isset($errors['building_type']) ? 'border-red-500' : ''; ?>"
                                        required>
                                    <option value="" disabled <?php echo getOldInput('building_type') == '' ? 'selected' : ''; ?>>Wybierz typ</option>
                                    <option value="apartment" <?php echo getOldInput('building_type') == 'apartment' ? 'selected' : ''; ?>>Mieszkanie</option>
                                    <option value="block" <?php echo getOldInput('building_type') == 'block' ? 'selected' : ''; ?>>Blok mieszkalny</option>
                                    <option value="house" <?php echo getOldInput('building_type') == 'house' ? 'selected' : ''; ?>>Dom wolnostojący</option>
                                    <option value="studio" <?php echo getOldInput('building_type') == 'studio' ? 'selected' : ''; ?>>Kawalerka</option>
                                    <option value="loft" <?php echo getOldInput('building_type') == 'loft' ? 'selected' : ''; ?>>Loft</option>
                                </select>
                                <?php if (isset($errors['building_type'])): ?>
                                    <p class="text-red-500 text-sm mt-1"><?php echo htmlspecialchars($errors['building_type']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="mb-4 md:col-span-2">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Opis*</label>
                                <textarea name="description" placeholder="Opisz nieruchomość..."
                                          class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition <?php echo isset($errors['description']) ? 'border-red-500' : ''; ?>"
                                          rows="4" required><?php echo htmlspecialchars(getOldInput('description')); ?></textarea>
                                <?php if (isset($errors['description'])): ?>
                                    <p class="text-red-500 text-sm mt-1"><?php echo htmlspecialchars($errors['description']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Location Section -->
                    <div class="bg-white rounded-xl shadow-card p-6 form-section">
                        <h2 class="text-xl font-semibold text-dark mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Szczegóły lokalizacji
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Miasto*</label>
                                <input type="text" name="city" id="city" placeholder="np. Warszawa"
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition <?php echo isset($errors['city']) ? 'border-red-500' : ''; ?>"
                                       value="<?php echo htmlspecialchars(getOldInput('city')); ?>" required>
                                <?php if (isset($errors['city'])): ?>
                                    <p class="text-red-500 text-sm mt-1"><?php echo htmlspecialchars($errors['city']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Ulica*</label>
                                <input type="text" name="street" id="street" placeholder="np. Główna 14"
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition <?php echo isset($errors['street']) ? 'border-red-500' : ''; ?>"
                                       value="<?php echo htmlspecialchars(getOldInput('street')); ?>" required>
                                <?php if (isset($errors['street'])): ?>
                                    <p class="text-red-500 text-sm mt-1"><?php echo htmlspecialchars($errors['street']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Size Section -->
                    <div class="bg-white rounded-xl shadow-card p-6 form-section">
                        <h2 class="text-xl font-semibold text-dark mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Cena i metraż
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="mb-4">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Cena (PLN)*</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-secondary-500">PLN</span>
                                    <input type="number" step="1" name="price" placeholder="np. 2500"
                                           class="w-full pl-12 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition <?php echo isset($errors['price']) ? 'border-red-500' : ''; ?>"
                                           value="<?php echo htmlspecialchars(getOldInput('price')); ?>" required>
                                    <?php if (isset($errors['price'])): ?>
                                        <p class="text-red-500 text-sm mt-1"><?php echo htmlspecialchars($errors['price']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Powierzchnia (m²)*</label>
                                <div class="relative">
                                    <span class="absolute right-3 top-3 text-secondary-500">m²</span>
                                    <input type="number" name="size" placeholder="np. 65"
                                           class="w-full pr-12 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition <?php echo isset($errors['size']) ? 'border-red-500' : ''; ?>"
                                           value="<?php echo htmlspecialchars(getOldInput('size')); ?>" required>
                                    <?php if (isset($errors['size'])): ?>
                                        <p class="text-red-500 text-sm mt-1"><?php echo htmlspecialchars($errors['size']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Piętro</label>
                                <input type="number" name="floor" placeholder="np. 3"
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition"
                                       value="<?php echo htmlspecialchars(getOldInput('floor')); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Property Features Section -->
                    <div class="bg-white rounded-xl shadow-card p-6 form-section">
                        <h2 class="text-xl font-semibold text-dark mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Cechy nieruchomości
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="mb-4">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Pokoje*</label>
                                <select name="rooms"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition <?php echo isset($errors['rooms']) ? 'border-red-500' : ''; ?>" required>
                                    <option value="" disabled <?php echo getOldInput('rooms') == '' ? 'selected' : ''; ?>>Wybierz</option>
                                    <option value="1" <?php echo getOldInput('rooms') == '1' ? 'selected' : ''; ?>>1</option>
                                    <option value="2" <?php echo getOldInput('rooms') == '2' ? 'selected' : ''; ?>>2</option>
                                    <option value="3" <?php echo getOldInput('rooms') == '3' ? 'selected' : ''; ?>>3</option>
                                    <option value="4" <?php echo getOldInput('rooms') == '4' ? 'selected' : ''; ?>>4</option>
                                    <option value="5+" <?php echo getOldInput('rooms') == '5+' ? 'selected' : ''; ?>>5+</option>
                                </select>
                                <?php if (isset($errors['rooms'])): ?>
                                    <p class="text-red-500 text-sm mt-1"><?php echo htmlspecialchars($errors['rooms']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Łazienki*</label>
                                <select name="bathrooms"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition <?php echo isset($errors['bathrooms']) ? 'border-red-500' : ''; ?>" required>
                                    <option value="" disabled <?php echo getOldInput('bathrooms') == '' ? 'selected' : ''; ?>>Select</option>
                                    <option value="1" <?php echo getOldInput('bathrooms') == '1' ? 'selected' : ''; ?>>1</option>
                                    <option value="2" <?php echo getOldInput('bathrooms') == '2' ? 'selected' : ''; ?>>2</option>
                                    <option value="3" <?php echo getOldInput('bathrooms') == '3' ? 'selected' : ''; ?>>3</option>
                                    <option value="4+" <?php echo getOldInput('bathrooms') == '4+' ? 'selected' : ''; ?>>4+</option>
                                </select>
                                <?php if (isset($errors['bathrooms'])): ?>
                                    <p class="text-red-500 text-sm mt-1"><?php echo htmlspecialchars($errors['bathrooms']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Rodzaj ogrzewania*</label>
                                <select name="heating_type"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition <?php echo isset($errors['heating_type']) ? 'border-red-500' : ''; ?>" required>
                                    <option value="" disabled <?php echo getOldInput('heating_type') == '' ? 'selected' : ''; ?>>Wybierz</option>
                                    <option value="gas" <?php echo getOldInput('heating_type') == 'gas' ? 'selected' : ''; ?>>Gazowe</option>
                                    <option value="electric" <?php echo getOldInput('heating_type') == 'electric' ? 'selected' : ''; ?>>Elektryczne</option>
                                    <option value="district" <?php echo getOldInput('heating_type') == 'district' ? 'selected' : ''; ?>>Miejskie</option>
                                    <option value="other" <?php echo getOldInput('heating_type') == 'other' ? 'selected' : ''; ?>>Inne</option>
                                </select>
                                <?php if (isset($errors['heating_type'])): ?>
                                    <p class="text-red-500 text-sm mt-1"><?php echo htmlspecialchars($errors['heating_type']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Stan*</label>
                                <select name="condition_type"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition <?php echo isset($errors['condition_type']) ? 'border-red-500' : ''; ?>" required>
                                    <option value="" disabled <?php echo getOldInput('condition_type') == '' ? 'selected' : ''; ?>>Wybierz</option>
                                    <option value="new" <?php echo getOldInput('condition_type') == 'new' ? 'selected' : ''; ?>>Nowe</option>
                                    <option value="renovated" <?php echo getOldInput('condition_type') == 'renovated' ? 'selected' : ''; ?>>Po remoncie</option>
                                    <option value="used" <?php echo getOldInput('condition_type') == 'used' ? 'selected' : ''; ?>>Używane</option>
                                    <option value="to_renovate" <?php echo getOldInput('condition_type') == 'to_renovate' ? 'selected' : ''; ?>>Do remontu</option>
                                </select>
                                <?php if (isset($errors['condition_type'])): ?>
                                    <p class="text-red-500 text-sm mt-1"><?php echo htmlspecialchars($errors['condition_type']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Rok budowy</label>
                                <input type="number" name="year_built" placeholder="np. 2010"
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition"
                                       value="<?php echo htmlspecialchars(getOldInput('year_built')); ?>">
                            </div>
                            <div class="mb-4">
                                <label class="block text-secondary-600 text-sm font-medium mb-1">Dostępne od</label>
                                <input type="date" name="available_from"
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition"
                                       value="<?php echo htmlspecialchars(getOldInput('available_from')); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Amenities Section -->
                    <div class="bg-white rounded-xl shadow-card p-6 form-section">
                        <h2 class="text-xl font-semibold text-dark mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            Udogodnienia
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            <label class="checkbox-label flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer">
                                <input type="checkbox" name="has_balcony" value="1" class="h-5 w-5 text-primary-600 focus:ring-primary-600 border-gray-300 rounded" <?php echo getOldInput('has_balcony') ? 'checked' : ''; ?> >
                                <span class="ml-3 text-secondary-600 text-sm font-medium">Balkon</span>
                            </label>
                            <label class="checkbox-label flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer">
                                <input type="checkbox" name="has_elevator" value="1" class="h-5 w-5 text-primary-600 focus:ring-primary-600 border-gray-300 rounded" <?php echo getOldInput('has_elevator') ? 'checked' : ''; ?> >
                                <span class="ml-3 text-secondary-600 text-sm font-medium">Winda</span>
                            </label>
                            <label class="checkbox-label flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer">
                                <input type="checkbox" name="parking" value="1" class="h-5 w-5 text-primary-600 focus:ring-primary-600 border-gray-300 rounded" <?php echo getOldInput('parking') ? 'checked' : ''; ?> >
                                <span class="ml-3 text-secondary-600 text-sm font-medium">Parking</span>
                            </label>
                            <label class="checkbox-label flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer">
                                <input type="checkbox" name="garage" value="1" class="h-5 w-5 text-primary-600 focus:ring-primary-600 border-gray-300 rounded" <?php echo getOldInput('garage') ? 'checked' : ''; ?> >
                                <span class="ml-3 text-secondary-600 text-sm font-medium">Garaż</span>
                            </label>
                            <label class="checkbox-label flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer">
                                <input type="checkbox" name="garden" value="1" class="h-5 w-5 text-primary-600 focus:ring-primary-600 border-gray-300 rounded" <?php echo getOldInput('garden') ? 'checked' : ''; ?> >
                                <span class="ml-3 text-secondary-600 text-sm font-medium">Ogród</span>
                            </label>
                            <label class="checkbox-label flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer">
                                <input type="checkbox" name="furnished" value="1" class="h-5 w-5 text-primary-600 focus:ring-primary-600 border-gray-300 rounded" <?php echo getOldInput('furnished') ? 'checked' : ''; ?> >
                                <span class="ml-3 text-secondary-600 text-sm font-medium">Umeblowane</span>
                            </label>
                            <label class="checkbox-label flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer">
                                <input type="checkbox" name="pets_allowed" value="1" class="h-5 w-5 text-primary-600 focus:ring-primary-600 border-gray-300 rounded" <?php echo getOldInput('pets_allowed') ? 'checked' : ''; ?> >
                                <span class="ml-3 text-secondary-600 text-sm font-medium">Zwierzęta dozwolone</span>
                            </label>
                        </div>
                    </div>

                    <!-- Images Section -->
                    <div class="bg-white rounded-xl shadow-card p-6 form-section">
                        <h2 class="text-xl font-semibold text-dark mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Zdjęcia nieruchomości
                        </h2>
                        <div class="mb-4">
                            <label class="block text-secondary-600 text-sm font-medium mb-1">Prześlij zdjęcia (JPEG/PNG, maks. 5)*</label>
                            <div class="flex items-center justify-center w-full">
                                <label for="images" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-secondary-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <p class="text-sm text-secondary-500">
                                            <span class="font-semibold">Kliknij, aby przesłać</span> lub upuść pliki
                                        </p>
                                        <p class="text-xs text-secondary-400">JPEG lub PNG (max 5MB)</p>
                                    </div>
                                    <input id="images" name="images[]" type="file" accept="image/jpeg,image/png" multiple onchange="updatePrimaryImageOptions()" class="hidden" required>
                                </label>
                            </div>
                        </div>
                        <div class="mb-2">
                            <p class="text-secondary-500 text-sm">Kliknij zdjęcie, aby ustawić je jako główne (wyświetlane jako pierwsze)</p>
                        </div>
                        <div id="image-preview" class="flex flex-wrap gap-4 mt-2"></div>
                    </div>

                    <!-- Submit Section -->
                    <div class="flex justify-end space-x-4">
                        <a href="index.php?action=dashboard" class="px-6 py-3 border border-gray-300 hover:bg-gray-50 rounded-lg transition font-medium">
                            Anuluj
                        </a>
                        <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition font-medium flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Dodaj ogłoszenie
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>

<script>
    function updatePrimaryImageOptions() {
        const imageInput = document.getElementById('images');
        const previewContainer = document.getElementById('image-preview');
        const primaryImageInput = document.getElementById('primary_image');
        previewContainer.innerHTML = '';
        
        if (imageInput.files.length > 5) {
            alert('Możesz przesłać maksymalnie 5 zdjęć');
            imageInput.value = '';
            return;
        }

        for (let i = 0; i < imageInput.files.length; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.createElement('div');
                imgContainer.className = `relative cursor-pointer image-preview-container ${i === 0 ? 'primary' : ''}`;
                imgContainer.style.width = '150px';
                imgContainer.style.height = '150px';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-full h-full object-cover rounded-lg';
                img.alt = 'Property image ' + (i + 1);
                
                const overlay = document.createElement('div');
                overlay.className = 'absolute inset-0 bg-black/20 rounded-lg opacity-0 hover:opacity-100 transition flex items-center justify-center';
                
                const checkIcon = document.createElement('div');
                checkIcon.className = 'bg-white rounded-full p-1';
                checkIcon.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                `;
                
                overlay.appendChild(checkIcon);
                imgContainer.appendChild(img);
                imgContainer.appendChild(overlay);
                
                imgContainer.addEventListener('click', () => {
                    // Remove primary class from all images
                    previewContainer.querySelectorAll('.image-preview-container').forEach(container => {
                        container.classList.remove('primary');
                    });
                    // Add primary class to clicked image
                    imgContainer.classList.add('primary');
                    // Update hidden input
                    primaryImageInput.value = i;
                });
                
                previewContainer.appendChild(imgContainer);
            };
            reader.readAsDataURL(imageInput.files[i]);
        }
        
        // Set first image as primary by default
        if (imageInput.files.length > 0) {
            primaryImageInput.value = 0;
        }
    }

    // Drag and drop functionality
    const dropArea = document.querySelector('label[for="images"]');
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        dropArea.classList.add('border-primary-600', 'bg-gray-100');
    }

    function unhighlight() {
        dropArea.classList.remove('border-primary-600', 'bg-gray-100');
    }

    dropArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        document.getElementById('images').files = files;
        updatePrimaryImageOptions();
    }
</script>