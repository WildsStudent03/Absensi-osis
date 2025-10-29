<?php
include "../../assets/boot.php";
include "../../database/connect.php";
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Absensi OSIS - Mode Checklist Cepat</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="../../assets/common.css" rel="stylesheet">
  <style>
    .status-hadir {
      color: #22c55e !important;
      font-weight: bold;
    }

    .status-izin {
      color: #38bdf8 !important;
      font-weight: bold;
    }

    .status-sakit {
      color: #a855f7 !important;
      font-weight: bold;
    }

    .status-alpha {
      color: #ef4444 !important;
      font-weight: bold;
    }
  </style>
</head>

<body class=" text-light">
  <!-- Navbar -->
  <header class="navbar navbar-expand-lg navbar-dark border-bottom border-soft">
    <div class="container-fluid">
      <button class="btn btn-outline-light d-md-none" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
        ☰
      </button>
      <a class="navbar-brand ms-2" href="#">Absensi OSIS</a>
      <div class="d-flex align-items-center gap-3 ms-auto">
        <span class="badge bg-info text-dark">ADMIN</span>
        <a href="../../core/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
      </div>
    </div>
  </header>

  <!-- Sidebar Mobile -->
  <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="offcanvasSidebar">
    <div class="offcanvas-header border-bottom border-secondary">
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
      <div class="col-md-3 d-none d-md-block p-0 border-end border-secondary">
        <?php include './partials/sidebarAdmin.php'; ?>
      </div>

      <!-- Main Content -->
      <div class="col-md-9 p-4">
        <main>
          <h1 class="h4 mb-3">Absensi</h1>
          <p class="text-muted">Centang siswa yang hadir, lalu klik “Simpan Kehadiran”.</p>

          <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
          <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
          <?php endif; ?>

          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
              <form method="POST" action="../../core/tambah_kehadiran.php" id="massAbsensiForm">
                <div class="row g-3 align-items-end mb-3">
                  <div class="col-md-6">
                    <label class="form-label text-white">Pilih Jadwal Kegiatan</label>
                    <select name="jadwal_id" id="selectJadwal" class="form-select bg-dark text-light border-secondary" required>
                      <option value="" disabled selected>-- Pilih Jadwal --</option>
                      <?php
                      $today = date('Y-m-d');
                      $selectedTanggalVar = '';
                      $stmt = $conn->prepare("SELECT id, judul, tanggal FROM jadwal_kegiatan WHERE tanggal >= ? ORDER BY tanggal ASC");
                      $stmt->bind_param('s', $today);
                      $stmt->execute();
                      $resJ = $stmt->get_result();
                      while ($rj = $resJ->fetch_assoc()):
                        $optDate = $rj['tanggal'];
                        $formattedDate = date('d/m/Y', strtotime($optDate));
                        $isToday = ($optDate === date('Y-m-d'));
                        if ($isToday) $selectedTanggalVar = $optDate;
                      ?>
                        <option value="<?= $rj['id']; ?>" data-tanggal="<?= $optDate; ?>" <?= $isToday ? ' selected' : '' ?>><?= htmlspecialchars($rj['judul']); ?> (<?= $formattedDate; ?>)</option>
                      <?php endwhile;
                      $stmt->close(); ?>
                    </select>
                  </div>
                  <div class="col-md-6 text-end">
                    <button type="button" id="toggleAll" class="btn btn-outline-primary me-2">Pilih Semua</button>
                    <button type="reset" class="btn btn-danger me-2">Reset</button>
                    <button type="submit" class="btn btn-primary">Simpan Kehadiran</button>
                  </div>
                </div>
<div class="input-group mb-3">
  <span class="input-group-text bg-dark text-light border-secondary">🔍</span>
  <input type="text" id="searchInput" class="form-control bg-dark text-light border-secondary" placeholder="Cari nama, kelas, atau jurusan...">
