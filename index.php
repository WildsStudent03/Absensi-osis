<?php
include __DIR__ . "/assets/boot.php";
session_start();
$errors = $_SESSION['login_errors'] ?? [];
unset($_SESSION['login_errors']);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Login Sistem Absensi OSIS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="./assets/common.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
  /* Tema hitam */
  .input-dark {
    background-color: #111 !important;
    color: #fff !important;
    border: 1px solid #444;
  }

  .input-dark::placeholder {
    color: #aaa;
  }

  .input-dark:focus {
    background-color: #000 !important;
    border-color: #0dcaf0;
    box-shadow: 0 0 5px rgba(13, 202, 240, 0.5);
  }

  /* Bungkus input + ikon */
  .password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
  }

  .password-wrapper input {
    padding-right: 2.5rem; /* ruang untuk ikon */
  }

  .password-wrapper .toggle-password {
    position: absolute;
    right: 12px;
    cursor: pointer;
    color: #888;
    font-size: 1.1rem;
    transition: color 0.2s ease, transform 0.15s ease;
  }

  .password-wrapper .toggle-password:hover {
    color: #0dcaf0;
    transform: scale(1.1);
  }
  </style>
</head>

<body class="d-flex align-items-center py-5">
  <main class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-7 col-lg-5">
        <div class="card rounded-3xl card-glow-cyan fade-in slide-up p-2">
          <div class="card-body p-4">
            <h1 class="h3 mb-3 typing-caret text-white" id="loginTitle">Login Sistem Absensi OSIS</h1>
            <p class="text-muted">Silakan masuk menggunakan Email atau NIS dan password Anda.</p>

            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger fade show" role="alert">
                <?= implode('<br>', $errors) ?>
              </div>
            <?php endif; ?>

            <?php if (isset($_GET['msg'])): ?>
              <div id="autoAlert" class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['msg']) ?>
              </div>
              <script>
                setTimeout(() => {
                  const alert = document.getElementById('autoAlert');
                  if (alert) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 500);
                  }
                }, 3000);
              </script>
            <?php endif; ?>

            <form id="loginForm" class="mt-3" method="POST" action="core/proses-login.php">
              <div class="mb-3">
                <label class="form-label text-white">Email</label>
                <input type="text"
                       class="form-control input-dark"
                       name="emailNis"
                       placeholder="@gmail.com"
                       required>
              </div>

              <div class="mb-2">
                <label class="form-label text-white">Password</label>
                <div class="password-wrapper">
                  <input type="password"
                         class="form-control input-dark"
                         name="password"
                         id="passwordField"
                         placeholder="••••••••"
                         required>
                  <i class="bi bi-eye toggle-password" id="togglePassword" title="Tampilkan/Sembunyikan Password"></i>
                </div>
              </div>

              <div class="d-flex align-items-center justify-content-center mt-3 text-center">
                <button class="btn btn-primary hover-glow" type="submit">
                  <span class="me-1">Login</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    // animasi judul
    document.addEventListener('DOMContentLoaded', () => {
      typeText(document.getElementById('loginTitle'), 'Login Absensi OSIS', 24);
    });

    // toggle password
    const passwordField = document.getElementById('passwordField');
    const togglePassword = document.getElementById('togglePassword');

    togglePassword.addEventListener('click', () => {
      const isPassword = passwordField.type === 'password';
      passwordField.type = isPassword ? 'text' : 'password';
      togglePassword.classList.toggle('bi-eye');
      togglePassword.classList.toggle('bi-eye-slash');
    });
  </script>
</body>
</html>
