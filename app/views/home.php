<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'auth.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'offers.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Apartments | Wynajem apartamentów premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/heroicons@2.0.18/outline/index.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Playfair Display', serif; }
        .hero-bg {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), #1f2937; /* Ciemnoszare tło zamiast zdjęcia */
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">

<?php include __DIR__ . '/../header.php'; ?>

<main class="min-h-screen">

    <!-- Hero Section – bez zdjęcia w tle -->
    <section class="hero-bg py-32 md:py-48 lg:py-64 text-white">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight animate-fade-in">
                Luksusowe apartamenty<br>w zasięgu ręki
            </h1>
            <p class="text-xl md:text-2xl mb-12 max-w-3xl mx-auto opacity-90">
                Odkryj zweryfikowane oferty premium. Najwyższy standard, elegancja i komfort na co dzień.
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                <a href="index.php?action=search" class="bg-amber-500 text-gray-900 px-10 py-5 rounded-full text-lg font-semibold hover:bg-amber-400 transition transform hover:scale-105">
                    Przeglądaj oferty
                </a>
                <a href="<?php echo isLoggedIn() ? 'index.php?action=add_offer' : 'index.php?action=login'; ?>" 
                   class="border-2 border-white px-10 py-5 rounded-full text-lg font-semibold hover:bg-white hover:text-gray-900 transition transform hover:scale-105">
                    Dodaj ofertę
                </a>
            </div>
        </div>
    </section>

    <!-- Flash Message -->
    <?php $flash = getFlashMessage(); if ($flash): ?>
        <div class="container mx-auto px-6 mt-12">
            <div class="p-6 rounded-lg shadow-lg <?php echo $flash['type'] === 'error' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Atuty -->
    <section class="py-24 bg-white">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl md:text-5xl font-bold text-center mb-16">Dlaczego warto wybrać nas?</h2>
            <div class="grid md:grid-cols-3 gap-12">
                <div class="text-center group">
                    <div class="inline-block p-6 bg-gray-100 rounded-full mb-6 group-hover:bg-amber-100 transition">
                        <svg class="w-16 h-16 text-amber-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4">Zweryfikowane oferty</h3>
                    <p class="text-gray-600">Każdy apartament sprawdzony pod kątem jakości, bezpieczeństwa i standardu premium.</p>
                </div>
                <div class="text-center group">
                    <div class="inline-block p-6 bg-gray-100 rounded-full mb-6 group-hover:bg-amber-100 transition">
                        <svg class="w-16 h-16 text-amber-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4">Inteligentne wyszukiwanie</h3>
                    <p class="text-gray-600">Zaawansowane filtry, mapy i powiadomienia o idealnie dopasowanych ofertach.</p>
                </div>
                <div class="text-center group">
                    <div class="inline-block p-6 bg-gray-100 rounded-full mb-6 group-hover:bg-amber-100 transition">
                        <svg class="w-16 h-16 text-amber-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-6a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4">Wsparcie premium 24/7</h3>
                    <p class="text-gray-600">Dedykowana pomoc na każdym etapie – od wyszukiwania po podpisanie umowy.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-24 bg-gradient-to-br from-gray-900 to-black text-white">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-4xl md:text-6xl font-bold mb-8">Znajdź apartament marzeń już dziś</h2>
            <p class="text-xl md:text-2xl mb-12 max-w-3xl mx-auto opacity-90">
                Dołącz do elitarnego grona najemców i właścicieli luksusowych nieruchomości.
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                <a href="index.php?action=register" class="bg-amber-500 text-gray-900 px-12 py-6 rounded-full text-xl font-semibold hover:bg-amber-400 transition transform hover:scale-105">
                    Zarejestruj się bezpłatnie
                </a>
                <a href="index.php?action=search" class="border-2 border-white px-12 py-6 rounded-full text-xl font-semibold hover:bg-white hover:text-gray-900 transition transform hover:scale-105">
                    Przeglądaj oferty
                </a>
            </div>
        </div>
    </section>

</main>

</body>
</html>