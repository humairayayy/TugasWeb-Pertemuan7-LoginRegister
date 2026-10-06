<?php
require_once 'includes/functions.php';
redirect(is_logged_in() ? 'dashboard.php' : 'login.php');
