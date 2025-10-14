<?php
require_once __DIR__ . '/auth.php';

$action = $_GET['action'] ?? 'home';
$isHomePage = $action === 'home';

$headerClasses = [
    'primary-header',
    $isHomePage ? 'primary-header--home' : 'primary-header--default'
];

$desktopLinkClass = 'primary-header__link';
$secondaryLinkClass = 'primary-header__link primary-header__link--secondary';
$primaryCtaClass = 'primary-header__cta';
$logoutButtonClass = 'primary-header__logout';

$mobileMenuClasses = 'primary-header__mobile-menu';
$mobileLinkClass = 'primary-header__mobile-link';
$mobileAccentButtonClass = 'primary-header__mobile-cta';
$menuToggleClass = 'primary-header__menu-toggle';
?>

<header class="<?php echo implode(' ', array_filter($headerClasses)); ?>">
    <nav class="primary-header__inner">
        <a href="index.php?action=home" class="primary-header__logo">
            <span class="primary-header__logo-glow" aria-hidden="true"></span>
            <span class="primary-header__logo-text">ApartmentRental</span>
        </a>

        <?php if ($isHomePage): ?>
            <div class="primary-header__promo">
                <div class="primary-header__promo-badge">
                    <span class="primary-header__status-dot"></span>
                    <span>Zweryfikowane oferty premium</span>
                </div>
                <div class="primary-header__rating">
                    <svg class="primary-header__rating-icon" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z" />
                    </svg>
                    <span>Średnia ocena 4.9/5 wśród najemców</span>
                </div>
            </div>
        <?php endif; ?>

        <div class="primary-header__nav-links">
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="index.php?action=admin_dashboard" class="<?php echo $desktopLinkClass; ?>">
                        Panel administracyjny
                    </a>
                    <a href="index.php?action=dashboard" class="<?php echo $desktopLinkClass; ?>">Panel użytkownika</a>
                <?php else: ?>
                    <a href="index.php?action=dashboard" class="<?php echo $desktopLinkClass; ?>">Panel</a>
                <?php endif; ?>
                <a href="index.php?action=add_offer" class="<?php echo $desktopLinkClass; ?>">Dodaj ogłoszenie</a>
                <a href="index.php?action=search" class="<?php echo $desktopLinkClass; ?>">Szukaj</a>
                <form method="POST" action="index.php?action=logout" class="primary-header__logout-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <button type="submit" class="<?php echo $logoutButtonClass; ?>">
                        <span class="primary-header__logout-label">Wyloguj</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="primary-header__logout-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            <?php else: ?>
                <a href="index.php?action=search" class="<?php echo $secondaryLinkClass; ?>">Przeglądaj oferty</a>
                <div class="primary-header__cta-group">
                    <a href="index.php?action=register" class="<?php echo $desktopLinkClass; ?>">Rejestracja</a>
                    <a href="index.php?action=login" class="<?php echo $primaryCtaClass; ?>">
                        Logowanie
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <button id="menu-toggle" class="<?php echo $menuToggleClass; ?>" type="button" aria-expanded="false" aria-controls="mobile-menu">
            <span class="visually-hidden">Przełącz menu nawigacji</span>
            <svg class="primary-header__menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </nav>

    <div id="mobile-menu" class="<?php echo $mobileMenuClasses; ?>" aria-hidden="true">
        <div class="primary-header__mobile-inner">
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="index.php?action=admin_dashboard" class="<?php echo $mobileLinkClass; ?>">
                        Panel administracyjny
                    </a>
                    <a href="index.php?action=dashboard" class="<?php echo $mobileLinkClass; ?>">Panel użytkownika</a>
                <?php else: ?>
                    <a href="index.php?action=dashboard" class="<?php echo $mobileLinkClass; ?>">Panel</a>
                <?php endif; ?>
                <a href="index.php?action=add_offer" class="<?php echo $mobileLinkClass; ?>">Dodaj ogłoszenie</a>
                <a href="index.php?action=search" class="<?php echo $mobileLinkClass; ?>">Szukaj</a>
                <form method="POST" action="index.php?action=logout" class="primary-header__logout-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <button type="submit" class="primary-header__mobile-logout">
                        <span>Wyloguj</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="primary-header__logout-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            <?php else: ?>
                <a href="index.php?action=search" class="<?php echo $mobileLinkClass; ?>">Przeglądaj oferty</a>
                <a href="index.php?action=register" class="<?php echo $mobileLinkClass; ?>">Rejestracja</a>
                <a href="index.php?action=login" class="<?php echo $mobileAccentButtonClass; ?>">
                    Logowanie
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');

    menuToggle.addEventListener('click', () => {
        const isOpen = mobileMenu.classList.toggle('open');
        menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        mobileMenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        menuToggle.classList.toggle('primary-header__menu-toggle--active', isOpen);
        menuToggle.innerHTML = isOpen
            ? `
                <span class="visually-hidden">Zamknij menu nawigacji</span>
                <svg class="primary-header__menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            `
            : `
                <span class="visually-hidden">Przełącz menu nawigacji</span>
                <svg class="primary-header__menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            `;
    });

    document.addEventListener('click', (event) => {
        const isClickInsideMenu = mobileMenu.contains(event.target);
        const isClickOnButton = menuToggle.contains(event.target);

        if (!isClickInsideMenu && !isClickOnButton && mobileMenu.classList.contains('open')) {
            mobileMenu.classList.remove('open');
            mobileMenu.setAttribute('aria-hidden', 'true');
            menuToggle.setAttribute('aria-expanded', 'false');
            menuToggle.classList.remove('primary-header__menu-toggle--active');
            menuToggle.innerHTML = `
                <span class="visually-hidden">Przełącz menu nawigacji</span>
                <svg class="primary-header__menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            `;
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
