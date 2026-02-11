<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';

// Hanya superadmin yang bisa akses
if (!isSuperAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: form_tambah_user.php');
    exit;
}

// Ambil data dari form
$username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$nama_lengkap = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
$role = mysqli_real_escape_string($koneksi, $_POST['role']);
$is_active = (int)$_POST['is_active'];

// Validasi
if (empty($username) || empty($password) || empty($nama_lengkap) || empty($role)) {
    header('Location: form_tambah_user.php?error=Semua field wajib diisi');
    exit;
}

if (strlen($password) < 6) {
    header('Location: form_tambah_user.php?error=Password minimal 6 karakter');
    exit;
}

if ($password !== $confirm_password) {
    header('Location: form_tambah_user.php?error=Password tidak sama');
    exit;
}

// Cek username sudah ada atau belum
$check_query = "SELECT id FROM users WHERE username = '$username'";
$check_result = mysqli_query($koneksi, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    header('Location: form_tambah_user.php?error=Username sudah digunakan');
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert data (password plaintext - sesuai permintaan)
$query = "INSERT INTO users (username, password, nama_lengkap, role, is_active, created_at) 
          VALUES ('$username', '$hashedPassword', '$nama_lengkap', '$role', $is_active, NOW())";

if (mysqli_query($koneksi, $query)) {
    header('Location: users.php?success=User berhasil ditambahkan');
} else {
    header('Location: form_tambah_user.php?error=Gagal menambahkan user: ' . mysqli_error($koneksi));
}

mysqli_close($koneksi);
exit;
?>