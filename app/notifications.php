<?php
/**
 * Centralized helper for sending system emails with graceful fallbacks.
 */
function sendSystemEmail(string $email, string $subject, string $message, string $category = 'general', array $additionalHeaders = []): bool
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $defaultHeaders = [
        "From: Apartment Rental <no-reply@$host>",
        "Reply-To: no-reply@$host",
        "MIME-Version: 1.0",
        "Content-Type: text/plain; charset=UTF-8"
    ];

    $headers = array_merge($defaultHeaders, $additionalHeaders);
    $sent = mail($email, $subject, $message, implode("\r\n", $headers));

    if (!$sent) {
        $dir = __DIR__ . '/sent_emails';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $safeEmail = preg_replace('/[^a-zA-Z0-9_]+/', '_', $email);
        $filename = sprintf('%s/%s_%s_%s.txt', $dir, $category, $safeEmail, time());
        $content  = "To: $email\nSubject: $subject\n\n$message";
        file_put_contents($filename, $content);
    }

    return $sent;
}
?>
