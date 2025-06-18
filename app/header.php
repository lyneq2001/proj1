<?php
require_once __DIR__ . '/auth.php';
?>
<header class="text-black shadow-nav sticky top-0 z-50 bg-white">
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
