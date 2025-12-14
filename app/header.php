<?php
require_once __DIR__ . '/auth.php';

$action = $_GET['action'] ?? 'home';
$isHomePage = $action === 'home';

$headerClasses = [
    'primary-header',
    'relative z-40',
    'backdrop-blur-xl',
    'transition-all duration-500',
];

if ($isHomePage) {
    $headerClasses[] = 'header-home';
    $headerClasses[] = 'border-b border-white/10';
    $headerClasses[] = 'bg-gradient-to-r from-slate-900/80 via-blue-900/70 to-indigo-900/80 text-white shadow-[0_20px_45px_-25px_rgba(30,64,175,0.65)]';

    $desktopLinkClass = 'nav-link font-medium transition-colors duration-300 text-slate-100 hover:text-white';
    $secondaryLinkClass = 'nav-link font-medium transition-colors duration-300 text-slate-200 hover:text-white/95';
    $primaryCtaClass = 'bg-white/10 hover:bg-white/20 text-white px-6 py-2.5 rounded-xl border border-white/20 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 font-medium backdrop-blur';
    $logoutButtonClass = 'flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white px-5 py-2.5 rounded-xl border border-white/20 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 group backdrop-blur';

    $mobileMenuClasses = 'mobile-menu md:hidden transition-all duration-300 mobile-menu-home bg-gradient-to-b from-slate-900/95 via-blue-900/90 to-indigo-900/95 backdrop-blur-2xl border-white/10';
    $mobileLinkClass = 'py-3 px-4 text-white hover:text-white hover:bg-white/10 rounded-xl transition-all duration-300 border border-white/10 hover:border-white/20';
    $mobileAccentButtonClass = 'py-3 px-4 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl transition-all duration-300 border border-white/20 text-center';
    $mobileLogoutButtonClass = 'w-full text-left py-3 px-4 text-white hover:bg-red-500/20 rounded-xl transition-all duration-300 border border-white/10 hover:border-red-400/40 flex items-center justify-between group';

    $menuButtonClasses = 'md:hidden focus:outline-none p-2 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 text-white transition-colors duration-300 group';
    $menuIconClass = 'w-6 h-6 text-white group-hover:text-white transition-colors duration-300';
    $menuButtonOpenClass = 'bg-white/20';
} else {
    $headerClasses[] = 'bg-white text-slate-900 shadow-nav border-b border-slate-200/80';

    $desktopLinkClass = 'nav-link font-medium transition-colors duration-300 text-slate-700 hover:text-slate-900';
    $secondaryLinkClass = 'nav-link font-medium transition-colors duration-300 text-slate-600 hover:text-slate-900';
    $primaryCtaClass = 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-2.5 rounded-xl border border-blue-700/20 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 font-medium';
    $logoutButtonClass = 'flex items-center space-x-2 bg-slate-900/90 hover:bg-slate-900 text-white px-5 py-2.5 rounded-xl border border-slate-900/30 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 group';

    $mobileMenuClasses = 'mobile-menu md:hidden transition-all duration-300 bg-white text-slate-900 border-b border-slate-200 shadow-lg';
    $mobileLinkClass = 'py-3 px-4 text-slate-800 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-all duration-300 border border-slate-200 hover:border-slate-300';
    $mobileAccentButtonClass = 'py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-xl transition-all duration-300 border border-blue-700/20 text-center';
    $mobileLogoutButtonClass = 'w-full text-left py-3 px-4 text-slate-900 hover:bg-red-50 rounded-xl transition-all duration-300 border border-slate-200 hover:border-red-300 flex items-center justify-between group';

    $menuButtonClasses = 'md:hidden focus:outline-none p-2 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-900 transition-colors duration-300 group shadow-sm';
    $menuIconClass = 'w-6 h-6 text-slate-900 group-hover:text-slate-700 transition-colors duration-300';
    $menuButtonOpenClass = 'bg-slate-200';
}
?>

