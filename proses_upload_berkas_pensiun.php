<?php
// proses_upload_berkas_pensiun.php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';
require_once 'includes/helper_berkas.php';

if (!isAdmin()) {
    alertWarning('usulan_pensiun.php', 'Akses ditolak');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alertWarning('usulan_pensiun.php', 'Method tidak valid');
    exit;
}

$id_usulan_pensiun = (int)($_POST['id_usulan_pensiun'] ?? 0);

if (!$id_usulan_pensiun) {
    alertWarning('usulan_pensiun.php', 'Data tidak valid');
    exit;
}

// Ambil data usulan (nama & nip ditambahkan untuk nama folder per pegawai)
$stmt = $koneksi->prepare("
    SELECT nama, nip, jenis_pensiun, tanggal_pensiun, status, sumber_data
    FROM usulan_pensiun
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $id_usulan_pensiun);
$stmt->execute();
$up = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$up) {
    alertWarning('usulan_pensiun.php', 'Data usulan tidak ditemukan');
    exit;
}

if ($up['status'] !== 'disetujui') {
    alertWarning('usulan_pensiun.php', 'Berkas hanya bisa diupload untuk usulan yang sudah disetujui');
    exit;
}

// Jenis berkas wajib BEDA tergantung sumber_data
// DUK = 2 berkas (Pengantar + Pernyataan), Penyuluh = 1 berkas (Pengantar PKB saja)
if ($up['sumber_data'] === 'penyuluh') {
    $jenis_wajib = ['Surat Pengantar PKB'];
} else {
    $jenis_wajib = ['Surat Pengantar', 'Surat Pernyataan'];
}

$rootPath = __DIR__;
// TAMBAHAN: folder per nama pegawai, sama seperti kenaikan pangkat
// -> uploads/sk_pensiun/NamaPegawai/Surat_Pengantar.pdf
$folderRelatif = 'uploads/sk_pensiun/' . folderPegawaiBerkas($up['nama'], $up['nip']);

$berhasil_upload = [];
$gagal_upload     = [];

/**
 * Simpan satu berkas (replace otomatis kalau jenis sama sudah ada,
 * karena nama file di dalam folder pegawai itu tetap/fixed per jenis).
 */
function simpanBerkasPensiun($koneksi, $id_usulan_pensiun, $jenis_berkas, $file, $folderRelatif, $rootPath, &$berhasil_upload, &$gagal_upload) {

    // Field dikosongkan / tidak diisi — bukan error, dilewati saja
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return;
    }

    $validasi = validasiFileBerkas($file, ['pdf', 'jpg', 'jpeg', 'png'], 5);
    if (!$validasi['ok']) {
        $gagal_upload[] = "$jenis_berkas ({$validasi['error']})";
        return;
    }

    $namaFileTanpaExt = slugBerkas($jenis_berkas);
    $path_relatif = simpanFileBerkas($file, $folderRelatif, $namaFileTanpaExt, $validasi['ext'], $rootPath);

    if ($path_relatif === false) {
        $gagal_upload[] = "$jenis_berkas (gagal menyimpan file ke server, cek permission folder uploads/)";
        return;
    }

    // Cek apakah jenis berkas ini sudah ada untuk usulan ini -> UPDATE, kalau belum -> INSERT
    $stmt_cek = $koneksi->prepare("SELECT id FROM berkas_pensiun WHERE id_usulan_pensiun = ? AND jenis_berkas = ? LIMIT 1");
    $stmt_cek->bind_param("is", $id_usulan_pensiun, $jenis_berkas);
    $stmt_cek->execute();
    $existing = $stmt_cek->get_result()->fetch_assoc();
    $stmt_cek->close();

    if ($existing) {
        $stmt_upd = $koneksi->prepare("UPDATE berkas_pensiun SET nama_file = ?, file_path = ?, tanggal_upload = NOW() WHERE id = ?");
        $stmt_upd->bind_param("ssi", $file['name'], $path_relatif, $existing['id']);
        $stmt_upd->execute();
        $stmt_upd->close();
    } else {
        $stmt_ins = $koneksi->prepare("INSERT INTO berkas_pensiun (id_usulan_pensiun, jenis_berkas, nama_file, file_path, tanggal_upload) VALUES (?, ?, ?, ?, NOW())");
        $stmt_ins->bind_param("isss", $id_usulan_pensiun, $jenis_berkas, $file['name'], $path_relatif);
        $stmt_ins->execute();
        $stmt_ins->close();
    }

    $berhasil_upload[] = $jenis_berkas;
}

