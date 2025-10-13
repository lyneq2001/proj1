<?php
require_once __DIR__ . '/auth.php';
?>
<header class="text-dark-blue shadow-nav sticky top-0 z-50 bg-white/95">
    <nav class="container mx-auto px-4 py-3 flex justify-between items-center">
        <!-- Logo -->
        <a href="index.php?action=home" class="flex items-center space-x-3">
            <span class="text-2xl font-playfair font-bold text-gold">ApartmentRental</span>
        </a>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-8">
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <div class="relative group">
                        <a href="index.php?action=admin_dashboard" class="nav-link hover:text-gold font-medium">Panel administracyjny</a>
                        <a href="index.php?action=dashboard" class="nav-link hover:text-gold font-medium ml-4">Panel użytkownika</a>
                        <span class="admin-badge">ADMIN</span>
                    </div>
                <?php else: ?>
                    <a href="index.php?action=dashboard" class="nav-link hover:text-gold font-medium">Panel</a>
                <?php endif; ?>
                <a href="index.php?action=add_offer" class="nav-link hover:text-gold font-medium">Dodaj ogłoszenie</a>
                <a href="index.php?action=search" class="nav-link hover:text-gold font-medium">Szukaj</a>
                <form method="POST" action="index.php?action=logout" class="inline">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <button type="submit" class="flex items-center space-x-1 bg-accent-500 hover:bg-accent-600 text-white px-4 py-2 rounded-lg transition-colors duration-300">
                        <span>Wyloguj</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            <?php else: ?>
                <a href="index.php?action=search" class="nav-link hover:text-gold font-medium">Przeglądaj oferty</a>
                <div class="flex items-center space-x-4">
                    <a href="index.php?action=register" class="nav-link hover:text-gold font-medium">Rejestracja</a>
                    <a href="index.php?action=login" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg transition-colors duration-300">Logowanie</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mobile menu button -->
        <button id="menu-toggle" class="md:hidden focus:outline-none text-gray-700 hover:text-gold">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </nav>

    <!-- Mobile Navigation -->
    <div id="mobile-menu" class="mobile-menu md:hidden bg-dark-blue">
        <div class="container mx-auto px-4 py-2 flex flex-col space-y-3">
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="index.php?action=admin_dashboard" class="py-2 px-2 text-white hover:text-gold hover:bg-white/10 rounded-md transition-colors">Panel administracyjny</a>
                    <a href="index.php?action=dashboard" class="py-2 px-2 text-white hover:text-gold hover:bg-white/10 rounded-md transition-colors">Panel użytkownika</a>
                    <div class="py-2 px-2 text-gold">Status: <span class="admin-badge">ADMIN</span></div>
                <?php else: ?>
                    <a href="index.php?action=dashboard" class="py-2 px-2 text-white hover:text-gold hover:bg-white/10 rounded-md transition-colors">Panel</a>
                <?php endif; ?>
                <a href="index.php?action=add_offer" class="py-2 px-2 text-white hover:text-gold hover:bg-white/10 rounded-md transition-colors">Dodaj ogłoszenie</a>
                <a href="index.php?action=search" class="py-2 px-2 text-white hover:text-gold hover:bg-white/10 rounded-md transition-colors">Szukaj</a>
                <form method="POST" action="index.php?action=logout" class="inline">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <button type="submit" class="w-full text-left py-2 px-2 text-white hover:text-gold hover:bg-white/10 rounded-md transition-colors flex items-center space-x-2">
                        <span>Wyloguj</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            <?php else: ?>
                <a href="index.php?action=search" class="py-2 px-2 text-white hover:text-gold hover:bg-white/10 rounded-md transition-colors">Przeglądaj oferty</a>
                <a href="index.php?action=register" class="py-2 px-2 text-white hover:text-gold hover:bg-white/10 rounded-md transition-colors">Rejestracja</a>
                <a href="index.php?action=login" class="py-2 px-2 text-gold hover:bg-white/10 rounded-md font-medium transition-colors">Logowanie</a>
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
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            `;
        } else {
            button.innerHTML = `
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            `;
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
