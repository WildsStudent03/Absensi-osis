<?php
session_start();
include __DIR__ . '/../../database/connect.php';
include "../../assets/boot.php";

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = trim($_POST['nama_lengkap']);
  $email = trim($_POST['email']);
  $password = $_POST['password'] ?? '';
  $foto = $_FILES['foto'] ?? null;

  // Validasi dasar
  if (empty($nama) || empty($email) || empty($password)) {
    $errors[] = "Semua field wajib diisi!";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Format email tidak valid!";
  } else {
    // Cek email sudah ada belum
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
      $errors[] = "Email sudah terdaftar!";
    }
    $check->close();
  }

 
  if (count($errors) === 0) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = "admin";
    $foto_name = null;

    if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
      $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
      $ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));

      if (in_array($ext, $allowed)) {
        $upload_dir = __DIR__ . '/../../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $foto_name = uniqid('admin_') . '.' . $ext;
        $target = $upload_dir . $foto_name;

        if (!move_uploaded_file($foto['tmp_name'], $target)) {
          $errors[] = "Gagal mengunggah foto.";
        }
      } else {
        $errors[] = "Format foto harus JPG, PNG, atau WEBP.";
      }
    }

  
    if (count($errors) === 0) {
      $insert = $conn->prepare("INSERT INTO users (nama_lengkap, email, password, role, foto) VALUES (?, ?, ?, ?, ?)");
      $insert->bind_param("sssss", $nama, $email, $hash, $role, $foto_name);

      if ($insert->execute()) {
        $success = "Admin baru berhasil ditambahkan!";
      } else {
        $errors[] = "Gagal menambahkan admin.";
      }
      $insert->close();
    }
  }
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah Admin - Absensi OSIS</title>
  <link href="../../assets/common.css" rel="stylesheet">
</head>

<body>
  <header class="navbar navbar-expand-lg border-bottom border-soft">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Absensi OSIS</a>
      <button class="navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="d-flex align-items-center gap-3">
        <span class="badge badge-cyan" data-user-role>ADMIN</span>
        <a href="../../core/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
      </div>
    </div>
  </header>

  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title text-light">Menu</h5>
      <button class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
      <?php include __DIR__ . '/./partials/sidebarAdmin.php'; ?>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-md-3 d-none d-md-block p-0">
        <?php include __DIR__ . '/./partials/sidebarAdmin.php'; ?>
      </div>
      <main class="col-md-9 p-4">
        <h2 class="mb-3 text-white">Tambah Admin Baru</h2>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger"><?= implode("<br>", $errors) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label text-white">Nama Lengkap</label>
            <input type="text"class="form-control bg-dark text-white border-secondary" name="nama_lengkap" required>
          </div>
          <div class="mb-3">
            <label class="form-label text-white">Email</label>
            <input type="email" class="form-control bg-dark text-white border-secondary" name="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label text-white">Password</label>
            <input type="password" class="form-control bg-dark text-white border-secondary" name="password" required>
          </div>
          <div class="mb-3">
            <label class="form-label text-white">Foto Admin (opsional)</label>
            <input type="file" class="form-control bg-dark text-white border-secondary" name="foto" accept="image/*">
          </div>
          <button type="submit" class="btn btn-primary">Tambah Admin</button>
          <a href="./dashboard-admin.php" class="btn btn-secondary">Kembali</a>
        </form>
      </main>
    </div>
  </div>

  <script>
    // Auto refresh dashboard setiap 30 detik
    setInterval(() => location.reload(), 30000);

    document.addEventListener('DOMContentLoaded', () => {
      const here = location.pathname.split('/').pop();
      document.querySelectorAll('.sidebar .nav-link').forEach(a => {
        if (a.getAttribute('href').endsWith(here)) a.classList.add('active');
      });
    });
  </script>
</body>

</html>
