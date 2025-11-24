<?php
include "../../assets/boot.php";
include "../../includes/auth_guard.php";
?>

<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Kelola Anggota - Absensi OSIS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 -->
  <link href="../../assets/common.css" rel="stylesheet">
</head>

<body>
  <header class="navbar navbar-expand-lg navbar-dark border-bottom border-soft">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Absensi OSIS</a>
      <button class="navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="d-none d-md-flex align-items-center gap-3">
        <span class="badge badge-cyan" data-user-role>ADMIN</span>
          <a href="../../core/logout.php"
          data-logout
          class="btn btn-outline-light btn-sm"
          onclick="return confirm('Apakah Anda yakin ingin keluar (Logout)?');">Logout</a>
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
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h1 class="h4 mb-1">Kelola Anggota</h1>
            <p class="text-muted mb-0">Tambah, ubah, atau hapus data anggota.</p>
          </div>
          <button class="btn btn-secondary hover-glow" data-bs-toggle="modal" data-bs-target="#modalAdd">+ Tambah Anggota</button>
        </div>

        <div class="card mt-3 rounded-3xl">
          <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
              <div class="col-md-5">
                <label class="form-label text-light">Pencarian</label>
                <input type="text" name="q" class="form-control bg-light" placeholder="Cari nama atau NIS..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label text-light">Jurusan</label>
                <select name="jurusan" class="form-select">
                  <option value="">Semua</option>
                  <option value="RPL" <?php echo (isset($_GET['jurusan']) && $_GET['jurusan'] === 'RPL') ? 'selected' : ''; ?>>RPL</option>
                  <option value="TBSM" <?php echo (isset($_GET['jurusan']) && $_GET['jurusan'] === 'TBSM') ? 'selected' : ''; ?>>TBSM</option>
                  <option value="ATPH" <?php echo (isset($_GET['jurusan']) && $_GET['jurusan'] === 'ATPH') ? 'selected' : ''; ?>>ATPH</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label text-light">Status</label>
                <select name="status" class="form-select">
                  <option value="">Semua</option>
                  <option value="Aktif" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                  <option value="Nonaktif" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                </select>
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Cari</button>
              </div>
            </form>
            <?php

            include '../../database/connect.php';

            // Initialize where clauses and parameters for prepared statement
            $where = [];
            $params = [];
            $types = '';


            if (!empty($_GET['q'])) {
              $where[] = '(nama LIKE ? OR nis LIKE ?)';
              $params[] = '%' . $_GET['q'] . '%';
              $params[] = '%' . $_GET['q'] . '%';
              $types .= 'ss';
            }

            // Add status filter if selected
            if (!empty($_GET['status'])) {
              $where[] = 'status_osis = ?';
              $params[] = $_GET['status'];
              $types .= 's';
            }

            // Add jurusan filter if selected
            if (!empty($_GET['jurusan'])) {
              $where[] = 'jurusan = ?';
              $params[] = $_GET['jurusan'];
              $types .= 's';
            }

            // Build the SQL query
            $sql = 'SELECT * FROM datasiswa';
            if ($where) {
              $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY nama ASC';

            // Prepare and execute the statement
            $stmt = $conn->prepare($sql);
            if ($params) {
              $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            <div class="table-responsive mt-3">
              <table class="table table-dark table-hover align-middle">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>NIS</th>
                    <th>Kelas</th>
                    <th>Jurusan</th>
                    <th>No. HP</th>
                    <th>Jabatan OSIS</th>
                    <th>Status</th>
                    <th style="width:140px">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                      <tr data-id="<?php echo $row['idsiswa']; ?>"
                        data-alamat="<?php echo htmlspecialchars($row['alamat'] ?? ''); ?>">
                        <td class="col-nama"><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td class="col-nis"><?php echo htmlspecialchars($row['nis']); ?></td>
                        <td class="col-kelas"><?php echo htmlspecialchars($row['kelas']); ?></td>
                        <td class="col-jurusan"><?php echo htmlspecialchars($row['jurusan']); ?></td>
                        <td class="col-nohp"><?php echo htmlspecialchars($row['no_hp'] ?? '-'); ?></td>
                        <td class="col-jabatan"><?php echo htmlspecialchars($row['jabatan_osis'] ?? 'Anggota'); ?></td>
                        <td class="col-status">
                          <span class="badge <?php echo $row['status_osis'] === 'Aktif' ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo htmlspecialchars($row['status_osis']); ?>
                          </span>
                        </td>
                        <td style="width:140px">
                          <button class="btn btn-sm btn-info" onclick="editAnggota(<?php echo $row['idsiswa']; ?>)">Edit</button>
                          <button class="btn btn-sm btn-danger"
                            onclick="if(confirm('Yakin ingin menghapus data ini?')) location.href='../../core/hapus-anggota.php?id=<?php echo $row['idsiswa']; ?>';">Hapus</button>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="8" class="text-center">Belum ada data anggota</td>
                    </tr>
                  <?php endif; ?>
                </tbody>

              </table>
            </div>
            <?php $conn->close(); ?>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Modal Add -->
  <div class="modal modal-zoom fade" id="modalAdd" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content bg-dark text-white rounded-3xl border-0 shadow-lg">
        <div class="modal-header border-secondary">
          <h5 class="modal-title text-white">Tambah Anggota</h5>
          <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo $_GET['error']; ?></div>
          <?php endif; ?>
          <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
          <?php endif; ?>

          <form action="../../core/tambah-anggota.php" method="POST">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label text-white">NIS</label>
                <input class="form-control bg-dark text-white border-secondary" name="nis" required>
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Nama</label>
                <input class="form-control bg-dark text-white border-secondary" name="nama" required>
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Kelas</label>
                <select class="form-select bg-dark text-white border-secondary" name="kelas" required>
                  <option value="">Pilih Kelas</option>
                  <option value="X">X</option>
                  <option value="XI">XI</option>
                  <option value="XII">XII</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Jurusan</label>
                <select class="form-select bg-dark text-white border-secondary" name="jurusan" required>
                  <option value="RPL">RPL</option>
                  <option value="TBSM">TBSM</option>
                  <option value="ATPH">ATPH</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">No. HP</label>
                <input class="form-control bg-dark text-white border-secondary" name="no_hp" placeholder="0812xxxx">
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Jabatan OSIS</label>
                <input class="form-control bg-dark text-white border-secondary" name="jabatan_osis" placeholder="(opsional)">
              </div>
              <input type="hidden" name="status_osis" value="Aktif">
            </div>

            <div class="modal-footer border-secondary mt-3">
              <button type="button" class="btn btn-outline-light text-white" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-light text-dark fw-semibold">
                <span class="me-1">Simpan</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Edit Anggota -->
  <div class="modal modal-zoom fade" id="modalEditAnggota" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content bg-dark text-white rounded-3xl border-0 shadow-lg">
        <form method="POST" action="../../core/edit-anggota.php">
          <div class="modal-header border-secondary">
            <h5 class="modal-title text-white">Edit Anggota</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <input type="hidden" name="id" id="editId">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label text-white">NIS</label>
                <input class="form-control bg-dark text-white border-secondary" name="nis" id="editNis" required>
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Nama</label>
                <input class="form-control bg-dark text-white border-secondary" name="nama" id="editNama" required>
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Kelas</label>
                <select class="form-select bg-dark text-white border-secondary" name="kelas" id="editKelas" required>
                  <option value="">Pilih Kelas</option>
                  <option value="X">X</option>
                  <option value="XI">XI</option>
                  <option value="XII">XII</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Jurusan</label>
                <select class="form-select bg-dark text-white border-secondary" name="jurusan" id="editJurusan" required>
                  <option value="RPL">RPL</option>
                  <option value="TBSM">TBSM</option>
                  <option value="ATPH">ATPH</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">No. HP</label>
                <input class="form-control bg-dark text-white border-secondary" name="no_hp" id="editNoHp" placeholder="0812xxxx">
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Jabatan OSIS</label>
                <input class="form-control bg-dark text-white border-secondary" name="jabatan_osis" id="editJabatanOsis" placeholder="(opsional)">
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Status OSIS</label>
                <select name="status_osis" id="editStatusOsis" class="form-select bg-dark text-white border-secondary" required>
                  <option value="Aktif">Aktif</option>
                  <option value="Nonaktif">Nonaktif</option>
                </select>
              </div>
            </div>
          </div>

          <div class="modal-footer border-secondary mt-3">
            <button type="button" class="btn btn-outline-light text-white" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-light text-dark fw-semibold">
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <script>
    function editAnggota(id) {
      // Ambil data dari baris tabel
      let row = document.querySelector('tr[data-id="' + id + '"]');
      document.getElementById('editId').value = id;
      document.getElementById('editNis').value = row.querySelector('.col-nis').textContent.trim();
      document.getElementById('editNama').value = row.querySelector('.col-nama').textContent.trim();
      document.getElementById('editKelas').value = row.querySelector('.col-kelas').textContent.trim();
      document.getElementById('editNoHp').value = row.querySelector('.col-nohp').textContent.trim().replace('-', '');
      document.getElementById('editJabatanOsis').value = row.querySelector('.col-jabatan').textContent.trim().replace('Anggota', '');
      // Ambil jurusan
      let jurusanText = row.querySelector('.col-jurusan').textContent.trim();
      document.getElementById('editJurusan').value = jurusanText;

      // Ambil status dan normalize to 'Nonaktif' if needed
      let statusText = row.querySelector('.col-status span').textContent.trim();
      if (statusText === 'Tidak Aktif') statusText = 'Nonaktif';
      document.getElementById('editStatusOsis').value = statusText;

      // Tampilkan modal
      let myModal = new bootstrap.Modal(document.getElementById('modalEditAnggota'));
      myModal.show();
    }
  </script>
</body>

</html>