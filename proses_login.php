<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/koneksi.php"; // sesuaikan path jika berbeda

// Pastikan hanya diproses saat method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// Ambil input dengan pengecekan
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validasi cepat
if ($username === '' || $password === '') {
    echo "<script>alert('Username dan password harus diisi.'); window.location='index.php';</script>";
    exit;
}

// Prepared statement aman
$sql = "SELECT id, username, password, role FROM users WHERE username = ?";
if (!$stmt = $conn->prepare($sql)) {
    // jika prepare gagal, tampilkan error koneksi (debug)
    echo "Query prepare failed: " . htmlspecialchars($conn->error);
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Pastikan $user['password'] ada sebelum dipakai
    $hash = isset($user['password']) ? $user['password'] : '';

    if ($hash !== '' && password_verify($password, $hash)) {
        // Login sukses
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect sesuai role
        if ($user['role'] === 'Admin') {
            header("Location: dashboard_admin.php");
        } elseif ($user['role'] === 'Pelaksana') {
            header("Location: dashboard_pelaksana.php");
        } elseif ($user['role'] === 'Kadis') {
            header("Location: dashboard_kadis.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        // Debug terkontrol — hanya tampil saat gagal verify
        echo "<pre>";
        echo "Password salah!\n";
        echo "Input password   : " . htmlspecialchars($password) . "\n";
        echo "Hash dari DB     : " . htmlspecialchars($hash) . "\n\n";

        // Tes apakah hash cocok dengan beberapa kata sandi umum (opsional)
        $tests = ['12345', 'admin', 'admin123', 'password'];
        foreach ($tests as $t) {
            if ($hash !== '' && password_verify($t, $hash)) {
                echo "➡ Hash ini cocok dengan: " . htmlspecialchars($t) . "\n";
            }
        }

        echo "\n(Tutup pesan debug ini setelah selesai.)";
        echo "</pre>";
        exit;
    }
} else {
    echo "<script>alert('User tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}
?>