// ============================================
// 1. PROSES BERKAS WAJIB — field name="berkas[Jenis Berkas]"
// ============================================
if (isset($_FILES['berkas']) && is_array($_FILES['berkas']['name'])) {
    foreach ($jenis_wajib as $jenis) {
        if (!isset($_FILES['berkas']['error'][$jenis])) continue;

        $file_tunggal = [
            'name'     => $_FILES['berkas']['name'][$jenis],
            'type'     => $_FILES['berkas']['type'][$jenis],
            'tmp_name' => $_FILES['berkas']['tmp_name'][$jenis],
            'error'    => $_FILES['berkas']['error'][$jenis],
            'size'     => $_FILES['berkas']['size'][$jenis],
        ];

        simpanBerkasPensiun($koneksi, $id_usulan_pensiun, $jenis, $file_tunggal, $folderRelatif, $rootPath, $berhasil_upload, $gagal_upload);
    }
}

// ============================================
// 2. PROSES DOKUMEN TAMBAHAN — name="tambahan_jenis[]" & name="tambahan_file[]"
// ============================================
if (isset($_POST['tambahan_jenis']) && isset($_FILES['tambahan_file'])) {
    $jumlah_tambahan = count($_POST['tambahan_jenis']);

    for ($i = 0; $i < $jumlah_tambahan; $i++) {
        $jenis_tambahan = trim($_POST['tambahan_jenis'][$i] ?? '');
        if (empty($jenis_tambahan)) continue;
        if (!isset($_FILES['tambahan_file']['error'][$i])) continue;

        $file_tunggal = [
            'name'     => $_FILES['tambahan_file']['name'][$i],
            'type'     => $_FILES['tambahan_file']['type'][$i],
            'tmp_name' => $_FILES['tambahan_file']['tmp_name'][$i],
            'error'    => $_FILES['tambahan_file']['error'][$i],
            'size'     => $_FILES['tambahan_file']['size'][$i],
        ];

        simpanBerkasPensiun($koneksi, $id_usulan_pensiun, $jenis_tambahan, $file_tunggal, $folderRelatif, $rootPath, $berhasil_upload, $gagal_upload);
    }
}

if (empty($berhasil_upload) && empty($gagal_upload)) {
    alertWarning('usulan_pensiun.php', 'Tidak ada file yang dipilih untuk diupload');
    exit;
}

// ============================================
// CEK KELENGKAPAN — apakah semua jenis wajib sudah ada
// ============================================
$stmt_cek_lengkap = $koneksi->prepare("
    SELECT jenis_berkas FROM berkas_pensiun WHERE id_usulan_pensiun = ?
");
$stmt_cek_lengkap->bind_param("i", $id_usulan_pensiun);
$stmt_cek_lengkap->execute();
$result_berkas = $stmt_cek_lengkap->get_result();

$jenis_terupload = [];
while ($row = $result_berkas->fetch_assoc()) {
    $jenis_terupload[] = $row['jenis_berkas'];
}
$stmt_cek_lengkap->close();

$lengkap = count(array_intersect($jenis_wajib, $jenis_terupload)) === count($jenis_wajib);

if ($lengkap) {
    // Update status_berkas di usulan_pensiun
    $stmt_status = $koneksi->prepare("
        UPDATE usulan_pensiun SET status_berkas = 'sudah' WHERE id = ?
    ");
    $stmt_status->bind_param("i", $id_usulan_pensiun);
    $stmt_status->execute();
    $stmt_status->close();
}

$koneksi->close();

// Susun pesan hasil
$pesan = '';
if (!empty($berhasil_upload)) {
    $pesan .= 'Berkas berhasil diupload: ' . implode(', ', $berhasil_upload) . '. ';
}
if ($lengkap) {
    $pesan .= 'Semua berkas wajib lengkap, Perbarui status pegawai segera. ';
}
if (!empty($gagal_upload)) {
    $pesan .= 'Gagal: ' . implode(', ', $gagal_upload) . '.';
}

if (!empty($gagal_upload) && empty($berhasil_upload)) {
    alertWarning('usulan_pensiun.php', $pesan);
} else {
    alertSuksesUbah('usulan_pensiun.php', $pesan);
}