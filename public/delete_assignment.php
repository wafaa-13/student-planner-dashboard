<?php
require_once '../config/db.php';

// Only allow delete by POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?error=request_method');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    header('Location: dashboard.php?error=invalid_id');
    exit;
}

// Prepared statement keeps query safe
$sql = 'DELETE FROM assignments WHERE id = ?';
$stmt = $conn->prepare($sql);

if (!$stmt) {
    header('Location: dashboard.php?error=delete_failed');
    exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

// Back to dashboard after deleting
header('Location: dashboard.php?message=deleted');
exit;
