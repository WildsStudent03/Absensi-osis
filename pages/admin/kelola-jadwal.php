<?php
include "../../assets/boot.php";
include "../../includes/auth_guard.php";
?>

<!doctype html>
<html lang="id">

<head>
 <meta charset="utf-8">
 <title>Kelola Jadwal Kegiatan - Absensi OSIS</title>
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
 <link href="../../assets/common.css" rel="stylesheet">
</head>

<body>
 <header class="navbar navbar-expand-lg border-bottom border-soft">
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
    <?php
    // ambil daftar jadwal dari database
    include '../../database/connect.php';
    $jadwalList = [];
        // FIX 1: Menghilangkan 'waktu' dari SELECT dan ORDER BY. Diganti dengan 'created_at' untuk urutan kedua.
    $res = $conn->query('SELECT id, judul, tanggal, lokasi FROM jadwal_kegiatan ORDER BY tanggal DESC, created_at DESC');
    if ($res) {
     while ($r = $res->fetch_assoc()) $jadwalList[] = $r;
    }
    $conn->close();
    ?>

        <?php if (isset($_GET['success'])): ?>
     <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
     <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
     <h1 class="h4 mb-0">Kelola Jadwal Kegiatan</h1>
     <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
      <i class="bi bi-plus-circle"></i> Tambah Jadwal
     </button>
    </div>

    <div class="card rounded-3xl card-glow-purple">
     <div class="card-body">
      <div class="table-responsive">
       <table class="table table-dark table-hover">
        <thead>
         <tr>
          <th>No</th>
          <th>Tanggal</th>
                                <th>Judul Kegiatan</th>
          <th>Lokasi</th>
          <th>Aksi</th>
         </tr>
        </thead>
        <tbody>
         <?php if (count($jadwalList) > 0): ?>
          <?php $no = 1;
          foreach ($jadwalList as $jadwal): ?>
           <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d F Y', strtotime($jadwal['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($jadwal['judul']) ?></td>
            <td><?= htmlspecialchars($jadwal['lokasi']) ?></td>
            <td>
             <button type="button" class="btn btn-sm btn-info"
              onclick="editJadwal(<?= $jadwal['id'] ?>)">
              <i class="bi bi-pencil"></i>
             </button>
             <button type="button" class="btn btn-sm btn-danger"
              onclick="confirmDelete(<?= $jadwal['id'] ?>, '<?= htmlspecialchars($jadwal['judul']) ?>')">
              <i class="bi bi-trash"></i>
             </button>
            </td>
           </tr>
          <?php endforeach; ?>
         <?php else: ?>
          <tr>
                                   <td colspan="5" class="text-center">Belum ada jadwal kegiatan</td>
          </tr>
         <?php endif; ?>
        </tbody>
       </table>
      </div>
     </div>
    </div>
   </main>
  </div>
 </div>

  <div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-labelledby="modalTambahJadwalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
   <div class="modal-content bg-dark text-white">
    <div class="modal-header border-soft">
     <h5 class="modal-title" id="modalTambahJadwalLabel">Tambah Jadwal Kegiatan</h5>
     <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="../../core/proses-jadwal.php" method="POST">
     <div class="modal-body">
      <div class="row g-3">
       <div class="col-12">
        <div class="form-floating">
         <input type="text" class="form-control bg-dark text-white" id="judul" name="judul" placeholder="Judul Kegiatan" required>
         <label for="judul">Judul Kegiatan</label>
        </div>
       </div>
                       <div class="col-12">
        <div class="form-floating">
         <input type="date" class="form-control bg-dark text-white" id="tanggal" name="tanggal" required>
         <label for="tanggal">Tanggal</label>
        </div>
       </div>
       <div class="col-12">
        <div class="form-floating">
         <input type="text" class="form-control bg-dark text-white" id="lokasi" name="lokasi" placeholder="Lokasi" required>
         <label for="lokasi">Lokasi</label>
        </div>
       </div>
      </div>
     </div>
     <div class="modal-footer border-soft">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      <button type="submit" name="action" value="create" class="btn btn-primary">Simpan</button>
     </div>
    </form>
   </div>
  </div>
 </div>

  <div class="modal fade" id="modalEditJadwal" tabindex="-1" aria-labelledby="modalEditJadwalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
   <div class="modal-content bg-dark text-white">
    <div class="modal-header border-soft">
     <h5 class="modal-title" id="modalEditJadwalLabel">Edit Jadwal Kegiatan</h5>
     <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="../../core/proses-jadwal.php" method="POST">
     <input type="hidden" id="edit_id" name="id">
     <div class="modal-body">
      <div class="row g-3">
       <div class="col-12">
        <div class="form-floating">
         <input type="text" class="form-control bg-dark text-white" id="edit_judul" name="judul" placeholder="Judul Kegiatan" required>
         <label for="edit_judul">Judul Kegiatan</label>
        </div>
       </div>
                       <div class="col-12">
        <div class="form-floating">
         <input type="date" class="form-control bg-dark text-white" id="edit_tanggal" name="tanggal" required>
         <label for="edit_tanggal">Tanggal</label>
        </div>
       </div>
       <div class="col-12">
        <div class="form-floating">
         <input type="text" class="form-control bg-dark text-white" id="edit_lokasi" name="lokasi" placeholder="Lokasi" required>
         <label for="edit_lokasi">Lokasi</label>
        </div>
       </div>
      </div>
      </div>
     <div class="modal-footer border-soft">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      <button type="submit" name="action" value="update" class="btn btn-primary">Simpan Perubahan</button>
     </div>
    </form>
   </div>
  </div>
 </div>

  <div class="modal fade" id="modalKonfirmasiHapus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
   <div class="modal-content bg-dark text-white">
    <div class="modal-header border-soft">
     <h5 class="modal-title">Konfirmasi Hapus</h5>
     <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
     <p>Apakah Anda yakin ingin menghapus jadwal kegiatan "<span id="jadwalTitle"></span>"?</p>
     <p class="text-danger">Tindakan ini tidak dapat dibatalkan.</p>
    </div>
    <div class="modal-footer border-soft">
     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
     <form action="../../core/proses-jadwal.php" method="POST">
      <input type="hidden" id="delete_id" name="id">
      <button type="submit" name="action" value="delete" class="btn btn-danger">Hapus</button>
     </form>
     </div>
    </div>
   </div>
  </div>


 <script>
  // Fungsi untuk menampilkan modal edit dengan data jadwal
