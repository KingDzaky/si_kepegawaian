<?php
include "config/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role     = trim($_POST['role']);

    if ($username === "" || $password === "" || $role === "") {
        echo "<script>alert('Semua field harus diisi!'); window.history.back();</script>";
        exit;
    }

    // Hash password sebelum simpan
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Simpan ke database
    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Gagal registrasi: " . $conn->error . "'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Register User</title>
</head>
<body>
  <h2>Form Register User</h2>
  <form method="post" action="">
    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Role:</label><br>
    <select name="role" required>
      <option value="">-- Pilih Role --</option>
      <option value="Admin">Admin</option>
      <option value="Pelaksana">Pelaksana</option>
      <option value="Kadis">Kadis</option>
    </select><br><br>

    <button type="submit">Register</button>
  </form>
</body>
</html>
