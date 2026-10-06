<?php
require_once 'includes/functions.php';
require_guest();

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $error = 'Token keamanan tidak valid. Muat ulang halaman.';
    } else {
        $emailRaw = trim($_POST['email'] ?? '');
        $email    = clean($emailRaw);
        $password = $_POST['password'] ?? '';

        if ($emailRaw === '' || $password === '') {
            $error = 'Email dan password wajib diisi.';
        } elseif (!filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } else {
            $user = find_user_by_email($emailRaw);
            if ($user && password_verify($password, $user['password'])) {
                login_user($user);                       // set session
                if (!empty($_POST['remember'])) remember_login($user); // bonus
                set_flash('success', 'Selamat datang kembali, ' . $user['name'] . '!');
                redirect('dashboard.php');
            }
            $error = 'Email atau password salah.';
        }
    }
}

$title = 'Login';
include 'includes/header.php';
?>
<div class="card">
    <h1>Login</h1>
    <p class="muted">Masuk ke akun Anda.</p>

    <?php show_flash(); ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" novalidate>
        <?= csrf_field() ?>
        <label>Email</label>
        <input type="email" name="email" value="<?= e($email) ?>" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label class="check"><input type="checkbox" name="remember" value="1"> Remember Me (30 hari)</label>

        <button type="submit">Login</button>
    </form>
    <p class="center">Belum punya akun? <a href="register.php">Daftar</a></p>
</div>
<?php include 'includes/footer.php'; ?>
