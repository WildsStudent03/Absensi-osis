<?php
include '../database/connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input (Peningkatan keamanan, meskipun bind_param sudah membantu)
    $user     = trim(strip_tags($_POST['emailNis'] ?? ''));
    $password = $_POST['password'] ?? '';
    $errors   = [];

    if (empty($user) || empty($password)) {
        $errors[] = 'Email/nama dan password wajib diisi!';
    } else {
        // --- PERUBAHAN UTAMA DI SINI ---
        // Mengubah nis menjadi nama_lengkap
        $login = $conn->prepare('SELECT * FROM users WHERE nama_lengkap = ? OR email = ? LIMIT 1');
        // bind_param tetap 'ss' karena nama_lengkap dan email keduanya adalah string
        $login->bind_param('ss', $user, $user);
        $login->execute();
        $result = $login->get_result();
        $data   = $result->fetch_assoc();

        if ($data && password_verify($password, $data['password'])) {
            // Login Berhasil
            $_SESSION['user_id']      = $data['id'];
            $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
            $_SESSION['role']         = $data['role'];      
            
            if ($data['role'] === 'admin') {
                header('Location: ../pages/admin/dashboard-admin.php');
            } else {
                // Asumsi 'index.php' adalah halaman dashboard Anggota
                header('Location: index.php');
            }
            exit;
        } else {
            $errors[] = 'Email/nama atau password salah!';
        }

        $login->close();
    }

    // Login Gagal
    $_SESSION['login_errors'] = $errors;
    header('Location: ../index.php');
    exit;
} else {
    // Akses langsung ke file ini dicegah
    header('Location: ../index.php');
    exit;
}