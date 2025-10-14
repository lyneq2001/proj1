<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Offer - Apartment Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    
    <style>
        .form-section {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 30px 60px -45px rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(10px);
        }
        .form-section:hover {
            transform: translateY(-4px);
            box-shadow: 0 35px 70px -40px rgba(30, 64, 175, 0.25);
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
        .image-preview-container {
            transition: all 0.2s ease;
            border-radius: 0.75rem;
            border: 2px solid transparent;
        }
        .image-preview-container:hover {
            transform: scale(1.05);
        }
        .image-preview-container.primary {
            box-shadow: 0 0 0 3px #3b82f6;
            border-color: #3b82f6;
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
        .form-input.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }
        .drop-zone {
            border: 2px dashed rgba(148, 163, 184, 0.4);
            border-radius: 1rem;
            transition: all 0.3s ease;
            background: rgba(248, 250, 252, 0.8);
        }
        .drop-zone:hover, .drop-zone.dragover {
            border-color: #3b82f6;
            background: rgba(239, 246, 255, 0.8);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen font-roboto">
    <?php include 'header.php'; ?>
    
    <main class="page-shell py-12">
        <div class="container mx-auto px-4 sm:px-6">
            <!-- Page Heading -->
            <div class="page-heading">
                <span class="page-heading__eyebrow">Dodawanie ogłoszenia</span>
                <h1 class="page-heading__title">Stwórz nowe ogłoszenie</h1>
                <p class="page-heading__subtitle">Uzupełnij szczegóły nieruchomości, aby dotrzeć do zweryfikowanych najemców.</p>
            </div>

            <?php
            $flash = getFlashMessage();
            $errors = getFormErrors();
            clearOldInput();
            if ($flash):
            ?>
                <div class="glass-panel mb-8 p-6 flex items-start gap-4 <?php echo $flash['type'] === 'error' ? 'flash-error' : 'flash-success'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $flash['type'] === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M5 13l4 4L19 7'; ?>"></path>
                    </svg>
                    <div class="font-medium leading-relaxed text-lg"><?php echo htmlspecialchars($flash['message']); ?></div>
                </div>
            <?php endif; ?>

            <?php if (!isLoggedIn()): ?>
                <div class="max-w-2xl mx-auto glass-panel p-8 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h2 class="text-2xl font-playfair font-bold text-slate-800 mb-2">Musisz być zalogowany</h2>
                    <p class="text-slate-600 mb-6">Zaloguj się, aby dodać nowe ogłoszenie.</p>
                    <div class="flex justify-center space-x-4">
                        <a href="index.php?action=login" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl">
                            Zaloguj się
                        </a>
                        <a href="index.php?action=register" class="px-6 py-3 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-all duration-300 font-semibold">
                            Zarejestruj
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="max-w-4xl mx-auto">
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                        <input type="hidden" name="primary_image" id="primary_image" value="0">

                        <!-- Basic Information Section -->
                        <div class="form-section p-6">
                            <h2 class="text-2xl font-playfair font-bold text-slate-800 mb-6 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Informacje podstawowe
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Tytuł*</label>
                                    <input type="text" name="title" placeholder="np. Nowoczesne mieszkanie w centrum"
                                           class="w-full p-4 form-input <?php echo isset($errors['title']) ? 'error' : ''; ?>"
                                           value="<?php echo htmlspecialchars(getOldInput('title')); ?>" required>
                                    <?php if (isset($errors['title'])): ?>
                                        <p class="text-red-500 text-sm mt-2 font-medium"><?php echo htmlspecialchars($errors['title']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Typ nieruchomości*</label>
                                    <select name="building_type"
                                            class="w-full p-4 form-input <?php echo isset($errors['building_type']) ? 'error' : ''; ?>"
                                            required>
                                        <option value="" disabled <?php echo getOldInput('building_type') == '' ? 'selected' : ''; ?>>Wybierz typ</option>
                                        <option value="apartment" <?php echo getOldInput('building_type') == 'apartment' ? 'selected' : ''; ?>>Mieszkanie</option>
                                        <option value="block" <?php echo getOldInput('building_type') == 'block' ? 'selected' : ''; ?>>Blok mieszkalny</option>
                                        <option value="house" <?php echo getOldInput('building_type') == 'house' ? 'selected' : ''; ?>>Dom wolnostojący</option>
                                        <option value="studio" <?php echo getOldInput('building_type') == 'studio' ? 'selected' : ''; ?>>Kawalerka</option>
                                        <option value="loft" <?php echo getOldInput('building_type') == 'loft' ? 'selected' : ''; ?>>Loft</option>
                                    </select>
                                    <?php if (isset($errors['building_type'])): ?>
                                        <p class="text-red-500 text-sm mt-2 font-medium"><?php echo htmlspecialchars($errors['building_type']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Opis*</label>
                                    <textarea name="description" placeholder="Opisz nieruchomość..."
                                              class="w-full p-4 form-input <?php echo isset($errors['description']) ? 'error' : ''; ?>"
                                              rows="5" required><?php echo htmlspecialchars(getOldInput('description')); ?></textarea>
                                    <?php if (isset($errors['description'])): ?>
                                        <p class="text-red-500 text-sm mt-2 font-medium"><?php echo htmlspecialchars($errors['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Location Section -->
                        <div class="form-section p-6">
                            <h2 class="text-2xl font-playfair font-bold text-slate-800 mb-6 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Szczegóły lokalizacji
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Miasto*</label>
                                    <input type="text" name="city" id="city" placeholder="np. Warszawa"
                                           class="w-full p-4 form-input <?php echo isset($errors['city']) ? 'error' : ''; ?>"
                                           value="<?php echo htmlspecialchars(getOldInput('city')); ?>" required>
                                    <?php if (isset($errors['city'])): ?>
                                        <p class="text-red-500 text-sm mt-2 font-medium"><?php echo htmlspecialchars($errors['city']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Ulica*</label>
                                    <input type="text" name="street" id="street" placeholder="np. Główna 14"
                                           class="w-full p-4 form-input <?php echo isset($errors['street']) ? 'error' : ''; ?>"
                                           value="<?php echo htmlspecialchars(getOldInput('street')); ?>" required>
                                    <?php if (isset($errors['street'])): ?>
                                        <p class="text-red-500 text-sm mt-2 font-medium"><?php echo htmlspecialchars($errors['street']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing & Size Section -->
                        <div class="form-section p-6">
                            <h2 class="text-2xl font-playfair font-bold text-slate-800 mb-6 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Cena i metraż
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Cena (PLN)*</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-4 text-slate-500 font-medium">PLN</span>
                                        <input type="number" step="1" name="price" placeholder="np. 2500"
                                               class="w-full pl-14 p-4 form-input <?php echo isset($errors['price']) ? 'error' : ''; ?>"
                                               value="<?php echo htmlspecialchars(getOldInput('price')); ?>" required>
                                        <?php if (isset($errors['price'])): ?>
                                            <p class="text-red-500 text-sm mt-2 font-medium"><?php echo htmlspecialchars($errors['price']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Powierzchnia (m²)*</label>
                                    <div class="relative">
                                        <span class="absolute right-4 top-4 text-slate-500 font-medium">m²</span>
                                        <input type="number" name="size" placeholder="np. 65"
                                               class="w-full pr-14 p-4 form-input <?php echo isset($errors['size']) ? 'error' : ''; ?>"
                                               value="<?php echo htmlspecialchars(getOldInput('size')); ?>" required>
                                        <?php if (isset($errors['size'])): ?>
                                            <p class="text-red-500 text-sm mt-2 font-medium"><?php echo htmlspecialchars($errors['size']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Piętro</label>
                                    <input type="number" name="floor" placeholder="np. 3"
                                           class="w-full p-4 form-input"
                                           value="<?php echo htmlspecialchars(getOldInput('floor')); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Property Features Section -->
                        <div class="form-section p-6">
                            <h2 class="text-2xl font-playfair font-bold text-slate-800 mb-6 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Cechy nieruchomości
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Pokoje*</label>
                                    <select name="rooms"
                                            class="w-full p-4 form-input <?php echo isset($errors['rooms']) ? 'error' : ''; ?>" required>
                                        <option value="" disabled <?php echo getOldInput('rooms') == '' ? 'selected' : ''; ?>>Wybierz</option>
                                        <option value="1" <?php echo getOldInput('rooms') == '1' ? 'selected' : ''; ?>>1</option>
                                        <option value="2" <?php echo getOldInput('rooms') == '2' ? 'selected' : ''; ?>>2</option>
                                        <option value="3" <?php echo getOldInput('rooms') == '3' ? 'selected' : ''; ?>>3</option>
                                        <option value="4" <?php echo getOldInput('rooms') == '4' ? 'selected' : ''; ?>>4</option>
                                        <option value="5+" <?php echo getOldInput('rooms') == '5+' ? 'selected' : ''; ?>>5+</option>
                                    </select>
                                    <?php if (isset($errors['rooms'])): ?>
                                        <p class="text-red-500 text-sm mt-2 font-medium"><?php echo htmlspecialchars($errors['rooms']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Łazienki*</label>
                                    <select name="bathrooms"
                                            class="w-full p-4 form-input <?php echo isset($errors['bathrooms']) ? 'error' : ''; ?>" required>
                                        <option value="" disabled <?php echo getOldInput('bathrooms') == '' ? 'selected' : ''; ?>>Wybierz</option>
                                        <option value="1" <?php echo getOldInput('bathrooms') == '1' ? 'selected' : ''; ?>>1</option>
                                        <option value="2" <?php echo getOldInput('bathrooms') == '2' ? 'selected' : ''; ?>>2</option>
                                        <option value="3" <?php echo getOldInput('bathrooms') == '3' ? 'selected' : ''; ?>>3</option>
                                        <option value="4+" <?php echo getOldInput('bathrooms') == '4+' ? 'selected' : ''; ?>>4+</option>
                                    </select>
                                    <?php if (isset($errors['bathrooms'])): ?>
                                        <p class="text-red-500 text-sm mt-2 font-medium"><?php echo htmlspecialchars($errors['bathrooms']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Rodzaj ogrzewania*</label>
                                    <select name="heating_type"
                                            class="w-full p-4 form-input <?php echo isset($errors['heating_type']) ? 'error' : ''; ?>" required>
                                        <option value="" disabled <?php echo getOldInput('heating_type') == '' ? 'selected' : ''; ?>>Wybierz</option>
                                        <option value="gas" <?php echo getOldInput('heating_type') == 'gas' ? 'selected' : ''; ?>>Gazowe</option>
                                        <option value="electric" <?php echo getOldInput('heating_type') == 'electric' ? 'selected' : ''; ?>>Elektryczne</option>
                                        <option value="district" <?php echo getOldInput('heating_type') == 'district' ? 'selected' : ''; ?>>Miejskie</option>
                                        <option value="other" <?php echo getOldInput('heating_type') == 'other' ? 'selected' : ''; ?>>Inne</option>
                                    </select>
                                    <?php if (isset($errors['heating_type'])): ?>
                                        <p class="text-red-500 text-sm mt-2 font-medium"><?php echo htmlspecialchars($errors['heating_type']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Stan*</label>
                                    <select name="condition_type"
                                            class="w-full p-4 form-input <?php echo isset($errors['condition_type']) ? 'error' : ''; ?>" required>
                                        <option value="" disabled <?php echo getOldInput('condition_type') == '' ? 'selected' : ''; ?>>Wybierz</option>
                                        <option value="new" <?php echo getOldInput('condition_type') == 'new' ? 'selected' : ''; ?>>Nowe</option>
                                        <option value="renovated" <?php echo getOldInput('condition_type') == 'renovated' ? 'selected' : ''; ?>>Po remoncie</option>
                                        <option value="used" <?php echo getOldInput('condition_type') == 'used' ? 'selected' : ''; ?>>Używane</option>
                                        <option value="to_renovate" <?php echo getOldInput('condition_type') == 'to_renovate' ? 'selected' : ''; ?>>Do remontu</option>
                                    </select>
                                    <?php if (isset($errors['condition_type'])): ?>
                                        <p class="text-red-500 text-sm mt-2 font-medium"><?php echo htmlspecialchars($errors['condition_type']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Rok budowy</label>
                                    <input type="number" name="year_built" placeholder="np. 2010"
                                           class="w-full p-4 form-input"
                                           value="<?php echo htmlspecialchars(getOldInput('year_built')); ?>">
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Dostępne od</label>
                                    <input type="date" name="available_from"
                                           class="w-full p-4 form-input"
                                           value="<?php echo htmlspecialchars(getOldInput('available_from')); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Amenities Section -->
                        <div class="form-section p-6">
                            <h2 class="text-2xl font-playfair font-bold text-slate-800 mb-6 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                                Udogodnienia
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <label class="checkbox-label flex items-center p-4 cursor-pointer">
                                    <input type="checkbox" name="has_balcony" value="1" class="h-5 w-5 text-blue-600 focus:ring-blue-600 border-slate-300 rounded" <?php echo getOldInput('has_balcony') ? 'checked' : ''; ?> >
                                    <span class="ml-3 text-slate-700 text-sm font-semibold">Balkon</span>
                                </label>
                                <label class="checkbox-label flex items-center p-4 cursor-pointer">
                                    <input type="checkbox" name="has_elevator" value="1" class="h-5 w-5 text-blue-600 focus:ring-blue-600 border-slate-300 rounded" <?php echo getOldInput('has_elevator') ? 'checked' : ''; ?> >
                                    <span class="ml-3 text-slate-700 text-sm font-semibold">Winda</span>
                                </label>
                                <label class="checkbox-label flex items-center p-4 cursor-pointer">
                                    <input type="checkbox" name="parking" value="1" class="h-5 w-5 text-blue-600 focus:ring-blue-600 border-slate-300 rounded" <?php echo getOldInput('parking') ? 'checked' : ''; ?> >
                                    <span class="ml-3 text-slate-700 text-sm font-semibold">Parking</span>
                                </label>
                                <label class="checkbox-label flex items-center p-4 cursor-pointer">
                                    <input type="checkbox" name="garage" value="1" class="h-5 w-5 text-blue-600 focus:ring-blue-600 border-slate-300 rounded" <?php echo getOldInput('garage') ? 'checked' : ''; ?> >
                                    <span class="ml-3 text-slate-700 text-sm font-semibold">Garaż</span>
                                </label>
                                <label class="checkbox-label flex items-center p-4 cursor-pointer">
                                    <input type="checkbox" name="garden" value="1" class="h-5 w-5 text-blue-600 focus:ring-blue-600 border-slate-300 rounded" <?php echo getOldInput('garden') ? 'checked' : ''; ?> >
                                    <span class="ml-3 text-slate-700 text-sm font-semibold">Ogród</span>
                                </label>
                                <label class="checkbox-label flex items-center p-4 cursor-pointer">
                                    <input type="checkbox" name="furnished" value="1" class="h-5 w-5 text-blue-600 focus:ring-blue-600 border-slate-300 rounded" <?php echo getOldInput('furnished') ? 'checked' : ''; ?> >
                                    <span class="ml-3 text-slate-700 text-sm font-semibold">Umeblowane</span>
                                </label>
                                <label class="checkbox-label flex items-center p-4 cursor-pointer">
                                    <input type="checkbox" name="pets_allowed" value="1" class="h-5 w-5 text-blue-600 focus:ring-blue-600 border-slate-300 rounded" <?php echo getOldInput('pets_allowed') ? 'checked' : ''; ?> >
                                    <span class="ml-3 text-slate-700 text-sm font-semibold">Zwierzęta dozwolone</span>
                                </label>
                            </div>
                        </div>

                        <!-- Images Section -->
                        <div class="form-section p-6">
                            <h2 class="text-2xl font-playfair font-bold text-slate-800 mb-6 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Zdjęcia nieruchomości
                            </h2>
                            <div class="mb-6">
                                <label class="block text-slate-700 text-sm font-semibold mb-3">Prześlij zdjęcia (JPEG/PNG, maks. 5)*</label>
                                <div class="flex items-center justify-center w-full">
                                    <label for="images" class="flex flex-col items-center justify-center w-full h-32 drop-zone cursor-pointer">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            <p class="text-sm text-slate-600">
                                                <span class="font-semibold">Kliknij, aby przesłać</span> lub upuść pliki
                                            </p>
                                            <p class="text-xs text-slate-500 mt-1">JPEG lub PNG (max 5MB)</p>
                                        </div>
                                        <input id="images" name="images[]" type="file" accept="image/jpeg,image/png" multiple onchange="updatePrimaryImageOptions()" class="hidden" required>
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4">
                                <p class="text-slate-600 text-sm font-medium">Kliknij zdjęcie, aby ustawić je jako główne (wyświetlane jako pierwsze)</p>
                            </div>
                            <div id="image-preview" class="flex flex-wrap gap-4 mt-2"></div>
                        </div>

                        <!-- Submit Section -->
                        <div class="flex justify-end space-x-4 pt-6">
                            <a href="index.php?action=dashboard" class="px-8 py-4 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-all duration-300 font-semibold">
                                Anuluj
                            </a>
                            <button type="submit" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Dodaj ogłoszenie
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>

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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            dropArea.classList.add('dragover');
        }

        function unhighlight() {
            dropArea.classList.remove('dragover');
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            document.getElementById('images').files = files;
            updatePrimaryImageOptions();
        }
    </script>

    <?php include __DIR__ . '/../ai_assistant_widget.php'; ?>
</body>
</html>