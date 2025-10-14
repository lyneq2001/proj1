<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie | Apartment Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">

    <style>
        .login-card {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 30px 60px -45px rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(10px);
        }
        .login-card:hover {
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
    <?php include 'header.php'; ?>
    
    <main class="page-shell py-12">
        <div class="container mx-auto px-4 sm:px-6">
            <!-- Page Heading -->
            <div class="page-heading">
                <span class="page-heading__eyebrow">Twoje konto</span>
                <h1 class="page-heading__title">Logowanie</h1>
                <p class="page-heading__subtitle">Zaloguj się, aby zarządzać swoimi ogłoszeniami i wiadomościami.</p>
            </div>

            <div class="max-w-md mx-auto">
                <div class="login-card p-8">
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                            </svg>
                        </div>
                        <h2 class="text-3xl font-playfair font-bold text-slate-800 mb-3">Logowanie</h2>
                        <p class="text-slate-600 text-lg">Zaloguj się, aby uzyskać dostęp do konta</p>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="glass-panel mb-6 p-4 flex items-start gap-3 flash-error">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="font-medium text-sm"><?php echo htmlspecialchars($error); ?></div>
                        </div>
                    <?php endif; ?>

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

                    <form method="POST" action="index.php?action=login" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">

                        <div>
                            <label for="email" class="block text-slate-700 text-sm font-semibold mb-3">Adres email</label>
                            <input type="email" id="email" name="email" required 
                                   class="w-full p-4 form-input" 
                                   placeholder="wpisz@email.com">
                        </div>

                        <div>
                            <label for="password" class="block text-slate-700 text-sm font-semibold mb-3">Hasło</label>
                            <input type="password" id="password" name="password" required 
                                   class="w-full p-4 form-input" 
                                   placeholder="••••••••">
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input type="checkbox" id="remember" name="remember" 
                                       class="h-5 w-5 text-blue-600 focus:ring-blue-600 border-slate-300 rounded">
                                <label for="remember" class="ml-3 text-slate-700 text-sm font-medium">Zapamiętaj mnie</label>
                            </div>
                            <a href="index.php?action=forgot_password" class="text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors">
                                Zapomniałeś hasła?
                            </a>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white p-4 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl hover:scale-105">
                            Zaloguj się
                        </button>
                    </form>

                    <div class="mt-8 pt-6 border-t border-slate-200 text-center">
                        <p class="text-slate-600 font-medium">
                            Nie masz jeszcze konta?
                            <a href="index.php?action=register" class="text-blue-600 hover:text-blue-700 font-semibold ml-2 transition-colors">
                                Zarejestruj się
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../ai_assistant_widget.php'; ?>
</body>
</html>