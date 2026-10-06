<?php
require_once 'includes/functions.php';
$user = require_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $name     = clean($_POST['name'] ?? '');
        $emailRaw = trim($_POST['email'] ?? '');
        $current  = $_POST['current_password'] ?? '';
        $new      = $_POST['new_password'] ?? '';

        if (mb_strlen($name) < 3) $errors[] = 'Nama minimal 3 karakter.';

        if (!filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        } else {
            $other = find_user_by_email($emailRaw);
            if ($other && $other['id'] !== $user['id']) $errors[] = 'Email sudah dipakai akun lain.';
        }

        $changes = ['name' => $name, 'email' => $emailRaw];

        if ($new !== '') {
            if (!password_verify($current, $user['password'])) {
                $errors[] = 'Password saat ini salah.';
            } elseif (strlen($new) < 8 || !preg_match('/[A-Za-z]/', $new) || !preg_match('/\d/', $new)) {
                $errors[] = 'Password baru minimal 8 karakter dan mengandung huruf & angka.';
            } else {
                $changes['password'] = password_hash($new, PASSWORD_DEFAULT);
            }
        }

        if (!$errors) {
            update_user($user['id'], $changes);
            set_flash('success', 'Profile berhasil diperbarui.');
            redirect('dashboard.php');
        }
        $user['name'] = $name;
        $user['email'] = $emailRaw;
    }
}

$title = 'Edit Profile';
include 'includes/header.php';
?>
<div class="card">
    <h1>Edit Profile</h1>
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" novalidate>
        <?= csrf_field() ?>
        <label>Nama</label>
        <input type="text" name="name" value="<?= e($user['name']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= e($user['email']) ?>" required>

        <hr>
        <p class="muted">Kosongkan jika tidak ingin mengganti password.</p>
        <label>Password Saat Ini</label>
        <input type="password" name="current_password">

        <label>Password Baru</label>
        <input type="password" name="new_password">

        <button type="submit">Simpan</button>
    </form>
    <p class="center"><a href="dashboard.php">← Kembali</a></p>
</div>
<?php include 'includes/footer.php'; ?>
