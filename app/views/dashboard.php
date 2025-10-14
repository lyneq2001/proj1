<?php
$authPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'auth.php';
$offersPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'offers.php';
if (!file_exists($authPath)) {
    die("Error: auth.php not found at $authPath");
}
if (!file_exists($offersPath)) {
    die("Error: offers.php not found at $offersPath");
}
require_once $authPath;
require_once $offersPath;
if (!isLoggedIn()) {
    header("Location: index.php?action=login");
    exit;
}
global $pdo;
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$offers_result = getUserOffers($_SESSION['user_id']);
$offers = $offers_result['offers'] ?? [];
$favorites_result = getUserFavorites($_SESSION['user_id']);
$favorites = $favorites_result['offers'] ?? [];
$conversations = getConversations($_SESSION['user_id']);
$show_message_form = isset($_GET['offer_id'], $_GET['receiver_id']);
$userStats = getUserStatistics($_SESSION['user_id']);
$userPendingReports = $userStats['pending_reports'] ?? 0;

// Handle AJAX request to mark messages as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_read') {
    header('Content-Type: application/json');
    if (!validateCsrfToken($_POST['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF token verification failed']);
        exit;
    }
    $offer_id = (int)$_POST['offer_id'];
    $other_user_id = (int)$_POST['other_user_id'];
    if (markMessagesAsRead($_SESSION['user_id'], $offer_id, $other_user_id)) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to mark messages as read']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Apartment Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">

    <style>
        .conversation-card {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 30px 60px -45px rgba(15, 23, 42, 0.3);
        }
        .conversation-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 35px 70px -40px rgba(30, 64, 175, 0.25);
        }
        .message-bubble {
            max-width: 80%;
            border-radius: 1rem;
        }
        .unread-badge {
            min-width: 1.5rem;
            background: linear-gradient(135deg, #EF4444, #DC2626);
        }
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        .tab-content.active {
            display: block;
        }
        .tab-button {
            transition: all 0.3s ease;
            position: relative;
            border-bottom: 2px solid transparent;
        }
        .tab-button.active {
            border-bottom-color: #1d4ed8;
            color: #1d4ed8;
            font-weight: 600;
        }
        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, #1d4ed8, #1e3a8a);
        }
        .stat-card {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 30px 60px -45px rgba(15, 23, 42, 0.3);
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 35px 70px -40px rgba(30, 64, 175, 0.25);
        }
        .offer-card {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 30px 60px -45px rgba(15, 23, 42, 0.3);
        }
        .offer-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 35px 70px -40px rgba(30, 64, 175, 0.35);
        }
        .user-avatar {
            background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .page-heading {
            text-align: center;
            margin-bottom: 3rem;
        }
        .page-heading__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            background: rgba(30, 64, 175, 0.12);
            color: var(--primary-700);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 1rem;
        }
        .page-heading__eyebrow::before {
            content: '';
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, var(--accent-500), var(--accent-600));
        }
        .page-heading__title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-700);
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        .page-heading__subtitle {
            color: var(--secondary-500);
            font-size: 1.25rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 30px 60px -45px rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen font-roboto">
    <?php include 'header.php'; ?>
    
    <main class="page-shell py-12">
        <div class="container mx-auto px-4 sm:px-6">
            <!-- Page Heading -->
            <div class="page-heading">
                <span class="page-heading__eyebrow">Panel użytkownika</span>
                <h1 class="page-heading__title">Witaj, <?php echo htmlspecialchars($user['username'] ?? 'Użytkowniku'); ?></h1>
                <p class="page-heading__subtitle">Zarządzaj swoimi ogłoszeniami, ulubionymi nieruchomościami oraz rozmowami z jednego miejsca.</p>
            </div>

            <?php
            $flash = getFlashMessage();
            if ($flash):
            ?>
                <div class="glass-panel mb-8 p-6 flex items-start gap-4 <?php echo $flash['type'] === 'error' ? 'flash-error' : 'flash-success'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $flash['type'] === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M5 13l4 4L19 7'; ?>"></path>
                    </svg>
                    <div class="font-medium leading-relaxed text-lg"><?php echo htmlspecialchars($flash['message']); ?></div>
                </div>
            <?php endif; ?>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            <aside class="w-full lg:w-80 flex-shrink-0">
                <!-- User Profile Card -->
                <div class="glass-panel p-6 mb-6">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-16 h-16 rounded-full user-avatar flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                            <?php echo strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-800 text-xl"><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></h2>
                            <p class="text-slate-600 text-sm"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <a href="index.php?action=add_offer" class="flex items-center space-x-3 text-slate-700 hover:text-blue-600 font-medium p-3 rounded-xl hover:bg-blue-50 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span class="font-semibold">Dodaj nowe ogłoszenie</span>
                        </a>
                        <a href="index.php?action=search" class="flex items-center space-x-3 text-slate-700 hover:text-blue-600 font-medium p-3 rounded-xl hover:bg-blue-50 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span class="font-semibold">Przeglądaj oferty</span>
                        </a>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="glass-panel p-6">
                    <h3 class="font-semibold text-slate-800 text-lg mb-6 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Szybkie statystyki
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl">
                            <p class="text-slate-600 font-medium">Aktywne oferty</p>
                            <p class="text-2xl font-bold text-blue-600"><?php echo (int)($userStats['active_offers'] ?? 0); ?></p>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl">
                            <p class="text-slate-600 font-medium">Oferty w przygotowaniu</p>
                            <p class="text-2xl font-bold text-amber-600"><?php echo (int)($userStats['inactive_offers'] ?? 0); ?></p>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl">
                            <p class="text-slate-600 font-medium">Ulubione ogłoszenia</p>
                            <p class="text-2xl font-bold text-emerald-600"><?php echo (int)($userStats['favorites'] ?? 0); ?></p>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl">
                            <p class="text-slate-600 font-medium">Nieprzeczytane wiadomości</p>
                            <p class="text-2xl font-bold text-purple-600"><?php echo (int)($userStats['unread_messages'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 space-y-8">
                <!-- Stats Overview -->
                <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="stat-card p-6 border-l-4 border-blue-500">
                        <p class="text-sm text-slate-600 font-medium mb-2">Łączna liczba wyświetleń</p>
                        <p class="text-3xl font-bold text-slate-800"><?php echo number_format((int)($userStats['total_views'] ?? 0)); ?></p>
                    </div>
                    <div class="stat-card p-6 border-l-4 border-indigo-500">
                        <p class="text-sm text-slate-600 font-medium mb-2">Aktywne konwersacje</p>
                        <p class="text-3xl font-bold text-slate-800"><?php echo count($conversations); ?></p>
                    </div>
                    <div class="stat-card p-6 border-l-4 border-amber-500">
                        <p class="text-sm text-slate-600 font-medium mb-2">Ulubione oferty</p>
                        <p class="text-3xl font-bold text-slate-800"><?php echo count($favorites); ?></p>
                    </div>
                    <div class="stat-card p-6 border-l-4 <?php echo $userPendingReports ? 'border-red-500' : 'border-slate-400'; ?>">
                        <p class="text-sm text-slate-600 font-medium mb-2">Zgłoszenia w toku</p>
                        <p class="text-3xl font-bold <?php echo $userPendingReports ? 'text-red-600' : 'text-slate-600'; ?>"><?php echo (int)$userPendingReports; ?></p>
                    </div>
                </section>

                <?php if ($userPendingReports): ?>
                    <div class="glass-panel p-6 flex items-center space-x-4 text-red-700 border border-red-200 bg-red-50/80 rounded-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.64 5.64l12.72 12.72M5.64 18.36L18.36 5.64" />
                        </svg>
                        <div>
                            <p class="font-semibold">Masz <?php echo (int)$userPendingReports; ?> zgłoszeń oczekujących na decyzję administratora.</p>
                            <p class="text-sm text-red-600 mt-1">Status Twoich zgłoszeń możesz śledzić w tym panelu.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Send New Message (via Contact Owner) -->
                <?php if ($show_message_form): ?>
                    <?php
                    $offer_id = (int)$_GET['offer_id'];
                    $receiver_id = (int)$_GET['receiver_id'];
                    $stmt = $pdo->prepare("SELECT title FROM offers WHERE id = ?");
                    $stmt->execute([$offer_id]);
                    $offer = $stmt->fetch(PDO::FETCH_ASSOC);
                    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                    $stmt->execute([$receiver_id]);
                    $receiver = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <section class="glass-panel p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-playfair font-bold text-slate-800">Wyślij wiadomość</h2>
                            <a href="index.php?action=dashboard" class="text-slate-500 hover:text-slate-700 p-2 rounded-lg hover:bg-slate-100 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </div>
                        <?php if (!$offer || !$receiver): ?>
                            <div class="rounded-2xl border border-red-200 bg-red-50 p-6 text-red-700">
                                <p class="font-semibold">Nieprawidłowa oferta lub odbiorca.</p>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="index.php?action=dashboard" class="max-w-2xl space-y-6">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                <input type="hidden" name="send_message" value="1">
                                <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
                                <input type="hidden" name="offer_id" value="<?php echo $offer_id; ?>">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-slate-700 text-sm font-semibold mb-3">Odbiorca</label>
                                        <div class="rounded-xl border border-slate-200 bg-white p-4 text-slate-800 font-medium"><?php echo htmlspecialchars($receiver['username']); ?></div>
                                    </div>
                                    <div>
                                        <label class="block text-slate-700 text-sm font-semibold mb-3">Dotyczące oferty</label>
                                        <div class="rounded-xl border border-slate-200 bg-white p-4 text-slate-800 font-medium"><?php echo htmlspecialchars($offer['title']); ?></div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Wiadomość</label>
                                    <textarea name="message" class="w-full p-4 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition bg-white/80 text-lg" rows="5" required placeholder="Napisz wiadomość do właściciela..."></textarea>
                                </div>
                                
                                <div class="flex justify-end space-x-4">
                                    <a href="index.php?action=dashboard" class="px-6 py-3 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-all duration-300 font-semibold">
                                        Anuluj
                                    </a>
                                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105 flex items-center space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                        <span>Wyślij wiadomość</span>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <!-- Tabs Navigation -->
                <div class="border-b border-slate-200 mb-8">
                    <nav class="flex space-x-8">
                        <button class="tab-button py-4 px-1 font-semibold text-slate-600 hover:text-blue-600 active" data-tab="offers">
                            Twoje oferty
                        </button>
                        <button class="tab-button py-4 px-1 font-semibold text-slate-600 hover:text-blue-600" data-tab="favorites">
                            Ulubione
                        </button>
                        <button class="tab-button py-4 px-1 font-semibold text-slate-600 hover:text-blue-600" data-tab="messages">
                            Wiadomości
                        </button>
                    </nav>
                </div>

                <!-- Offers Tab -->
                <div id="offers-tab" class="tab-content active">
                    <?php if (empty($offers)): ?>
                        <div class="glass-panel p-12 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-slate-400 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <h3 class="text-2xl font-playfair font-bold text-slate-800 mb-4">Brak utworzonych ofert</h3>
                            <p class="text-slate-600 text-lg mb-8">Rozpocznij od dodania swojej pierwszej nieruchomości do wynajęcia</p>
                            <a href="index.php?action=add_offer" class="inline-block bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white px-8 py-4 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105">
                                Dodaj nową ofertę
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            <?php foreach ($offers as $offer): ?>
                                <?php
                                $is_valid_offer = is_array($offer) && isset(
                                    $offer['id'],
                                    $offer['title'],
                                    $offer['city'],
                                    $offer['street'],
                                    $offer['price'],
                                    $offer['size'],
                                    $offer['description']
                                );
                                if (!$is_valid_offer):
                                ?>
                                    <div class="bg-red-50 text-red-700 p-6 rounded-2xl border border-red-200">Nieprawidłowe dane oferty.</div>
                                <?php else: ?>
                                    <div class="offer-card overflow-hidden">
                                        <?php if (!empty($offer['primary_image'])): ?>
                                            <div class="w-full h-48 overflow-hidden relative">
                                                <img src="<?php echo htmlspecialchars($offer['primary_image']); ?>" alt="Offer Image" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                                                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-full p-2 shadow-lg">
                                                    <span class="text-sm font-semibold px-2 text-slate-700">
                                                        Wyświetlenia: <?php echo htmlspecialchars($offer['visits']); ?> • 24h: <?php echo htmlspecialchars($offer['views_last_24h'] ?? 0); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-full h-48 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center relative">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-full p-2 shadow-lg">
                                                    <span class="text-sm font-semibold px-2 text-slate-700">
                                                        Wyświetlenia: <?php echo htmlspecialchars($offer['visits']); ?> • 24h: <?php echo htmlspecialchars($offer['views_last_24h'] ?? 0); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="p-6">
                                            <div class="flex justify-between items-start mb-3">
                                                <h3 class="text-xl font-semibold text-slate-800 truncate"><?php echo htmlspecialchars($offer['title']); ?></h3>
                                                <span class="text-2xl font-bold text-blue-600 whitespace-nowrap ml-4"><?php echo htmlspecialchars(number_format((float)$offer['price'], 0, ',', ' ')); ?> PLN</span>
                                            </div>
                                            <p class="text-slate-600 text-base mb-3 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <?php echo htmlspecialchars($offer['city']); ?>, <?php echo htmlspecialchars($offer['street']); ?>
                                            </p>
                                            <p class="text-slate-600 text-base mb-4 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                                </svg>
                                                <?php echo htmlspecialchars($offer['size']); ?> m²
                                            </p>
                                            <p class="text-slate-600 text-sm mb-4 line-clamp-2 leading-relaxed"><?php echo htmlspecialchars($offer['description']); ?></p>
                                            <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                                                <div class="flex space-x-4">
                                                    <a href="index.php?action=edit_offer&offer_id=<?php echo $offer['id']; ?>" class="text-sm text-blue-600 hover:text-blue-700 font-semibold flex items-center transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Edytuj
                                                    </a>
                                                    <a href="index.php?action=delete_offer&offer_id=<?php echo $offer['id']; ?>" class="text-sm text-red-600 hover:text-red-700 font-semibold flex items-center transition-colors" onclick="return confirm('Czy na pewno chcesz usunąć tę ofertę?');">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Usuń
                                                    </a>
                                                </div>
                                                <a href="index.php?action=view_offer&offer_id=<?php echo $offer['id']; ?>" class="text-sm text-white bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 px-4 py-2 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105">
                                                    Zobacz
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Favorites Tab -->
                <div id="favorites-tab" class="tab-content">
                    <?php if (empty($favorites)): ?>
                        <div class="glass-panel p-12 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-slate-400 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <h3 class="text-2xl font-playfair font-bold text-slate-800 mb-4">Brak ulubionych ofert</h3>
                            <p class="text-slate-600 text-lg mb-8">Zapisz interesujące oferty, aby łatwo je znaleźć później</p>
                            <a href="index.php?action=search" class="inline-block bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white px-8 py-4 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105">
                                Przeglądaj oferty
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            <?php foreach ($favorites as $favorite): ?>
                                <?php
                                $is_valid_favorite = is_array($favorite) && isset(
                                    $favorite['id'],
                                    $favorite['title'],
                                    $favorite['city'],
                                    $favorite['street'],
                                    $favorite['price'],
                                    $favorite['size'],
                                    $favorite['description']
                                );
                                if (!$is_valid_favorite):
                                ?>
                                    <div class="bg-red-50 text-red-700 p-6 rounded-2xl border border-red-200">Nieprawidłowe dane ulubionej oferty.</div>
                                <?php else: ?>
                                    <div class="offer-card overflow-hidden">
                                        <?php if (!empty($favorite['primary_image'])): ?>
                                            <div class="w-full h-48 overflow-hidden relative">
                                                <img src="<?php echo htmlspecialchars($favorite['primary_image']); ?>" alt="Offer Image" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                                                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-full p-2 shadow-lg">
                                                    <span class="text-sm font-semibold px-2 text-slate-700">
                                                        Wyświetlenia: <?php echo htmlspecialchars($favorite['visits']); ?> • 24h: <?php echo htmlspecialchars($favorite['views_last_24h'] ?? 0); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-full h-48 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center relative">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-full p-2 shadow-lg">
                                                    <span class="text-sm font-semibold px-2 text-slate-700">
                                                        Wyświetlenia: <?php echo htmlspecialchars($favorite['visits']); ?> • 24h: <?php echo htmlspecialchars($favorite['views_last_24h'] ?? 0); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="p-6">
                                            <div class="flex justify-between items-start mb-3">
                                                <h3 class="text-xl font-semibold text-slate-800 truncate"><?php echo htmlspecialchars($favorite['title']); ?></h3>
                                                <span class="text-2xl font-bold text-blue-600 whitespace-nowrap ml-4"><?php echo htmlspecialchars(number_format((float)$favorite['price'], 0, ',', ' ')); ?> PLN</span>
                                            </div>
                                            <p class="text-slate-600 text-base mb-3 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <?php echo htmlspecialchars($favorite['city']); ?>, <?php echo htmlspecialchars($favorite['street']); ?>
                                            </p>
                                            <p class="text-slate-600 text-base mb-4 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                                </svg>
                                                <?php echo htmlspecialchars($favorite['size']); ?> m²
                                            </p>
                                            <p class="text-slate-600 text-sm mb-4 line-clamp-2 leading-relaxed"><?php echo htmlspecialchars($favorite['description']); ?></p>
                                            <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                                                <a href="index.php?action=toggle_favorite&offer_id=<?php echo $favorite['id']; ?>" class="text-sm text-red-600 hover:text-red-700 font-semibold flex items-center transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Usuń z ulubionych
                                                </a>
                                                <a href="index.php?action=view_offer&offer_id=<?php echo $favorite['id']; ?>" class="text-sm text-white bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 px-4 py-2 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105">
                                                    Zobacz szczegóły
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Messages Tab -->
                <div id="messages-tab" class="tab-content">
                    <?php if (empty($conversations)): ?>
                        <div class="glass-panel p-12 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-slate-400 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <h3 class="text-2xl font-playfair font-bold text-slate-800 mb-4">Brak wiadomości</h3>
                            <p class="text-slate-600 text-lg mb-8">Rozpocznij konwersację, kontaktując się z właścicielami ofert</p>
                            <a href="index.php?action=search" class="inline-block bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white px-8 py-4 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105">
                                Przeglądaj oferty
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($conversations as $index => $conversation): ?>
                                <?php
                                $is_valid_conversation = is_array($conversation) && isset(
                                    $conversation['offer_id'],
                                    $conversation['offer_title'],
                                    $conversation['other_user_id'],
                                    $conversation['other_user'],
                                    $conversation['is_owner'],
                                    $conversation['messages'],
                                    $conversation['unread_count']
                                ) && is_array($conversation['messages']);
                                if (!$is_valid_conversation):
                                ?>
                                    <div class="bg-red-50 text-red-700 p-6 rounded-2xl border border-red-200">Nieprawidłowe dane konwersacji.</div>
                                <?php else: ?>
                                    <div class="conversation-card overflow-hidden" data-index="<?php echo $index; ?>">
                                        <div class="p-6 flex justify-between items-center cursor-pointer hover:bg-slate-50/50 transition-colors">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center text-blue-600 font-bold text-lg">
                                                    <?php echo strtoupper(substr($conversation['other_user'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <h3 class="font-semibold text-slate-800 text-lg"><?php echo htmlspecialchars($conversation['offer_title']); ?></h3>
                                                    <p class="text-slate-600">Z <?php echo htmlspecialchars($conversation['other_user']); ?></p>
                                                    <p class="text-slate-500 text-sm mt-1">
                                                        <?php echo $conversation['is_owner'] ? 'Jesteś właścicielem' : 'Jesteś zainteresowany'; ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <?php if ($conversation['unread_count'] > 0): ?>
                                                    <span class="unread-badge text-white text-sm font-bold rounded-full h-6 w-6 flex items-center justify-center shadow-lg">
                                                        <?php echo $conversation['unread_count']; ?>
                                                    </span>
                                                <?php endif; ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="conversation-content hidden px-6 pb-6">
                                            <div class="max-h-96 overflow-y-auto space-y-4 mb-6 border-t border-slate-100 pt-6">
                                                <?php foreach ($conversation['messages'] as $msg): ?>
                                                    <?php
                                                    $is_valid_message = is_array($msg) && isset(
                                                        $msg['message'],
                                                        $msg['sent_at'],
                                                        $msg['sender_id']
                                                    );
                                                    if (!$is_valid_message):
                                                    ?>
                                                        <div class="bg-red-50 text-red-700 p-4 rounded-2xl">Nieprawidłowe dane wiadomości.</div>
                                                    <?php else: ?>
                                                        <div class="flex <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'justify-end' : 'justify-start'; ?>">
                                                            <div class="max-w-xs md:max-w-md p-4 rounded-2xl message-bubble <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'bg-gradient-to-r from-blue-600 to-indigo-700 text-white' : 'bg-slate-100 text-slate-800'; ?> shadow-sm">
                                                                <p class="text-sm leading-relaxed"><?php echo htmlspecialchars($msg['message']); ?></p>
                                                                <p class="text-xs <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'text-blue-100' : 'text-slate-500'; ?> mt-2">
                                                                    <?php echo htmlspecialchars($msg['sent_at']); ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                            <!-- Reply Form -->
                                            <form method="POST" action="index.php?action=dashboard" class="flex items-start space-x-4">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                                <input type="hidden" name="send_message" value="1">
                                                <input type="hidden" name="receiver_id" value="<?php echo htmlspecialchars($conversation['other_user_id']); ?>">
                                                <input type="hidden" name="offer_id" value="<?php echo htmlspecialchars($conversation['offer_id']); ?>">
                                                <textarea name="message" class="flex-1 p-4 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition bg-white/80" rows="3" placeholder="Odpowiedz <?php echo htmlspecialchars($conversation['other_user']); ?>..." required></textarea>
                                                <button type="submit" class="h-full px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105 flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                // Update active tab button
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('active');
                });
                button.classList.add('active');

                // Show corresponding tab content
                const tabId = button.getAttribute('data-tab');
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(`${tabId}-tab`).classList.add('active');
            });
        });

        // Conversation functionality
        document.querySelectorAll('.conversation-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Prevent toggling if clicking inside the form or content
                if (e.target.closest('form') || e.target.closest('.conversation-content')) {
                    return;
                }

                // Collapse all other conversations
                document.querySelectorAll('.conversation-card').forEach(otherCard => {
                    if (otherCard !== this) {
                        otherCard.querySelector('.conversation-content').classList.add('hidden');
                        otherCard.querySelector('svg').classList.remove('rotate-180');
                    }
                });

                // Toggle the clicked conversation
                const content = this.querySelector('.conversation-content');
                const icon = this.querySelector('svg');
                const wasHidden = content.classList.contains('hidden');

                content.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');

                // If opening, mark messages as read
                if (wasHidden) {
                    const offerId = this.querySelector('input[name="offer_id"]').value;
                    const otherUserId = this.querySelector('input[name="receiver_id"]').value;
                    const csrfToken = this.querySelector('input[name="csrf_token"]').value;

                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=mark_read&offer_id=${offerId}&other_user_id=${otherUserId}&csrf_token=${encodeURIComponent(csrfToken)}`
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Remove unread badge
                            const badge = this.querySelector('.unread-badge');
                            if (badge) {
                                badge.remove();
                            }
                        } else {
                            console.error('Failed to mark messages as read:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error marking messages as read:', error);
                    });
                }
            });
        });
    </script>

</body>
</html>
