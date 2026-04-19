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
    header('Location: users.php');
    exit;
}

// Ambil data dari form
$id = (int)$_POST['id'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$nama_lengkap = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
$role = mysqli_real_escape_string($koneksi, $_POST['role']);
$is_active = (int)$_POST['is_active'];

// Validasi
if (empty($nama_lengkap) || empty($role)) {
    header('Location: form_edit_user.php?id=' . $id . '&error=Semua field wajib diisi');
    exit;
}

// Jika password diisi, validasi password
if (!empty($password)) {
    if (strlen($password) < 6) {
        header('Location: form_edit_user.php?id=' . $id . '&error=Password minimal 6 karakter');
        exit;
    }
    
    if ($password !== $confirm_password) {
        header('Location: form_edit_user.php?id=' . $id . '&error=Password tidak sama');
        exit;
    }
    
    // Update dengan password baru (TANPA updated_at)
   // ✅ SESUDAH - di-hash dulu
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$query = "UPDATE users SET 
          password = '$hashed_password',
          nama_lengkap = '$nama_lengkap',
          role = '$role',
          is_active = $is_active
          WHERE id = $id";
} else {
    // Update tanpa mengubah password (TANPA updated_at)
    $query = "UPDATE users SET 
              nama_lengkap = '$nama_lengkap',
              role = '$role',
              is_active = $is_active
              WHERE id = $id";
}

if (mysqli_query($koneksi, $query)) {
    header('Location: users.php?success=User berhasil diupdate');
} else {
    header('Location: form_edit_user.php?id=' . $id . '&error=Gagal update user: ' . mysqli_error($koneksi));
}

mysqli_close($koneksi);
exit;
?>