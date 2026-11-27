<?php
/**
 * D'Tools Authentication & Password Recovery Helpers
 * - Uses prepared statements & SHA-256 hashed single-use reset tokens
 * - Preserves compatibility with legacy plaintext demo passwords while hashing new/reset passwords
 */

function dt_db_connect(): mysqli {
    $servername = getenv('DB_HOST') ?: 'localhost';
    $username   = getenv('DB_USER') ?: 'root';
    $password   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    $dbname     = getenv('DB_NAME') ?: 'peminjaman_alat';

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

/**
 * Ensures email column and password_resets table exist on older databases.
 */
function dt_ensure_recovery_schema(mysqli $conn): void {
    $colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($colCheck && $colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN email VARCHAR(120) UNIQUE NULL AFTER username");
    }

    $conn->query(
        "CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_hash CHAR(64) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_password_resets_user_created (user_id, created_at)
        )"
    );
}

function dt_hash_password(string $plain): string {
    return password_hash($plain, PASSWORD_DEFAULT);
}

/**
 * Supports modern password_hash() hashes and legacy plaintext seed accounts.
 */
function dt_verify_password(string $plain, string $stored): bool {
    if ($stored === '') {
        return false;
    }
    $info = password_get_info($stored);
    if (!empty($info['algo'])) {
        return password_verify($plain, $stored);
    }
    return hash_equals($stored, $plain);
}

