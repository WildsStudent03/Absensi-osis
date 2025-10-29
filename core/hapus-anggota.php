<?php
require_once '../database/connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/admin/kelola-anggota.php?error=Akses ditolak!');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../pages/admin/kelola-anggota.php?error=ID tidak valid');
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare('DELETE FROM datasiswa WHERE idsiswa = ?');
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    header('Location: ../pages/admin/kelola-anggota.php?success=Data anggota berhasil dihapus');
} else {
    header('Location: ../pages/admin/kelola-anggota.php?error=Gagal menghapus data anggota');
}
$stmt->close();
$conn->close();
