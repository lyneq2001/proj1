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
        }
        .register-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 dark:text-white min-h-screen font-sans">
    <?php include 'header.php'; ?>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
        <div class="register-card bg-white rounded-xl shadow-card p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-primary-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-dark mb-2">Rejestracja</h1>
                <p class="text-secondary-500">Stwórz nowe konto</p>
            </div>

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

            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">

                <div>
                    <label for="username" class="block text-sm font-medium text-secondary-600 mb-1">Nazwa użytkownika</label>
                    <input type="text" id="username" name="username" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition"
                           placeholder="Wprowadź nazwę użytkownika"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-secondary-600 mb-1">Adres email</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition"
                           placeholder="wpisz@email.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-secondary-600 mb-1">Hasło</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition"
                           placeholder="••••••••"
                           value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>">
                    <p class="mt-1 text-xs text-secondary-500">Hasło powinno mieć co najmniej 6 znaków oraz zawierać literę i cyfrę</p>
                </div>

                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-lg transition font-medium">
                    Zarejestruj się
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-secondary-600">
                    Masz już konto?
                    <a href="index.php?action=login" class="text-primary-600 hover:text-primary-700 font-medium">Zaloguj się</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>