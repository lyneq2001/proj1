<?php
require_once 'config.php';
require_once __DIR__ . '/notifications.php';

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function setFormErrors($errors) {
    $_SESSION['form_errors'] = $errors;
}

function getFormErrors() {
    $errors = $_SESSION['form_errors'] ?? [];
    unset($_SESSION['form_errors']);
    return $errors;
}

function setOldInput($data) {
    $_SESSION['old_input'] = $data;
}

function getOldInput($field) {
    return $_SESSION['old_input'][$field] ?? '';
}

function clearOldInput() {
    unset($_SESSION['old_input']);
}

function sendVerificationEmail($email, $token) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $link = "http://$host/index.php?action=verify&token=$token";
    $subject = 'Potwierdzenie konta';
    $message = "Kliknij link, aby aktywować konto: $link";

    sendSystemEmail($email, $subject, $message, 'verification');
}

function emailDomainExists($email) {
    $domain = substr(strrchr($email, '@'), 1);
    return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
}

function ensureUserPhoneColumn(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }

    global $pdo;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute(['users', 'phone']);
    $exists = (bool)$stmt->fetchColumn();

    if (!$exists) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(30) NULL");
        } catch (PDOException $e) {
            // Column may already exist due to race condition.
        }
    }

    $checked = true;
}

function isValidPassword($password) {
    return strlen($password) >= 6 &&
           preg_match('/[A-Za-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

function register($username, $email, $password, $phone) {
    global $pdo;
    // Validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('error', 'Invalid email format.');
        return;
    }
    if (!emailDomainExists($email)) {
        setFlashMessage('error', 'Email domain does not exist.');
        return;
    }
    if (!isValidPassword($password)) {
        setFlashMessage('error', 'Password must be at least 6 characters and include a letter and a number.');
        return;
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        setFlashMessage('error', 'Username can only contain letters, numbers, and underscores.');
        return;
    }

    $phone = trim((string)$phone);
    if ($phone === '') {
        setFlashMessage('error', 'Numer telefonu jest wymagany.');
        return;
    }
    if (!preg_match('/^\+?[0-9][0-9\s-]{6,20}$/', $phone)) {
        setFlashMessage('error', 'Podaj poprawny numer telefonu.');
        return;
    }

    ensureUserPhoneColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        setFlashMessage('error', 'Adres email jest już zajęty.');
        return;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_verified, verification_token, phone) VALUES (?, ?, ?, 'user', 1, NULL, ?)");
    try {
        $stmt->execute([$username, $email, $hashedPassword, $phone]);
        setFlashMessage('success', 'Rejestracja udana. Możesz się zalogować.');
        header("Location: index.php?action=login");
    } catch (PDOException $e) {
        setFlashMessage('error', 'Registration failed: ' . $e->getMessage());
    }
}

function login($email, $password) {
    global $pdo;
    // Validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('error', 'Invalid email format.');
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        setFlashMessage('success', 'Logged in successfully.');
        header("Location: index.php");
    } else {
        setFlashMessage('error', 'Invalid credentials.');
    }
}

function logout() {
    session_destroy();
    setFlashMessage('success', 'Logged out successfully.');
    header("Location: index.php");
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    if (!isLoggedIn()) {
        return 'guest';
    }
    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user['role'] ?? 'user';
}

function isAdmin() {
    return isLoggedIn() && getUserRole() === 'admin';
}

function verifyAccount($token) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        setFlashMessage('success', 'Konto zostało aktywowane. Możesz się zalogować.');
    } else {
        setFlashMessage('error', 'Nieprawidłowy link aktywacyjny.');
    }
    header("Location: index.php?action=login");
}
?>
