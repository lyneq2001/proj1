<?php
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj użytkownika - Panel administratora</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen font-roboto">
    <?php include __DIR__ . '/../header.php'; ?>

    <main class="page-shell py-12">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="page-heading">
                <span class="page-heading__eyebrow">Panel administratora</span>
                <h1 class="page-heading__title">Edytuj konto użytkownika</h1>
                <p class="page-heading__subtitle">Zaktualizuj dane użytkownika i zapisz zmiany.</p>
            </div>

            <div class="max-w-2xl mx-auto">
                <div class="glass-panel p-8">
                    <?php if ($flash): ?>
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

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="role" class="block text-slate-700 text-sm font-semibold mb-3">Rola</label>
                                <select id="role" name="role" class="w-full p-4 form-input bg-white">
                                    <option value="user" <?php echo ($user['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>Użytkownik</option>
                                    <option value="admin" <?php echo ($user['role'] ?? 'user') === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-3 pt-8">
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
        </div>
    </main>
</body>
</html>
