<?php
require_once 'config/koneksi.php';

// Password yang akan di-hash
$password = 'admin123';
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Update untuk semua user
$users = ['superadmin', 'admin', 'kepaladinas'];

foreach ($users as $username) {
    $query = "UPDATE users SET password = ? WHERE username = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ss", $hashed, $username);
    $stmt->execute();
    echo "Password untuk $username berhasil di-update!<br>";
}

echo "<br>Password baru: admin123<br>";
echo "Hash: $hashed<br>";
echo "<br><a href='login.php'>Kembali ke Login</a>";
?>