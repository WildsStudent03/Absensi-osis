<?php
include __DIR__ . '/../database/connect.php';

if (isset($_POST['id'], $_POST['status'])) {
    $id = (int) $_POST['id'];
    $status = trim($_POST['status']);

    $allowed = ['Hadir', 'Izin', 'Sakit', 'Alpha'];
    if (!in_array($status, $allowed)) {
        exit('invalid');
    }

    // accept optional keterangan (note) from client
    $keterangan = isset($_POST['keterangan']) ? trim($_POST['keterangan']) : null;


    if (in_array($status, ['Izin', 'Sakit'])) {
        $q = $conn->prepare("UPDATE absensi SET status = ?, keterangan = ? WHERE id = ?");
        $q->bind_param('ssi', $status, $keterangan, $id);
    } else {
        // clear keterangan for Hadir/Alpha
        $q = $conn->prepare("UPDATE absensi SET status = ?, keterangan = NULL WHERE id = ?");
        $q->bind_param('si', $status, $id);
    }

    echo $q->execute() ? 'ok' : 'error';
}
