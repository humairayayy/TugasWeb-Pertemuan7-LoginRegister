<?php
session_start();

define('USERS_FILE', __DIR__ . '/../data/users.json');
define('REMEMBER_DAYS', 30);

/* ---------- Sanitasi & Output ---------- */
// Sanitasi input (req. 9)
function clean(string $s): string {
    return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8', false);
}
// Escape saat menampilkan (double_encode=false agar tidak ter-escape dua kali)
function e($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8', false);
}

/* ---------- Penyimpanan JSON (req. 4) ---------- */
function get_users(): array {
    if (!file_exists(USERS_FILE)) return [];
    $data = json_decode(file_get_contents(USERS_FILE), true);
    return is_array($data) ? $data : [];
}
function save_users(array $users): bool {
    return file_put_contents(
        USERS_FILE,
        json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    ) !== false;
}
function find_user_by_email(string $email): ?array {
    foreach (get_users() as $u) {
        if (strcasecmp($u['email'], $email) === 0) return $u;
    }
    return null;
}
function find_user_by_id(string $id): ?array {
    foreach (get_users() as $u) {
        if ($u['id'] === $id) return $u;
    }
    return null;
}
function update_user(string $id, array $changes): bool {
    $users = get_users();
    foreach ($users as &$u) {
        if ($u['id'] === $id) { $u = array_merge($u, $changes); return save_users($users); }
    }
    return false;
}

/* ---------- Flash message (req. 10) ---------- */
function set_flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['msg']) . '</div>';
        unset($_SESSION['flash']);
    }
}

/* ---------- Helper ---------- */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/* ---------- CSRF ---------- */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}
function csrf_valid(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

/* ---------- Auth & Session (req. 6, 7) ---------- */
function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
}
function remember_login(array $user): void {
    $token = bin2hex(random_bytes(32));
    update_user($user['id'], ['remember_token' => hash('sha256', $token)]);
    setcookie('remember_me', $user['id'] . ':' . $token, [
        'expires'  => time() + 86400 * REMEMBER_DAYS,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
function forget_login(): void {
    if (!empty($_SESSION['user_id'])) update_user($_SESSION['user_id'], ['remember_token' => null]);
    setcookie('remember_me', '', time() - 3600, '/');
}
// Auto-login dari cookie "Remember Me"
function check_remember_cookie(): void {
    if (!empty($_SESSION['user_id']) || empty($_COOKIE['remember_me'])) return;
    $parts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($parts) !== 2) return;
    $user = find_user_by_id($parts[0]);
    if ($user && !empty($user['remember_token'])
        && hash_equals($user['remember_token'], hash('sha256', $parts[1]))) {
        login_user($user);
    }
}
check_remember_cookie();

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}
function current_user(): ?array {
    return is_logged_in() ? find_user_by_id($_SESSION['user_id']) : null;
}
function require_login(): array {
    $user = current_user();
    if (!$user) {
        set_flash('error', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
    return $user;
}
function require_guest(): void {
    if (is_logged_in()) redirect('dashboard.php');
}
