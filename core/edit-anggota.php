<?php
require_once '../database/connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/admin/kelola-anggota.php?error=Akses ditolak!');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: ../pages/admin/kelola-anggota.php?error=Permintaan tidak valid');
    exit;
}


$id = intval($_POST['id']);
$nis = trim($_POST['nis']);
$nama = trim($_POST['nama']);
$kelas = trim($_POST['kelas']);
$jurusan = trim($_POST['jurusan']);
$no_hp = isset($_POST['no_hp']) ? trim($_POST['no_hp']) : null;
$jabatan_osis = isset($_POST['jabatan_osis']) ? trim($_POST['jabatan_osis']) : null;
$status_osis = isset($_POST['status_osis']) ? trim($_POST['status_osis']) : 'Aktif';

$stmt = $conn->prepare('UPDATE datasiswa SET nis=?, nama=?, kelas=?, jurusan=?, no_hp=?, jabatan_osis=?, status_osis=? WHERE idsiswa=?');
$stmt->bind_param('sssssssi', $nis, $nama, $kelas, $jurusan, $no_hp, $jabatan_osis, $status_osis, $id);

if ($stmt->execute()) {
    header('Location: ../pages/admin/kelola-anggota.php?success=Data anggota berhasil diupdate');
} else {
    header('Location: ../pages/admin/kelola-anggota.php?error=Gagal update data anggota');
}
$stmt->close();
$conn->close();