</div>

                <div class="table-responsive">
                  <table class="table table-dark table-hover align-middle">
                    <thead>
                      <tr>
                        <th style="width:60px;"><input type="checkbox" id="headerCheckbox" title="Pilih semua"></th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Jabatan OSIS</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $today = date('Y-m-d');

                      // Ambil hanya siswa yang BELUM absen hari ini
                      $sQ = $conn->prepare("
  SELECT idsiswa, nama, kelas, jurusan, jabatan_osis 
  FROM datasiswa 
  WHERE status_osis='Aktif'
  AND idsiswa NOT IN (
    SELECT user_id FROM absensi WHERE tanggal = ?
  )
  ORDER BY nama ASC
");
                      $sQ->bind_param('s', $today);
                      $sQ->execute();
                      $result = $sQ->get_result();
                      while ($s = $result->fetch_assoc()):
                      ?>
                        <tr>
                          <td><input class="form-check-input hadirCheckbox" type="checkbox" name="hadir[]" value="<?= $s['idsiswa']; ?>"></td>
                          <td><?= htmlspecialchars($s['nama']); ?></td>
                          <td><?= htmlspecialchars($s['kelas']); ?></td>
                          <td><?= htmlspecialchars($s['jurusan']); ?></td>
                          <td><?= htmlspecialchars($s['jabatan_osis']); ?></td>
                        </tr>
                      <?php endwhile; ?>

                    </tbody>
                  </table>
                </div>
                <input type="hidden" name="tanggal" id="hiddenTanggal" value="<?= isset($selectedTanggalVar) ? htmlspecialchars($selectedTanggalVar) : ''; ?>">
              </form>
            </div>
          </div>

          <!-- Riwayat Hari Ini -->
          <div class="card rounded-3xl mt-3">
            <div class="card-body">
              <h5 class="text-white mt-4">Riwayat Absensi Hari Ini</h5>
              <table class="table table-dark table-striped align-middle mt-2">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Jurusan</th>
                    <th>Jabatan</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $today = date('Y-m-d');
                  $qRiwayat = $conn->prepare("
      SELECT a.id, d.nama, d.kelas, d.jurusan, d.jabatan_osis, a.status
      FROM absensi a
      JOIN datasiswa d ON a.user_id = d.idsiswa
      WHERE a.tanggal = ?
      ORDER BY d.nama ASC
    ");
                  $qRiwayat->bind_param('s', $today);
                  $qRiwayat->execute();
                  $riwayat = $qRiwayat->get_result();

                  while ($r = $riwayat->fetch_assoc()):
                  ?>
                    <tr>
                      <td><?= htmlspecialchars($r['nama']); ?></td>
                      <td><?= htmlspecialchars($r['kelas']); ?></td>
                      <td><?= htmlspecialchars($r['jurusan']); ?></td>
                      <td><?= htmlspecialchars($r['jabatan_osis']); ?></td>
                      <td>
                        <select class="form-select form-select-sm bg-dark text-white statusDropdown" data-id="<?= $r['id']; ?>">
                          <option value="Hadir" <?= $r['status'] == 'Hadir' ? 'selected' : ''; ?>>Hadir</option>
                          <option value="Izin" <?= $r['status'] == 'Izin' ? 'selected' : ''; ?>>Izin</option>
                          <option value="Sakit" <?= $r['status'] == 'Sakit' ? 'selected' : ''; ?>>Sakit</option>
                          <option value="Alpha" <?= $r['status'] == 'Alpha' ? 'selected' : ''; ?>>Alpha</option>
                        </select>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>

            </div>
          </div>
      </div>
      </main>
    </div>
  </div>
  </div>

  <script>
document.querySelectorAll('.statusDropdown').forEach(select => {
  select.addEventListener('change', async (e) => {
    const id = e.target.dataset.id;
    const status = e.target.value;

    let keterangan = '';
    if (status === 'Izin' || status === 'Sakit') {
      keterangan = prompt(`Masukkan keterangan untuk status ${status}:`, '');
      if (keterangan === null) {
        // Jika dibatalkan, kembalikan dropdown ke status lama
        e.target.value = e.target.getAttribute('data-prev') || 'Hadir';
        return;
      }
    }

    const res = await fetch('../../core/update-status.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `id=${id}&status=${status}&keterangan=${encodeURIComponent(keterangan)}`
    });

    const text = await res.text();
    if (text.trim() === 'ok') {
      e.target.style.backgroundColor = '#198754'; // hijau sukses
      e.target.setAttribute('data-prev', status);
      setTimeout(() => e.target.style.backgroundColor = '#212529', 700);
    } else {
      e.target.style.backgroundColor = '#dc3545'; // merah error
    }
  });
});



    document.addEventListener("DOMContentLoaded", () => {
      const checkboxes = document.querySelectorAll('.hadirCheckbox');
      const toggleAllBtn = document.getElementById('toggleAll');
      const headerCheckbox = document.getElementById('headerCheckbox');
      const selectJadwal = document.getElementById('selectJadwal');
      const hiddenTanggal = document.getElementById('hiddenTanggal');
      const form = document.getElementById('massAbsensiForm');

      // === 1️⃣ Efek hilang saat dicentang satu per satu ===
      checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
          if (cb.checked) {
            const row = cb.closest('tr');
            row.style.transition = "opacity 0.3s ease";
            row.style.opacity = "0";
            setTimeout(() => row.style.display = "none", 300);
          }
        });
      });

      // === 2️⃣ Tombol "Pilih Semua" ===
      let allChecked = false;
      toggleAllBtn.addEventListener('click', () => {
        allChecked = !allChecked;
        checkboxes.forEach(cb => {
          cb.checked = allChecked;
          if (allChecked) {
            const row = cb.closest('tr');
            row.style.transition = "opacity 0.3s ease";
            row.style.opacity = "0";
            setTimeout(() => row.style.display = "none", 300);
          } else {
            // Jika dibatalkan, tampilkan lagi semua
            const row = cb.closest('tr');
            row.style.display = "";
            row.style.opacity = "1";
          }
        });
        toggleAllBtn.textContent = allChecked ? 'Batal Pilih Semua' : 'Pilih Semua';
      });

      // === 3️⃣ Checkbox header (atas tabel) ===
      headerCheckbox.addEventListener('change', e => {
        const checked = e.target.checked;
        checkboxes.forEach(cb => {
          cb.checked = checked;
          const row = cb.closest('tr');
          if (checked) {
            row.style.transition = "opacity 0.3s ease";
            row.style.opacity = "0";
            setTimeout(() => row.style.display = "none", 300);
          } else {
            row.style.display = "";
            row.style.opacity = "1";
          }
        });
      });

      // === 4️⃣ Set tanggal otomatis dari jadwal ===
      selectJadwal.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        hiddenTanggal.value = opt.getAttribute('data-tanggal') || '';
      });

      // === 5️⃣ Validasi sebelum submit ===
      form.addEventListener('submit', e => {
        if (!selectJadwal.value) {
          e.preventDefault();
          alert("⚠️ Pilih jadwal kegiatan terlebih dahulu!");
        }
      });
    });
document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById('searchInput');
  const rows = document.querySelectorAll('tbody tr');

  if (searchInput) {
    searchInput.addEventListener('keyup', () => {
      const keyword = searchInput.value.toLowerCase();

      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(keyword)) {
          row.style.display = "";
          row.style.opacity = "1";
        } else {
          row.style.display = "none";
        }
      });
    });
  }
});

  </script>

</body>

</html>