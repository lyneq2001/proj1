<?php
require_once 'config.php';

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

function sendVerificationEmail($email, $token) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $link = "http://$host/index.php?action=verify&token=$token";
    $subject = 'Potwierdzenie konta';
    $message = "Kliknij link, aby aktywować konto: $link";

    $headers   = [];
    $headers[] = "From: Apartment Rental <no-reply@$host>";
    $headers[] = "Reply-To: no-reply@$host";
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/plain; charset=UTF-8";

    // Attempt to send the email. If this fails, store the email contents so the
    // verification link can still be accessed manually.
    $sent = mail($email, $subject, $message, implode("\r\n", $headers));
    if (!$sent) {
        $dir = __DIR__ . '/sent_emails';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $safeEmail = preg_replace('/[^a-zA-Z0-9_]+/', '_', $email);
        $filename = $dir . '/verification_' . $safeEmail . '_' . time() . '.txt';
        $content  = "To: $email\nSubject: $subject\n\n$message";
        file_put_contents($filename, $content);
    }
}

function emailDomainExists($email) {
    $domain = substr(strrchr($email, '@'), 1);
    return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
}

function isValidPassword($password) {
    return strlen($password) >= 6 &&
           preg_match('/[A-Za-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

function register($username, $email, $password) {
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

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        setFlashMessage('error', 'Adres email jest już zajęty.');
        return;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_verified, verification_token) VALUES (?, ?, ?, 'user', 0, ?)");
    try {
        $stmt->execute([$username, $email, $hashedPassword, $token]);
        sendVerificationEmail($email, $token);
        setFlashMessage('success', 'Rejestracja udana. Sprawdź email, aby aktywować konto.');
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
        if (!$user['is_verified']) {
            setFlashMessage('error', 'Najpierw potwierdź swój adres email.');
            return;
        }
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