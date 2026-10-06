<?php
require_once 'includes/functions.php';
require_guest();

$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Token keamanan tidak valid. Muat ulang halaman.';
    } else {
        $name     = clean($_POST['name'] ?? '');
        $emailRaw = trim($_POST['email'] ?? '');
        $email    = clean($emailRaw);
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        // Validasi nama
        if ($name === '') {
            $errors[] = 'Nama wajib diisi.';
        } elseif (mb_strlen($name) < 3) {
            $errors[] = 'Nama minimal 3 karakter.';
        }

        // Validasi email dengan filter_var (req. 2)
        if ($emailRaw === '') {
            $errors[] = 'Email wajib diisi.';
        } elseif (!filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        } elseif (find_user_by_email($emailRaw)) {
            // Cek duplikasi email (req. 5)
            $errors[] = 'Email sudah terdaftar. Gunakan email lain atau login.';
        }

        // Validasi password
        if ($password === '') {
            $errors[] = 'Password wajib diisi.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password minimal 8 karakter.';
        } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            $errors[] = 'Password harus mengandung huruf dan angka.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        }

        if (!$errors) {
            $users = get_users();
            $users[] = [
                'id'             => bin2hex(random_bytes(8)),
                'name'           => $name,
                'email'          => $emailRaw,
                // Hash password (req. 3)
                'password'       => password_hash($password, PASSWORD_DEFAULT),
                'remember_token' => null,
                'created_at'     => date('Y-m-d H:i:s'),
            ];
            if (save_users($users)) {
                set_flash('success', 'Registrasi berhasil! Silakan login.');
                redirect('login.php');
            }
            $errors[] = 'Gagal menyimpan data. Periksa izin folder data/.';
        }
    }
}

$title = 'Register';
include 'includes/header.php';
?>
<div class="card">
    <h1>Buat Akun</h1>
    <p class="muted">Daftar untuk mulai menggunakan sistem.</p>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" novalidate>
        <?= csrf_field() ?>
        <label>Nama</label>
        <input type="text" name="name" value="<?= e($name) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= e($email) ?>" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Konfirmasi Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Daftar</button>
    </form>
    <p class="center">Sudah punya akun? <a href="login.php">Login</a></p>
</div>
<?php include 'includes/footer.php'; ?>