function dt_csrf_token(): string {
    if (empty($_SESSION['dt_csrf_token'])) {
        $_SESSION['dt_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['dt_csrf_token'];
}

function dt_verify_csrf(?string $token): bool {
    if (empty($token) || empty($_SESSION['dt_csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['dt_csrf_token'], $token);
}

/**
 * Session-scoped sliding-window rate limiter.
 */
function dt_check_rate_limit(string $bucket, int $maxAttempts = 5, int $windowSeconds = 900): bool {
    $now = time();
    if (!isset($_SESSION['dt_rate_limits'][$bucket]) || !is_array($_SESSION['dt_rate_limits'][$bucket])) {
        $_SESSION['dt_rate_limits'][$bucket] = [];
    }
    $_SESSION['dt_rate_limits'][$bucket] = array_values(array_filter(
        $_SESSION['dt_rate_limits'][$bucket],
        static fn($ts) => ($now - (int)$ts) < $windowSeconds
    ));

    if (count($_SESSION['dt_rate_limits'][$bucket]) >= $maxAttempts) {
        return false;
    }

    $_SESSION['dt_rate_limits'][$bucket][] = $now;
    return true;
}

function dt_build_reset_url(string $rawToken): string {
    $configuredBase = getenv('APP_URL');
    if ($configuredBase) {
        return rtrim($configuredBase, '/') . '/reset-password.php?token=' . urlencode($rawToken);
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $scheme . '://' . $host . $dir . '/reset-password.php?token=' . urlencode($rawToken);
}

/**
 * Generates a single-use 256-bit reset token, stores only its SHA-256 hash.
 * Returns null if per-user DB rate limit is exceeded.
 */
function dt_create_password_reset(mysqli $conn, int $userId, int $ttlMinutes = 30): ?array {
    // Per-user DB rate limit: max 5 tokens in last 15 minutes
    $rateStmt = $conn->prepare(
        "SELECT COUNT(*) AS cnt FROM password_resets WHERE user_id = ? AND created_at >= (NOW() - INTERVAL 15 MINUTE)"
    );
    $rateStmt->bind_param("i", $userId);
    $rateStmt->execute();
    $cntRow = $rateStmt->get_result()->fetch_assoc();
    if ($cntRow && (int)$cntRow['cnt'] >= 5) {
        return null;
    }

    // Invalidate previous unused tokens for this user
    $invStmt = $conn->prepare("UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL");
    $invStmt->bind_param("i", $userId);
    $invStmt->execute();

    $rawToken  = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);

    $insStmt = $conn->prepare(
        "INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))"
    );
    $insStmt->bind_param("isi", $userId, $tokenHash, $ttlMinutes);
    if (!$insStmt->execute()) {
        return null;
    }

    return [
        'token' => $rawToken,
        'ttl_minutes' => $ttlMinutes,
    ];
}

/**
 * Validates a raw reset token against hashed records.
 * Returns ['status' => 'valid'|'invalid'|'expired'|'used', 'record' => array|null]
 */
function dt_validate_reset_token(mysqli $conn, string $rawToken): array {
    $rawToken = trim($rawToken);
    if ($rawToken === '' || !preg_match('/^[a-f0-9]{64}$/', $rawToken)) {
        return ['status' => 'invalid', 'record' => null];
    }

    $tokenHash = hash('sha256', $rawToken);
    $stmt = $conn->prepare(
        "SELECT pr.id AS reset_id, pr.user_id, pr.expires_at, pr.used_at,
                (pr.expires_at > NOW()) AS is_unexpired,
                u.username
         FROM password_resets pr
         JOIN users u ON u.id = pr.user_id
         WHERE pr.token_hash = ?
         LIMIT 1"
    );
    $stmt->bind_param("s", $tokenHash);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        return ['status' => 'invalid', 'record' => null];
    }
    if (!empty($row['used_at'])) {
        return ['status' => 'used', 'record' => null];
    }
    if (empty($row['is_unexpired'])) {
        return ['status' => 'expired', 'record' => null];
    }

    return ['status' => 'valid', 'record' => $row];
}

/**
 * Updates password securely and invalidates the token + all other open tokens for this user.
 */
function dt_complete_password_reset(mysqli $conn, int $resetId, int $userId, string $newPassword): bool {
    $hashed = dt_hash_password($newPassword);

    $conn->begin_transaction();
    try {
        $uStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $uStmt->bind_param("si", $hashed, $userId);
        if (!$uStmt->execute()) {
            throw new RuntimeException('Failed to update password');
        }

        $tStmt = $conn->prepare(
            "UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND (id = ? OR used_at IS NULL)"
        );
        $tStmt->bind_param("ii", $userId, $resetId);
        if (!$tStmt->execute()) {
            throw new RuntimeException('Failed to invalidate reset token');
        }

        $conn->commit();
        return true;
    } catch (Throwable $e) {
        $conn->rollback();
        return false;
    }
}

/**
 * Sends password recovery email when SMTP / MAIL_ENABLED is configured.
 * Does NOT fabricate delivery when email infrastructure is unavailable.
 */
function dt_send_recovery_email(string $toEmail, string $username, string $resetUrl, int $ttlMinutes = 30): array {
    $mailEnabled = getenv('MAIL_ENABLED') === '1' || (bool)getenv('SMTP_HOST');
    if (!$mailEnabled) {
        return [
            'configured' => false,
            'sent' => false,
        ];
    }

    $fromAddress = getenv('MAIL_FROM') ?: 'no-reply@dtools.lab';
    $subject = "Reset Password — D'Tools";
    $body = "Halo {$username},\n\n"
          . "Kami menerima permintaan untuk mengatur ulang password akun D'Tools Anda.\n"
          . "Gunakan tautan berikut untuk membuat password baru (berlaku {$ttlMinutes} menit):\n\n"
          . "{$resetUrl}\n\n"
          . "Jika Anda tidak merasa mengajukan permintaan ini, abaikan email ini. Password akun Anda tetap aman.\n\n"
          . "— D'Tools Sistem Informasi Peminjaman Alat";

    $headers = implode("\r\n", [
        'From: D\'Tools <' . $fromAddress . '>',
        'Reply-To: ' . $fromAddress,
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion(),
    ]);

    $sent = @mail($toEmail, $subject, $body, $headers);
    return [
        'configured' => true,
        'sent' => (bool)$sent,
    ];
}
