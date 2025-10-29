<?php
include "../../assets/boot.php";
?>

<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Laporan Absensi - Absensi OSIS</title>
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
      <div class="d-flex align-items-center gap-3">
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
        <div class="d-flex flex-wrap gap-2 align-items-end justify-content-between">
          <div>
            <h1 class="h4 mb-1">Laporan Absensi</h1>
            <p class="text-muted mb-0">Rekap mingguan dan bulanan, bisa diekspor/print.</p>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-light" id="btnExport">Export PDF</button>
            <button class="btn btn-secondary" id="btnPrint">Print</button>
          </div>
        </div>

        <div class="card rounded-3xl mt-3">
          <div class="card-body">
            <form method="GET" class="row g-3">
              <div class="col-md-4">
                <label class="form-label text-light">Filter Jurusan</label>
                <select name="jurusan" class="form-select">
                  <option value="ALL" <?= (isset($_GET['jurusan']) && $_GET['jurusan'] === 'ALL') ? 'selected' : ''; ?>>Semua</option>
                  <option value="RPL" <?= (isset($_GET['jurusan']) && $_GET['jurusan'] === 'RPL') ? 'selected' : ''; ?>>RPL</option>
                  <option value="TBSM" <?= (isset($_GET['jurusan']) && $_GET['jurusan'] === 'TBSM') ? 'selected' : ''; ?>>TBSM</option>
                  <option value="ATPH" <?= (isset($_GET['jurusan']) && $_GET['jurusan'] === 'ATPH') ? 'selected' : ''; ?>>ATPH</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label text-light">Tanggal Acuan</label>
                <input type="date" name="tanggal" class="form-control" value="<?= isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d'); ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label text-light">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Filter</button>
              </div>
            </form>

            <?php
            // Include database connection
            include '../../database/connect.php';

            // Get filter parameters
            $jurusanFilter = $_GET['jurusan'] ?? 'ALL';
            $tanggalAcuan = $_GET['tanggal'] ?? date('Y-m-d');

            // Function to get weekly data
            function getWeeklyData($conn, $tanggalAcuan, $jurusanFilter)
            {
              $startDate = date('Y-m-d', strtotime($tanggalAcuan . ' -6 days'));
              $endDate = $tanggalAcuan;

              $whereClause = "WHERE a.tanggal BETWEEN ? AND ?";
              $params = [$startDate, $endDate];
              $types = 'ss';

              if ($jurusanFilter !== 'ALL') {
                $whereClause .= " AND d.jurusan = ?";
                $params[] = $jurusanFilter;
                $types .= 's';
              }

              // Select only the latest absensi per user/tanggal to avoid duplicates
              $sql = "SELECT 
  a.tanggal,
  a.status,
  a.keterangan,
  d.nama,
  d.kelas,
  d.jurusan,
  a.created_at AS waktu_input
        FROM absensi a
        JOIN (
          SELECT user_id, tanggal, MAX(created_at) AS max_created
          FROM absensi
          WHERE tanggal BETWEEN ? AND ?
          GROUP BY user_id, tanggal
        ) m ON a.user_id = m.user_id AND a.tanggal = m.tanggal AND a.created_at = m.max_created
        JOIN datasiswa d ON a.user_id = d.idsiswa
        LEFT JOIN jadwal_kegiatan j ON a.jadwal_id = j.id
        WHERE a.tanggal BETWEEN ? AND ?";

              // build types and params: subquery uses start/end, outer WHERE repeats start/end; append jurusan if needed
              $execTypes = $types . $types; // 'ss' + possible 's' => duplicate start/end for subquery and outer
              $execParams = array_merge($params, $params);

              if ($jurusanFilter !== 'ALL') {
                $sql .= " AND d.jurusan = ?";
                $execTypes .= 's';
                $execParams[] = $jurusanFilter;
              }

              $sql .= " ORDER BY a.tanggal ASC, d.nama ASC";

              $stmt = $conn->prepare($sql);
              $stmt->bind_param($execTypes, ...$execParams);
              $stmt->execute();
              $result = $stmt->get_result();

              $data = [];
              while ($row = $result->fetch_assoc()) {
                $data[$row['tanggal']][] = $row;
              }

              return $data;
            }

            // Function to get monthly data
            function getMonthlyData($conn, $tanggalAcuan, $jurusanFilter)
            {
              $year = date('Y', strtotime($tanggalAcuan));
              $month = date('m', strtotime($tanggalAcuan));

              // Select only the latest absensi per user/tanggal for the month to avoid duplicates
              $sql = "SELECT 
  a.status,
  a.keterangan,
  d.nama,
  d.kelas,
  d.jurusan,
  a.tanggal,
  a.created_at AS waktu_input
        FROM absensi a
        JOIN (
          SELECT user_id, tanggal, MAX(created_at) AS max_created
          FROM absensi
          WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ?
          GROUP BY user_id, tanggal
        ) m ON a.user_id = m.user_id AND a.tanggal = m.tanggal AND a.created_at = m.max_created
        JOIN datasiswa d ON a.user_id = d.idsiswa
        LEFT JOIN jadwal_kegiatan j ON a.jadwal_id = j.id
        WHERE YEAR(a.tanggal) = ? AND MONTH(a.tanggal) = ?";

              // build base params/types for year/month
              $params = [$year, $month];
              $types = 'ss';

              // build exec types/params (subquery year/month and outer year/month)
              $execTypes = $types . $types; // 'ss' + possible 's'
              $execParams = array_merge($params, $params);

              if ($jurusanFilter !== 'ALL') {
                $sql .= " AND d.jurusan = ?";
                $execTypes .= 's';
                $execParams[] = $jurusanFilter;
              }

              $sql .= " ORDER BY a.status ASC, d.nama ASC";

              $stmt = $conn->prepare($sql);
              $stmt->bind_param($execTypes, ...$execParams);
              $stmt->execute();
              $result = $stmt->get_result();

              $data = [];
              while ($row = $result->fetch_assoc()) {
                $data[$row['status']][] = $row;
              }

              return $data;
            }

            // Get data
            $weeklyData = getWeeklyData($conn, $tanggalAcuan, $jurusanFilter);
            $monthlyData = getMonthlyData($conn, $tanggalAcuan, $jurusanFilter);

            // Generate date range for weekly
            $startDate = date('Y-m-d', strtotime($tanggalAcuan . ' -6 days'));
            $endDate = $tanggalAcuan;
            $dateRange = [];
            for ($i = 0; $i < 7; $i++) {
              $dateRange[] = date('Y-m-d', strtotime($startDate . " +$i days"));
            }
            ?>

            <div class="row g-3 mt-3">
              <!-- Rekap Mingguan -->
              <div class="col-12">
                <div class="card rounded-3xl">
                  <div class="card-body">
                    <h6 class="mb-3 text-light">Rekap Mingguan (<?= date('d/m/Y', strtotime($startDate)) ?> - <?= date('d/m/Y', strtotime($endDate)) ?>)</h6>
                    <div class="table-responsive">
                      <table class="table table-dark table-hover">
                        <thead>
                          <tr>
                            <th>Tanggal</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Jurusan</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $totalHadir = $totalIzin = $totalSakit = $totalAlpha = 0;
                          foreach ($dateRange as $date):
                            if (isset($weeklyData[$date])):
                              foreach ($weeklyData[$date] as $record):
                                $status = $record['status'];
                                $keterangan = $record['keterangan'] ?? '';

                                // Count untuk total
                                switch ($status) {
                                  case 'Hadir':
                                    $totalHadir++;
                                    break;
                                  case 'Izin':
                                    $totalIzin++;
                                    break;
                                  case 'Sakit':
                                    $totalSakit++;
                                    break;
                                  case 'Alpha':
                                    $totalAlpha++;
                                    break;
                                }

                                // Tentukan warna status
                                $statusColor = '';
                                switch ($status) {
                                  case 'Hadir':
                                    $statusColor = 'text-success fw-bold';
                                    break;
                                  case 'Izin':
                                    $statusColor = 'text-info fw-bold';
                                    break;
                                  case 'Sakit':
                                    $statusColor = 'text-warning fw-bold';
                                    break;
                                  case 'Alpha':
                                    $statusColor = 'text-danger fw-bold';
                                    break;
                                }
                          ?>
                                <tr>
                                  <td><?= date('d/m/Y', strtotime($date)) ?></td>
                                  <td><?= htmlspecialchars($record['nama']) ?></td>
                                  <td><?= htmlspecialchars($record['kelas'] ?? '') ?></td>
                                  <td><?= htmlspecialchars($record['jurusan']) ?></td>
                                  <?php $time = isset($record['waktu_input']) ? date('H:i', strtotime($record['waktu_input'])) : ''; ?>
                                  <td><?= htmlspecialchars($time) ?></td>
                                  <td><span class="<?= $statusColor ?>"><?= $status ?></span></td>
                                  <td><?= htmlspecialchars($keterangan) ?></td>
                                </tr>
                          <?php
                              endforeach;
                            endif;
                          endforeach; ?>
                          <tr class="table-secondary text-center align-middle">
                            <td colspan="3"><strong>TOTAL</strong></td>
                            <td class="text-success fw-bold">Hadir: <?= $totalHadir ?></td>
                            <td class="text-info fw-bold">Izin: <?= $totalIzin ?></td>
                            <td class="text-warning fw-bold">Sakit: <?= $totalSakit ?></td>
                            <td class="text-danger fw-bold">Alpha: <?= $totalAlpha ?></td>
                          </tr>
                          <tr class="table-light text-center">
                            <td colspan="7"><strong>Total Kehadiran: <?= $totalHadir + $totalIzin + $totalSakit + $totalAlpha ?></strong></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Rekap Bulanan -->
              <div class="col-12">
                <div class="card rounded-3xl">
                  <div class="card-body">
                    <h6 class="mb-3 text-light">Rekap Bulanan (<?= date('F Y', strtotime($tanggalAcuan)) ?>)</h6>
                    <div class="table-responsive">
                      <table class="table table-dark table-hover">
                        <thead>
                          <tr>
                            <th>Status</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Jurusan</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Keterangan</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $totalBulanan = 0;
                          $statuses = ['Hadir', 'Izin', 'Sakit', 'Alpha'];
                          $colors = ['text-success', 'text-info', 'text-warning', 'text-danger'];

                          foreach ($statuses as $index => $status):
                            if (isset($monthlyData[$status])):
                              foreach ($monthlyData[$status] as $record):
                                $totalBulanan++;
                                $keterangan = $record['keterangan'] ?? '';
                          ?>
                                <tr>
                                  <td><span class="<?= $colors[$index] ?> fw-bold"><?= $status ?></span></td>
                                  <td><?= htmlspecialchars($record['nama']) ?></td>
                                  <td><?= htmlspecialchars($record['kelas'] ?? '') ?></td>
                                  <td><?= htmlspecialchars($record['jurusan']) ?></td>
                                  <td><?= date('d/m/Y', strtotime($record['tanggal'])) ?></td>
                                  <?php $time = isset($record['waktu_input']) ? date('H:i', strtotime($record['waktu_input'])) : ''; ?>
                                  <td><?= htmlspecialchars($time) ?></td>
                                  <td><?= htmlspecialchars($keterangan) ?></td>
                                </tr>
                          <?php
                              endforeach;
                            endif;
                          endforeach; ?>
                          <tr class="table-secondary">
                            <td colspan="5"><strong>TOTAL</strong></td>
                            <td><strong><?= $totalBulanan ?></strong></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <!-- Statistik Bulanan -->
                    <div class="row mt-4">
                      <div class="col-md-12">
                        <div class="card rounded-3xl">
                          <div class="card-body">
                            <h6 class="text-light mb-3">Statistik Bulanan</h6>
                            <div class="row text-center">
                              <div class="col-md-3">
                                <div class="fs-1 text-success fw-bold">
                                  <?php
                                  $hadir = isset($monthlyData['Hadir']) ? count($monthlyData['Hadir']) : 0;
                                  $total = $totalBulanan;
                                  $persentaseHadir = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;
                                  echo $persentaseHadir;
                                  ?>%
                                </div>
                                <div class="text-muted">Tingkat Kehadiran</div>
                              </div>
                              <div class="col-md-3">
                                <div class="fs-2 text-success fw-bold"><?= $hadir ?></div>
                                <div class="text-muted">Hadir</div>
                              </div>
                              <div class="col-md-3">
                                <div class="fs-2 text-info fw-bold"><?= isset($monthlyData['Izin']) ? count($monthlyData['Izin']) : 0 ?></div>
                                <div class="text-muted">Izin</div>
                              </div>
                              <div class="col-md-3">
                                <div class="fs-2 text-danger fw-bold"><?= $total - $hadir ?></div>
                                <div class="text-muted">Tidak Hadir</div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
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


  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Print button
      document.getElementById('btnPrint').addEventListener('click', () => {
        // Get only the report content
        const reportContent = document.querySelector('.row.g-3.mt-3');

        if (!reportContent) {
          alert('Tidak ada data laporan untuk dicetak');
          return;
        }

        // Create a new window for printing
        const printWindow = window.open('', '_blank');

        // Create print content with only the reports
        const printContent = `
          <!DOCTYPE html>
          <html>
          <head>
            <title>Laporan Absensi OSIS - ${new Date().toLocaleDateString('id-ID')}</title>
            <style>
              body { 
                font-family: Arial, sans-serif; 
                margin: 20px; 
                background: white; 
                color: black; 
              }
              .card { 
                border: 1px solid #ccc; 
                border-radius: 8px; 
                margin-bottom: 20px; 
                background: white; 
              }
              .card-body { padding: 15px; }
              .table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-bottom: 20px; 
              }
              .table th, .table td { 
                border: 1px solid #ccc; 
                padding: 8px; 
                text-align: left; 
              }
              .table th { 
                background: #f8f9fa; 
                font-weight: bold; 
              }
              .table-secondary { background: #f8f9fa; }
              .text-success { color: #28a745; font-weight: bold; }
              .text-info { color: #17a2b8; font-weight: bold; }
              .text-warning { color: #ffc107; font-weight: bold; }
              .text-danger { color: #dc3545; font-weight: bold; }
              .fw-bold { font-weight: bold; }
              .fs-1 { font-size: 2.5rem; }
              .fs-2 { font-size: 2rem; }
              .text-center { text-align: center; }
              .text-muted { color: #6c757d; }
              .mb-3 { margin-bottom: 1rem; }
              .mt-4 { margin-top: 1.5rem; }
              .row { display: flex; flex-wrap: wrap; }
              .col-md-3 { width: 25%; }
              .col-md-12 { width: 100%; }
              h6 { font-size: 1.1rem; font-weight: bold; margin-bottom: 15px; }
              @media print {
                body { margin: 0; }
                .card { break-inside: avoid; }
              }
            </style>
          </head>
          <body>
            <h1 style="text-align: center; margin-bottom: 30px;">Laporan Absensi OSIS</h1>
            <p style="text-align: center; color: #666; margin-bottom: 30px;">Tanggal: ${new Date().toLocaleDateString('id-ID')}</p>
            ${reportContent.innerHTML}
          </body>
          </html>
        `;

        printWindow.document.write(printContent);
        printWindow.document.close();

        // Wait for content to load then print
        printWindow.onload = () => {
          setTimeout(() => {
            printWindow.print();
            printWindow.close();
          }, 500);
        };
      });

      // Export PDF button
      document.getElementById('btnExport').addEventListener('click', () => {
        // Get only the report content
        const reportContent = document.querySelector('.row.g-3.mt-3');

        if (!reportContent) {
          alert('Tidak ada data laporan untuk diekspor');
          return;
        }

        // Create a new window for PDF export
        const printWindow = window.open('', '_blank');

        // Create PDF content with only the reports
        const pdfContent = `
          <!DOCTYPE html>
          <html>
          <head>
            <title>Laporan Absensi OSIS - ${new Date().toLocaleDateString('id-ID')}</title>
            <style>
              body { 
                font-family: Arial, sans-serif; 
                margin: 20px; 
                background: white; 
                color: black; 
              }
              .card { 
                border: 1px solid #ccc; 
                border-radius: 8px; 
                margin-bottom: 20px; 
                background: white; 
              }
              .card-body { padding: 15px; }
              .table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-bottom: 20px; 
              }
              .table th, .table td { 
                border: 1px solid #ccc; 
                padding: 8px; 
                text-align: left; 
              }
              .table th { 
                background: #f8f9fa; 
                font-weight: bold; 
              }
              .table-secondary { background: #f8f9fa; }
              .text-success { color: #28a745; font-weight: bold; }
              .text-info { color: #17a2b8; font-weight: bold; }
              .text-warning { color: #ffc107; font-weight: bold; }
              .text-danger { color: #dc3545; font-weight: bold; }
              .fw-bold { font-weight: bold; }
              .fs-1 { font-size: 2.5rem; }
              .fs-2 { font-size: 2rem; }
              .text-center { text-align: center; }
              .text-muted { color: #6c757d; }
              .mb-3 { margin-bottom: 1rem; }
              .mt-4 { margin-top: 1.5rem; }
              .row { display: flex; flex-wrap: wrap; }
              .col-md-3 { width: 25%; }
              .col-md-12 { width: 100%; }
              h6 { font-size: 1.1rem; font-weight: bold; margin-bottom: 15px; }
              @media print {
                body { margin: 0; }
                .card { break-inside: avoid; }
              }
            </style>
          </head>
          <body>
            <h1 style="text-align: center; margin-bottom: 30px;">Laporan Absensi OSIS</h1>
            <p style="text-align: center; color: #666; margin-bottom: 30px;">Tanggal: ${new Date().toLocaleDateString('id-ID')}</p>
            ${reportContent.innerHTML}
          </body>
          </html>
        `;

        printWindow.document.write(pdfContent);
        printWindow.document.close();

        // Wait for content to load then print
        printWindow.onload = () => {
          setTimeout(() => {
            printWindow.print();
            printWindow.close();
          }, 500);
        };
      });
    });
  </script>
</body>

</html>