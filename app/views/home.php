<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'auth.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'offers.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Wynajmij luksusowe apartamenty premium w najlepszych lokalizacjach. Przeglądaj oferty i dodaj własne apartamenty na Luxury Apartments.">
    <meta name="keywords" content="luksusowe apartamenty, wynajem apartamentów, premium nieruchomości, mieszkania na wynajem">
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
                        'gold': '#D4AF37',
                        'dark-blue': '#1E3656',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 dark:text-white font-roboto">
    <?php include 'header.php'; ?>
    
    <main class="relative">
        <!-- Hero Section -->
        <section class="relative h-screen max-h-[700px] sm:max-h-[800px] overflow-hidden">
            <div class="absolute inset-0 bg-black/40 z-10"></div>
            <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                 alt="Luksusowy apartament z nowoczesnym wnętrzem" 
                 class="w-full h-full object-cover">
            
            <div class="absolute inset-0 z-20 flex items-center">
                <div class="container mx-auto px-4 sm:px-6 text-white">
                    <h1 class="text-4xl sm:text-5xl md:text-6xl font-playfair font-bold mb-6 leading-tight">
                        Odkryj <span class="text-gold">luksusowe</span><br>przestrzenie
                    </h1>
                    <p class="text-lg sm:text-xl max-w-xl sm:max-w-2xl mb-8 font-light">
                        Wynajmij wyjątkowe apartamenty premium w najlepszych lokalizacjach.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="index.php?action=search" class="bg-gold hover:bg-yellow-600 text-dark-blue font-medium py-3 px-8 rounded-md transition duration-300 text-center">
                            Przeglądaj oferty
                        </a>
                        <a href="<?php echo isLoggedIn() ? 'index.php?action=add_offer' : 'index.php?action=login'; ?>" 
                           class="border-2 border-white hover:bg-black/20 font-medium py-3 px-8 rounded-md transition duration-300 text-center">
                            Dodaj ofertę
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Flash Message -->
        <div class="container mx-auto px-4 sm:px-6 mt-6 sm:-mt-12 relative z-30">
            <?php
            $flash = getFlashMessage();
            if ($flash):
            ?>
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

        <!-- Features Section -->
        <section class="py-12 sm:py-20 bg-white">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-playfair font-bold text-dark-blue mb-4">Dlaczego warto wybrać nasze apartamenty?</h2>
                    <div class="w-24 h-1 bg-gold mx-auto"></div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 sm:gap-12">
                    <div class="text-center px-4">
                        <div class="bg-gray-100 w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-dark-blue mb-3">Premium lokalizacje</h3>
                        <p class="text-gray-600 text-sm sm:text-base">Najlepsze adresy w mieście, blisko atrakcji i centrum biznesowego.</p>
                    </div>
                    
                    <div class="text-center px-4">
                        <div class="bg-gray-100 w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-dark-blue mb-3">Pełne bezpieczeństwo</h3>
                        <p class="text-gray-600 text-sm sm:text-base">Monitoring, ochrona i systemy bezpieczeństwa dla Twojego spokoju.</p>
                    </div>
                    
                    <div class="text-center px-4">
                        <div class="bg-gray-100 w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-dark-blue mb-3">Luksusowe wyposażenie</h3>
                        <p class="text-gray-600 text-sm sm:text-base">Designerskie meble, wysokiej klasy sprzęty i dbałość o każdy detal.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>