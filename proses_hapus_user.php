<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';

// Hanya superadmin yang bisa akses
if (!isSuperAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: users.php?error=ID tidak valid');
    exit;
}

$id = (int)$_GET['id'];

// Cek apakah user yang akan dihapus adalah diri sendiri
if ($id == $_SESSION['user_id']) {
    header('Location: users.php?error=Tidak dapat menghapus akun sendiri');
    exit;
}

// Cek apakah user ada
$check_query = "SELECT username FROM users WHERE id = $id LIMIT 1";
$check_result = mysqli_query($koneksi, $check_query);

if (mysqli_num_rows($check_result) === 0) {
    header('Location: users.php?error=User tidak ditemukan');
    exit;
}

// Hapus user
$delete_query = "DELETE FROM users WHERE id = $id";

if (mysqli_query($koneksi, $delete_query)) {
    header('Location: users.php?success=User berhasil dihapus');
} else {
    header('Location: users.php?error=Gagal menghapus user: ' . mysqli_error($koneksi));
}

mysqli_close($koneksi);
exit;
?>