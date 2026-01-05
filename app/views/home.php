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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

<?php include __DIR__ . '/../header.php'; ?>

<main class="container mx-auto px-4 py-12">

    <!-- Hero -->
    <section class="text-center py-16 bg-blue-900 text-white rounded-lg">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Luksusowe apartamenty w zasięgu ręki</h1>
        <p class="text-xl mb-8 max-w-2xl mx-auto">
            Wybierz mieszkanie premium z zweryfikowanych ofert. Najwyższy standard i proste warunki wynajmu.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="index.php?action=search" class="bg-white text-blue-900 px-8 py-4 rounded font-semibold hover:bg-gray-200 transition">
                Przeglądaj oferty
            </a>
            <a href="<?php echo isLoggedIn() ? 'index.php?action=add_offer' : 'index.php?action=login'; ?>" 
               class="border-2 border-white px-8 py-4 rounded font-semibold hover:bg-white hover:text-blue-900 transition">
                Dodaj ofertę
            </a>
        </div>
    </section>

    <!-- Flash Message -->
    <?php $flash = getFlashMessage(); if ($flash): ?>
        <div class="mt-8 p-4 rounded <?php echo $flash['type'] === 'error' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <!-- Podstawowe atuty -->
    <section class="py-16">
        <h2 class="text-3xl font-bold text-center mb-10">Dlaczego my?</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center p-6 bg-white rounded shadow">
                <h3 class="text-xl font-semibold mb-2">Zweryfikowane oferty</h3>
                <p>Każde mieszkanie sprawdzone pod kątem jakości i bezpieczeństwa.</p>
            </div>
            <div class="text-center p-6 bg-white rounded shadow">
                <h3 class="text-xl font-semibold mb-2">Szybkie wyszukiwanie</h3>
                <p>Inteligentne filtry i powiadomienia o nowych ofertach.</p>
            </div>
            <div class="text-center p-6 bg-white rounded shadow">
                <h3 class="text-xl font-semibold mb-2">Wsparcie 24/7</h3>
                <p>Pomoc na każdym etapie – od wyszukiwania po podpisanie umowy.</p>
            </div>
        </div>
    </section>

    <!-- CTA na dole -->
    <section class="text-center py-16 bg-gray-800 text-white rounded-lg">
        <h2 class="text-3xl md:text-4xl font-bold mb-6">Znajdź swoje wymarzone mieszkanie</h2>
        <p class="text-xl mb-8">Dołącz do tysięcy zadowolonych najemców i właścicieli.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="index.php?action=register" class="bg-blue-600 px-8 py-4 rounded font-semibold hover:bg-blue-700 transition">
                Zarejestruj się
            </a>
            <a href="index.php?action=search" class="border-2 border-white px-8 py-4 rounded font-semibold hover:bg-white hover:text-gray-800 transition">
                Przeglądaj oferty
            </a>
        </div>
    </section>

</main>

</body>
</html>
