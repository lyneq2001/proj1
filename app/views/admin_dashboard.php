<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'auth.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'offers.php';

if (!isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    header("Location: index.php?action=home");
    exit;
}

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'CSRF token verification failed.');
        header("Location: index.php?action=admin_dashboard");
        exit;
    }

    try {
        if ($_POST['action'] === 'delete_user' && isset($_POST['user_id'])) {
            deleteUser((int)$_POST['user_id']);
            setFlashMessage('success', 'User deleted successfully.');
        } elseif ($_POST['action'] === 'delete_offer' && isset($_POST['offer_id'])) {
            deleteOfferAdmin((int)$_POST['offer_id']);
            setFlashMessage('success', 'Offer deleted successfully.');
        } elseif ($_POST['action'] === 'delete_message' && isset($_POST['message_id'])) {
            deleteMessage((int)$_POST['message_id']);
            setFlashMessage('success', 'Message deleted successfully.');
        }
    } catch (Exception $e) {
        setFlashMessage('error', 'Error: ' . $e->getMessage());
    }
    header("Location: index.php?action=admin_dashboard");
    exit;
}

// Fetch data for dashboard
$users = getAllUsers();
$offers = getAllOffers();
$messages = getAllMessages();
$reports = getReports();
$platformStats = getPlatformStatistics();
$aiOfferStats = getAiOfferUsageSummary();
$aiUserStats = getAiOfferUsageByUser();

