<?php
include "../../assets/boot.php";
include "../../includes/auth_guard.php";
include "../../database/connect.php";

// Hitung jumlah anggota berdasarkan jurusan
$counts = ['RPL' => 0, 'TBSM' => 0, 'ATPH' => 0];
$qJurusan = $conn->query("SELECT jurusan, COUNT(*) AS cnt FROM datasiswa GROUP BY jurusan");
if ($qJurusan && $qJurusan->num_rows > 0) {
  while ($r = $qJurusan->fetch_assoc()) {
    if (isset($counts[$r['jurusan']])) $counts[$r['jurusan']] = (int)$r['cnt'];
  }
}

// Hitung kehadiran hari ini
$tanggal = date('Y-m-d');
$kehadiran = [
  'total' => 0,
  'hadir' => 0,
  'izin' => 0,
  'sakit' => 0,
  'alpha' => 0
];

$qKehadiran = $conn->query("SELECT status, COUNT(*) AS cnt FROM absensi WHERE tanggal = '$tanggal' GROUP BY status");
if ($qKehadiran && $qKehadiran->num_rows > 0) {
  while ($row = $qKehadiran->fetch_assoc()) {
    $kehadiran['total'] += $row['cnt'];
    switch (strtolower($row['status'])) {
      case 'hadir':
        $kehadiran['hadir'] = $row['cnt'];
        break;
      case 'izin':
        $kehadiran['izin'] = $row['cnt'];
        break;
      case 'sakit':
        $kehadiran['sakit'] = $row['cnt'];
        break;
      case 'alpha':
        $kehadiran['alpha'] = $row['cnt'];
        break;
    }
  }
}

$conn->close();
?>

<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Dashboard Admin - Absensi OSIS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link href="../../assets/common.css" rel="stylesheet">
</head>

<body>
  <!-- Header -->
  <header class="navbar navbar-expand-lg navbar-dark border-bottom border-soft">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Absensi OSIS</a>
      <button class="navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="d-none d-md-flex align-items-center gap-3">
        <span class="badge badge-cyan">ADMIN</span>
         <a href="../../core/logout.php" 
       data-logout 
       class="btn btn-outline-light btn-sm"
       onclick="return confirm('Apakah Anda yakin ingin keluar (Logout)?');">Logout</a>
      </div>
    </div>
  </header>

  <!-- Sidebar Offcanvas (mobile) -->
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
      <!-- Sidebar Desktop -->
      <div class="col-md-3 d-none d-md-block p-0">
        <?php include './partials/sidebarAdmin.php'; ?>
      </div>

      <!-- Main Content -->
      <main class="col-md-9 p-4">
        <h1 class="h4 mb-3">Dashboard Admin</h1>
        <p class="text-muted">Ringkasan aktivitas dan statistik OSIS.</p>

        <div class="row g-3">
          <!-- Statistik Anggota per Jurusan -->
          <div class="col-12 col-md-4">
            <div class="card rounded-3xl card-glow-cyan">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <h6 class="mb-1 text-light">Total Anggota RPL</h6>
                  <span class="badge badge-cyan">RPL</span>
                </div>
                <div class="fs-3 text-light"><?php echo $counts['RPL']; ?></div>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="card rounded-3xl card-glow-cyan">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <h6 class="mb-1 text-light">Total Anggota TBSM</h6>
                  <span class="badge badge-cyan">TBSM</span>
                </div>
                <div class="fs-3 text-light"><?php echo $counts['TBSM']; ?></div>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="card rounded-3xl card-glow-cyan">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <h6 class="mb-1 text-light">Total Anggota ATPH</h6>
                  <span class="badge badge-cyan">ATPH</span>
                </div>
                <div class="fs-3 text-light"><?php echo $counts['ATPH']; ?></div>
              </div>
            </div>
          </div>

          <!-- Kehadiran Hari Ini -->
          <div class="col-12">
            <div class="card rounded-3xl card-glow-purple">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="mb-0 text-light">Kehadiran Hari Ini (<?php echo date('d M Y'); ?>)</h6>
                  <a href="./laporan.php" class="btn btn-secondary btn-sm">Lihat Laporan Mingguan</a>
                </div>
                <hr class="hr-soft my-3">
                <div class="row text-center text-light">
                  <div class="col-6 col-md-3">
                    <div class="text-muted">Total</div>
                    <div class="fs-4"><?php echo $kehadiran['total']; ?></div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="text-muted">Hadir</div>
                    <div class="fs-4 text-success"><?php echo $kehadiran['hadir']; ?></div>
                  </div>
                  <div class="col-6 col-md-3 mt-3 mt-md-0">
                    <div class="text-muted">Izin</div>
                    <div class="fs-4 text-info"><?php echo $kehadiran['izin']; ?></div>
                  </div>
                  <div class="col-6 col-md-3 mt-3 mt-md-0">
                    <div class="text-muted">Sakit / Alpha</div>
                    <div class="fs-6">
                      <span class="text-warning"><?php echo $kehadiran['sakit']; ?></span> /
                      <span class="text-danger"><?php echo $kehadiran['alpha']; ?></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>

  <!-- Auto Refresh tiap 30 detik -->
  <script>
    setTimeout(() => {
      window.location.reload();
    }, 30000); // 30 detik
  </script>

</body>

</html>