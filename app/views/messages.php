<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wiadomości | Apartment Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen font-roboto">
    <?php include 'header.php'; ?>

    <main class="page-shell">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="page-heading">
                <span class="page-heading__eyebrow">Komunikacja</span>
                <h1 class="page-heading__title">Twoje wiadomości</h1>
                <p class="page-heading__subtitle">Pozostawaj w kontakcie z najemcami i właścicielami w jednym, dopracowanym panelu rozmów.</p>
            </div>

            <?php
            $flash = getFlashMessage();
            if ($flash):
            ?>
                <div class="glass-panel mb-8 p-5 flex items-start gap-4 <?php echo $flash['type'] === 'error' ? 'flash-error' : 'flash-success'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $flash['type'] === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M5 13l4 4L19 7'; ?>"></path>
                    </svg>
                    <div class="font-medium leading-relaxed"><?php echo htmlspecialchars($flash['message']); ?></div>
                </div>
            <?php endif; ?>

            <div class="max-w-5xl mx-auto space-y-10">
                <?php if (!isLoggedIn()): ?>
                    <div class="glass-panel p-10 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="mt-6 text-2xl font-semibold text-slate-800">Zaloguj się, aby kontynuować rozmowy</h2>
                        <p class="mt-3 text-secondary-500">Dostęp do skrzynki wiadomości otrzymasz po zalogowaniu. Dzięki temu możesz wygodnie zarządzać pytaniami i odpowiedziami dotyczącymi ofert.</p>
                        <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                            <a href="index.php?action=login" class="btn">Zaloguj się</a>
                            <a href="index.php?action=register" class="btn btn-secondary">Załóż konto</a>
                        </div>
                    </div>
                <?php else: ?>
                    <section class="glass-panel p-8 space-y-6">
                        <div class="flex flex-col gap-2">
                            <span class="inline-flex items-center gap-2 self-start rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-600">
                                <span class="h-2 w-2 rounded-full bg-primary-600"></span>
                                Nowa wiadomość
                            </span>
                            <h2 class="text-2xl font-semibold text-slate-800">Rozpocznij konwersację</h2>
                            <p class="text-secondary-500">Połącz się szybko z drugim użytkownikiem, aby dopracować szczegóły wynajmu.</p>
                        </div>
                        <form method="POST" action="index.php?action=send_message" class="grid gap-6">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">

                            <div class="grid gap-2">
                                <label class="text-sm font-medium text-secondary-600">Odbiorca</label>
                                <div class="relative">
                                    <input type="text" id="receiver_username" name="receiver_username"
                                           class="form-input"
                                           placeholder="Wpisz nazwę użytkownika" autocomplete="off" required>
                                    <div id="autocomplete-suggestions" class="autocomplete-suggestions hidden"></div>
                                    <input type="hidden" id="receiver_id" name="receiver_id">
                                </div>
                                <p class="text-xs text-secondary-400">Wpisz min. 2 znaki, aby wyszukać użytkownika.</p>
                            </div>

                            <div class="grid gap-2">
                                <label class="text-sm font-medium text-secondary-600">ID oferty</label>
                                <input type="number" name="offer_id" class="form-input" placeholder="np. 102" required>
                            </div>

                            <div class="grid gap-2">
                                <label class="text-sm font-medium text-secondary-600">Treść wiadomości</label>
                                <textarea name="message" rows="4" class="form-textarea" placeholder="Napisz swoją wiadomość..." required></textarea>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <button type="submit" class="btn">
                                    Wyślij wiadomość
                                </button>
                                <span class="text-xs text-secondary-400">Otrzymasz powiadomienie, gdy druga strona odpowie.</span>
                            </div>
                        </form>
                    </section>

                    <section class="glass-panel p-8">
                        <div class="flex flex-col gap-2">
                            <h2 class="text-2xl font-semibold text-slate-800">Twoje konwersacje</h2>
                            <p class="text-secondary-500">Zobacz historię wymiany wiadomości dla wybranych ofert i kontynuuj rozmowę bez opuszczania panelu.</p>
                        </div>

                        <?php
                        $conversations = getConversations($_SESSION['user_id'] ?? 0);
                        if (empty($conversations)):
                        ?>
                            <div class="mt-6 rounded-2xl border border-dashed border-secondary-200 bg-white/70 p-8 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary-50 text-primary-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v10l-4-4H7a2 2 0 01-2-2V6a2 2 0 012-2h5" />
                                    </svg>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-slate-800">Brak aktywnych rozmów</h3>
                                <p class="mt-2 text-sm text-secondary-500">Gdy tylko wymienisz pierwsze wiadomości z innym użytkownikiem, pojawią się one w tym miejscu.</p>
                            </div>
                        <?php else: ?>
                            <div class="mt-8 space-y-6">
                                <?php foreach ($conversations as $conversation): ?>
                                    <?php if (is_array($conversation) && isset($conversation['offer_id'], $conversation['offer_title'], $conversation['other_user_id'], $conversation['other_user'], $conversation['messages']) && is_array($conversation['messages'])): ?>
                                        <article class="conversation-card">
                                            <div class="flex flex-col gap-3 border-b border-secondary-100 pb-4 sm:flex-row sm:items-start sm:justify-between">
                                                <div>
                                                    <p class="text-xs uppercase tracking-wide text-secondary-400">Oferta</p>
                                                    <h3 class="text-lg font-semibold text-slate-800"><?php echo htmlspecialchars($conversation['offer_title']); ?></h3>
                                                    <p class="text-sm text-secondary-500">Rozmowa z: <span class="font-medium text-primary-600"><?php echo htmlspecialchars($conversation['other_user']); ?></span></p>
                                                </div>
                                                <a href="index.php?action=view_offer&id=<?php echo $conversation['offer_id']; ?>" class="btn btn-secondary self-start">
                                                    Zobacz ofertę
                                                </a>
                                            </div>

                                            <div class="mt-6 space-y-3">
                                                <?php foreach ($conversation['messages'] as $msg): ?>
                                                    <?php if (is_array($msg) && isset($msg['message'], $msg['sent_at'], $msg['sender_id'])): ?>
                                                        <div class="flex <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'justify-end' : 'justify-start'; ?>">
                                                            <div class="message-bubble <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'message-bubble--self' : 'message-bubble--other'; ?>">
                                                                <p class="leading-relaxed"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                                                <p class="mt-2 text-[11px] uppercase tracking-wide <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'text-primary-200' : 'text-secondary-400'; ?>">
                                                                    <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'Ty' : htmlspecialchars($conversation['other_user']); ?> • <?php echo htmlspecialchars($msg['sent_at']); ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>

                                            <form method="POST" action="index.php?action=send_message" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                                <input type="hidden" name="receiver_id" value="<?php echo $conversation['other_user_id']; ?>">
                                                <input type="hidden" name="offer_id" value="<?php echo $conversation['offer_id']; ?>">
                                                <input type="text" name="message" class="form-input flex-1" placeholder="Odpowiedz..." required>
                                                <button type="submit" class="btn">Wyślij</button>
                                            </form>
                                        </article>
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
</body>
</html>