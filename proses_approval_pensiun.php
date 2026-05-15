<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

// Hanya Kepala Dinas yang bisa akses
if (!isKepalaDinas()) {
    alertGagal('dashboard.php', 'Akses ditolak! Hanya Kepala Dinas yang dapat melakukan approval.');
    exit;
}

// Validasi method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alertGagal('approval_pensiun.php', 'Method tidak valid!');
    exit;
}

$id         = (int)($_POST['id'] ?? 0);
$action     = $_POST['action'] ?? '';
$keterangan = mysqli_real_escape_string($koneksi, trim($_POST['keterangan'] ?? ''));

// Validasi id
if ($id <= 0) {
    alertGagal('approval_pensiun.php', 'ID usulan tidak valid!');
    exit;
}

// Validasi action
if (!in_array($action, ['approve', 'reject'])) {
    alertGagal('approval_pensiun.php', 'Aksi tidak dikenali!');
    exit;
}

// Validasi alasan wajib jika ditolak
if ($action === 'reject' && empty($keterangan)) {
    alertWarning('approval_pensiun.php', 'Alasan penolakan wajib diisi!');
    exit;
}

// Cek usulan ada & statusnya masih 'diajukan'
$cek = $koneksi->prepare("SELECT id, nama, nip, status FROM usulan_pensiun WHERE id = ? LIMIT 1");
$cek->bind_param("i", $id);
$cek->execute();
$cek_result = $cek->get_result();

if ($cek_result->num_rows === 0) {
    $cek->close();
    alertGagal('approval_pensiun.php', 'Usulan tidak ditemukan!');
    exit;
}

$usulan = $cek_result->fetch_assoc();
$cek->close();

if ($usulan['status'] !== 'diajukan') {
    alertWarning('approval_pensiun.php', 'Usulan ini sudah diproses sebelumnya (status: ' . $usulan['status'] . ').');
    exit;
}

// Tentukan status baru
$status_baru = ($action === 'approve') ? 'disetujui' : 'ditolak';

// Update status + keterangan + waktu diproses
$stmt = $koneksi->prepare(
    "UPDATE usulan_pensiun 
     SET status = ?, keterangan = ?, updated_at = NOW() 
     WHERE id = ?"
);
$stmt->bind_param("ssi", $status_baru, $keterangan, $id);

if ($stmt->execute()) {
    $stmt->close();
    $koneksi->close();

    $nama = $usulan['nama'];

    if ($action === 'approve') {
        alertSuksesTambah('approval_pensiun.php', "Usulan pensiun atas nama $nama berhasil disetujui.");
    } else {
        alertSuksesTambah('approval_pensiun.php', "Usulan pensiun atas nama $nama telah ditolak.");
    }
} else {
    $error = $stmt->error;
    $stmt->close();
    $koneksi->close();
    alertGagal('approval_pensiun.php', 'Gagal memperbarui status: ' . $error);
}
?>
