<?php
require_once __DIR__ . '/../database/connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/admin/kelola-jadwal.php?error=Akses ditolak');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $judul = trim($_POST['judul']);
    $tanggal = $_POST['tanggal'];
    $waktu = !empty($_POST['waktu']) ? $_POST['waktu'] : null;
    $lokasi = trim($_POST['lokasi']);
    $deskripsi = !empty($_POST['deskripsi']) ? trim($_POST['deskripsi']) : null;

    if (!$judul || !$tanggal || !$lokasi) {
        header('Location: ../pages/admin/kelola-jadwal.php?error=Field wajib belum diisi');
        exit;
    }

    $stmt = $conn->prepare('INSERT INTO jadwal_kegiatan (judul, tanggal, waktu, lokasi, deskripsi) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $judul, $tanggal, $waktu, $lokasi, $deskripsi);
    if ($stmt->execute()) {
        header('Location: ../pages/admin/kelola-jadwal.php?success=Jadwal berhasil ditambahkan');
    } else {
        header('Location: ../pages/admin/kelola-jadwal.php?error=Gagal menambahkan jadwal');
    }
    $stmt->close();
    exit;
}

if ($action === 'update') {
    $id = intval($_POST['id']);
    $judul = trim($_POST['judul']);
    $tanggal = $_POST['tanggal'];
    $waktu = !empty($_POST['waktu']) ? $_POST['waktu'] : null;
    $lokasi = trim($_POST['lokasi']);
    $deskripsi = !empty($_POST['deskripsi']) ? trim($_POST['deskripsi']) : null;

    $stmt = $conn->prepare('UPDATE jadwal_kegiatan SET judul=?, tanggal=?, waktu=?, lokasi=?, deskripsi=?, updated_at=CURRENT_TIMESTAMP WHERE id=?');
    $stmt->bind_param('sssssi', $judul, $tanggal, $waktu, $lokasi, $deskripsi, $id);
    if ($stmt->execute()) {
        header('Location: ../pages/admin/kelola-jadwal.php?success=Jadwal berhasil diupdate');
    } else {
        header('Location: ../pages/admin/kelola-jadwal.php?error=Gagal mengupdate jadwal');
    }
    $stmt->close();
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare('DELETE FROM jadwal_kegiatan WHERE id=?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        header('Location: ../pages/admin/kelola-jadwal.php?success=Jadwal berhasil dihapus');
    } else {
        header('Location: ../pages/admin/kelola-jadwal.php?error=Gagal menghapus jadwal');
    }
    $stmt->close();
    exit;
}

header('Location: ../pages/admin/kelola-jadwal.php');
exit;
