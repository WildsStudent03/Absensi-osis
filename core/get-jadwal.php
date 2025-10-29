<?php
require_once __DIR__ . '/../database/connect.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    echo json_encode(['error' => 'ID tidak valid']);
    exit;
}

$stmt = $conn->prepare('SELECT id, judul, tanggal, waktu, lokasi, deskripsi FROM jadwal_kegiatan WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Jadwal tidak ditemukan']);
}
$stmt->close();
$conn->close();
