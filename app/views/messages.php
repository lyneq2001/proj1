<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wiadomości | Apartment Rental</title>
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
            backdrop-filter: blur(10px);
        }
        .conversation-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 35px 70px -40px rgba(30, 64, 175, 0.25);
        }
        .message-bubble {
            max-width: 70%;
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            word-wrap: break-word;
        }
        .message-bubble--self {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            margin-left: auto;
        }
        .message-bubble--other {
            background: rgba(241, 245, 249, 0.8);
            color: #334155;
            border: 1px solid rgba(148, 163, 184, 0.2);
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
            color: #1e3a8a;
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
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .page-heading__title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        .page-heading__subtitle {
            color: #64748b;
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
        .form-input {
            border-radius: 0.75rem;
            border: 1px solid rgba(148, 163, 184, 0.3);
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }
        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: rgba(255, 255, 255, 0.95);
        }
        .autocomplete-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: 0.75rem;
            margin-top: 0.25rem;
            max-height: 200px;
            overflow-y: auto;
            z-index: 10;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .autocomplete-suggestion {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            transition: background-color 0.2s ease;
        }
        .autocomplete-suggestion:hover {
            background-color: #f8fafc;
        }
        .autocomplete-suggestion:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen font-roboto">
    <?php include 'header.php'; ?>
    
    <main class="page-shell py-12">
        <div class="container mx-auto px-4 sm:px-6">
            <!-- Page Heading -->
            <div class="page-heading">
                <span class="page-heading__eyebrow">Komunikacja</span>
                <h1 class="page-heading__title">Twoje wiadomości</h1>
                <p class="page-heading__subtitle">Pozostawaj w kontakcie z najemcami i właścicielami w jednym, dopracowanym panelu rozmów.</p>
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

            <div class="max-w-5xl mx-auto space-y-8">
                <?php if (!isLoggedIn()): ?>
                    <div class="glass-panel p-12 text-center">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-playfair font-bold text-slate-800 mb-4">Zaloguj się, aby kontynuować rozmowy</h2>
                        <p class="text-slate-600 text-lg mb-8">Dostęp do skrzynki wiadomości otrzymasz po zalogowaniu. Dzięki temu możesz wygodnie zarządzać pytaniami i odpowiedziami dotyczącymi ofert.</p>
                        <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                            <a href="index.php?action=login" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl">
                                Zaloguj się
                            </a>
                            <a href="index.php?action=register" class="px-8 py-4 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-all duration-300 font-semibold">
                                Załóż konto
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- New Message Section -->
                    <section class="glass-panel p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">
                                    <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                                    Nowa wiadomość
                                </span>
                                <h2 class="text-2xl font-playfair font-bold text-slate-800 mt-1">Rozpocznij konwersację</h2>
                            </div>
                        </div>
                        <p class="text-slate-600 text-lg mb-6">Połącz się szybko z drugim użytkownikiem, aby dopracować szczegóły wynajmu.</p>
                        
                        <form method="POST" action="index.php?action=send_message" class="space-y-6">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">Odbiorca</label>
                                    <div class="relative">
                                        <input type="text" id="receiver_username" name="receiver_username"
                                               class="w-full p-4 form-input"
                                               placeholder="Wpisz nazwę użytkownika" autocomplete="off" required>
                                        <div id="autocomplete-suggestions" class="autocomplete-suggestions hidden"></div>
                                        <input type="hidden" id="receiver_id" name="receiver_id">
                                    </div>
                                    <p class="text-sm text-slate-500 mt-2 font-medium">Wpisz min. 2 znaki, aby wyszukać użytkownika.</p>
                                </div>

                                <div>
                                    <label class="block text-slate-700 text-sm font-semibold mb-3">ID oferty</label>
                                    <input type="number" name="offer_id" class="w-full p-4 form-input" placeholder="np. 102" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-slate-700 text-sm font-semibold mb-3">Treść wiadomości</label>
                                <textarea name="message" rows="5" class="w-full p-4 form-input" placeholder="Napisz swoją wiadomość..." required></textarea>
                            </div>

                            <div class="flex flex-wrap items-center gap-4">
                                <button type="submit" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105">
                                    Wyślij wiadomość
                                </button>
                                <span class="text-sm text-slate-500 font-medium">Otrzymasz powiadomienie, gdy druga strona odpowie.</span>
                            </div>
                        </form>
                    </section>

                    <!-- Conversations Section -->
                    <section class="glass-panel p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-xl flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v10l-4-4H7a2 2 0 01-2-2V6a2 2 0 012-2h5" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-playfair font-bold text-slate-800">Twoje konwersacje</h2>
                                <p class="text-slate-600 text-lg mt-1">Zobacz historię wymiany wiadomości dla wybranych ofert i kontynuuj rozmowę bez opuszczania panelu.</p>
                            </div>
                        </div>

                        <?php
                        $conversations = getConversations($_SESSION['user_id'] ?? 0);
                        if (empty($conversations)):
                        ?>
                            <div class="glass-panel p-12 text-center">
                                <div class="w-20 h-20 bg-gradient-to-br from-slate-400 to-slate-500 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v10l-4-4H7a2 2 0 01-2-2V6a2 2 0 012-2h5" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-playfair font-bold text-slate-800 mb-4">Brak aktywnych rozmów</h3>
                                <p class="text-slate-600 text-lg mb-8">Gdy tylko wymienisz pierwsze wiadomości z innym użytkownikiem, pojawią się one w tym miejscu.</p>
                                <a href="#new-message" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl">
                                    Rozpocznij rozmowę
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="space-y-6">
                                <?php foreach ($conversations as $conversation): ?>
                                    <?php if (is_array($conversation) && isset($conversation['offer_id'], $conversation['offer_title'], $conversation['other_user_id'], $conversation['other_user'], $conversation['messages']) && is_array($conversation['messages'])): ?>
                                        <div class="conversation-card p-6">
                                            <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
                                                <div class="flex-1">
                                                    <p class="text-sm font-semibold text-slate-500 mb-2">Oferta</p>
                                                    <h3 class="text-xl font-semibold text-slate-800 mb-2"><?php echo htmlspecialchars($conversation['offer_title']); ?></h3>
                                                    <p class="text-slate-600 font-medium">Rozmowa z: <span class="text-blue-600"><?php echo htmlspecialchars($conversation['other_user']); ?></span></p>
                                                </div>
                                                <a href="index.php?action=view_offer&id=<?php echo $conversation['offer_id']; ?>" class="px-6 py-3 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-all duration-300 font-semibold self-start">
                                                    Zobacz ofertę
                                                </a>
                                            </div>

                                            <div class="mt-6 space-y-4 max-h-96 overflow-y-auto p-2">
                                                <?php foreach ($conversation['messages'] as $msg): ?>
                                                    <?php if (is_array($msg) && isset($msg['message'], $msg['sent_at'], $msg['sender_id'])): ?>
                                                        <div class="flex <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'justify-end' : 'justify-start'; ?>">
                                                            <div class="message-bubble <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'message-bubble--self' : 'message-bubble--other'; ?>">
                                                                <p class="leading-relaxed text-sm"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                                                <p class="mt-2 text-xs font-medium <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'text-blue-100' : 'text-slate-500'; ?>">
                                                                    <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'Ty' : htmlspecialchars($conversation['other_user']); ?> • <?php echo htmlspecialchars($msg['sent_at']); ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>

                                            <form method="POST" action="index.php?action=send_message" class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                                <input type="hidden" name="receiver_id" value="<?php echo $conversation['other_user_id']; ?>">
                                                <input type="hidden" name="offer_id" value="<?php echo $conversation['offer_id']; ?>">
                                                <input type="text" name="message" class="flex-1 p-4 form-input" placeholder="Odpowiedz..." required>
                                                <button type="submit" class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl">
                                                    Wyślij
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </main>

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

    <?php include __DIR__ . '/../ai_assistant_widget.php'; ?>
</body>
</html>