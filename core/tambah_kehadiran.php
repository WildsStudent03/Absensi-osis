<?php
include '../database/connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/admin/absensi.php?error=Akses ditolak');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/admin/absensi.php');
    exit;
}

// If mass checklist mode is used: POST contains 'jadwal_id' and 'hadir'[]
if (isset($_POST['jadwal_id']) && isset($_POST['hadir'])) {
    $jadwal_id = intval($_POST['jadwal_id']);
    $hadir = is_array($_POST['hadir']) ? array_map('intval', $_POST['hadir']) : [];
    if (!$jadwal_id) {
        header('Location: ../pages/admin/absensi.php?error=Pilih jadwal terlebih dahulu');
        exit;
    }

    $today = isset($_POST['tanggal']) && $_POST['tanggal'] !== '' ? trim($_POST['tanggal']) : date('Y-m-d');

    // Get all active students
    $students = [];
    $sq = $conn->prepare("SELECT idsiswa FROM datasiswa WHERE status_osis='Aktif'");
    $sq->execute();
    $sr = $sq->get_result();
    while ($row = $sr->fetch_assoc()) {
        $students[] = (int)$row['idsiswa'];
    }
    $sq->close();

    // Prepare statements for existence check, insert and update
    $checkStmt = $conn->prepare('SELECT id FROM absensi WHERE user_id = ? AND jadwal_id = ? AND tanggal = ? LIMIT 1');
    $insStmt = $conn->prepare('INSERT INTO absensi (user_id, jadwal_id, tanggal, status, keterangan, created_at) VALUES (?, ?, ?, ?, NULL, NOW())');
    $updStmt = $conn->prepare('UPDATE absensi SET status = ?, keterangan = NULL WHERE id = ?');

    if (!$checkStmt || !$insStmt || !$updStmt) {
        header('Location: ../pages/admin/absensi.php?error=Gagal menyiapkan query');
        exit;
    }

    foreach ($students as $uid) {
        $status = in_array($uid, $hadir, true) ? 'Hadir' : 'Alpha';

        // check existing
        $checkStmt->bind_param('iis', $uid, $jadwal_id, $today);
        $checkStmt->execute();
        $cres = $checkStmt->get_result();
        if ($cres && $cres->num_rows > 0) {
            $row = $cres->fetch_assoc();
            $aid = $row['id'];
            // update
            $updStmt->bind_param('si', $status, $aid);
            $updStmt->execute();
        } else {
            // insert
            $insStmt->bind_param('iiss', $uid, $jadwal_id, $today, $status);
            $insStmt->execute();
        }
    }

    $checkStmt->close();
    $insStmt->close();
    $updStmt->close();

    header('Location: ../pages/admin/absensi.php?success=' . urlencode('✅ Absensi berhasil disimpan'));
    exit;
}

// Single add fallback (existing behavior)
$id_jadwal = isset($_POST['id_jadwal']) && $_POST['id_jadwal'] !== '' ? intval($_POST['id_jadwal']) : null;
$idsiswa = isset($_POST['idsiswa']) ? intval($_POST['idsiswa']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$tanggal = isset($_POST['tanggal']) ? trim($_POST['tanggal']) : '';
$keterangan = isset($_POST['keterangan']) && $_POST['keterangan'] !== '' ? trim($_POST['keterangan']) : null;

if (!$idsiswa || !$status || !$tanggal) {
    header('Location: ../pages/admin/absensi.php?error=Field wajib belum diisi');
    exit;
}

// normalize status typo 'Alfa' -> 'Alpha'
if (strtolower($status) === 'alfa') $status = 'Alpha';

// Ensure siswa exists
$chk = $conn->prepare('SELECT idsiswa FROM datasiswa WHERE idsiswa = ? LIMIT 1');
$chk->bind_param('i', $idsiswa);
$chk->execute();
$res = $chk->get_result();
if (!$res || $res->num_rows === 0) {
    header('Location: ../pages/admin/absensi.php?error=Siswa tidak ditemukan');
    exit;
}
$chk->close();

// Prevent duplicate for same siswa/tanggal/jadwal (treat null jadwal as NULL)
if ($id_jadwal === null) {
    $dupSql = 'SELECT id FROM absensi WHERE user_id = ? AND tanggal = ? AND jadwal_id IS NULL LIMIT 1';
    $dupStmt = $conn->prepare($dupSql);
    $dupStmt->bind_param('is', $idsiswa, $tanggal);
} else {
    $dupSql = 'SELECT id FROM absensi WHERE user_id = ? AND tanggal = ? AND jadwal_id = ? LIMIT 1';
    $dupStmt = $conn->prepare($dupSql);
    $dupStmt->bind_param('isi', $idsiswa, $tanggal, $id_jadwal);
}
$dupStmt->execute();
$dupRes = $dupStmt->get_result();
if ($dupRes && $dupRes->num_rows > 0) {
    // update existing record instead of inserting duplicate
    $row = $dupRes->fetch_assoc();
    $existingId = $row['id'];
    $update = $conn->prepare('UPDATE absensi SET status = ?, keterangan = ? WHERE id = ?');
    $update->bind_param('ssi', $status, $keterangan, $existingId);
    $update->execute();
    $update->close();
    $dupStmt->close();
    header('Location: ../pages/admin/absensi.php?success=Kehadiran berhasil diperbarui');
    exit;
}
$dupStmt->close();

// Insert
if ($id_jadwal === null) {
    $ins = $conn->prepare('INSERT INTO absensi (user_id, jadwal_id, tanggal, status, keterangan, created_at) VALUES (?, NULL, ?, ?, ?, NOW())');
    $ins->bind_param('isss', $idsiswa, $tanggal, $status, $keterangan);
} else {
    $ins = $conn->prepare('INSERT INTO absensi (user_id, jadwal_id, tanggal, status, keterangan, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $ins->bind_param('iisss', $idsiswa, $id_jadwal, $tanggal, $status, $keterangan);
}

if ($ins->execute()) {
    $ins->close();
    header('Location: ../pages/admin/absensi.php?success=Kehadiran berhasil ditambahkan');
    exit;
} else {
    $err = $conn->error;
    $ins->close();
    header('Location: ../pages/admin/absensi.php?error=Gagal menambahkan kehadiran: ' . urlencode($err));
    exit;
}
