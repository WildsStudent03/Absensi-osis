<?php
include "../../assets/boot.php";
include "../../includes/auth_guard.php";
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Absensi - Absensi OSIS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        input[type="date"] {
            background-color: #000;
            color: #fff;
            border: 1px solid #444;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            /* agar ikon kalender ikut jadi putih */
        }
    </style>

    <link href="../../assets/common.css" rel="stylesheet">
    <style>
      /* Paksa teks hitam saat print */
      @media print {
        * { color: #000 !important; text-shadow: none !important; box-shadow: none !important; }
        html, body { background: #fff !important; }
        .card, .table, .table * { background: #fff !important; }
        .badge, .text-success, .text-info, .text-warning, .text-danger, .text-light, .text-muted { color: #000 !important; }
        .table-dark { --bs-table-bg: #fff !important; --bs-table-color: #000 !important; --bs-table-border-color: #000 !important; }
        .table-dark thead th { border-bottom: 1px solid #000 !important; }
        .navbar, .offcanvas, .sidebar { display: none !important; }
        .no-print { display: none !important; }
        a { color: #000 !important; }
      }
    </style>
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
                        <p class="text-muted mb-0 no-print">Rekap mingguan dan bulanan, bisa diekspor/print.</p>
                    </div>
                    <div class="d-flex gap-2 no-print">
                        <button class="btn btn-outline-light" id="btnExport">Export PDF</button>
                        <button class="btn btn-secondary" id="btnPrint">Print</button>
                    </div>
                </div>

                <div class="card rounded-3xl mt-3">
                    <div class="card-body">
                        <form method="GET" class="row g-3 no-print">
                            <div class="col-md-3">
                                <label class="form-label text-light">Filter Jurusan</label>
                                <select name="jurusan" class="form-select">
                                    <option value="ALL" <?= (isset($_GET['jurusan']) && $_GET['jurusan'] === 'ALL') ? 'selected' : ''; ?>>Semua</option>
                                    <option value="RPL" <?= (isset($_GET['jurusan']) && $_GET['jurusan'] === 'RPL') ? 'selected' : ''; ?>>RPL</option>
                                    <option value="TBSM" <?= (isset($_GET['jurusan']) && $_GET['jurusan'] === 'TBSM') ? 'selected' : ''; ?>>TBSM</option>
                                    <option value="ATPH" <?= (isset($_GET['jurusan']) && $_GET['jurusan'] === 'ATPH') ? 'selected' : ''; ?>>ATPH</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-light">Pilih Jadwal</label>
                                <select name="jadwal_id" class="form-select">
                                    <option value="">-- Semua Jadwal --</option>
                                    <?php
                                    include '../../database/connect.php';
                                    $selectedJadwalId = $_GET['jadwal_id'] ?? '';
                                    $stmtJadwal = $conn->prepare("SELECT id, judul, tanggal FROM jadwal_kegiatan ORDER BY tanggal DESC");
                                    $stmtJadwal->execute();
                                    $resultJadwal = $stmtJadwal->get_result();
                                    while ($row = $resultJadwal->fetch_assoc()):
                                        $isSelected = ($selectedJadwalId == $row['id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row['id'] ?>" <?= $isSelected ?>><?= htmlspecialchars($row['judul']) ?> (<?= date('d/m/Y', strtotime($row['tanggal'])) ?>)</option>
                                    <?php endwhile;
                                    $stmtJadwal->close(); ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-light">Tanggal Acuan</label>
                                <input type="date" name="tanggal" class="form-control" value="<?= isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-light">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>

                        <?php
                        // Get filter parameters
                        $jurusanFilter = $_GET['jurusan'] ?? 'ALL';
                        $jadwalFilter = $_GET['jadwal_id'] ?? '';

                        // Handle month input (YYYY-MM format) or date input (YYYY-MM-DD format)
                        if (isset($_GET['bulan']) && !empty($_GET['bulan'])) {
                            // bulan format: YYYY-MM, convert to first day of month
                            $tanggalAcuan = $_GET['bulan'] . '-01';
                        } else {
                            $tanggalAcuan = $_GET['tanggal'] ?? date('Y-m-d');
                        }

                        // Function to get weekly data
                        function getWeeklyData($conn, $tanggalAcuan, $jurusanFilter, $jadwalFilter)
                        {
                            $startDate = date('Y-m-d', strtotime($tanggalAcuan . ' -6 days'));
                            $endDate = $tanggalAcuan;

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

                            $execParams = [$startDate, $endDate, $startDate, $endDate];
                            $execTypes = 'ssss';

                            if ($jurusanFilter !== 'ALL') {
                                $sql .= " AND d.jurusan = ?";
                                $execParams[] = $jurusanFilter;
                                $execTypes .= 's';
                            }

                            if (!empty($jadwalFilter)) {
                                $sql .= " AND a.jadwal_id = ?";
                                $execParams[] = intval($jadwalFilter);
                                $execTypes .= 'i';
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
                        function getMonthlyData($conn, $tanggalAcuan, $jurusanFilter, $jadwalFilter)
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

                            $execParams = [$year, $month, $year, $month];
                            $execTypes = 'siss';

                            if ($jurusanFilter !== 'ALL') {
                                $sql .= " AND d.jurusan = ?";
                                $execParams[] = $jurusanFilter;
                                $execTypes .= 's';
                            }

                            if (!empty($jadwalFilter)) {
                                $sql .= " AND a.jadwal_id = ?";
                                $execParams[] = intval($jadwalFilter);
                                $execTypes .= 'i';
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
                        $weeklyData = getWeeklyData($conn, $tanggalAcuan, $jurusanFilter, $jadwalFilter);
                        $monthlyData = getMonthlyData($conn, $tanggalAcuan, $jurusanFilter, $jadwalFilter);

                        // Generate date range for weekly
                        $startDate = date('Y-m-d', strtotime($tanggalAcuan . ' -6 days'));
                        $endDate = $tanggalAcuan;
                        $dateRange = [];
                        for ($i = 0; $i < 7; $i++) {
                            $dateRange[] = date('Y-m-d', strtotime($startDate . " +$i days"));
                        }
                        ?>

                        <div class="row g-3 mt-3">
                            <!-- Detail Riwayat Absensi Per Siswa -->
                            <?php
                            // FILE: laporan.php (Laporan Ringkasan Bulanan)

                            // ASUMSI: Variabel $conn, $tanggalAcuan, dan $jurusanFilter sudah didefinisikan

                            $year = date('Y', strtotime($tanggalAcuan));
                            $month = date('m', strtotime($tanggalAcuan));

                            // Kueri tetap mengambil semua detail absensi bulanan untuk tujuan PENGHITUNGAN TOTAL
                            $sqlDetail = "SELECT 
    a.tanggal,
    a.status,
    a.keterangan,
    d.nama,
    d.nis,
    d.kelas,
    d.jurusan,
    d.no_hp,
    d.jabatan_osis,
    d.status_osis,
    d.idsiswa,
    a.created_at AS waktu_input
  FROM absensi a
  JOIN (
    -- Subquery untuk memastikan kita hanya menghitung status ABSENSI terakhir per tanggal/user
    SELECT user_id, tanggal, MAX(created_at) AS max_created
    FROM absensi
    WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ?
    GROUP BY user_id, tanggal
  ) m ON a.user_id = m.user_id AND a.tanggal = m.tanggal AND a.created_at = m.max_created
  JOIN datasiswa d ON a.user_id = d.idsiswa
  WHERE YEAR(a.tanggal) = ? AND MONTH(a.tanggal) = ?";

                            $execParamsDetail = [$year, $month, $year, $month];
                            $execTypesDetail = 'iiii';

                            if (isset($jurusanFilter) && $jurusanFilter !== 'ALL') {
                                $sqlDetail .= " AND d.jurusan = ?";
                                $execParamsDetail[] = $jurusanFilter;
                                $execTypesDetail .= 's';
                            }

                            $sqlDetail .= " ORDER BY d.nama ASC"; // Order by nama saja, karena tanggal sudah tidak relevan

                            // ASUMSI: $conn adalah objek koneksi database yang valid
                            $stmtDetail = $conn->prepare($sqlDetail);
                            $stmtDetail->bind_param($execTypesDetail, ...$execParamsDetail);
                            $stmtDetail->execute();
                            $resultDetail = $stmtDetail->get_result();

                            $detailData = [];
                            while ($rowDetail = $resultDetail->fetch_assoc()) {
                                $detailData[] = $rowDetail;
                            }

                            // Group by siswa DAN MENGHITUNG TOTAL (Logic ini TIDAK BERUBAH)
                            $siswaGroups = [];
                            foreach ($detailData as $row) {
                                $nama = $row['nama'];
                                if (!isset($siswaGroups[$nama])) {
                                    $siswaGroups[$nama] = [
                                        'nis' => $row['nis'],
                                        'kelas' => $row['kelas'],
                                        'jurusan' => $row['jurusan'],
                                        'no_hp' => $row['no_hp'],
                                        'jabatan_osis' => $row['jabatan_osis'],
                                        'status_osis' => $row['status_osis'],
                                        'idsiswa' => $row['idsiswa'],
                                        'records' => [],
                                        'total_hadir' => 0,
                                        'total_izin' => 0,
                                        'total_sakit' => 0,
                                        'total_alpha' => 0
                                    ];
                                }
                                // Tidak perlu menyimpan records, hanya perlu status untuk menghitung total
                                // Kita tetap iterasi semua data untuk menghitung total
                                switch ($row['status']) {
                                    case 'Hadir':
                                        $siswaGroups[$nama]['total_hadir']++;
                                        break;
                                    case 'Izin':
                                        $siswaGroups[$nama]['total_izin']++;
                                        break;
                                    case 'Sakit':
                                        $siswaGroups[$nama]['total_sakit']++;
                                        break;
                                    case 'Alpha':
                                        $siswaGroups[$nama]['total_alpha']++;
                                        break;
                                }
                            }
                            ?>



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
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="text-light mb-0">Rekap Bulanan (<?= date('F Y', strtotime($tanggalAcuan)) ?>)</h6>
                                            <div>
                                                <form method="GET" class="d-flex gap-2 align-items-center">
                                                    <input type="hidden" name="jurusan" value="<?= htmlspecialchars($jurusanFilter) ?>">
                                                    <input type="hidden" name="jadwal_id" value="<?= htmlspecialchars($jadwalFilter) ?>">

                                                    <label class="form-label text-light mb-0 me-2">Pilih Bulan:</label>

                                                    <select
                                                        name="bulan"
                                                        class="form-select form-select-sm bg-dark text-light border-secondary"
                                                        style="width: 180px;"
                                                        onchange="this.form.submit()">
                                                        <?php
                                                        // Generate bulan dropdown untuk 2 tahun ke belakang hingga 2 tahun ke depan
                                                        $currentYear = date('Y');
                                                        $currentMonth = date('m');
                                                        $selectedMonth = date('Y-m', strtotime($tanggalAcuan));

                                                        for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
                                                            for ($m = 1; $m <= 12; $m++) {
                                                                $monthValue = sprintf('%04d-%02d', $y, $m);
                                                                $monthLabel = date('F Y', strtotime("$y-$m-01"));
                                                                $isSelected = ($monthValue === $selectedMonth) ? 'selected' : '';
                                                        ?>
                                                                <option value="<?= $monthValue ?>" <?= $isSelected ?>><?= $monthLabel ?></option>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </form>

                                            </div>
                                        </div>
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
                                                        <?php
                                                          $hadir  = isset($monthlyData['Hadir']) ? count($monthlyData['Hadir']) : 0;
                                                          $izin   = isset($monthlyData['Izin'])  ? count($monthlyData['Izin'])  : 0;
                                                          $sakit  = isset($monthlyData['Sakit']) ? count($monthlyData['Sakit']) : 0;
                                                          $alpha  = isset($monthlyData['Alpha']) ? count($monthlyData['Alpha']) : 0;
                                                          $total  = $hadir + $izin + $sakit + $alpha; // gunakan penjumlahan eksplisit agar konsisten
                                                          $tidakhadir = $alpha;
                                                          $persentaseHadir = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;
                                                        ?>
                                                        <div class="row text-center g-3">
                                                          <div class="col-6 col-md-3">
                                                            <div class="fs-1 text-success fw-bold"><?= $persentaseHadir ?>%</div>
                                                            <div class="text-muted">Tingkat Kehadiran</div>
                                                          </div>
                                                          <div class="col-6 col-md-2">
                                                            <div class="fs-2 text-success fw-bold"><?= $hadir ?></div>
                                                            <div class="text-muted">Hadir</div>
                                                          </div>
                                                          <div class="col-6 col-md-2">
                                                            <div class="fs-2 text-info fw-bold"><?= $izin ?></div>
                                                            <div class="text-muted">Izin</div>
                                                          </div>
                                                          <div class="col-6 col-md-2">
                                                            <div class="fs-2 text-warning fw-bold"><?= $sakit ?></div>
                                                            <div class="text-muted">Sakit</div>
                                                          </div>
                                                          <div class="col-6 col-md-3">
                                                            <div class="fs-2 text-danger fw-bold"><?= $tidakhadir ?></div>
                                                            <div class="text-muted">alpha</div>
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
                        <div class="col-12">
                            <div class="card rounded-3xl">
                                <div class="card-body">
                                    <h6 class="mb-3 text-light">Ringkasan Absensi Anggota OSIS (<?= date('F Y', strtotime($tanggalAcuan)) ?>)</h6>
                                    <div class="table-responsive">
                                        <table class="table table-dark table-hover">

                                            <thead>
                                                <tr>
                                                    <th>Nama</th>
                                                    <th>NIS</th>
                                                    <th>Kelas</th>
                                                    <th>Jurusan</th>
                                                    <th>No. HP</th>
                                                    <th>Jabatan OSIS</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Display grouped data
                                                if (empty($siswaGroups)): ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted py-4">Tidak ada data absensi untuk periode ini</td>
                                                    </tr>
                                                    <?php else:
                                                    foreach ($siswaGroups as $nama => $siswaData):

                                                        // Status Keanggotaan untuk penanda warna
                                                        $statusOsis = $siswaData['status_osis'];
                                                        $statusColor = ($statusOsis === 'Aktif') ? 'text-success fw-bold' : 'text-danger fw-bold';
                                                    ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($nama) ?></td>
                                                            <td><?= htmlspecialchars($siswaData['nis']) ?></td>
                                                            <td><?= htmlspecialchars($siswaData['kelas'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($siswaData['jurusan']) ?></td>
                                                            <td><?= htmlspecialchars($siswaData['no_hp']) ?></td>
                                                            <td><?= htmlspecialchars($siswaData['jabatan_osis']) ?></td>
                                                            <td><span class="<?= $statusColor ?>"><?= $statusOsis ?></span></td>
                                                        </tr>

                                                        <tr style="background-color: #2d3748; color: #fff;">
                                                            <td colspan="7">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <strong><?= htmlspecialchars($nama) ?> - TOTAL ABSENSI</strong>
                                                                    <div>
                                                                        <span class="badge bg-success me-2">Hadir: <?= $siswaData['total_hadir'] ?></span>
                                                                        <span class="badge bg-info me-2">Izin: <?= $siswaData['total_izin'] ?></span>
                                                                        <span class="badge bg-warning me-2">Sakit: <?= $siswaData['total_sakit'] ?></span>
                                                                        <span class="badge bg-danger me-2">Alpha: <?= $siswaData['total_alpha'] ?></span>
                                                                        <span class="badge bg-light text-dark">Total: <?= $siswaData['total_hadir'] + $siswaData['total_izin'] + $siswaData['total_sakit'] + $siswaData['total_alpha'] ?></span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                <?php endforeach;
                                                endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const reportContent = document.querySelector('main.col-md-9');
            if (!reportContent) return;

            // 🔹 Tombol Print (tetap pakai print dialog)
            document.getElementById('btnPrint').addEventListener('click', () => {
                // Buat area print khusus (paksa teks hitam)
                const printArea = document.createElement('div');
                printArea.innerHTML = `
        <h1 style=\"text-align:center; margin-bottom:20px; color:#000;\">Laporan Absensi OSIS</h1>
        <p style=\"text-align:center; margin-bottom:20px; color:#000;\">
          Tanggal: ${new Date().toLocaleDateString('id-ID')}
        </p>
        <div style=\"color:#000;\">${reportContent.innerHTML}</div>
      `;

                const originalBody = document.body.innerHTML;
                document.body.innerHTML = printArea.innerHTML;
                window.print();
                document.body.innerHTML = originalBody;
            });

            // 🔹 Tombol Export PDF (langsung download tanpa pindah halaman)
            document.getElementById('btnExport').addEventListener('click', () => {
                const element = document.createElement('div');
                element.innerHTML = `
        <h1 style=\"text-align:center; margin-bottom:20px; color:#000;\">Laporan Absensi OSIS</h1>
        <p style=\"text-align:center; margin-bottom:20px; color:#000;\">
          Tanggal: ${new Date().toLocaleDateString('id-ID')}
        </p>
        <div style=\"color:#000;\">${reportContent.innerHTML}</div>
      `;

                const opt = {
                    margin: 0.5,
                    filename: `Laporan_Absensi_OSIS_${new Date().toLocaleDateString('id-ID')}.pdf`,
                    image: {
                        type: 'jpeg',
                        quality: 0.98
                    },
                    html2canvas: {
                        scale: 2,
                        useCORS: true,
                        backgroundColor: '#ffffff'
                    },
                    jsPDF: {
                        unit: 'in',
                        format: 'a4',
                        orientation: 'portrait'
                    }
                };

                html2pdf().set(opt).from(element).save();
            });
        });
    </script>


</body>

</html>