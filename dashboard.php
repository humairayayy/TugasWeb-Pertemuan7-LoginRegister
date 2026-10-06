<?php
require_once 'includes/functions.php';
$user = require_login(); // redirect jika belum login (req. 7)

$title = 'Dashboard';
include 'includes/header.php';
?>
<div class="card">
    <h1>Dashboard</h1>
    <?php show_flash(); ?>
    <p>Halo, <strong><?= e($user['name']) ?></strong> 👋</p>
    <table>
        <tr><td>Nama</td><td><?= e($user['name']) ?></td></tr>
        <tr><td>Email</td><td><?= e($user['email']) ?></td></tr>
        <tr><td>Terdaftar</td><td><?= e($user['created_at']) ?></td></tr>
    </table>
    <div class="actions">
        <a class="btn" href="profile.php">Edit Profile</a>
        <a class="btn btn-danger" href="logout.php">Logout</a>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
