<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja | Apartment Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">

    <style>
        .register-card {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 30px 60px -45px rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(10px);
        }
        .register-card:hover {
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
            <!-- Page Heading -->
            <div class="page-heading">
                <span class="page-heading__eyebrow">Dołącz do nas</span>
                <h1 class="page-heading__title">Stwórz konto ApartmentRental</h1>
                <p class="page-heading__subtitle">Zarejestruj się, aby zapisywać ulubione oferty, kontaktować się z właścicielami i publikować swoje mieszkania.</p>
            </div>

            <div class="max-w-md mx-auto">
                <div class="register-card p-8">
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <h2 class="text-3xl font-playfair font-bold text-slate-800 mb-3">Rejestracja</h2>
                        <p class="text-slate-600 text-lg">Stwórz nowe konto</p>
                    </div>

                    <?php
                    $flash = getFlashMessage();
                    if ($flash):
                    ?>
                        <div class="glass-panel mb-6 p-4 flex items-start gap-3 <?php echo $flash['type'] === 'error' ? 'flash-error' : 'flash-success'; ?>">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $flash['type'] === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M5 13l4 4L19 7'; ?>"></path>
                            </svg>
                            <div class="font-medium text-sm"><?php echo htmlspecialchars($flash['message']); ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">

                        <div>
                            <label for="username" class="block text-slate-700 text-sm font-semibold mb-3">Nazwa użytkownika</label>
                            <input type="text" id="username" name="username" required
                                   class="w-full p-4 form-input"
                                   placeholder="Wprowadź nazwę użytkownika"
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>

                        <div>
                            <label for="email" class="block text-slate-700 text-sm font-semibold mb-3">Adres email</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full p-4 form-input"
                                   placeholder="wpisz@email.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>

                        <div>
                            <label for="password" class="block text-slate-700 text-sm font-semibold mb-3">Hasło</label>
                            <input type="password" id="password" name="password" required
                                   class="w-full p-4 form-input"
                                   placeholder="••••••••"
                                   value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>">
                            <p class="mt-2 text-sm text-slate-500 font-medium">Hasło powinno mieć co najmniej 6 znaków oraz zawierać literę i cyfrę</p>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white p-4 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105">
                            Zarejestruj się
                        </button>
                    </form>

                    <div class="mt-8 pt-6 border-t border-slate-200 text-center">
                        <p class="text-slate-600 font-medium">
                            Masz już konto?
                            <a href="index.php?action=login" class="text-blue-600 hover:text-blue-700 font-semibold ml-2 transition-colors">
                                Zaloguj się
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
