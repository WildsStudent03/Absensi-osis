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
  <style>
  /* Tema hitam untuk input */
  .input-dark {
    background-color: #111 !important;  /* hitam pekat */
    color: #fff !important;             /* teks putih */
    border: 1px solid #444;             /* garis abu gelap */
  }

  .input-dark::placeholder {
    color: #aaa; /* warna placeholder lebih lembut */
  }

  .input-dark:focus {
    background-color: #000 !important;
    color: #fff !important;
    border-color: #0dcaf0; /* biru cyan saat fokus */
    box-shadow: 0 0 5px rgba(13, 202, 240, 0.5);
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
    <label class="form-label text-white">Email atau NIS</label>
    <input type="text" 
           class="form-control input-dark" 
           name="emailNis" 
           placeholder="@gmail.com" 
           required>
  </div>

  <div class="mb-2">
    <label class="form-label text-white">Password</label>
    <input type="password" 
           class="form-control input-dark" 
           name="password" 
           placeholder="••••••••" 
           required>
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
    document.addEventListener('DOMContentLoaded', () => {
      // Typing animation
      typeText(document.getElementById('loginTitle'), 'Login Absensi OSIS', 24);
    });
  </script>
</body>
</html>
