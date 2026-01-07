<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj użytkownika - Panel administratora</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .form-section {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 30px 60px -45px rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(10px);
        }
        .form-section:hover {
            transform: translateY(-4px);
            box-shadow: 0 35px 70px -40px rgba(30, 64, 175, 0.25);
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
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen font-roboto">
    <?php include __DIR__ . '/../header.php'; ?>

    <main class="page-shell py-12">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="page-heading">
                <span class="page-heading__eyebrow">Edycja użytkownika</span>
                <h1 class="page-heading__title">Zarządzaj kontem użytkownika</h1>
                <p class="page-heading__subtitle">Zaktualizuj dane kontaktowe, rolę i status konta.</p>
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

            <div class="max-w-4xl mx-auto">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">

                    <div class="form-section p-6">
                        <h2 class="text-2xl font-playfair font-bold text-slate-800 mb-6 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 018 16h8a4 4 0 012.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Dane użytkownika
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="username" class="block text-slate-700 text-sm font-semibold mb-3">Nazwa użytkownika</label>
                                <input type="text" id="username" name="username" required
                                       class="w-full p-4 form-input"
                                       value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>">
                            </div>

                            <div>
                                <label for="email" class="block text-slate-700 text-sm font-semibold mb-3">Adres email</label>
                                <input type="email" id="email" name="email" required
                                       class="w-full p-4 form-input"
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                            </div>

                            <div>
                                <label for="phone" class="block text-slate-700 text-sm font-semibold mb-3">Numer telefonu</label>
                                <input type="tel" id="phone" name="phone" required
                                       class="w-full p-4 form-input"
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>

                            <div>
                                <label for="role" class="block text-slate-700 text-sm font-semibold mb-3">Rola</label>
                                <select id="role" name="role" class="w-full p-4 form-input bg-white">
                                    <option value="user" <?php echo ($user['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>Użytkownik</option>
                                    <option value="admin" <?php echo ($user['role'] ?? 'user') === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section p-6">
                        <h2 class="text-2xl font-playfair font-bold text-slate-800 mb-6 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Status konta
                        </h2>
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="is_verified" name="is_verified" class="h-5 w-5 text-blue-600 border-slate-300 rounded" <?php echo !empty($user['is_verified']) ? 'checked' : ''; ?>>
                            <label for="is_verified" class="text-sm font-semibold text-slate-700">Konto zweryfikowane</label>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white p-4 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105">
                            Zapisz zmiany
                        </button>
                        <a href="index.php?action=admin_dashboard" class="flex-1 border border-slate-300 text-slate-700 p-4 rounded-xl transition-all duration-300 font-semibold text-center hover:bg-slate-50">
                            Wróć do panelu
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
