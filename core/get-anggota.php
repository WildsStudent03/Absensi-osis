<?php
// Koneksi database
require_once '../../database/connect.php';

// Query untuk mengambil semua data siswa
$query = "SELECT * FROM datasiswa ORDER BY nama ASC";
$result = $conn->query($query);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Return data dalam format JSON
header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
