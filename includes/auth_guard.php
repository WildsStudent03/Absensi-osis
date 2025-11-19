<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['user_id'])) {
    
    // Simpan pesan error sebelum redirect (Opsional)
    $_SESSION['login_errors'] = ["Anda harus login untuk mengakses halaman ini."];

 
    header('Location: ../../index.php'); 
    exit();
}