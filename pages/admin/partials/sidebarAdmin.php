<?php /* Sidebar Admin Partial */ ?>
<aside class="sidebar p-3">
  <a href="../admin/dashboard-admin.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
    <span class="fs-5">OSIS Admin</span>
  </a>
  <hr class="hr-soft">
  <ul class="nav nav-pills flex-column mb-auto gap-1">
       <li class="nav-item"><a class="nav-link" href="../admin/dashboard-admin.php">Dashboard</a></li>
    <li class="nav-item"><a class="nav-link" href="../admin/kelola-anggota.php">Kelola Anggota</a></li>
    <li class="nav-item"><a class="nav-link" href="../admin/absensi.php">Absensi</a></li>
    <li class="nav-item"><a class="nav-link" href="../admin/kelola-jadwal.php">Jadwal Kegiatan</a></li>
    <li class="nav-item"><a class="nav-link" href="../admin/laporan.php">Laporan</a></li>
    <li class="nav-item"><a class="nav-link" href="../admin/profile-admin.php">Profil Admin</a></li>
    <li class="nav-item"><a class="nav-link" href="../admin/tambah-admin.php">Tambah Admin</a></li>
  </ul>
  <hr class="hr-soft">
  <div class="d-flex align-items-center justify-content-between">
    <div>
      <div class="small text-muted">Masuk sebagai</div>
      <div data-user-name class="text-light">Admin</div>
    </div>
    <a href="../../core/logout.php" data-logout class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</aside>