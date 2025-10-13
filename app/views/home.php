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

</head>
<body class="bg-slate-50 text-slate-900 font-roboto">
    <?php include __DIR__ . '/../header.php'; ?>

    <main>
        <!-- Hero Section -->
        <section class="hero-section text-white overflow-hidden">
            <div class="hero-overlay"></div>
            <div class="relative z-10 container mx-auto px-4 sm:px-6 py-24 lg:py-32 flex flex-col justify-center min-h-[80vh]">
                <div class="max-w-3xl">
                    <span class="floating-badge">Nowoczesne mieszkania premium</span>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-playfair font-bold leading-tight mt-6">
                        Luksusowe apartamenty
                        <span class="text-gold block">w zasięgu ręki</span>
                    </h1>
                    <p class="text-lg sm:text-xl text-slate-100/90 mt-6 max-w-2xl">
                        Wybierz mieszkanie dopasowane do Twojego stylu życia. Nasze oferty przechodzą ręczną selekcję,
                        dzięki czemu możesz liczyć na najwyższy standard i przejrzyste warunki wynajmu.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-4">
                        <a href="index.php?action=search" class="btn shadow-lg shadow-blue-900/30">Przeglądaj oferty</a>
                        <a href="<?php echo isLoggedIn() ? 'index.php?action=add_offer' : 'index.php?action=login'; ?>" class="btn btn-outline">
                            Dodaj ofertę
                        </a>
                    </div>
                    <div class="mt-10 flex flex-col sm:flex-row sm:items-center gap-6 text-slate-100/80">
                        <div class="flex items-center gap-3">
                            <div class="rounded-full bg-white/10 p-2">
                                <svg class="w-6 h-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-sm sm:text-base">Zweryfikowani właściciele i aktualne oferty</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="rounded-full bg-white/10 p-2">
                                <svg class="w-6 h-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3-.895 3-2s-1.343-2-3-2-3 .895-3 2 1.343 2 3 2zm0 3c-2 0-6 1-6 3v1c0 .552.448 1 1 1h10c.552 0 1-.448 1-1v-1c0-2-4-3-6-3z"></path>
                                </svg>
                            </div>
                            <span class="text-sm sm:text-base">Ponad 15&nbsp;000 zadowolonych najemców</span>
                        </div>
                    </div>
                </div>

                <div class="hero-card mt-14 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <span class="hero-card__label">Średni czas znalezienia mieszkania</span>
                        <p class="hero-card__value">48 h</p>
                        <p class="hero-card__text">Dzięki inteligentnym filtrom oraz powiadomieniom znajdziesz wymarzony apartament w ekspresowym tempie.</p>
                    </div>
                    <div>
                        <span class="hero-card__label">Ekskluzywne lokalizacje</span>
                        <p class="hero-card__value">+120</p>
                        <p class="hero-card__text">Oferujemy nieruchomości w najmodniejszych dzielnicach dużych miast i spokojnych enklawach.</p>
                    </div>
                    <div>
                        <span class="hero-card__label">Obsługa 7 dni w tygodniu</span>
                        <p class="hero-card__value">24/7</p>
                        <p class="hero-card__text">Nasz zespół wsparcia pomaga na każdym etapie wynajmu – od pierwszego kontaktu aż po podpisanie umowy.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Flash Message -->
        <div class="container mx-auto px-4 sm:px-6 mt-10">
            <?php $flash = getFlashMessage(); if ($flash): ?>
                <div class="flash-message <?php echo $flash['type'] === 'error' ? 'flash-error' : 'flash-success'; ?>">
                    <div class="flex items-start gap-4">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $flash['type'] === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M5 13l4 4L19 7'; ?>"></path>
                        </svg>
                        <span class="font-medium leading-relaxed"><?php echo htmlspecialchars($flash['message']); ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Features Section -->
        <section class="py-20 relative">
            <div class="absolute inset-0 bg-gradient-to-b from-white/0 via-white/40 to-white pointer-events-none"></div>
            <div class="relative container mx-auto px-4 sm:px-6">
                <div class="max-w-2xl mx-auto text-center">
                    <span class="section-badge">Dlaczego ApartmentRental?</span>
                    <h2 class="section-title mt-4 text-3xl sm:text-4xl">Twój przewodnik po rynku premium</h2>
                    <p class="section-subtitle mt-4">Od pierwszego kliknięcia do przekazania kluczy – zapewniamy najwyższą jakość obsługi oraz narzędzia, które ułatwiają wynajem.</p>
                </div>

                <div class="feature-grid mt-16">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-6 0h6"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V8a2 2 0 012-2h3l2-3h4l2 3h3a2 2 0 012 2v11a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3>Starannie wyselekcjonowane oferty</h3>
                        <p>Każda nieruchomość przechodzi wieloetapową weryfikację, aby zapewnić Ci komfort i bezpieczeństwo.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1"></path>
                            </svg>
                        </div>
                        <h3>Automatyczne powiadomienia</h3>
                        <p>Otrzymuj natychmiastowe alerty o nowych mieszkaniach spełniających Twoje kryteria wyszukiwania.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h6a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path>
                            </svg>
                        </div>
                        <h3>Bezpieczne przechowywanie dokumentów</h3>
                        <p>Wygodnie zarządzaj umowami, protokołami oraz wszystkimi niezbędnymi dokumentami online.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3>Transparentne warunki</h3>
                        <p>Czytelne umowy, brak ukrytych kosztów i jasne zasady współpracy z właścicielem mieszkania.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-16">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-value">4.9/5</span>
                        <span class="stat-label">Średnia ocena użytkowników</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">8&nbsp;500+</span>
                        <span class="stat-label">Nieruchomości dostępnych od ręki</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">32</span>
                        <span class="stat-label">Miasta w naszej ofercie</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">96%</span>
                        <span class="stat-label">Skuteczność dopasowania do potrzeb</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="how-it-works">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="max-w-2xl mx-auto text-center">
                    <span class="section-badge">Jak to działa?</span>
                    <h2 class="section-title mt-4 text-3xl sm:text-4xl text-white">Trzy proste kroki do nowego apartamentu</h2>
                    <p class="section-subtitle mt-4 text-slate-200">Proces wynajmu uprościliśmy tak, abyś mógł skupić się na tym, co najważniejsze – wyborze idealnego mieszkania.</p>
                </div>

                <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="step-card">
                        <div class="step-icon">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.737 0 5.268.784 7.379 2.137M15 10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h3>1. Załóż konto</h3>
                        <p>Utwórz profil, dzięki któremu możesz zapisywać ulubione mieszkania i otrzymywać spersonalizowane rekomendacje.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-icon">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2 4 4 8-8 4 4"></path>
                            </svg>
                        </div>
                        <h3>2. Znajdź lub dodaj ofertę</h3>
                        <p>Korzystaj z rozbudowanych filtrów wyszukiwania lub zaprezentuj swoją nieruchomość tysiącom aktywnych użytkowników.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-icon">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"></path>
                            </svg>
                        </div>
                        <h3>3. Zabezpiecz wynajem</h3>
                        <p>Uzgodnij szczegóły, podpisz umowę online i odbierz klucze – wszystko w jednym miejscu, bez zbędnych formalności.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-20">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="max-w-2xl mx-auto text-center">
                    <span class="section-badge">Historie naszych klientów</span>
                    <h2 class="section-title mt-4 text-3xl sm:text-4xl">Opinie, którym możesz zaufać</h2>
                </div>

                <div class="testimonial-grid mt-14">
                    <div class="testimonial-card">
                        <p>„W ciągu dwóch dni znalazłam apartament w centrum Krakowa. Obsługa klienta prowadziła mnie krok po kroku i zadbała o każdy detal.”</p>
                        <div class="testimonial-author">
                            <span class="author-name">Karolina, projektantka wnętrz</span>
                            <span class="author-location">Kraków</span>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <p>„Jako właściciel kilku mieszkań cenię przejrzystość platformy. System powiadomień pomaga mi szybko znaleźć zaufanych najemców.”</p>
                        <div class="testimonial-author">
                            <span class="author-name">Marek, inwestor</span>
                            <span class="author-location">Warszawa</span>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <p>„To jedyna platforma, która rzeczywiście dba o doświadczenie użytkowników. Polecam każdemu, kto szuka mieszkania premium.”</p>
                        <div class="testimonial-author">
                            <span class="author-name">Agnieszka, menedżerka HR</span>
                            <span class="author-location">Gdańsk</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="cta-card">
                    <div>
                        <h2>Gotowy na nowy rozdział?</h2>
                        <p>Dołącz do ApartmentRental i zacznij zarządzać swoimi ofertami lub znajdź miejsce, do którego będziesz wracać z przyjemnością.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="index.php?action=register" class="btn shadow-lg shadow-blue-900/30">Utwórz konto</a>
                        <a href="index.php?action=search" class="btn btn-outline">Odkryj oferty</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
