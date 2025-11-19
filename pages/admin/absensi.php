<?php
include "../../assets/boot.php";
include "../../database/connect.php";
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Absensi OSIS </title>
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
      <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
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
          <p class="text-muted">Pilih status untuk setiap siswa, lalu klik "Simpan Semua".</p>

          <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
          <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
          <?php endif; ?>

          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
              <div class="row g-3 align-items-end mb-3">
                <div class="col-md-6">
                  <label class="form-label text-white">Pilih Jadwal Kegiatan</label>
                  <select id="selectJadwal" class="form-select bg-dark text-light border-secondary" required>
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
                  <button type="button" id="btnReset" class="btn btn-outline-secondary me-2">Reset</button>
                  <button type="button" id="btnSaveAll" class="btn btn-primary">Simpan Semua</button>
                </div>
              </div>
              <div class="input-group mb-3">
                <span class="input-group-text bg-dark text-light border-secondary">🔍</span>
                <input type="text" id="searchInput" class="form-control bg-dark text-light border-secondary" placeholder="Cari nama, kelas, atau jurusan...">
              </div>

              <div class="table-responsive">
                <table class="table table-dark table-hover align-middle" id="absensiTable">
                  <thead>
                    <tr>
                      <th>Nama</th>
                      <th>Kelas</th>
                      <th>Jurusan</th>
                      <th>Jabatan OSIS</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $today = date('Y-m-d');

                    // Ambil siswa aktif yang BELUM absen hari ini
                    $sQ = $conn->prepare("
  SELECT idsiswa, nama, kelas, jurusan, jabatan_osis 
  FROM datasiswa 
  WHERE status_osis='Aktif'
  AND idsiswa NOT IN (
    SELECT DISTINCT user_id FROM absensi WHERE tanggal = ?
  )
  ORDER BY nama ASC
");
                    $sQ->bind_param('s', $today);
                    $sQ->execute();
                    $result = $sQ->get_result();
                    while ($s = $result->fetch_assoc()):
                    ?>
                      <tr>
                        <td><?= htmlspecialchars($s['nama']); ?></td>
                        <td><?= htmlspecialchars($s['kelas']); ?></td>
                        <td><?= htmlspecialchars($s['jurusan']); ?></td>
                        <td><?= htmlspecialchars($s['jabatan_osis']); ?></td>
                        <td>
                          <select class="form-select form-select-sm bg-dark text-white statusDropdown" data-user-id="<?= $s['idsiswa']; ?>" data-status="">
                            <option value="Hadir" selected>Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Alpha">Alpha</option>
                          </select>
                        </td>
                      </tr>
                    <?php endwhile; ?>

                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Riwayat Hari Ini -->
          <div class="card rounded-3xl mt-3">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white mt-4 mb-0">Riwayat Absensi Hari Ini</h5>
                <button type="button" id="btnResetRiwayat" class="btn btn-outline-danger btn-sm">Reset Semua</button>
              </div>
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
                <tbody id="riwayatBody">
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
                    <tr data-absensi-id="<?= $r['id']; ?>">
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
    // Store modified statuses
    let modifiedStatuses = {};

    document.addEventListener('DOMContentLoaded', () => {
      const selectJadwal = document.getElementById('selectJadwal');
      const btnSaveAll = document.getElementById('btnSaveAll');
      const btnReset = document.getElementById('btnReset');
      const btnResetRiwayat = document.getElementById('btnResetRiwayat');
      const searchInput = document.getElementById('searchInput');
      const rows = document.querySelectorAll('#absensiTable tbody tr');
      const dropdowns = document.querySelectorAll('#absensiTable .statusDropdown');
      const riwayatDropdowns = document.querySelectorAll('#riwayatBody .statusDropdown');

      // Initialize modifiedStatuses with current data
      dropdowns.forEach(dd => {
        const userId = dd.dataset.userId;
        const currentValue = dd.value || 'Hadir';
        modifiedStatuses[userId] = {
          status: currentValue,
          keterangan: ''
        };
      });

      // Track changes in dropdowns (absensi utama)
      dropdowns.forEach(dd => {
        dd.addEventListener('change', (e) => {
          const userId = e.target.dataset.userId;
          const status = e.target.value;

          let keterangan = '';
          if (status === 'Izin' || status === 'Sakit') {
            keterangan = prompt(`Masukkan keterangan untuk status ${status}:`, '');
            if (keterangan === null) {
              // Cancel: revert to previous value
              e.target.value = modifiedStatuses[userId] || '';
              return;
            }
          }

          // Store the change
          modifiedStatuses[userId] = {
            status: status,
            keterangan: keterangan
          };

          // Visual feedback
          e.target.style.backgroundColor = '#198754'; // green
          setTimeout(() => e.target.style.backgroundColor = '#212529', 700);
        });
      });

      // Track changes in riwayat dropdowns
      riwayatDropdowns.forEach(select => {
        select.addEventListener('change', async (e) => {
          const id = e.target.dataset.id;
          const status = e.target.value;

          let keterangan = '';
          if (status === 'Izin' || status === 'Sakit') {
            keterangan = prompt(`Masukkan keterangan untuk status ${status}:`, '');
            if (keterangan === null) {
              // Cancel: revert to previous value
              const prevStatus = e.target.getAttribute('data-prev') || 'Hadir';
              e.target.value = prevStatus;
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
            e.target.style.backgroundColor = '#198754'; // green
            e.target.setAttribute('data-prev', status);
            setTimeout(() => e.target.style.backgroundColor = '#212529', 700);
          } else {
            e.target.style.backgroundColor = '#dc3545'; // red
          }
        });
      });

      // Reset riwayat button
      btnResetRiwayat.addEventListener('click', () => {
        if (confirm('⚠️ Apakah Anda yakin ingin menghapus SEMUA riwayat absensi hari ini?')) {
          const riwayatBody = document.getElementById('riwayatBody');
          const rows = riwayatBody.querySelectorAll('tr');

          rows.forEach(row => {
            const absensiId = row.getAttribute('data-absensi-id');

            // Delete from database
            fetch('../../core/hapus-absensi.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: `id=${absensiId}`
            }).then(res => res.text()).then(text => {
              if (text.trim() === 'ok') {
                row.style.transition = 'opacity 0.3s ease';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
              }
            });
          });

          // Reload absensi table after delay
          setTimeout(() => {
            location.reload();
          }, 1000);
        }
      });

      // Save all button
      btnSaveAll.addEventListener('click', async () => {
        const jadwalId = selectJadwal.value;
        if (!jadwalId) {
          alert('⚠️ Pilih jadwal kegiatan terlebih dahulu!');
          return;
        }

        const selectedDate = selectJadwal.options[selectJadwal.selectedIndex].getAttribute('data-tanggal');

        // Prepare data to send
        const dataToSend = [];
        for (const [userId, statusData] of Object.entries(modifiedStatuses)) {
          if (typeof statusData === 'object' && statusData.status) {
            dataToSend.push({
              user_id: userId,
              jadwal_id: jadwalId,
              tanggal: selectedDate,
              status: statusData.status,
              keterangan: statusData.keterangan || ''
            });
          }
        }

        if (dataToSend.length === 0) {
          alert('⚠️ Tidak ada perubahan status!');
          return;
        }

        try {
          const res = await fetch('../../core/tambah_kehadiran_api.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              jadwal_id: jadwalId,
              tanggal: selectedDate,
              records: dataToSend
            })
          });

          const result = await res.json();
          if (result.success) {
            alert('✅ Absensi berhasil disimpan!');

            // Reload halaman untuk menampilkan data terbaru
            // Siswa yang sudah absen akan hilang dari tabel absensi
            // dan muncul di tabel riwayat
            setTimeout(() => {
              location.reload();
            }, 800);
          } else {
            alert('❌ Error: ' + (result.message || 'Terjadi kesalahan'));
          }
        } catch (err) {
          alert('❌ Error: ' + err.message);
        }
      });

      // Reset button
      btnReset.addEventListener('click', () => {
        if (confirm('Yakin ingin reset semua perubahan?')) {
          modifiedStatuses = {};
          dropdowns.forEach(dd => {
            dd.value = dd.dataset.status || '';
          });
          searchInput.value = '';
          rows.forEach(row => {
            row.style.display = '';
            row.style.opacity = '1';
          });
        }
      });

      // Search functionality
      searchInput.addEventListener('keyup', () => {
        const keyword = searchInput.value.toLowerCase();
        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          if (text.includes(keyword)) {
            row.style.display = '';
            row.style.opacity = '1';
          } else {
            row.style.display = 'none';
          }
        });
      });

      // Jadwal change handler
      selectJadwal.addEventListener('change', () => {
        // Reset modified statuses when changing jadwal
        modifiedStatuses = {};
        dropdowns.forEach(dd => {
          dd.value = dd.dataset.status || '';
        });
      });
    });
  </script>

</body>

</html>