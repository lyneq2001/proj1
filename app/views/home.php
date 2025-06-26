<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'auth.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'offers.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Luksusowe apartamenty w najlepszych lokalizacjach.">
    <title>Luxury Apartments | Wynajem apartamentów premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'playfair': ['"Playfair Display"', 'serif'],
                        'roboto': ['Roboto', 'sans-serif'],
                    },
                    colors: {
                        'gold': '#A0D9A0',
                        'dark-blue': '#8CCF83',
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
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 dark:text-white font-roboto">
    <?php include __DIR__ . '/../header.php'; ?>

    <main>
        <!-- Hero Section -->
        <section class="h-screen flex items-center bg-gradient-to-r from-dark-blue via-gray-700 to-black text-white">
            <div class="container mx-auto px-4 sm:px-6">
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-playfair font-bold mb-6">
                    Luksusowe apartamenty<br><span class="text-gold">w zasięgu ręki</span>
                </h1>
                <p class="text-lg sm:text-xl max-w-xl sm:max-w-2xl mb-8 font-light">
                    Znajdź idealne miejsce do zamieszkania w kilka chwil.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="index.php?action=search" class="bg-gold hover:bg-green-300 text-dark-blue font-medium py-3 px-8 rounded-md transition text-center">
                        Przeglądaj oferty
                    </a>
                    <a href="<?php echo isLoggedIn() ? 'index.php?action=add_offer' : 'index.php?action=login'; ?>" class="border-2 border-white hover:bg-white/20 font-medium py-3 px-8 rounded-md transition text-center">
                        Dodaj ofertę
                    </a>
                </div>
            </div>
        </section>

        <!-- Flash Message -->
        <div class="container mx-auto px-4 sm:px-6 mt-6">
            <?php $flash = getFlashMessage(); if ($flash): ?>
                <div class="mb-8 p-4 rounded-lg shadow-lg <?php echo $flash['type'] === 'error' ? 'bg-red-100 text-red-800 border-l-4 border-red-500' : 'bg-green-100 text-green-800 border-l-4 border-green-500'; ?>">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $flash['type'] === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M5 13l4 4L19 7'; ?>"></path>
                        </svg>
                        <span class="font-medium"><?php echo htmlspecialchars($flash['message']); ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- How It Works Section -->
        <section class="py-12 sm:py-20 bg-dark-blue text-white">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-playfair font-bold mb-4">Jak to działa?</h2>
                    <div class="w-24 h-1 bg-gold mx-auto"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 sm:gap-12">
                    <div class="text-center px-4">
                        <div class="bg-white/10 w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.737 0 5.268.784 7.379 2.137M15 10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold mb-3">1. Załóż konto</h3>
                        <p class="text-gray-200 text-sm sm:text-base">Zarejestruj się i dołącz do grona naszych użytkowników.</p>
                    </div>

                    <div class="text-center px-4">
                        <div class="bg-white/10 w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m-4-4l4 4 4-4m-4-9V3m0 5H3m9 0h9"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold mb-3">2. Dodaj ofertę lub przeglądaj</h3>
                        <p class="text-gray-200 text-sm sm:text-base">Prosto zarządzaj swoimi nieruchomościami lub szukaj wymarzonego miejsca.</p>
                    </div>

                    <div class="text-center px-4">
                        <div class="bg-white/10 w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold mb-3">3. Sfinalizuj wynajem</h3>
                        <p class="text-gray-200 text-sm sm:text-base">Skontaktuj się z właścicielem i ciesz się nowym apartamentem.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
