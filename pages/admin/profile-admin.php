<?php
include "../../assets/boot.php";
include "../../database/connect.php";
session_start();

// Pastikan admin sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../../login.php");
  exit;
}

$id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$admin = $query->get_result()->fetch_assoc();
?>

<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Profil Admin - Absensi OSIS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="../../assets/common.css" rel="stylesheet">
  <style>
  input.form-control {
    color: #000 !important; /* teks yang diketik jadi hitam */
  }

  input.form-control::placeholder {
    color: #555; /* placeholder agak abu biar tetap terlihat */
  }

  input.form-control.bg-light {
    background-color: #fff !important; /* latar belakang tetap terang */
  }
</style>

</head>

<body>
  <header class="navbar navbar-expand-lg navbar-dark border-bottom border-soft justify-content-between px-3 ">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Absensi OSIS</a>
      <button class="navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="d-none d-md-flex align-items-center gap-3">
        <span class="badge badge-cyan" data-user-role>ADMIN</span>
        <a href="../../core/logout.php" data-logout class="btn btn-outline-light btn-sm">Logout</a>
      </div>
    </div>
  </header>

  <!-- Sidebar Offcanvas -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title text-light">Menu</h5>
      <button class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
      <?php include './partials/sidebarAdmin.php'; ?>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar kiri -->
      <div class="col-md-3 d-none d-md-block p-0">
        <?php include './partials/sidebarAdmin.php'; ?>
      </div>

      <!-- Konten utama -->
      <main class="col-md-9 p-4 justify-content-center">
        <h1 class="h4 mb-3 text-white">Profil Admin</h1>

        <div class="card rounded-3xl fade-in" style="max-width:500px;">
          <div class="card-body text-center">

            <!-- Foto Profil -->
            <div class="mx-auto mb-3 rounded-circle border border-soft shadow"
              style="
                width: 120px; height: 120px;
                background-image: url('../../uploads/admins/<?php echo $admin['foto'] ?: 'default.png'; ?>');
                background-size: cover;
                background-position: center;
              ">
            </div>

            <h5 class="mb-0 text-white"><?php echo htmlspecialchars($admin['nama_lengkap']); ?></h5>
            <div class="text-muted small mb-3" id="profileEmail"><?php echo htmlspecialchars($admin['email']); ?></div>
            <span class="badge bg-cyan mb-3">OSIS Admin</span>

            <hr class="hr-soft">

            <!-- Ubah Email -->
            <h6 class="text-white mb-2">Ubah Email</h6>

            <?php
            if (isset($_POST['ubahEmail'])) {
              $newEmail = trim($_POST['newEmail']);

              // Validasi format email
              if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                echo '<div class="alert alert-danger mt-2">Format email tidak valid.</div>';
              }
              // Cek apakah email sudah digunakan
              else {
                $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $checkEmail->bind_param("si", $newEmail, $id);
                $checkEmail->execute();
                if ($checkEmail->get_result()->num_rows > 0) {
                  echo '<div class="alert alert-danger mt-2">Email sudah digunakan oleh pengguna lain.</div>';
                } else {
                  $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
                  $stmt->bind_param("si", $newEmail, $id);
                  $stmt->execute();
                  $admin['email'] = $newEmail;
                  echo '<div class="alert alert-success mt-2">Email berhasil diubah.</div>';
                }
              }
            }
            ?>

            <form method="POST" class="mt-3">
              <div class="input-group">
                <input type="email" name="newEmail" class="form-control bg-light" placeholder="Email baru" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                <button class="btn btn-secondary" name="ubahEmail">Simpan</button>
              </div>
            </form>

            <hr class="hr-soft">

            <!-- Ubah Password -->
            <h6 class="text-white mb-2">Ubah Password</h6>

            <?php
            if (isset($_POST['ubahPassword'])) {
              $newPass = trim($_POST['newPass']);
              if (strlen($newPass) < 6) {
                echo '<div class="alert alert-danger mt-2">Password minimal 6 karakter.</div>';
              } else {
                $hashed = password_hash($newPass, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $stmt->bind_param("si", $hashed, $id);
                $stmt->execute();
                echo '<div class="alert alert-success mt-2">Password berhasil diubah.</div>';
              }
            }
            ?>

            <form method="POST" class="mt-3">
              <div class="input-group">
                <input type="password" name="newPass" class="form-control bg-light" placeholder="Password baru" required>
                <button class="btn btn-secondary" name="ubahPassword">Simpan</button>
              </div>
            </form>

          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    setInterval(() => location.reload(), 30000);

    // Update email display when form is submitted
    document.addEventListener('DOMContentLoaded', () => {
      const emailForm = document.querySelector('form');
      if (emailForm && emailForm.querySelector('input[name="newEmail"]')) {
        emailForm.addEventListener('submit', function() {
          setTimeout(() => {
            location.reload();
          }, 500);
        });
      }
    });
  </script>
</body>

</html>