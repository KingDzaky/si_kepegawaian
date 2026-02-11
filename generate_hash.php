<?php
require_once 'config/koneksi.php';

// Password yang mau di-hash
$password = 'admin123';

// Generate hash baru
$new_hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Hash Password Baru</h2>";
echo "Password: <strong>$password</strong><br>";
echo "Hash: <strong>$new_hash</strong><br><br>";

// Test apakah hash cocok
if (password_verify($password, $new_hash)) {
    echo "<span style='color: green;'>✅ Hash VALID - password_verify() berhasil!</span><br><br>";
} else {
    echo "<span style='color: red;'>❌ Hash TIDAK VALID</span><br><br>";
}

// Update langsung ke database
$users = ['superadmin', 'admin', 'kepaladinas'];

echo "<h3>Update Database:</h3>";
foreach ($users as $username) {
    $query = "UPDATE users SET password = ? WHERE username = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ss", $new_hash, $username);
    
    if ($stmt->execute()) {
        echo "✅ Password untuk <strong>$username</strong> berhasil di-update!<br>";
    } else {
        echo "❌ Gagal update password untuk <strong>$username</strong><br>";
    }
    $stmt->close();
}

echo "<br><br><a href='login.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Kembali ke Login</a>";
?>