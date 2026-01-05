<?php
require_once __DIR__ . '/auth.php';

// Zmienne pomocnicze
$isLoggedIn = isLoggedIn();
$isAdmin = isAdmin();
?>

<header class="relative z-40 bg-gradient-to-r from-gray-900/90 via-black/85 to-gray-900/90 backdrop-blur-xl border-b border-white/10 text-white">
    <nav class="container mx-auto px-4 py-4 flex items-center justify-between">
        <!-- Logo -->
        <a href="index.php?action=home" class="flex items-center space-x-3 hover:scale-105 transition">
            <span class="text-3xl font-bold font-playfair tracking-tight">LuxApart</span>
        </a>

        <!-- Desktop Navigation -->
        <div class="flex flex-wrap items-center gap-4 sm:gap-6 md:gap-8">
            <a href="index.php?action=search" class="font-medium hover:text-amber-400 transition">Oferty</a>
            
            <?php if ($isLoggedIn): ?>
                <a href="index.php?action=add_offer" class="font-medium hover:text-amber-400 transition">Dodaj ofertę</a>
                <?php if ($isAdmin): ?>
                    <a href="index.php?action=admin_dashboard" class="font-medium hover:text-amber-400 transition">Admin</a>
                <?php endif; ?>
                <a href="index.php?action=dashboard" class="font-medium hover:text-amber-400 transition">Konto</a>
                
                <form method="POST" action="index.php?action=logout" class="inline">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <button type="submit" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 rounded-full border border-white/20 transition hover:scale-105">
                        Wyloguj
                    </button>
                </form>
            <?php else: ?>
                <a href="index.php?action=register" class="font-medium hover:text-amber-400 transition">Rejestracja</a>
                <a href="index.php?action=login" class="px-6 py-2.5 bg-amber-500 text-gray-900 rounded-full font-semibold hover:bg-amber-400 transition hover:scale-105">
                    Zaloguj się
                </a>
            <?php endif; ?>
        </div>

    </nav>
</header>

<?php include_once __DIR__ . '/ai_assistant_widget.php'; ?>
