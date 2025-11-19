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
  // Kolom 'waktu' dan 'deskripsi' dihapus
  $lokasi = trim($_POST['lokasi']);
    
  if (!$judul || !$tanggal || !$lokasi) {
    header('Location: ../pages/admin/kelola-jadwal.php?error=Field wajib belum diisi');
    exit;
  }

    // Query diubah: Hanya menyisakan judul, tanggal, lokasi
  $stmt = $conn->prepare('INSERT INTO jadwal_kegiatan (judul, tanggal, lokasi) VALUES (?, ?, ?)');
    // bind_param diubah: Hanya menyisakan 3 string
  $stmt->bind_param('sss', $judul, $tanggal, $lokasi);
    
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
  // Kolom 'waktu' dan 'deskripsi' dihapus
  $lokasi = trim($_POST['lokasi']);
    
    // Query diubah: Menghilangkan 'waktu' dan 'deskripsi'
  $stmt = $conn->prepare('UPDATE jadwal_kegiatan SET judul=?, tanggal=?, lokasi=?, updated_at=CURRENT_TIMESTAMP WHERE id=?');
    // bind_param diubah: Hanya menyisakan 3 string + 1 integer
  $stmt->bind_param('sssi', $judul, $tanggal, $lokasi, $id);
    
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