<?php
require_once __DIR__ . '/auth.php';
?>
<header class="text-dark-blue shadow-nav sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/60">
    <nav class="container mx-auto px-4 py-3 flex justify-between items-center">
        <!-- Logo -->
        <a href="index.php?action=home" class="flex items-center space-x-3 group">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-lg blur opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                <span class="relative text-2xl font-playfair font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-indigo-800 group-hover:from-blue-800 group-hover:to-indigo-900 transition-all duration-300">
                    ApartmentRental
                </span>
            </div>
        </a>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-6">
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <div class="flex items-center space-x-6">
                        <a href="index.php?action=admin_dashboard" class="nav-link font-medium text-slate-700 hover:text-blue-700 relative group">
                            Panel administracyjny
                            <span class="absolute -top-1 -right-8">
                                <span class="admin-badge bg-gradient-to-r from-red-500 to-red-600 text-white text-xs px-2 py-1 rounded-full shadow-lg">ADMIN</span>
                            </span>
                        </a>
                        <a href="index.php?action=dashboard" class="nav-link font-medium text-slate-700 hover:text-blue-700">Panel użytkownika</a>
                    </div>
                <?php else: ?>
                    <a href="index.php?action=dashboard" class="nav-link font-medium text-slate-700 hover:text-blue-700">Panel</a>
                <?php endif; ?>
                <a href="index.php?action=add_offer" class="nav-link font-medium text-slate-700 hover:text-blue-700">Dodaj ogłoszenie</a>
                <a href="index.php?action=search" class="nav-link font-medium text-slate-700 hover:text-blue-700">Szukaj</a>
                <form method="POST" action="index.php?action=logout" class="inline">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <button type="submit" class="flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white px-5 py-2.5 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 group">
                        <span class="font-medium">Wyloguj</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:translate-x-1 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            <?php else: ?>
                <a href="index.php?action=search" class="nav-link font-medium text-slate-700 hover:text-blue-700 transition-colors duration-300">Przeglądaj oferty</a>
                <div class="flex items-center space-x-4">
                    <a href="index.php?action=register" class="nav-link font-medium text-slate-700 hover:text-blue-700 transition-colors duration-300">Rejestracja</a>
                    <a href="index.php?action=login" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white px-6 py-2.5 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 font-medium">
                        Logowanie
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mobile menu button -->
        <button id="menu-toggle" class="md:hidden focus:outline-none p-2 rounded-lg bg-slate-100 hover:bg-slate-200 transition-colors duration-300 group">
            <svg class="w-6 h-6 text-slate-700 group-hover:text-blue-700 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </nav>

    <!-- Mobile Navigation -->
    <div id="mobile-menu" class="mobile-menu md:hidden bg-gradient-to-b from-slate-900 to-blue-900 backdrop-blur-lg border-t border-slate-700/50">
        <div class="container mx-auto px-4 py-4 flex flex-col space-y-2">
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="index.php?action=admin_dashboard" class="group py-3 px-4 text-white hover:text-white hover:bg-white/10 rounded-xl transition-all duration-300 border border-white/10 hover:border-white/20 flex items-center justify-between">
                        <span>Panel administracyjny</span>
                        <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">ADMIN</span>
                    </a>
                    <a href="index.php?action=dashboard" class="py-3 px-4 text-white hover:text-white hover:bg-white/10 rounded-xl transition-all duration-300 border border-white/10 hover:border-white/20">Panel użytkownika</a>
                <?php else: ?>
                    <a href="index.php?action=dashboard" class="py-3 px-4 text-white hover:text-white hover:bg-white/10 rounded-xl transition-all duration-300 border border-white/10 hover:border-white-20">Panel</a>
                <?php endif; ?>
                <a href="index.php?action=add_offer" class="py-3 px-4 text-white hover:text-white hover:bg-white/10 rounded-xl transition-all duration-300 border border-white/10 hover:border-white/20">Dodaj ogłoszenie</a>
                <a href="index.php?action=search" class="py-3 px-4 text-white hover:text-white hover:bg-white/10 rounded-xl transition-all duration-300 border border-white/10 hover:border-white/20">Szukaj</a>
                <form method="POST" action="index.php?action=logout" class="inline">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <button type="submit" class="w-full text-left py-3 px-4 text-white hover:text-white hover:bg-red-500/20 rounded-xl transition-all duration-300 border border-white/10 hover:border-red-400/30 flex items-center justify-between group">
                        <span>Wyloguj</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:translate-x-1 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            <?php else: ?>
                <a href="index.php?action=search" class="py-3 px-4 text-white hover:text-white hover:bg-white/10 rounded-xl transition-all duration-300 border border-white/10 hover:border-white/20">Przeglądaj oferty</a>
                <a href="index.php?action=register" class="py-3 px-4 text-white hover:text-white hover:bg-white/10 rounded-xl transition-all duration-300 border border-white/10 hover:border-white/20">Rejestracja</a>
                <a href="index.php?action=login" class="py-3 px-4 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl transition-all duration-300 border border-white/20 hover:border-white/30 text-center">
                    Logowanie
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
    document.getElementById('menu-toggle').addEventListener('click', () => {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('open');

        const button = document.getElementById('menu-toggle');
        if (menu.classList.contains('open')) {
            button.innerHTML = `
                <svg class="w-6 h-6 text-slate-700 group-hover:text-blue-700 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            `;
            button.classList.add('bg-blue-100');
        } else {
            button.innerHTML = `
                <svg class="w-6 h-6 text-slate-700 group-hover:text-blue-700 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            `;
            button.classList.remove('bg-blue-100');
        }
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', (event) => {
        const menu = document.getElementById('mobile-menu');
        const button = document.getElementById('menu-toggle');
        const isClickInsideMenu = menu.contains(event.target);
        const isClickOnButton = button.contains(event.target);

        if (!isClickInsideMenu && !isClickOnButton && menu.classList.contains('open')) {
            menu.classList.remove('open');
            button.innerHTML = `
                <svg class="w-6 h-6 text-slate-700 group-hover:text-blue-700 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            `;
            button.classList.remove('bg-blue-100');
        }
    });
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll("input:not([type=hidden]):not([type=checkbox]):not([type=radio]):not([type=submit]):not([type=button]), textarea").forEach(function(elem) {
        if (!elem.title) {
            if (elem.placeholder) {
                elem.title = elem.placeholder;
            } else {
                var label = elem.id ? document.querySelector('label[for="' + elem.id + '"]') : null;
                if (label) {
                    elem.title = label.textContent.trim();
                } else if (elem.name) {
                    elem.title = elem.name;
                }
            }
        }
    });
});
</script>