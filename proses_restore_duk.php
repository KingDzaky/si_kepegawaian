<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/soft_delete_functions.php';

if (!isAdmin()) { header('Location: dashboard.php?error=Akses ditolak'); exit; }

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    $result = restoreDeleted($koneksi, 'duk', $id);
    
    if ($result['success']) {
        header("Location: recycle_bin.php?success=" . urlencode($result['message']));
    } else {
        header("Location: recycle_bin.php?error=" . urlencode($result['message']));
    }
} else {
    header("Location: recycle_bin.php?error=ID tidak valid");
}
?>