<?php
include '../database/connect.php';

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'error';
    exit;
}

// Ambil ID absensi
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo 'error';
    exit;
}

// Hapus absensi
$stmt = $conn->prepare("DELETE FROM absensi WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo 'ok';
} else {
    echo 'error';
}

$stmt->close();
