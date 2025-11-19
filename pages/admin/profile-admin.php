<?php
session_start(); // Pastikan session dimulai di paling atas
include "../../assets/boot.php";
include "../../includes/auth_guard.php";
include "../../database/connect.php";

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

$success = '';
$error = '';

// --- LOGIKA UBAH NAMA LENGKAP ---
if (isset($_POST['ubahNama'])) {
    $newName = trim($_POST['newName']);

    if (empty($newName)) {
        $error = "Nama lengkap tidak boleh kosong.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap = ? WHERE id = ?");
        $stmt->bind_param("si", $newName, $id);
        if ($stmt->execute()) {
            $admin['nama_lengkap'] = $newName; // Update variabel untuk tampilan
            $_SESSION['nama_lengkap'] = $newName; // Opsional: Update sesi jika nama digunakan di bagian lain
            $success = "Nama berhasil diubah.";
        } else {
            $error = "Gagal mengubah nama.";
        }
    }
}
// --- AKHIR LOGIKA UBAH NAMA LENGKAP ---

// --- LOGIKA UBAH EMAIL ---
if (isset($_POST['ubahEmail'])) {
    $newEmail = trim($_POST['newEmail']);

    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif ($newEmail == $admin['email']) {
        $error = 'Email yang dimasukkan sama dengan email saat ini.';
    } else {
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkEmail->bind_param("si", $newEmail, $id);
        $checkEmail->execute();
        if ($checkEmail->get_result()->num_rows > 0) {
            $error = 'Email sudah digunakan oleh pengguna lain.';
        } else {
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $newEmail, $id);
            if ($stmt->execute()) {
                $admin['email'] = $newEmail;
                $success = 'Email berhasil diubah.';
            } else {
                $error = 'Gagal mengubah email.';
            }
        }
    }
}
// --- AKHIR LOGIKA UBAH EMAIL ---

// --- LOGIKA UBAH PASSWORD ---
if (isset($_POST['ubahPassword'])) {
    $newPass = trim($_POST['newPass']);
    if (strlen($newPass) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $hashed = password_hash($newPass, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $id);
        if ($stmt->execute()) {
            $success = 'Password berhasil diubah.';
        } else {
            $error = 'Gagal mengubah password.';
        }
    }
}
// --- AKHIR LOGIKA UBAH PASSWORD ---

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
            color: #000 !important;
        }

        input.form-control::placeholder {
            color: #555;
        }

        input.form-control.bg-light {
            background-color: #fff !important;
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
            <div class="col-md-3 d-none d-md-block p-0">
                <?php include './partials/sidebarAdmin.php'; ?>
            </div>

            <main class="col-md-9 p-4"> 
                <h1 class="h4 mb-3 text-white text-center">Profil Admin</h1>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card rounded-3xl fade-in mx-auto" style="max-width:500px;"> 
                    <div class="card-body text-center">

                        <div class="mx-auto mb-3 rounded-circle border border-soft shadow" style="
                            width: 120px; height: 120px;
                            /* Path Foto Diperbaiki: */
                            background-image: url('../../uploads/<?php echo $admin['foto'] ?: 'default.png'; ?>');
                            background-size: cover;
                            background-position: center;
                        ">
                        </div>

                        <h5 class="mb-0 text-white" id="profileName"><?php echo htmlspecialchars($admin['nama_lengkap']); ?></h5>
                        <div class="text-muted small mb-3" id="profileEmail"><?php echo htmlspecialchars($admin['email']); ?></div>
                        <span class="badge bg-cyan mb-3">OSIS Admin</span>

                        <hr class="hr-soft">
                        
                        <h6 class="text-white mb-2">Ubah Nama Lengkap</h6>
                        <form method="POST" class="mt-3">
                            <div class="input-group">
                                <input type="text" name="newName" class="form-control bg-light" placeholder="Nama Lengkap baru" value="<?php echo htmlspecialchars($admin['nama_lengkap']); ?>" required>
                                <button class="btn btn-secondary" name="ubahNama">Simpan</button>
                            </div>
                        </form>

                        <hr class="hr-soft">
                        <h6 class="text-white mb-2">Ubah Email</h6>
                        <form method="POST" class="mt-3">
                            <div class="input-group">
                                <input type="email" name="newEmail" class="form-control bg-light" placeholder="Email baru" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                <button class="btn btn-secondary" name="ubahEmail">Simpan</button>
                            </div>
                        </form>

                        <hr class="hr-soft">

                        <h6 class="text-white mb-2">Ubah Password</h6>
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
        // Auto dismiss alerts
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(a => {
                    try {
                        a.remove();
                    } catch (e) {}
                });
            }, 5000);

            // Highlight sidebar
            const here = location.pathname.split('/').pop();
            document.querySelectorAll('.sidebar .nav-link').forEach(a => {
                if (a.getAttribute('href').endsWith(here)) a.classList.add('active');
            });
            
            // Reload window jika ada perubahan yang berhasil (Nama atau Email)
            <?php if ($success && (isset($_POST['ubahNama']) || isset($_POST['ubahEmail']))): ?>
                setTimeout(() => {
                    window.location.href = window.location.pathname; // Redirect bersih tanpa POST data
                }, 1500);
            <?php endif; ?>
        });
    </script>
</body>

</html>