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

// Handle AJAX request to mark messages as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_read') {
    header('Content-Type: application/json');
    if (!verifyCsrfToken($_POST['csrf_token'])) {
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            600: '#1D4ED8',
                            700: '#1E40AF',
                        },
                        secondary: {
                            500: '#6B7280',
                            600: '#4B5563',
                        },
                        accent: {
                            500: '#10B981',
                            600: '#059669',
                        },
                        dark: '#111827',
                        light: '#F9FAFB',
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                    boxShadow: {
                        'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                        'card-hover': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
                    },
                },
            },
        }
    </script>
    <style>
        .conversation-card {
            transition: all 0.3s ease;
        }
        .conversation-card:hover {
            transform: translateY(-2px);
        }
        .message-bubble {
            max-width: 80%;
        }
        .unread-badge {
            min-width: 1.5rem;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tab-button.active {
            border-bottom: 2px solid #1D4ED8;
            color: #1D4ED8;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <?php include 'header.php'; ?>
    <main class="container mx-auto px-4 py-8">
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
            <div class="mb-6 p-4 rounded-lg shadow <?php echo $flash['type'] === 'error' ? 'bg-red-100 text-red-700 border-l-4 border-red-500' : 'bg-green-100 text-green-700 border-l-4 border-green-500'; ?> flex items-start">
                <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $flash['type'] === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M5 13l4 4L19 7'; ?>"></path>
                </svg>
                <div><?php echo htmlspecialchars($flash['message']); ?></div>
            </div>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar -->
            <aside class="w-full md:w-64 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-card p-6 mb-6">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-16 h-16 rounded-full bg-primary-600 flex items-center justify-center text-white text-2xl font-bold">
                            <?php echo strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                        </div>
                        <div>
                            <h2 class="font-bold text-dark"><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></h2>
                            <p class="text-secondary-500 text-sm"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <a href="index.php?action=add_offer" class="flex items-center space-x-2 text-primary-600 hover:text-primary-700 font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span>Add New Offer</span>
                        </a>
                        <a href="index.php?action=search" class="flex items-center space-x-2 text-secondary-600 hover:text-dark font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span>Browse Offers</span>
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-card p-6">
                    <h3 class="font-semibold text-dark mb-4">Quick Stats</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-secondary-500 text-sm">Your Offers</p>
                            <p class="text-2xl font-bold text-dark"><?php echo count($offers); ?></p>
                        </div>
                        <div>
                            <p class="text-secondary-500 text-sm">Favorites</p>
                            <p class="text-2xl font-bold text-dark"><?php echo count($favorites); ?></p>
                        </div>
                        <div>
                            <p class="text-secondary-500 text-sm">Messages</p>
                            <p class="text-2xl font-bold text-dark"><?php echo count($conversations); ?></p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-dark mb-8">Dashboard Overview</h1>

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
                    <section class="mb-8 bg-white rounded-xl shadow-card p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-semibold text-dark">Send Message</h2>
                            <a href="index.php?action=dashboard" class="text-secondary-500 hover:text-dark">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </div>
                        <?php if (!$offer || !$receiver): ?>
                            <div class="bg-red-50 text-red-700 p-4 rounded-lg">
                                Invalid offer or recipient.
                            </div>
                        <?php else: ?>
                            <form method="POST" action="index.php?action=dashboard" class="max-w-2xl">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                <input type="hidden" name="send_message" value="1">
                                <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
                                <input type="hidden" name="offer_id" value="<?php echo $offer_id; ?>">
                                <div class="mb-4">
                                    <label class="block text-secondary-500 text-sm font-medium mb-1">To</label>
                                    <div class="p-3 bg-gray-50 rounded-lg"><?php echo htmlspecialchars($receiver['username']); ?></div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-secondary-500 text-sm font-medium mb-1">About Offer</label>
                                    <div class="p-3 bg-gray-50 rounded-lg"><?php echo htmlspecialchars($offer['title']); ?></div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-secondary-500 text-sm font-medium mb-1">Message</label>
                                    <textarea name="message" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition" rows="4" required placeholder="Write your message here..."></textarea>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <a href="index.php?action=dashboard" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</a>
                                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition font-medium flex items-center space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                        <span>Send Message</span>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <!-- Tabs Navigation -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex space-x-8">
                        <button class="tab-button py-3 px-1 font-medium text-secondary-600 hover:text-dark active" data-tab="offers">Your Offers</button>
                        <button class="tab-button py-3 px-1 font-medium text-secondary-600 hover:text-dark" data-tab="favorites">Favorites</button>
                        <button class="tab-button py-3 px-1 font-medium text-secondary-600 hover:text-dark" data-tab="messages">Messages</button>
                    </nav>
                </div>

                <!-- Offers Tab -->
                <div id="offers-tab" class="tab-content active">
                    <?php if (empty($offers)): ?>
                        <div class="bg-white rounded-xl shadow-card p-8 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-secondary-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <h3 class="text-xl font-semibold text-dark mb-2">No offers created yet</h3>
                            <p class="text-secondary-500 mb-4">Start by adding your first property to rent</p>
                            <a href="index.php?action=add_offer" class="inline-block px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition font-medium">
                                Add New Offer
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                                    <div class="bg-red-50 text-red-700 p-4 rounded-lg">Invalid offer data.</div>
                                <?php else: ?>
                                    <div class="bg-white rounded-xl shadow-card overflow-hidden hover:shadow-card-hover transition">
                                        <?php if (!empty($offer['primary_image'])): ?>
                                            <div class="w-full h-48 overflow-hidden relative">
                                                <img src="<?php echo htmlspecialchars($offer['primary_image']); ?>" alt="Offer Image" class="w-full h-full object-cover">
                                                <div class="absolute top-3 right-3 bg-white/90 rounded-full p-1.5 shadow">
                                                    <span class="text-sm font-medium px-2"><?php echo htmlspecialchars($offer['visits']); ?> views</span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-full h-48 bg-gray-100 flex items-center justify-center relative">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <div class="absolute top-3 right-3 bg-white/90 rounded-full p-1.5 shadow">
                                                    <span class="text-sm font-medium px-2"><?php echo htmlspecialchars($offer['visits']); ?> views</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="p-5">
                                            <div class="flex justify-between items-start mb-2">
                                                <h3 class="text-lg font-semibold text-dark truncate"><?php echo htmlspecialchars($offer['title']); ?></h3>
                                                <span class="text-lg font-bold text-primary-600 whitespace-nowrap"><?php echo htmlspecialchars($offer['price']); ?> PLN</span>
                                            </div>
                                            <p class="text-secondary-500 text-sm mb-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <?php echo htmlspecialchars($offer['city']); ?>, <?php echo htmlspecialchars($offer['street']); ?>
                                            </p>
                                            <p class="text-secondary-500 text-sm mb-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                                </svg>
                                                <?php echo htmlspecialchars($offer['size']); ?> m²
                                            </p>
                                            <p class="text-secondary-500 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($offer['description']); ?></p>
                                            <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                                                <div class="flex space-x-2">
                                                    <a href="index.php?action=edit_offer&offer_id=<?php echo $offer['id']; ?>" class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Edit
                                                    </a>
                                                    <a href="index.php?action=delete_offer&offer_id=<?php echo $offer['id']; ?>" class="text-sm text-red-600 hover:text-red-700 font-medium flex items-center" onclick="return confirm('Are you sure you want to delete this offer?');">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Delete
                                                    </a>
                                                </div>
                                                <a href="index.php?action=view_offer&offer_id=<?php echo $offer['id']; ?>" class="text-sm text-white bg-primary-600 hover:bg-primary-700 px-3 py-1 rounded-lg transition font-medium">
                                                    View
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
                        <div class="bg-white rounded-xl shadow-card p-8 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-secondary-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <h3 class="text-xl font-semibold text-dark mb-2">No favorite offers yet</h3>
                            <p class="text-secondary-500 mb-4">Save interesting offers to find them easily later</p>
                            <a href="index.php?action=search" class="inline-block px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition font-medium">
                                Browse Offers
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                                    <div class="bg-red-50 text-red-700 p-4 rounded-lg">Invalid favorite offer data.</div>
                                <?php else: ?>
                                    <div class="bg-white rounded-xl shadow-card overflow-hidden hover:shadow-card-hover transition">
                                        <?php if (!empty($favorite['primary_image'])): ?>
                                            <div class="w-full h-48 overflow-hidden relative">
                                                <img src="<?php echo htmlspecialchars($favorite['primary_image']); ?>" alt="Offer Image" class="w-full h-full object-cover">
                                                <div class="absolute top-3 right-3 bg-white/90 rounded-full p-1.5 shadow">
                                                    <span class="text-sm font-medium px-2"><?php echo htmlspecialchars($favorite['visits']); ?> views</span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-full h-48 bg-gray-100 flex items-center justify-center relative">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <div class="absolute top-3 right-3 bg-white/90 rounded-full p-1.5 shadow">
                                                    <span class="text-sm font-medium px-2"><?php echo htmlspecialchars($favorite['visits']); ?> views</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="p-5">
                                            <div class="flex justify-between items-start mb-2">
                                                <h3 class="text-lg font-semibold text-dark truncate"><?php echo htmlspecialchars($favorite['title']); ?></h3>
                                                <span class="text-lg font-bold text-primary-600 whitespace-nowrap"><?php echo htmlspecialchars($favorite['price']); ?> PLN</span>
                                            </div>
                                            <p class="text-secondary-500 text-sm mb-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <?php echo htmlspecialchars($favorite['city']); ?>, <?php echo htmlspecialchars($favorite['street']); ?>
                                            </p>
                                            <p class="text-secondary-500 text-sm mb-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                                </svg>
                                                <?php echo htmlspecialchars($favorite['size']); ?> m²
                                            </p>
                                            <p class="text-secondary-500 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($favorite['description']); ?></p>
                                            <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                                                <a href="index.php?action=toggle_favorite&offer_id=<?php echo $favorite['id']; ?>" class="text-sm text-red-600 hover:text-red-700 font-medium flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Remove
                                                </a>
                                                <a href="index.php?action=view_offer&offer_id=<?php echo $favorite['id']; ?>" class="text-sm text-white bg-primary-600 hover:bg-primary-700 px-3 py-1 rounded-lg transition font-medium">
                                                    View Details
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
                        <div class="bg-white rounded-xl shadow-card p-8 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-secondary-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <h3 class="text-xl font-semibold text-dark mb-2">No messages yet</h3>
                            <p class="text-secondary-500 mb-4">Start a conversation by contacting offer owners</p>
                            <a href="index.php?action=search" class="inline-block px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition font-medium">
                                Browse Offers
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
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
                                    <div class="bg-red-50 text-red-700 p-4 rounded-lg">Invalid conversation data.</div>
                                <?php else: ?>
                                    <div class="bg-white rounded-xl shadow-card overflow-hidden conversation-card" data-index="<?php echo $index; ?>">
                                        <div class="p-5 flex justify-between items-center cursor-pointer">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-secondary-500">
                                                    <?php echo strtoupper(substr($conversation['other_user'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <h3 class="font-semibold text-dark"><?php echo htmlspecialchars($conversation['offer_title']); ?></h3>
                                                    <p class="text-secondary-500 text-sm">With <?php echo htmlspecialchars($conversation['other_user']); ?></p>
                                                    <p class="text-secondary-400 text-xs mt-1">
                                                        <?php echo $conversation['is_owner'] ? 'You are the owner' : 'You are interested'; ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <?php if ($conversation['unread_count'] > 0): ?>
                                                    <span class="unread-badge bg-red-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center">
                                                        <?php echo $conversation['unread_count']; ?>
                                                    </span>
                                                <?php endif; ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-secondary-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="conversation-content hidden px-5 pb-5">
                                            <div class="max-h-96 overflow-y-auto space-y-4 mb-4 border-t border-gray-100 pt-4">
                                                <?php foreach ($conversation['messages'] as $msg): ?>
                                                    <?php
                                                    $is_valid_message = is_array($msg) && isset(
                                                        $msg['message'],
                                                        $msg['sent_at'],
                                                        $msg['sender_id']
                                                    );
                                                    if (!$is_valid_message):
                                                    ?>
                                                        <div class="bg-red-50 text-red-700 p-3 rounded-lg">Invalid message data.</div>
                                                    <?php else: ?>
                                                        <div class="flex <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'justify-end' : 'justify-start'; ?>">
                                                            <div class="max-w-xs md:max-w-md p-3 rounded-lg message-bubble <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'bg-primary-600 text-white' : 'bg-gray-100 text-dark'; ?>">
                                                                <p class="text-sm"><?php echo htmlspecialchars($msg['message']); ?></p>
                                                                <p class="text-xs <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'text-primary-100' : 'text-secondary-500'; ?> mt-1">
                                                                    <?php echo htmlspecialchars($msg['sent_at']); ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                            <!-- Reply Form -->
                                            <form method="POST" action="index.php?action=dashboard" class="flex items-start space-x-2">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                                <input type="hidden" name="send_message" value="1">
                                                <input type="hidden" name="receiver_id" value="<?php echo htmlspecialchars($conversation['other_user_id']); ?>">
                                                <input type="hidden" name="offer_id" value="<?php echo htmlspecialchars($conversation['offer_id']); ?>">
                                                <textarea name="message" class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition" rows="2" placeholder="Reply to <?php echo htmlspecialchars($conversation['other_user']); ?>..." required></textarea>
                                                <button type="submit" class="h-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition font-medium flex items-center">
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