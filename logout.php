<?php
require_once 'includes/functions.php';

forget_login();            // hapus cookie remember me
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();         // req. 8

session_start();
set_flash('success', 'Anda berhasil logout.');
redirect('login.php');