function editJadwal(id) {
      fetch(`../../core/get-jadwal.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                // Jika status HTTP bukan 200 (misalnya 404), lempar error
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
          if (data && !data.error) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_judul').value = data.judul;
            
            // Set edit tanggal robustly
            const editTanggalEl = document.getElementById('edit_tanggal');
            if (editTanggalEl) {
              if (data.tanggal) {
                editTanggalEl.value = data.tanggal;
                try {
                  editTanggalEl.valueAsDate = new Date(data.tanggal);
                } catch (e) {
                }
              } else {
                editTanggalEl.value = '';
              }
            }
            // Baris untuk edit_waktu dan edit_deskripsi sudah dihapus
            document.getElementById('edit_lokasi').value = data.lokasi;
            // document.getElementById('edit_deskripsi').value = data.deskripsi || ''; // Sudah dihapus

            const modalEdit = new bootstrap.Modal(document.getElementById('modalEditJadwal'));
            modalEdit.show();
            setTimeout(() => document.getElementById('edit_judul').focus(), 200);
          } else {
            // Jika PHP mengembalikan {'error': 'Jadwal tidak ditemukan'}
            alert(data.error || 'Gagal memuat data jadwal');
          }
        })
        .catch(error => {
          console.error('Error saat memuat data:', error);
          // Tampilkan error yang lebih spesifik ke pengguna jika bisa, 
            // atau gunakan pesan default Anda.
          alert('Terjadi kesalahan saat memuat data. Cek Console Log untuk detail.');
        });
    }

  // Fungsi untuk konfirmasi hapus
  function confirmDelete(id, title) {
   document.getElementById('delete_id').value = id;
   document.getElementById('jadwalTitle').textContent = title;

   const modalDelete = new bootstrap.Modal(document.getElementById('modalKonfirmasiHapus'));
   modalDelete.show();
  }

  // Set tanggal default untuk form tambah jadwal
  document.addEventListener('DOMContentLoaded', () => {
   const today = new Date().toISOString().split('T')[0];
   // Only set default date for the "Tambah" form if it's empty.
   const tanggalEl = document.getElementById('tanggal');
   if (tanggalEl && !tanggalEl.value) {
    tanggalEl.value = today;
   }
   // auto dismiss alerts
   setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
     try {
      a.remove();
     } catch (e) {}
    });
   }, 5000);
  });
 </script>
</body>

</html>