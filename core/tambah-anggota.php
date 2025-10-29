<?php
/* proses tambah anggota -> simpan ke tabel `datasiswa` */
include __DIR__ . '/../database/connect.php';
session_start();

// Pastikan admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/admin/kelola-anggota.php?error=Akses ditolak!');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/admin/kelola-anggota.php');
    exit;
}

// Validasi field wajib untuk datasiswa (nis, nama, kelas, jurusan)
$required = ['nis', 'nama', 'kelas', 'jurusan'];
foreach ($required as $f) {
    if (empty($_POST[$f])) {
        header('Location: ../pages/admin/kelola-anggota.php?error=Field ' . $f . ' wajib diisi');
        exit;
    }
}

$nis = trim($_POST['nis']);
$nama = trim($_POST['nama']);
$kelas = trim($_POST['kelas']);
$jurusan = trim($_POST['jurusan']);
$no_hp = isset($_POST['no_hp']) && $_POST['no_hp'] !== '' ? trim($_POST['no_hp']) : null;
$jabatan_osis = isset($_POST['jabatan_osis']) && $_POST['jabatan_osis'] !== '' ? trim($_POST['jabatan_osis']) : null;
$status_osis = isset($_POST['status_osis']) && $_POST['status_osis'] !== '' ? trim($_POST['status_osis']) : 'Aktif';

// Cek duplikasi NIS
$check = $conn->prepare('SELECT idsiswa FROM datasiswa WHERE nis = ?');
$check->bind_param('s', $nis);
$check->execute();
$res = $check->get_result();
if ($res->num_rows > 0) {
    header('Location: ../pages/admin/kelola-anggota.php?error=NIS sudah terdaftar');
    exit;
}

$insert = $conn->prepare('INSERT INTO datasiswa (nis, nama, kelas, jurusan, no_hp, jabatan_osis, status_osis) VALUES (?, ?, ?, ?, ?, ?, ?)');
$insert->bind_param('sssssss', $nis, $nama, $kelas, $jurusan, $no_hp, $jabatan_osis, $status_osis);

if ($insert->execute()) {
    header('Location: ../pages/admin/kelola-anggota.php?success=Anggota berhasil ditambahkan');
} else {
    header('Location: ../pages/admin/kelola-anggota.php?error=Gagal menambahkan anggota: ' . $conn->error);
}

$insert->close();
$conn->close();
