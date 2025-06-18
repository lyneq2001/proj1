<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wiadomości | Apartment Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .autocomplete-suggestions {
            position: absolute;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            z-index: 50;
            margin-top: 0.25rem;
        }
        .autocomplete-suggestion {
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .autocomplete-suggestion:hover {
            background-color: #f3f4f6;
        }
        .message-bubble {
            max-width: 80%;
            word-wrap: break-word;
        }
        .conversation-item:hover {
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <?php include 'header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border-l-4 border-red-500' : 'bg-green-50 text-green-700 border-l-4 border-green-500'; ?>">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p><?php echo htmlspecialchars($flash['message']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Wiadomości</h1>
            
            <?php if (!isLoggedIn()): ?>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <p class="text-gray-600">Musisz być zalogowany, aby wysyłać i przeglądać wiadomości.</p>
                    <div class="mt-4">
                        <a href="index.php?action=login" class="text-blue-600 hover:text-blue-800 font-medium">Zaloguj się</a> lub 
                        <a href="index.php?action=register" class="text-blue-600 hover:text-blue-800 font-medium">zarejestruj</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- New Message Form -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Wyślij nową wiadomość</h2>
                    <form method="POST" action="index.php?action=send_message">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Odbiorca</label>
                            <div class="relative">
                                <input type="text" id="receiver_username" name="receiver_username" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       placeholder="Wpisz nazwę użytkownika" autocomplete="off" required>
                                <div id="autocomplete-suggestions" class="autocomplete-suggestions hidden"></div>
                                <input type="hidden" id="receiver_id" name="receiver_id">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">ID Oferty</label>
                            <input type="number" name="offer_id" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Wpisz ID oferty" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Treść wiadomości</label>
                            <textarea name="message" rows="4" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                      placeholder="Napisz swoją wiadomość..." required></textarea>
                        </div>
                        
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                            Wyślij wiadomość
                        </button>
                    </form>
                </div>

                <!-- Conversations List -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Twoje konwersacje</h2>
                    
                    <?php
                    $conversations = getConversations($_SESSION['user_id'] ?? 0);
                    if (empty($conversations)):
                    ?>
                        <p class="text-gray-600">Nie masz jeszcze żadnych konwersacji.</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($conversations as $conversation): ?>
                                <?php if (is_array($conversation) && isset($conversation['offer_id'], $conversation['offer_title'], $conversation['other_user_id'], $conversation['other_user'], $conversation['messages']) && is_array($conversation['messages'])): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 conversation-item">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <h3 class="font-medium text-gray-800">Oferta: <?php echo htmlspecialchars($conversation['offer_title']); ?></h3>
                                                <p class="text-sm text-gray-600">Z: <?php echo htmlspecialchars($conversation['other_user']); ?></p>
                                            </div>
                                            <a href="index.php?action=view_offer&id=<?php echo $conversation['offer_id']; ?>" 
                                               class="text-blue-600 hover:text-blue-800 text-sm">
                                                Zobacz ofertę
                                            </a>
                                        </div>
                                        
                                        <div class="space-y-3 mt-3">
                                            <?php foreach ($conversation['messages'] as $msg): ?>
                                                <?php if (is_array($msg) && isset($msg['message'], $msg['sent_at'], $msg['sender_id'])): ?>
                                                    <div class="flex <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'justify-end' : 'justify-start'; ?>">
                                                        <div class="message-bubble <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?> p-3 rounded-lg">
                                                            <p><?php echo htmlspecialchars($msg['message']); ?></p>
                                                            <p class="text-xs mt-1 <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'text-blue-600' : 'text-gray-500'; ?>">
                                                                <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'Ty' : htmlspecialchars($conversation['other_user']); ?>, 
                                                                <?php echo htmlspecialchars($msg['sent_at']); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <form method="POST" action="index.php?action=send_message" class="mt-4">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                            <input type="hidden" name="receiver_id" value="<?php echo $conversation['other_user_id']; ?>">
                                            <input type="hidden" name="offer_id" value="<?php echo $conversation['offer_id']; ?>">
                                            <div class="flex gap-2">
                                                <input type="text" name="message" 
                                                       class="flex-grow px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                                       placeholder="Odpowiedz..." required>
                                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                                                    Wyślij
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const usernameInput = document.getElementById('receiver_username');
            const suggestionsDiv = document.getElementById('autocomplete-suggestions');
            const receiverIdInput = document.getElementById('receiver_id');

            // Autocomplete for username
            usernameInput.addEventListener('input', async () => {
                const query = usernameInput.value.trim();
                if (query.length < 2) {
                    suggestionsDiv.innerHTML = '';
                    suggestionsDiv.style.display = 'none';
                    receiverIdInput.value = '';
                    return;
                }

                try {
                    const response = await fetch(`index.php?action=search_users&query=${encodeURIComponent(query)}`);
                    if (!response.ok) throw new Error('Network response was not ok');
                    
                    const users = await response.json();
                    suggestionsDiv.innerHTML = '';
                    
                    if (users.length === 0) {
                        suggestionsDiv.style.display = 'none';
                        receiverIdInput.value = '';
                        return;
                    }

                    users.forEach(user => {
                        const div = document.createElement('div');
                        div.className = 'autocomplete-suggestion';
                        div.textContent = user.username;
                        div.dataset.id = user.id;
                        div.addEventListener('click', () => {
                            usernameInput.value = user.username;
                            receiverIdInput.value = user.id;
                            suggestionsDiv.innerHTML = '';
                            suggestionsDiv.style.display = 'none';
                        });
                        suggestionsDiv.appendChild(div);
                    });
                    suggestionsDiv.style.display = 'block';
                } catch (error) {
                    console.error('Error fetching users:', error);
                    suggestionsDiv.innerHTML = '';
                    suggestionsDiv.style.display = 'none';
                    receiverIdInput.value = '';
                }
            });

            // Hide suggestions when clicking outside
            document.addEventListener('click', (e) => {
                if (e.target !== usernameInput && !suggestionsDiv.contains(e.target)) {
                    suggestionsDiv.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>