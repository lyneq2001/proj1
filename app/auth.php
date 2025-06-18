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

function register($username, $email, $password) {
    global $pdo;
    // Validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('error', 'Invalid email format.');
        return;
    }
    if (strlen($password) < 8) {
        setFlashMessage('error', 'Password must be at least 8 characters.');
        return;
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        setFlashMessage('error', 'Username can only contain letters, numbers, and underscores.');
        return;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
    try {
        $stmt->execute([$username, $email, $hashedPassword]);
        setFlashMessage('success', 'Registration successful. Please log in.');
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
?>