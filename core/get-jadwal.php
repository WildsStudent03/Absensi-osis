<?php
require_once __DIR__ . '/../database/connect.php';
// Pastikan tidak ada karakter atau spasi di atas baris ini
header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    echo json_encode(['error' => 'ID tidak valid']);
    exit;
}

// QUERY FINAL: Hanya mengambil kolom yang ada
$stmt = $conn->prepare('SELECT id, judul, tanggal, lokasi FROM jadwal_kegiatan WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo json_encode($row);
} else {
    // Jika tidak ditemukan, tetap kembalikan JSON
    echo json_encode(['error' => 'Jadwal tidak ditemukan']);
}
$stmt->close();
$conn->close();
?>