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
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1d4ed8 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        
        .floating-animation {
            animation: floating 6s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        
        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes pulse-glow {
            from { box-shadow: 0 0 20px rgba(30, 64, 175, 0.4); }
            to { box-shadow: 0 0 30px rgba(30, 64, 175, 0.8); }
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .section-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, rgba(30, 64, 175, 0.3) 50%, transparent 100%);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-roboto">
    <?php include __DIR__ . '/../header.php'; ?>

    <main>
        <!-- Hero Section -->
        <section class="hero-section gradient-bg text-white overflow-hidden relative">
            <div class="absolute inset-0">
                <div class="absolute top-20 left-10 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 right-10 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl"></div>
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-white/5 rounded-full blur-2xl"></div>
            </div>
            
            <div class="relative z-10 container mx-auto px-4 sm:px-6 py-24 lg:py-32 flex flex-col justify-center min-h-[80vh]">
                <div class="max-w-3xl">
                    <div class="floating-animation">
                        <span class="floating-badge glass-effect border-white/20">Nowoczesne mieszkania premium</span>
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-playfair font-bold leading-tight mt-6">
                        <span class="text-gradient">Luksusowe apartamenty</span>
                        <span class="text-gold block">w zasięgu ręki</span>
                    </h1>
                    <p class="text-lg sm:text-xl text-slate-100/90 mt-6 max-w-2xl leading-relaxed">
                        Wybierz mieszkanie dopasowane do Twojego stylu życia. Nasze oferty przechodzą ręczną selekcję,
                        dzięki czemu możesz liczyć na najwyższy standard i przejrzyste warunki wynajmu.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-4">
                        <a href="index.php?action=search" class="btn shadow-lg shadow-blue-900/30 pulse-glow transform hover:scale-105 transition-all duration-300">
                            Przeglądaj oferty
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                        <a href="<?php echo isLoggedIn() ? 'index.php?action=add_offer' : 'index.php?action=login'; ?>" class="btn btn-outline transform hover:scale-105 transition-all duration-300">
                            Dodaj ofertę
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </a>
                    </div>
                    <div class="mt-10 flex flex-col sm:flex-row sm:items-center gap-6 text-slate-100/80">
                        <div class="flex items-center gap-3">
                            <div class="rounded-full bg-white/10 p-2 backdrop-blur-sm">
                                <svg class="w-6 h-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-sm sm:text-base">Zweryfikowani właściciele i aktualne oferty</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="rounded-full bg-white/10 p-2 backdrop-blur-sm">
                                <svg class="w-6 h-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3-.895 3-2s-1.343-2-3-2-3 .895-3 2 1.343 2 3 2zm0 3c-2 0-6 1-6 3v1c0 .552.448 1 1 1h10c.552 0 1-.448 1-1v-1c0-2-4-3-6-3z"></path>
                                </svg>
                            </div>
                            <span class="text-sm sm:text-base">Ponad 15&nbsp;000 zadowolonych najemców</span>
                        </div>
                    </div>
                </div>

                <div class="hero-card glass-effect mt-14 grid grid-cols-1 md:grid-cols-3 gap-6 backdrop-blur-xl">
                    <div class="text-center p-6 rounded-xl hover:bg-white/5 transition-all duration-300">
                        <span class="hero-card__label">Średni czas znalezienia mieszkania</span>
                        <p class="hero-card__value text-3xl font-bold text-white">48 h</p>
                        <p class="hero-card__text">Dzięki inteligentnym filtrom oraz powiadomieniom znajdziesz wymarzony apartament w ekspresowym tempie.</p>
                    </div>
                    <div class="text-center p-6 rounded-xl hover:bg-white/5 transition-all duration-300">
                        <span class="hero-card__label">Ekskluzywne lokalizacje</span>
                        <p class="hero-card__value text-3xl font-bold text-white">+120</p>
                        <p class="hero-card__text">Oferujemy nieruchomości w najmodniejszych dzielnicach dużych miast i spokojnych enklawach.</p>
                    </div>
                    <div class="text-center p-6 rounded-xl hover:bg-white/5 transition-all duration-300">
                        <span class="hero-card__label">Obsługa 7 dni w tygodniu</span>
                        <p class="hero-card__value text-3xl font-bold text-white">24/7</p>
                        <p class="hero-card__text">Nasz zespół wsparcia pomaga na każdym etapie wynajmu – od pierwszego kontaktu aż po podpisanie umowy.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Flash Message -->
        <div class="container mx-auto px-4 sm:px-6 mt-10">
            <?php $flash = getFlashMessage(); if ($flash): ?>
                <div class="flash-message <?php echo $flash['type'] === 'error' ? 'flash-error' : 'flash-success'; ?> transform transition-all duration-300 hover:scale-105">
                    <div class="flex items-start gap-4">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $flash['type'] === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M5 13l4 4L19 7'; ?>"></path>
                        </svg>
                        <span class="font-medium leading-relaxed"><?php echo htmlspecialchars($flash['message']); ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Features Section -->
        <section class="py-20 relative bg-gradient-to-b from-slate-50 to-white">
            <div class="absolute inset-0 bg-grid-slate-100 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))]"></div>
            <div class="relative container mx-auto px-4 sm:px-6">
                <div class="max-w-2xl mx-auto text-center">
                    <span class="section-badge bg-blue-50 text-blue-700 border border-blue-200">Dlaczego ApartmentRental?</span>
                    <h2 class="section-title mt-4 text-3xl sm:text-4xl text-slate-800">Twój przewodnik po rynku premium</h2>
                    <p class="section-subtitle mt-4 text-slate-600">Od pierwszego kliknięcia do przekazania kluczy – zapewniamy najwyższą jakość obsługi oraz narzędzia, które ułatwiają wynajem.</p>
                </div>

                <div class="feature-grid mt-16">
                    <div class="feature-card group hover:shadow-2xl transition-all duration-500">
                        <div class="feature-icon group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-6 0h6"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V8a2 2 0 012-2h3l2-3h4l2 3h3a2 2 0 012 2v11a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="group-hover:text-blue-700 transition-colors duration-300">Starannie wyselekcjonowane oferty</h3>
                        <p class="group-hover:text-slate-700 transition-colors duration-300">Każda nieruchomość przechodzi wieloetapową weryfikację, aby zapewnić Ci komfort i bezpieczeństwo.</p>
                    </div>
                    <div class="feature-card group hover:shadow-2xl transition-all duration-500">
                        <div class="feature-icon group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1"></path>
                            </svg>
                        </div>
                        <h3 class="group-hover:text-blue-700 transition-colors duration-300">Automatyczne powiadomienia</h3>
                        <p class="group-hover:text-slate-700 transition-colors duration-300">Otrzymuj natychmiastowe alerty o nowych mieszkaniach spełniających Twoje kryteria wyszukiwania.</p>
                    </div>
                    <div class="feature-card group hover:shadow-2xl transition-all duration-500">
                        <div class="feature-icon group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h6a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path>
                            </svg>
                        </div>
                        <h3 class="group-hover:text-blue-700 transition-colors duration-300">Bezpieczne przechowywanie dokumentów</h3>
                        <p class="group-hover:text-slate-700 transition-colors duration-300">Wygodnie zarządzaj umowami, protokołami oraz wszystkimi niezbędnymi dokumentami online.</p>
                    </div>
                    <div class="feature-card group hover:shadow-2xl transition-all duration-500">
                        <div class="feature-icon group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="group-hover:text-blue-700 transition-colors duration-300">Transparentne warunki</h3>
                        <p class="group-hover:text-slate-700 transition-colors duration-300">Czytelne umowy, brak ukrytych kosztów i jasne zasady współpracy z właścicielem mieszkania.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-16 bg-slate-900 relative overflow-hidden">
            <div class="absolute inset-0 bg-grid-white/[0.02] bg-[size:60px_60px]"></div>
            <div class="relative container mx-auto px-4 sm:px-6">
                <div class="stats-grid">
                    <div class="stat-card glass-effect border-white/10 text-white hover:scale-105 transition-transform duration-300">
                        <span class="stat-value text-4xl">4.9/5</span>
                        <span class="stat-label text-slate-300">Średnia ocena użytkowników</span>
                    </div>
                    <div class="stat-card glass-effect border-white/10 text-white hover:scale-105 transition-transform duration-300">
                        <span class="stat-value text-4xl">8&nbsp;500+</span>
                        <span class="stat-label text-slate-300">Nieruchomości dostępnych od ręki</span>
                    </div>
                    <div class="stat-card glass-effect border-white/10 text-white hover:scale-105 transition-transform duration-300">
                        <span class="stat-value text-4xl">32</span>
                        <span class="stat-label text-slate-300">Miasta w naszej ofercie</span>
                    </div>
                    <div class="stat-card glass-effect border-white/10 text-white hover:scale-105 transition-transform duration-300">
                        <span class="stat-value text-4xl">96%</span>
                        <span class="stat-label text-slate-300">Skuteczność dopasowania do potrzeb</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="how-it-works bg-gradient-to-br from-blue-900 via-slate-900 to-purple-900 relative overflow-hidden">
            <div class="absolute inset-0">
                <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-b from-white/10 to-transparent"></div>
                <div class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-white/10 to-transparent"></div>
            </div>
            <div class="relative container mx-auto px-4 sm:px-6 py-20">
                <div class="max-w-2xl mx-auto text-center">
                    <span class="section-badge bg-white/10 text-white border-white/20">Jak to działa?</span>
                    <h2 class="section-title mt-4 text-3xl sm:text-4xl text-white">Trzy proste kroki do nowego apartamentu</h2>
                    <p class="section-subtitle mt-4 text-slate-200">Proces wynajmu uprościliśmy tak, abyś mógł skupić się na tym, co najważniejsze – wyborze idealnego mieszkania.</p>
                </div>

                <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="step-card group hover:bg-white/10 transition-all duration-500 floating-animation">
                        <div class="step-icon group-hover:bg-white/25 transition-colors duration-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.737 0 5.268.784 7.379 2.137M15 10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h3 class="group-hover:text-gold transition-colors duration-300">1. Załóż konto</h3>
                        <p class="group-hover:text-white transition-colors duration-300">Utwórz profil, dzięki któremu możesz zapisywać ulubione mieszkania i otrzymywać spersonalizowane rekomendacje.</p>
                    </div>
                    <div class="step-card group hover:bg-white/10 transition-all duration-500 floating-animation" style="animation-delay: 2s;">
                        <div class="step-icon group-hover:bg-white/25 transition-colors duration-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2 4 4 8-8 4 4"></path>
                            </svg>
                        </div>
                        <h3 class="group-hover:text-gold transition-colors duration-300">2. Znajdź lub dodaj ofertę</h3>
                        <p class="group-hover:text-white transition-colors duration-300">Korzystaj z rozbudowanych filtrów wyszukiwania lub zaprezentuj swoją nieruchomość tysiącom aktywnych użytkowników.</p>
                    </div>
                    <div class="step-card group hover:bg-white/10 transition-all duration-500 floating-animation" style="animation-delay: 4s;">
                        <div class="step-icon group-hover:bg-white/25 transition-colors duration-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"></path>
                            </svg>
                        </div>
                        <h3 class="group-hover:text-gold transition-colors duration-300">3. Zabezpiecz wynajem</h3>
                        <p class="group-hover:text-white transition-colors duration-300">Uzgodnij szczegóły, podpisz umowę online i odbierz klucze – wszystko w jednym miejscu, bez zbędnych formalności.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-20 bg-white relative overflow-hidden">
            <div class="absolute inset-0 bg-grid-slate-100 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.8))]"></div>
            <div class="relative container mx-auto px-4 sm:px-6">
                <div class="max-w-2xl mx-auto text-center">
                    <span class="section-badge bg-blue-50 text-blue-700 border border-blue-200">Historie naszych klientów</span>
                    <h2 class="section-title mt-4 text-3xl sm:text-4xl text-slate-800">Opinie, którym możesz zaufać</h2>
                </div>

                <div class="testimonial-grid mt-14">
                    <div class="testimonial-card group hover:shadow-2xl transition-all duration-500">
                        <p class="group-hover:text-slate-800 transition-colors duration-300">„W ciągu dwóch dni znalazłam apartament w centrum Krakowa. Obsługa klienta prowadziła mnie krok po kroku i zadbała o każdy detal.”</p>
                        <div class="testimonial-author">
                            <span class="author-name group-hover:text-blue-700 transition-colors duration-300">Karolina, projektantka wnętrz</span>
                            <span class="author-location">Kraków</span>
                        </div>
                    </div>
                    <div class="testimonial-card group hover:shadow-2xl transition-all duration-500">
                        <p class="group-hover:text-slate-800 transition-colors duration-300">„Jako właściciel kilku mieszkań cenię przejrzystość platformy. System powiadomień pomaga mi szybko znaleźć zaufanych najemców.”</p>
                        <div class="testimonial-author">
                            <span class="author-name group-hover:text-blue-700 transition-colors duration-300">Marek, inwestor</span>
                            <span class="author-location">Warszawa</span>
                        </div>
                    </div>
                    <div class="testimonial-card group hover:shadow-2xl transition-all duration-500">
                        <p class="group-hover:text-slate-800 transition-colors duration-300">„To jedyna platforma, która rzeczywiście dba o doświadczenie użytkowników. Polecam każdemu, kto szuka mieszkania premium.”</p>
                        <div class="testimonial-author">
                            <span class="author-name group-hover:text-blue-700 transition-colors duration-300">Agnieszka, menedżerka HR</span>
                            <span class="author-location">Gdańsk</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

      <!-- CTA Section -->
<section class="cta-section relative overflow-hidden bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900">
    <div class="absolute inset-0 overflow-hidden">
        <!-- Animated background elements -->
        <div class="absolute -top-40 -left-40 w-80 h-80 bg-gradient-to-r from-blue-500/20 to-cyan-400/20 rounded-full blur-3xl animate-pulse-slow"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-white/5 rounded-full blur-2xl"></div>
        
        <!-- Grid pattern overlay -->
        <div class="absolute inset-0 bg-grid-white/[0.02] bg-[size:60px_60px]"></div>
    </div>
    
    <div class="relative container mx-auto px-4 sm:px-6 py-20">
        <div class="max-w-4xl mx-auto text-center">
            <!-- Decorative elements -->
            <div class="flex justify-center mb-8">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full blur-lg opacity-75 animate-pulse"></div>
                    <div class="relative bg-gradient-to-r from-blue-600 to-cyan-600 text-white px-6 py-3 rounded-full text-sm font-semibold uppercase tracking-wider">
                        Rozpocznij swoją przygodę
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <div class="cta-card bg-white/10 backdrop-blur-2xl border border-white/20 rounded-3xl p-8 sm:p-12 shadow-2xl">
                <div class="max-w-2xl mx-auto">
                    <h2 class="text-4xl sm:text-5xl font-playfair font-bold text-white mb-6 leading-tight">
                        Znajdź swój
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 to-blue-300">wymarzony apartament</span>
                    </h2>
                    
                    <p class="text-xl text-slate-200 mb-8 leading-relaxed">
                        Dołącz do tysięcy zadowolonych użytkowników i odkryj najłatwiejszy sposób na znalezienie lub wynajęcie luksusowego mieszkania.
                    </p>

                    <!-- Stats row -->
                    <div class="flex flex-wrap justify-center gap-6 mb-8">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white">15K+</div>
                            <div class="text-sm text-slate-300">Zadowolonych klientów</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white">8.5K+</div>
                            <div class="text-sm text-slate-300">Dostępnych ofert</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white">98%</div>
                            <div class="text-sm text-slate-300">Skuteczności</div>
                        </div>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <a href="index.php?action=register" class="group relative bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-semibold py-4 px-8 rounded-2xl transition-all duration-300 transform hover:scale-105 hover:shadow-2xl shadow-lg min-w-[200px] text-center">
                            <div class="absolute inset-0 bg-white/20 rounded-2xl transform scale-0 group-hover:scale-100 transition-transform duration-300"></div>
                            <span class="relative flex items-center justify-center gap-3">
                                Rozpocznij teraz
                                <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </span>
                        </a>
                        
                        <a href="index.php?action=search" class="group relative bg-transparent border-2 border-white/30 hover:border-white/50 text-white font-semibold py-4 px-8 rounded-2xl transition-all duration-300 transform hover:scale-105 hover:shadow-xl min-w-[200px] text-center backdrop-blur-sm">
                            <div class="absolute inset-0 bg-white/10 rounded-2xl transform scale-0 group-hover:scale-100 transition-transform duration-300"></div>
                            <span class="relative flex items-center justify-center gap-3">
                                Przeglądaj oferty
                                <svg class="w-5 h-5 transform group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                        </a>
                    </div>

                    <!-- Trust indicators -->
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-6 text-slate-300 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Bezpłatna rejestracja</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Pełne bezpieczeństwo</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span>24/7 Wsparcie</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>