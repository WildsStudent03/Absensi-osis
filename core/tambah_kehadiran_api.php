<?php
include '../database/connect.php';

header('Content-Type: application/json');

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Ambil data JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['records']) || !isset($input['jadwal_id']) || !isset($input['tanggal'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$jadwalId = intval($input['jadwal_id']);
$tanggal = $input['tanggal'];
$records = $input['records'];

$successCount = 0;
$errorCount = 0;

// Process each record
foreach ($records as $record) {
    $userId = intval($record['user_id']);
    $status = trim($record['status']);
    $keterangan = trim($record['keterangan'] ?? '');

    // Validasi status
    $validStatuses = ['Hadir', 'Izin', 'Sakit', 'Alpha'];
    if (!in_array($status, $validStatuses)) {
        $errorCount++;
        continue;
    }

    // Cek apakah sudah ada record untuk user ini di hari yang sama
    $check = $conn->prepare("SELECT id FROM absensi WHERE user_id = ? AND tanggal = ? AND jadwal_id = ? ORDER BY created_at DESC LIMIT 1");
    $check->bind_param('isi', $userId, $tanggal, $jadwalId);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // UPDATE existing record
        $row = $result->fetch_assoc();
        $id = $row['id'];

        $updateKeterangan = ($status === 'Izin' || $status === 'Sakit') ? $keterangan : NULL;
        $stmt = $conn->prepare("UPDATE absensi SET status = ?, keterangan = ? WHERE id = ?");
        $stmt->bind_param('ssi', $status, $updateKeterangan, $id);

        if ($stmt->execute()) {
            $successCount++;
        } else {
            $errorCount++;
        }
        $stmt->close();
    } else {
        // INSERT new record
        $updateKeterangan = ($status === 'Izin' || $status === 'Sakit') ? $keterangan : NULL;
        $stmt = $conn->prepare("INSERT INTO absensi (user_id, jadwal_id, tanggal, status, keterangan, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('iisss', $userId, $jadwalId, $tanggal, $status, $updateKeterangan);

        if ($stmt->execute()) {
            $successCount++;
        } else {
            $errorCount++;
        }
        $stmt->close();
    }

    $check->close();
}

echo json_encode([
    'success' => $errorCount === 0,
    'message' => "Disimpan: $successCount, Error: $errorCount",
    'successCount' => $successCount,
    'errorCount' => $errorCount
]);