<header class="<?php echo implode(' ', array_filter($headerClasses)); ?>">
    <nav class="container mx-auto px-4 py-3 flex justify-between items-center">
        <!-- Logo -->
        <a href="index.php?action=home" class="flex items-center space-x-3 group">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600/70 to-indigo-600/80 rounded-lg blur opacity-60 group-hover:opacity-100 transition-opacity duration-300"></div>
                <span class="relative text-2xl font-playfair font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-200 via-slate-100 to-indigo-200 group-hover:from-blue-100 group-hover:to-white transition-all duration-300">
                    ApartmentRental
                </span>
            </div>
        </a>

        <?php if ($isHomePage): ?>
            <div class="hidden lg:flex items-center space-x-6 text-sm text-slate-100/80">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-white/20 bg-white/5 backdrop-blur">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_0_4px_rgba(16,185,129,0.25)]"></span>
                    <span>Zweryfikowane oferty premium</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z" />
                    </svg>
                    <span>Średnia ocena 4.9/5 wśród najemców</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center justify-between flex-1 ml-8">
            <div class="flex items-center space-x-6 text-sm">
                <a href="index.php?action=home" class="<?php echo $desktopLinkClass; ?>">Strona główna</a>
                <a href="index.php?action=search" class="<?php echo $desktopLinkClass; ?>">Oferty</a>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="index.php?action=admin_dashboard" class="<?php echo $desktopLinkClass; ?>">Panel administracyjny</a>
                    <?php endif; ?>
                    <a href="index.php?action=dashboard" class="<?php echo $desktopLinkClass; ?>">Zarządzanie kontem</a>
                    <a href="index.php?action=add_offer" class="<?php echo $desktopLinkClass; ?>">Dodaj ogłoszenie</a>
                <?php endif; ?>
            </div>

            <div class="flex items-center space-x-6">
                <a href="index.php?action=search" class="<?php echo $secondaryLinkClass; ?> hidden lg:inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200/70 shadow-sm">Oferty</a>
                <?php if (isLoggedIn()): ?>
                    <a href="index.php?action=dashboard" class="<?php echo $primaryCtaClass; ?>">Zarządzaj kontem</a>
                    <?php if (isAdmin()): ?>
                        <a href="index.php?action=admin_dashboard" class="<?php echo $secondaryLinkClass; ?> hidden lg:inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200/70 shadow-sm">Widok admina</a>
                    <?php endif; ?>
                    <form method="POST" action="index.php?action=logout" class="inline">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                        <button type="submit" class="<?php echo $logoutButtonClass; ?>">
                            <span class="font-medium">Wyloguj</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:translate-x-1 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="index.php?action=register" class="<?php echo $desktopLinkClass; ?>">Rejestracja</a>
                    <a href="index.php?action=login" class="<?php echo $primaryCtaClass; ?>">
                        Logowanie
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mobile menu button -->
        <button id="menu-toggle" class="<?php echo $menuButtonClasses; ?>">
            <svg class="<?php echo $menuIconClass; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </nav>

    <!-- Mobile Navigation -->
    <div id="mobile-menu" class="<?php echo $mobileMenuClasses; ?>">
        <div class="container mx-auto px-4 py-4 flex flex-col space-y-2">
                <?php if (isLoggedIn()): ?>
                    <a href="index.php?action=home" class="<?php echo $mobileLinkClass; ?>">Strona główna</a>
                    <a href="index.php?action=search" class="<?php echo $mobileLinkClass; ?>">Oferty</a>
                    <a href="index.php?action=dashboard" class="<?php echo $mobileLinkClass; ?>">Zarządzanie kontem</a>
                    <?php if (isAdmin()): ?>
                        <a href="index.php?action=admin_dashboard" class="<?php echo $mobileLinkClass; ?>">
                            Panel administracyjny
                        </a>
                    <?php endif; ?>
                    <a href="index.php?action=add_offer" class="<?php echo $mobileLinkClass; ?>">Dodaj ogłoszenie</a>
                    <form method="POST" action="index.php?action=logout" class="inline">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                        <button type="submit" class="<?php echo $mobileLogoutButtonClass; ?>">
                            <span>Wyloguj</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:translate-x-1 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="index.php?action=home" class="<?php echo $mobileLinkClass; ?>">Strona główna</a>
                    <a href="index.php?action=search" class="<?php echo $mobileLinkClass; ?>">Oferty</a>
                    <a href="index.php?action=register" class="<?php echo $mobileLinkClass; ?>">Rejestracja</a>
                    <a href="index.php?action=login" class="<?php echo $mobileAccentButtonClass; ?>">
                        Logowanie
                    </a>
                <?php endif; ?>
            </div>
        </div>
</header>

<script>
    document.getElementById('menu-toggle').addEventListener('click', () => {
        const menu = document.getElementById('mobile-menu');
        const menuIconClass = '<?php echo $menuIconClass; ?>';
        const menuButtonOpenClass = '<?php echo $menuButtonOpenClass; ?>';
        menu.classList.toggle('open');

        const button = document.getElementById('menu-toggle');
        if (menu.classList.contains('open')) {
            button.innerHTML = `
                <svg class="${menuIconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            `;
            button.classList.add(menuButtonOpenClass);
        } else {
            button.innerHTML = `
                <svg class="${menuIconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            `;
            button.classList.remove(menuButtonOpenClass);
        }
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', (event) => {
        const menu = document.getElementById('mobile-menu');
        const button = document.getElementById('menu-toggle');
        const menuIconClass = '<?php echo $menuIconClass; ?>';
        const menuButtonOpenClass = '<?php echo $menuButtonOpenClass; ?>';
        const isClickInsideMenu = menu.contains(event.target);
        const isClickOnButton = button.contains(event.target);

        if (!isClickInsideMenu && !isClickOnButton && menu.classList.contains('open')) {
            menu.classList.remove('open');
            button.innerHTML = `
                <svg class="${menuIconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            `;
            button.classList.remove(menuButtonOpenClass);
        }
    });
</script>

<?php include_once __DIR__ . '/ai_assistant_widget.php'; ?>

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
