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

// Fetch data for dashboard with pagination parameters
$users = getAllUsers();
$offers = getAllOffers();
$messages = getAllMessages();

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
    <meta name="description" content="Admin dashboard for managing Luxury Apartments platform.">
    <meta name="keywords" content="admin dashboard, luksusowe apartamenty, wynajem apartamentów">
    <title>Admin Dashboard - Luxury Apartments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'playfair': ['"Playfair Display"', 'serif'],
                        'roboto': ['Roboto', 'sans-serif'],
                    },
                    colors: {
                        'gold': '#D4AF37',
                        'dark-blue': '#1E3656',
                        'primary': '#1D4ED8',
                        'secondary': '#6B7280',
                        'accent': '#10B981',
                        'danger': '#EF4444',
                    }
                }
            }
        }
    </script>
    <style>
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
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
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 dark:text-white min-h-screen font-roboto">
    <?php include 'header.php'; ?>
    
    <main class="container mx-auto px-4 py-8">
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
            <div class="mb-6 p-4 rounded-lg shadow-md <?php echo $flash['type'] === 'error' ? 'bg-red-100 text-red-700 border-l-4 border-red-500' : 'bg-green-100 text-green-700 border-l-4 border-green-500'; ?>">
                <div class="flex items-center">
                    <i class="fas <?php echo $flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> mr-3"></i>
                    <span><?php echo htmlspecialchars($flash['message']); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-playfair font-bold text-dark-blue">
                <i class="fas fa-tachometer-alt mr-2"></i>Admin Control Panel
            </h1>
            <div class="flex items-center space-x-4">
                <span class="px-3 py-1 bg-dark-blue text-white rounded-full text-sm">
                    <i class="fas fa-user-shield mr-1"></i> ADMIN
                </span>
                <a href="index.php?action=dashboard" class="flex items-center space-x-1 text-primary hover:text-primary-700">
                    <i class="fas fa-user-circle"></i>
                    <span>User Dashboard</span>
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="card bg-white p-6 rounded-lg shadow-md border-t-4 border-gold">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-secondary">Total Users</h3>
                        <p class="text-3xl font-bold text-dark-blue mt-2"><?php echo number_format($total_users); ?></p>
                    </div>
                    <div class="p-3 rounded-full bg-blue-50 text-primary">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="#users-section" class="text-primary hover:underline flex items-center">
                        <span>View All</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
            
            <div class="card bg-white p-6 rounded-lg shadow-md border-t-4 border-accent">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-secondary">Total Offers</h3>
                        <p class="text-3xl font-bold text-dark-blue mt-2"><?php echo number_format($total_offers); ?></p>
                    </div>
                    <div class="p-3 rounded-full bg-green-50 text-accent">
                        <i class="fas fa-home text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="#offers-section" class="text-primary hover:underline flex items-center">
                        <span>View All</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
            
            <div class="card bg-white p-6 rounded-lg shadow-md border-t-4 border-primary">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-secondary">Total Messages</h3>
                        <p class="text-3xl font-bold text-dark-blue mt-2"><?php echo number_format($total_messages); ?></p>
                    </div>
                    <div class="p-3 rounded-full bg-purple-50 text-primary">
                        <i class="fas fa-envelope text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="#messages-section" class="text-primary hover:underline flex items-center">
                        <span>View All</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Manage Users -->
        <section id="users-section" class="mb-8 bg-white p-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-dark-blue">
                    <i class="fas fa-users mr-2"></i>Manage Users
                </h2>
                <div class="relative">
                    <input type="text" id="user-search" placeholder="Search users..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gold">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            
            <?php if (empty($users)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-user-slash text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-600">No users found.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky-header">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">Registered</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-50 user-row" data-username="<?php echo htmlspecialchars(strtolower($user['username'] ?? '')); ?>" data-email="<?php echo htmlspecialchars(strtolower($user['email'] ?? '')); ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['id'] ?? ''); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                <i class="fas fa-user text-gray-500"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['username'] ?? 'Unknown'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo ($user['role'] ?? 'user') === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo ucfirst($user['role'] ?? 'user'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo isset($user['created_at']) ? date('M j, Y', strtotime($user['created_at'])) : 'N/A'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if (($user['role'] ?? 'user') !== 'admin'): ?>
                                            <form method="POST" action="index.php?action=admin_dashboard" onsubmit="return confirm('Are you sure you want to delete this user? All their offers and messages will also be deleted.');" class="inline-block">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id'] ?? ''; ?>">
                                                <button type="submit" class="action-btn text-danger hover:text-danger-700" title="Delete User">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-gray-400" title="Admin users cannot be deleted">
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
        </section>

        <!-- Manage Offers -->
        <section id="offers-section" class="mb-8 bg-white p-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-dark-blue">
                    <i class="fas fa-home mr-2"></i>Manage Offers
                </h2>
                <div class="relative">
                    <input type="text" id="offer-search" placeholder="Search offers..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gold">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            
            <?php if (empty($offers)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-home text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-600">No offers found.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky-header">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">City</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">Price (PLN)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($offers as $offer): ?>
                                <tr class="hover:bg-gray-50 offer-row" data-title="<?php echo htmlspecialchars(strtolower($offer['title'] ?? '')); ?>" data-city="<?php echo htmlspecialchars(strtolower($offer['city'] ?? '')); ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($offer['id'] ?? ''); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="font-medium"><?php echo htmlspecialchars($offer['title'] ?? 'No Title'); ?></div>
                                        <div class="text-xs text-gray-500 mt-1">by <?php echo htmlspecialchars($offer['username'] ?? 'Unknown User'); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>
                                        <?php echo htmlspecialchars($offer['city'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo isset($offer['price']) ? number_format($offer['price'], 2) : '0.00'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo ($offer['status'] ?? 'inactive') === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo ucfirst($offer['status'] ?? 'inactive'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-3">
                                            <a href="index.php?action=edit_offer&offer_id=<?php echo $offer['id'] ?? ''; ?>" class="action-btn text-gold hover:text-yellow-600" title="Edit Offer">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="index.php?action=admin_dashboard" onsubmit="return confirm('Are you sure you want to delete this offer?');" class="inline-block">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                                <input type="hidden" name="action" value="delete_offer">
                                                <input type="hidden" name="offer_id" value="<?php echo $offer['id'] ?? ''; ?>">
                                                <button type="submit" class="action-btn text-danger hover:text-danger-700" title="Delete Offer">
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
        </section>

        <!-- Manage Messages -->
        <section id="messages-section" class="mb-8 bg-white p-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-dark-blue">
                    <i class="fas fa-envelope mr-2"></i>Manage Messages
                </h2>
                <div class="relative">
                    <input type="text" id="message-search" placeholder="Search messages..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gold">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            
            <?php if (empty($messages)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-envelope-open-text text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-600">No messages found.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky-header">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">From/To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">Offer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">Message</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($messages as $message): ?>
                                <tr class="hover:bg-gray-50 message-row" data-sender="<?php echo htmlspecialchars(strtolower($message['sender_username'] ?? '')); ?>" data-receiver="<?php echo htmlspecialchars(strtolower($message['receiver_username'] ?? '')); ?>" data-offer="<?php echo htmlspecialchars(strtolower($message['offer_title'] ?? '')); ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($message['id'] ?? ''); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <div><i class="fas fa-user-circle text-gray-400 mr-1"></i> <?php echo htmlspecialchars($message['sender_username'] ?? 'Unknown'); ?></div>
                                            <div class="mt-1"><i class="fas fa-arrow-right text-xs text-gray-400 mr-1"></i> <?php echo htmlspecialchars($message['receiver_username'] ?? 'Unknown'); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php if (isset($message['offer_id']) && isset($message['offer_title'])): ?>
                                            <a href="index.php?action=offer_details&id=<?php echo $message['offer_id']; ?>" class="text-primary hover:underline">
                                                <?php echo htmlspecialchars($message['offer_title']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">No associated offer</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 truncate-text" title="<?php echo htmlspecialchars($message['message'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($message['message'] ?? 'No message content'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo isset($message['created_at']) ? date('M j, Y g:i a', strtotime($message['created_at'])) : 'N/A'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="POST" action="index.php?action=admin_dashboard" onsubmit="return confirm('Are you sure you want to delete this message?');" class="inline-block">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                            <input type="hidden" name="action" value="delete_message">
                                            <input type="hidden" name="message_id" value="<?php echo $message['id'] ?? ''; ?>">
                                            <button type="submit" class="action-btn text-danger hover:text-danger-700" title="Delete Message">
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
        </section>
    </main>

    <script>
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