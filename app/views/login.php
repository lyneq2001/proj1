<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie | Apartment Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
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
                    },
                },
            },
        }
    </script>
    <style>
        .login-card {
            transition: all 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 dark:text-white min-h-screen font-sans">
    <?php include 'header.php'; ?>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
        <div class="login-card bg-white rounded-xl shadow-card p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-primary-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-dark mb-2">Logowanie</h1>
                <p class="text-secondary-500">Zaloguj się, aby uzyskać dostęp do konta</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="mb-4 p-3 bg-red-50 text-red-600 rounded-lg flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <?php
            $flash = getFlashMessage();
            if ($flash):
            ?>
                <div class="mb-4 p-3 <?php echo $flash['type'] === 'error' ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600'; ?> rounded-lg flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div><?php echo htmlspecialchars($flash['message']); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?action=login" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                
                <div>
                    <label for="email" class="block text-sm font-medium text-secondary-600 mb-1">Adres email</label>
                    <input type="email" id="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition" placeholder="wpisz@email.com">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-secondary-600 mb-1">Hasło</label>
                    <input type="password" id="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition" placeholder="••••••••">
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-primary-600 focus:ring-primary-600 border-gray-300 rounded">
                        <label for="remember" class="ml-2 text-sm text-secondary-600">Zapamiętaj mnie</label>
                    </div>
                    <a href="index.php?action=forgot_password" class="text-sm text-primary-600 hover:text-primary-700">Zapomniałeś hasła?</a>
                </div>
                
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-lg transition font-medium">
                    Zaloguj się
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-sm text-secondary-600">
                    Nie masz jeszcze konta?
                    <a href="index.php?action=register" class="text-primary-600 hover:text-primary-700 font-medium">Zarejestruj się</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>