// Get counts for summary
global $pdo;
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_offers = $pdo->query("SELECT COUNT(*) FROM offers")->fetchColumn();
$total_messages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Apartment Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .conversation-card, .stat-card, .offer-card, .glass-panel {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 30px 60px -45px rgba(15, 23, 42, 0.3);
        }
        .conversation-card:hover, .stat-card:hover, .offer-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 35px 70px -40px rgba(30, 64, 175, 0.25);
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
        .table-container {
            max-height: 500px;
            overflow-y: auto;
        }
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .action-btn {
            transition: all 0.2s;
        }
        .action-btn:hover {
            transform: scale(1.05);
        }
        .truncate-text {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen font-roboto">
    <?php include __DIR__ . '/../header.php'; ?>
    
    <main class="page-shell py-12">
        <div class="container mx-auto px-4 sm:px-6">
            <!-- Page Heading -->
            <div class="page-heading">
                <span class="page-heading__eyebrow">Panel administracyjny</span>
                <h1 class="page-heading__title">Kontroluj platformę ApartmentRental</h1>
                <p class="page-heading__subtitle">Monitoruj statystyki, zarządzaj użytkownikami i reaguj na zgłoszenia w jednym miejscu.</p>
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

            <!-- Stats Overview -->
            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="stat-card p-6 border-l-4 border-blue-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-slate-600 font-medium mb-2">Użytkownicy</p>
                            <p class="text-3xl font-bold text-slate-800"><?php echo number_format($total_users); ?></p>
                            <p class="text-xs text-slate-500 mt-1">Nowi (7 dni): <?php echo number_format((int)($platformStats['new_users_week'] ?? 0)); ?></p>
                        </div>
                        <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card p-6 border-l-4 border-emerald-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-slate-600 font-medium mb-2">Aktywne oferty</p>
                            <p class="text-3xl font-bold text-slate-800"><?php echo number_format((int)($platformStats['active_offers'] ?? 0)); ?></p>
                            <p class="text-xs text-slate-500 mt-1">Oczekujące: <?php echo number_format((int)($platformStats['pending_offers'] ?? 0)); ?></p>
                        </div>
                        <div class="p-3 rounded-full bg-emerald-50 text-emerald-600">
                            <i class="fas fa-home text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card p-6 border-l-4 border-purple-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-slate-600 font-medium mb-2">Aktywność komunikacji</p>
                            <p class="text-3xl font-bold text-slate-800"><?php echo number_format($total_messages); ?></p>
                            <p class="text-xs text-slate-500 mt-1">Wiadomości (7 dni): <?php echo number_format((int)($platformStats['messages_week'] ?? 0)); ?></p>
                        </div>
                        <div class="p-3 rounded-full bg-purple-50 text-purple-600">
                            <i class="fas fa-envelope text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card p-6 border-l-4 <?php echo ($platformStats['pending_reports'] ?? 0) ? 'border-red-500' : 'border-slate-400'; ?>">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-slate-600 font-medium mb-2">Zgłoszenia treści</p>
                            <p class="text-3xl font-bold <?php echo ($platformStats['pending_reports'] ?? 0) ? 'text-red-600' : 'text-slate-600'; ?>"><?php echo number_format((int)($platformStats['pending_reports'] ?? 0)); ?></p>
                            <p class="text-xs text-slate-500 mt-1">Ulubione: <?php echo number_format((int)($platformStats['favorites_total'] ?? 0)); ?></p>
                        </div>
                        <div class="p-3 rounded-full bg-red-50 text-red-600">
                            <i class="fas fa-flag text-xl"></i>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tabs Navigation -->
            <div class="border-b border-slate-200 mb-8">
                <nav class="flex space-x-8">
                    <button class="tab-button py-4 px-1 font-semibold text-slate-600 hover:text-blue-600 active" data-tab="users">
                        Użytkownicy
                    </button>
                    <button class="tab-button py-4 px-1 font-semibold text-slate-600 hover:text-blue-600" data-tab="offers">
                        Oferty
                    </button>
                    <button class="tab-button py-4 px-1 font-semibold text-slate-600 hover:text-blue-600" data-tab="messages">
                        Wiadomości
                    </button>
                    <button class="tab-button py-4 px-1 font-semibold text-slate-600 hover:text-blue-600" data-tab="moderation">
                        Moderacja
                    </button>
                    <button class="tab-button py-4 px-1 font-semibold text-slate-600 hover:text-blue-600" data-tab="ai-insights">
                        AI analityka
                    </button>
                </nav>
            </div>

            <!-- Users Tab -->
            <div id="users-tab" class="tab-content active">
                <div class="glass-panel p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-playfair font-bold text-slate-800 flex items-center">
                            <i class="fas fa-users mr-3 text-blue-600"></i>
                            Zarządzanie użytkownikami
                        </h2>
                        <div class="relative">
                            <input type="text" id="user-search" placeholder="Szukaj użytkowników..." class="pl-10 pr-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition bg-white/80">
                            <i class="fas fa-search absolute left-3 top-3.5 text-slate-400"></i>
                        </div>
                    </div>

                    <?php if (empty($users)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-user-slash text-6xl text-slate-300 mb-4"></i>
                            <h3 class="text-xl font-semibold text-slate-600 mb-2">Brak użytkowników</h3>
                            <p class="text-slate-500">Nie znaleziono żadnych użytkowników w systemie.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container rounded-xl border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50 sticky-header">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Użytkownik</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Rola</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Telefon</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Zarejestrowany</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Akcje</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <?php foreach ($users as $user): ?>
                                        <tr class="hover:bg-slate-50 user-row transition-colors" data-username="<?php echo htmlspecialchars(strtolower($user['username'] ?? '')); ?>" data-email="<?php echo htmlspecialchars(strtolower($user['email'] ?? '')); ?>">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><?php echo htmlspecialchars($user['id'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center text-blue-600 font-bold">
                                                        <?php echo strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($user['username'] ?? 'Unknown'); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo ($user['role'] ?? 'user') === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                                    <?php echo ucfirst($user['role'] ?? 'user'); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                                <?php echo htmlspecialchars($user['phone'] ?? ''); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                                <?php echo isset($user['created_at']) ? date('d.m.Y', strtotime($user['created_at'])) : 'N/A'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="index.php?action=edit_user&user_id=<?php echo $user['id'] ?? ''; ?>" class="action-btn text-blue-600 hover:text-blue-700 p-2 rounded-lg hover:bg-blue-50 transition-colors" title="Edytuj użytkownika">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if (($user['role'] ?? 'user') !== 'admin'): ?>
                                                    <form method="POST" action="index.php?action=admin_dashboard" onsubmit="return confirm('Czy na pewno chcesz usunąć tego użytkownika? Wszystkie jego oferty i wiadomości również zostaną usunięte.');" class="inline-block">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id'] ?? ''; ?>">
                                                        <button type="submit" class="action-btn text-red-600 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Usuń użytkownika">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-slate-400 p-2" title="Administratorów nie można usunąć">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Offers Tab -->
            <div id="offers-tab" class="tab-content">
                <div class="glass-panel p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-playfair font-bold text-slate-800 flex items-center">
                            <i class="fas fa-home mr-3 text-emerald-600"></i>
                            Zarządzanie ofertami
                        </h2>
                        <div class="relative">
                            <input type="text" id="offer-search" placeholder="Szukaj ofert..." class="pl-10 pr-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition bg-white/80">
                            <i class="fas fa-search absolute left-3 top-3.5 text-slate-400"></i>
                        </div>
                    </div>

                    <?php if (empty($offers)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-home text-6xl text-slate-300 mb-4"></i>
                            <h3 class="text-xl font-semibold text-slate-600 mb-2">Brak ofert</h3>
                            <p class="text-slate-500">Nie znaleziono żadnych ofert w systemie.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container rounded-xl border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50 sticky-header">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Tytuł</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Miasto</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Cena (PLN)</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Akcje</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <?php $statusOptions = [
                                        'active' => 'Aktywne',
                                        'pending' => 'Oczekujące',
                                        'inactive' => 'Wstrzymane',
                                        'archived' => 'Zarchiwizowane',
                                        'suspended' => 'Zablokowane'
                                    ]; ?>
                                    <?php foreach ($offers as $offer): ?>
                                        <tr class="hover:bg-slate-50 offer-row transition-colors" data-title="<?php echo htmlspecialchars(strtolower($offer['title'] ?? '')); ?>" data-city="<?php echo htmlspecialchars(strtolower($offer['city'] ?? '')); ?>">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><?php echo htmlspecialchars($offer['id'] ?? ''); ?></td>
                                            <td class="px-6 py-4 text-sm text-slate-900">
                                                <div class="font-semibold"><?php echo htmlspecialchars($offer['title'] ?? 'No Title'); ?></div>
                                                <div class="text-xs text-slate-500 mt-1">przez <?php echo htmlspecialchars($offer['owner_username'] ?? 'Unknown User'); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                                <i class="fas fa-map-marker-alt mr-2 text-slate-400"></i>
                                                <?php echo htmlspecialchars($offer['city'] ?? 'N/A'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                                <?php echo isset($offer['price']) ? number_format($offer['price'], 0, ',', ' ') : '0'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo ($offer['status'] ?? 'inactive') === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                    <?php echo $statusOptions[$offer['status'] ?? 'inactive'] ?? ucfirst($offer['status'] ?? 'inactive'); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-3">
                                                    <a href="index.php?action=edit_offer&offer_id=<?php echo $offer['id'] ?? ''; ?>" class="action-btn text-blue-600 hover:text-blue-700 p-2 rounded-lg hover:bg-blue-50 transition-colors" title="Edytuj ofertę">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="index.php?action=update_offer_status" class="flex items-center space-x-2">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                                        <input type="hidden" name="offer_id" value="<?php echo $offer['id'] ?? ''; ?>">
                                                        <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white/80">
                                                            <?php foreach ($statusOptions as $value => $label): ?>
                                                                <option value="<?php echo $value; ?>" <?php echo (($offer['status'] ?? '') === $value) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" class="action-btn text-emerald-600 hover:text-emerald-700 p-2 rounded-lg hover:bg-emerald-50 transition-colors" title="Zaktualizuj status">
                                                            <i class="fas fa-save"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="index.php?action=admin_dashboard" onsubmit="return confirm('Czy na pewno chcesz usunąć tę ofertę?');" class="inline-block">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                                        <input type="hidden" name="action" value="delete_offer">
                                                        <input type="hidden" name="offer_id" value="<?php echo $offer['id'] ?? ''; ?>">
                                                        <button type="submit" class="action-btn text-red-600 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Usuń ofertę">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Messages Tab -->
            <div id="messages-tab" class="tab-content">
                <div class="glass-panel p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-playfair font-bold text-slate-800 flex items-center">
                            <i class="fas fa-envelope mr-3 text-purple-600"></i>
                            Zarządzanie wiadomościami
                        </h2>
                        <div class="relative">
                            <input type="text" id="message-search" placeholder="Szukaj wiadomości..." class="pl-10 pr-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition bg-white/80">
                            <i class="fas fa-search absolute left-3 top-3.5 text-slate-400"></i>
                        </div>
                    </div>

                    <?php if (empty($messages)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-envelope-open-text text-6xl text-slate-300 mb-4"></i>
                            <h3 class="text-xl font-semibold text-slate-600 mb-2">Brak wiadomości</h3>
                            <p class="text-slate-500">Nie znaleziono żadnych wiadomości w systemie.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container rounded-xl border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50 sticky-header">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Od/Do</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Oferta</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Wiadomość</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Data</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Akcje</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <?php foreach ($messages as $message): ?>
                                        <tr class="hover:bg-slate-50 message-row transition-colors" data-sender="<?php echo htmlspecialchars(strtolower($message['sender_username'] ?? '')); ?>" data-receiver="<?php echo htmlspecialchars(strtolower($message['receiver_username'] ?? '')); ?>" data-offer="<?php echo htmlspecialchars(strtolower($message['offer_title'] ?? '')); ?>">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><?php echo htmlspecialchars($message['id'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-slate-900">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-user-circle text-slate-400 mr-2"></i>
                                                        <?php echo htmlspecialchars($message['sender_username'] ?? 'Unknown'); ?>
                                                    </div>
                                                    <div class="mt-1 flex items-center text-slate-600">
                                                        <i class="fas fa-arrow-right text-xs text-slate-400 mr-2"></i>
                                                        <?php echo htmlspecialchars($message['receiver_username'] ?? 'Unknown'); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                                <?php if (isset($message['offer_id']) && isset($message['offer_title'])): ?>
                                                    <a href="index.php?action=offer_details&id=<?php echo $message['offer_id']; ?>" class="text-blue-600 hover:text-blue-700 hover:underline font-medium">
                                                        <?php echo htmlspecialchars($message['offer_title']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-slate-400">Brak powiązanej oferty</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-600 truncate-text" title="<?php echo htmlspecialchars($message['message'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($message['message'] ?? 'Brak treści wiadomości'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                                <?php echo isset($message['created_at']) ? date('d.m.Y H:i', strtotime($message['created_at'])) : 'N/A'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <form method="POST" action="index.php?action=admin_dashboard" onsubmit="return confirm('Czy na pewno chcesz usunąć tę wiadomość?');" class="inline-block">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                                    <input type="hidden" name="action" value="delete_message">
                                                    <input type="hidden" name="message_id" value="<?php echo $message['id'] ?? ''; ?>">
                                                    <button type="submit" class="action-btn text-red-600 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Usuń wiadomość">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Moderation Tab -->
            <div id="moderation-tab" class="tab-content">
                <div class="glass-panel p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-playfair font-bold text-slate-800 flex items-center">
                            <i class="fas fa-flag mr-3 text-red-600"></i>
                            Moderacja zgłoszeń
                        </h2>
                        <div class="flex items-center space-x-4 text-sm text-slate-600">
                            <span class="flex items-center"><span class="inline-block w-3 h-3 rounded-full bg-red-400 mr-2"></span>Oczekuje</span>
                            <span class="flex items-center"><span class="inline-block w-3 h-3 rounded-full bg-yellow-400 mr-2"></span>W trakcie</span>
                            <span class="flex items-center"><span class="inline-block w-3 h-3 rounded-full bg-green-400 mr-2"></span>Zamknięte</span>
                        </div>
                    </div>

                    <?php if (empty($reports)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-flag text-6xl text-slate-300 mb-4"></i>
                            <h3 class="text-xl font-semibold text-slate-600 mb-2">Brak zgłoszeń</h3>
                            <p class="text-slate-500">Nie znaleziono żadnych zgłoszeń do moderacji.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container rounded-xl border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50 sticky-header">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Oferta</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Zgłaszający</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Notatka administratora</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Akcje</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <?php foreach ($reports as $report): ?>
                                        <?php
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                        if (($report['status'] ?? '') === 'resolved') {
                                            $statusClass = 'bg-green-100 text-green-800';
                                        } elseif (($report['status'] ?? '') === 'pending') {
                                            $statusClass = 'bg-red-100 text-red-800';
                                        }
                                        ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><?php echo htmlspecialchars($report['id'] ?? ''); ?></td>
                                            <td class="px-6 py-4 text-sm text-slate-900">
                                                <div class="font-semibold"><?php echo htmlspecialchars($report['offer_title'] ?? ''); ?></div>
                                                <div class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars($report['owner_username'] ?? ''); ?></div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-600">
                                                <div><?php echo htmlspecialchars($report['reporter_username'] ?? ''); ?></div>
                                                <div class="text-xs text-slate-400"><?php echo isset($report['created_at']) ? date('d.m.Y H:i', strtotime($report['created_at'])) : ''; ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($report['status'] ?? 'pending'); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-600">
                                                <?php echo $report['admin_note'] ? htmlspecialchars($report['admin_note']) : '<span class="text-slate-400">Brak notatek</span>'; ?>
                                                <?php if (!empty($report['handled_by_username'])): ?>
                                                    <div class="text-xs text-slate-400 mt-1">Moderator: <?php echo htmlspecialchars($report['handled_by_username']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-600">
                                                <form method="POST" action="index.php?action=moderate_report" class="space-y-2">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                                    <input type="hidden" name="report_id" value="<?php echo $report['id'] ?? ''; ?>">
                                                    <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white/80">
                                                        <option value="pending" <?php echo ($report['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Oczekujące</option>
                                                        <option value="in_review" <?php echo ($report['status'] ?? '') === 'in_review' ? 'selected' : ''; ?>>W trakcie analizy</option>
                                                        <option value="resolved" <?php echo ($report['status'] ?? '') === 'resolved' ? 'selected' : ''; ?>>Zamknięte</option>
                                                    </select>
                                                    <textarea name="admin_note" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white/80" placeholder="Notatka (opcjonalnie)"><?php echo htmlspecialchars($report['admin_note'] ?? ''); ?></textarea>
                                                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-all duration-300 font-semibold shadow-lg hover:shadow-xl">
                                                        Zapisz zmiany
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- AI Insights Tab -->
            <div id="ai-insights-tab" class="tab-content">
                <div class="glass-panel p-6 space-y-10">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-playfair font-bold text-slate-800 flex items-center">
                                <i class="fas fa-robot mr-3 text-indigo-600"></i>
                                Wykorzystanie ofert AI
                            </h2>
                            <span class="text-sm text-slate-500">Top oferty rekomendowane przez AI</span>
                        </div>

                        <?php if (empty($aiOfferStats)): ?>
                            <div class="text-center py-12">
                                <i class="fas fa-robot text-6xl text-slate-300 mb-4"></i>
                                <h3 class="text-xl font-semibold text-slate-600 mb-2">Brak danych AI</h3>
                                <p class="text-slate-500">Użytkownicy nie klikali jeszcze ofert AI.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-container rounded-xl border border-slate-200">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50 sticky-header">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Oferta</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Kliknięcia AI</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Unikalni użytkownicy</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">👍</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">👎</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Ostatnia aktywność</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-200">
                                        <?php foreach ($aiOfferStats as $stat): ?>
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><?php echo htmlspecialchars($stat['id'] ?? ''); ?></td>
                                                <td class="px-6 py-4 text-sm text-slate-900">
                                                    <a href="index.php?action=view_offer&offer_id=<?php echo $stat['id']; ?>" class="font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                                                        <?php echo htmlspecialchars($stat['title'] ?? ''); ?>
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700"><?php echo number_format((int)($stat['usage_count'] ?? 0)); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700"><?php echo number_format((int)($stat['unique_users'] ?? 0)); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-emerald-600 font-semibold"><?php echo number_format((int)($stat['likes'] ?? 0)); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-semibold"><?php echo number_format((int)($stat['dislikes'] ?? 0)); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                                    <?php echo !empty($stat['last_used_at']) ? date('d.m.Y H:i', strtotime($stat['last_used_at'])) : '—'; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-playfair font-bold text-slate-800 flex items-center">
                                <i class="fas fa-user-clock mr-3 text-indigo-600"></i>
                                Aktywność użytkowników w ofertach AI
                            </h3>
                            <span class="text-sm text-slate-500">Najczęściej korzystający</span>
                        </div>

                        <?php if (empty($aiUserStats)): ?>
                            <p class="text-sm text-slate-500">Brak aktywności użytkowników w ofertach AI.</p>
                        <?php else: ?>
                            <div class="table-container rounded-xl border border-slate-200">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50 sticky-header">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Użytkownik</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Kliknięcia AI</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Ostatnia aktywność</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-200">
                                        <?php foreach ($aiUserStats as $stat): ?>
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><?php echo htmlspecialchars($stat['id'] ?? ''); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($stat['username'] ?? ''); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php echo htmlspecialchars($stat['email'] ?? ''); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700"><?php echo number_format((int)($stat['usage_count'] ?? 0)); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                                    <?php echo !empty($stat['last_used_at']) ? date('d.m.Y H:i', strtotime($stat['last_used_at'])) : '—'; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
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

        // Search functionality for each section
        document.getElementById('user-search')?.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.user-row').forEach(row => {
                const username = row.dataset.username;
                const email = row.dataset.email;
                if (username.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        document.getElementById('offer-search')?.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.offer-row').forEach(row => {
                const title = row.dataset.title;
                const city = row.dataset.city;
                if (title.includes(searchTerm) || city.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        document.getElementById('message-search')?.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.message-row').forEach(row => {
                const sender = row.dataset.sender;
                const receiver = row.dataset.receiver;
                const offer = row.dataset.offer;
                if (sender.includes(searchTerm) || receiver.includes(searchTerm) || offer.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>

</body>
</html>
