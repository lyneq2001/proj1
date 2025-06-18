<?php
require_once __DIR__ . '/auth.php';
?>
<header class="text-black dark:text-white shadow-nav sticky top-0 z-50 bg-white dark:bg-gray-800">
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
                        <a href="index.php?action=admin_dashboard" class="nav-link hover:text-gold font-medium">Admin Panel</a>
                        <a href="index.php?action=dashboard" class="nav-link hover:text-gold font-medium ml-4">User Dashboard</a>
                        <span class="admin-badge">ADMIN</span>
                    </div>
                <?php else: ?>
                    <a href="index.php?action=dashboard" class="nav-link hover:text-gold font-medium">Dashboard</a>
                <?php endif; ?>
                <a href="index.php?action=add_offer" class="nav-link hover:text-gold font-medium">Add Offer</a>
                <a href="index.php?action=search" class="nav-link hover:text-gold font-medium">Search</a>
                <form method="POST" action="index.php?action=logout" class="inline">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <button type="submit" class="flex items-center space-x-1 bg-accent-500 hover:bg-accent-600 text-black px-4 py-2 rounded-lg transition-colors duration-300">
                        <span>Logout</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            <?php else: ?>
                <a href="index.php?action=search" class="nav-link hover:text-gold font-medium">Browse Listings</a>
                <div class="flex items-center space-x-4">
                    <a href="index.php?action=register" class="nav-link hover:text-gold font-medium">Register</a>
                    <a href="index.php?action=login" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg transition-colors duration-300">Login</a>
                </div>
            <?php endif; ?>
            <button class="theme-toggle text-gray-700 dark:text-gray-200 hover:text-gold" aria-label="Toggle theme">
                <svg id="theme-toggle-dark" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707 6 6 0 1017.293 13.293z" />
                </svg>
                <svg id="theme-toggle-light" class="w-6 h-6 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 2a1 1 0 011 1v2a1 1 0 11-2 0V3a1 1 0 011-1zM10 15a1 1 0 011 1v2a1 1 0 11-2 0v-2a1 1 0 011-1zM4.22 4.22a1 1 0 011.42 0L7 5.59a1 1 0 11-1.42 1.42L4.22 5.64a1 1 0 010-1.42zM15.78 14.36a1 1 0 010 1.42l-1.36 1.36a1 1 0 11-1.42-1.42l1.36-1.36a1 1 0 011.42 0zM2 10a1 1 0 011-1h2a1 1 0 110 2H3a1 1 0 01-1-1zM15 9a1 1 0 100 2h2a1 1 0 100-2h-2zM4.22 15.78a1 1 0 01-1.42 1.42L1.44 15.84a1 1 0 011.42-1.42l1.36 1.36zM15.78 5.64a1 1 0 01-1.42-1.42L15.72 2.86a1 1 0 011.42 1.42l-1.36 1.36zM10 5a5 5 0 100 10A5 5 0 0010 5z" />
                </svg>
            </button>
        </div>

        <!-- Mobile menu button -->
        <button id="menu-toggle" class="md:hidden focus:outline-none text-gray-700 hover:text-gold">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </nav>

    <!-- Mobile Navigation -->
    <div id="mobile-menu" class="mobile-menu md:hidden bg-dark-blue dark:bg-gray-800">
        <div class="container mx-auto px-4 py-2 flex flex-col space-y-3">
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="index.php?action=admin_dashboard" class="py-2 px-2 text-white hover:text-gold hover:bg-blue-800 rounded-md">Admin Panel</a>
                    <a href="index.php?action=dashboard" class="py-2 px-2 text-white hover:text-gold hover:bg-blue-800 rounded-md">User Dashboard</a>
                    <div class="py-2 px-2 text-gold">Status: <span class="admin-badge">ADMIN</span></div>
                <?php else: ?>
                    <a href="index.php?action=dashboard" class="py-2 px-2 text-white hover:text-gold hover:bg-blue-800 rounded-md">Dashboard</a>
                <?php endif; ?>
                <a href="index.php?action=add_offer" class="py-2 px-2 text-white hover:text-gold hover:bg-blue-800 rounded-md">Add Offer</a>
                <a href="index.php?action=search" class="py-2 px-2 text-white hover:text-gold hover:bg-blue-800 rounded-md">Search</a>
                <form method="POST" action="index.php?action=logout" class="inline">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <button type="submit" class="w-full text-left py-2 px-2 text-white hover:text-gold hover:bg-blue-800 rounded-md flex items-center space-x-2">
                        <span>Logout</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            <?php else: ?>
                <a href="index.php?action=search" class="py-2 px-2 text-white hover:text-gold hover:bg-blue-800 rounded-md">Browse Listings</a>
                <a href="index.php?action=register" class="py-2 px-2 text-white hover:text-gold hover:bg-blue-800 rounded-md">Register</a>
                <a href="index.php?action=login" class="py-2 px-2 text-gold hover:bg-blue-800 rounded-md font-medium">Login</a>
            <?php endif; ?>
            <button class="theme-toggle text-white hover:text-gold hover:bg-blue-800 rounded-md py-2 px-2 flex items-center space-x-2" aria-label="Toggle theme">
                <svg class="w-5 h-5" id="theme-toggle-dark-mobile" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707 6 6 0 1017.293 13.293z" />
                </svg>
                <svg class="w-5 h-5 hidden" id="theme-toggle-light-mobile" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 2a1 1 0 011 1v2a1 1 0 11-2 0V3a1 1 0 011-1zM10 15a1 1 0 011 1v2a1 1 0 11-2 0v-2a1 1 0 011-1zM4.22 4.22a1 1 0 011.42 0L7 5.59a1 1 0 11-1.42 1.42L4.22 5.64a1 1 0 010-1.42zM15.78 14.36a1 1 0 010 1.42l-1.36 1.36a1 1 0 11-1.42-1.42l1.36-1.36a1 1 0 011.42 0zM2 10a1 1 0 011-1h2a1 1 0 110 2H3a1 1 0 01-1-1zM15 9a1 1 0 100 2h2a1 1 0 100-2h-2zM4.22 15.78a1 1 0 01-1.42 1.42L1.44 15.84a1 1 0 011.42-1.42l1.36 1.36zM15.78 5.64a1 1 0 01-1.42-1.42L15.72 2.86a1 1 0 011.42 1.42l-1.36 1.36zM10 5a5 5 0 100 10A5 5 0 0010 5z" />
                </svg>
                <span>Theme</span>
            </button>
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
    const themeButtons = document.querySelectorAll('.theme-toggle');
    const darkIconDesktop = document.getElementById('theme-toggle-dark');
    const lightIconDesktop = document.getElementById('theme-toggle-light');
    const darkIconMobile = document.getElementById('theme-toggle-dark-mobile');
    const lightIconMobile = document.getElementById('theme-toggle-light-mobile');

    function updateIcons(theme) {
        const showDark = theme === 'light';
        if (darkIconDesktop) darkIconDesktop.classList.toggle('hidden', !showDark);
        if (lightIconDesktop) lightIconDesktop.classList.toggle('hidden', showDark);
        if (darkIconMobile) darkIconMobile.classList.toggle('hidden', !showDark);
        if (lightIconMobile) lightIconMobile.classList.toggle('hidden', showDark);
    }

    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        updateIcons(theme);
    }

    let storedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(storedTheme);

    themeButtons.forEach(btn => btn.addEventListener('click', () => {
        storedTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
        localStorage.setItem('theme', storedTheme);
        applyTheme(storedTheme);
    }));
</script>
