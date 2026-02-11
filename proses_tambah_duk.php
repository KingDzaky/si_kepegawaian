<?php
/**
 * ============================================================================
 * PROSES TAMBAH DATA DUK BARU
 * ============================================================================
 */

session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

// Hanya superadmin dan admin yang bisa akses
if (!isAdmin()) {
    alertGagal('dashboard.php', 'Akses ditolak! Anda tidak memiliki izin.');
    exit;
}

// Validasi method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alertGagal('dataduk.php', 'Method tidak valid!');
    exit;
}

// ==================== AMBIL DATA DARI FORM ====================
$nama = trim($_POST['nama'] ?? '');
$nip = trim($_POST['nip'] ?? '');
$kartu_pegawai = trim($_POST['kartu_pegawai'] ?? '');
$ttl = trim($_POST['ttl'] ?? '');
$jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
$pendidikan_terakhir = trim($_POST['pendidikan_terakhir'] ?? '');
$prodi = trim($_POST['prodi'] ?? '');
$nomor_wa = trim($_POST['nomor_wa'] ?? '');
$pangkat_terakhir = trim($_POST['pangkat_terakhir'] ?? '');
$golongan = trim($_POST['golongan'] ?? '');
$tmt_pangkat = trim($_POST['tmt_pangkat'] ?? '');
$jabatan_terakhir = trim($_POST['jabatan_terakhir'] ?? '');
$eselon = trim($_POST['eselon'] ?? '');
$jenis_jabatan = trim($_POST['jenis_jabatan'] ?? '');
$jft_tingkat = trim($_POST['jft_tingkat'] ?? '');
$jfu_kelas = trim($_POST['jfu_kelas'] ?? '');
$tmt_eselon = trim($_POST['tmt_eselon'] ?? '');

// ✅ Field Kepala OPD (Foreign Key)
$id_opd = !empty($_POST['id_opd']) ? (int)$_POST['id_opd'] : null;

// ==================== VALIDASI DATA WAJIB ====================
$errors = [];

if (empty($nama)) $errors[] = "Nama wajib diisi";
if (strlen($nama) < 3 && !empty($nama)) $errors[] = "Nama minimal 3 karakter";
if (empty($kartu_pegawai)) $errors[] = "Kartu Pegawai wajib diisi";
if (empty($ttl)) $errors[] = "Tempat, Tanggal Lahir wajib diisi";
if (empty($jenis_kelamin)) $errors[] = "Jenis Kelamin wajib diisi";
if (empty($pendidikan_terakhir)) $errors[] = "Pendidikan Terakhir wajib diisi";
if (empty($prodi)) $errors[] = "Program Studi wajib diisi";
if (empty($nomor_wa)) $errors[] = "Nomor WhatsApp wajib diisi";
if (empty($pangkat_terakhir)) $errors[] = "Pangkat Terakhir wajib diisi";
if (empty($golongan)) $errors[] = "Golongan wajib diisi";
if (empty($tmt_pangkat)) $errors[] = "TMT Pangkat wajib diisi";
if (empty($jabatan_terakhir)) $errors[] = "Jabatan Terakhir wajib diisi";
if (empty($eselon)) $errors[] = "Eselon wajib diisi";
if (empty($tmt_eselon)) $errors[] = "TMT Eselon wajib diisi";
if (empty($id_opd)) $errors[] = "Kepala OPD wajib dipilih";

if (!empty($errors)) {
    alertGagal("form_tambah_duk.php", implode(', ', $errors));
    exit;
}

// ==================== VALIDASI NIP (Jika Diisi) ====================
if (!empty($nip)) {
    if (!preg_match('/^[0-9]{18}$/', $nip)) {
        alertGagal("form_tambah_duk.php", 'NIP harus 18 digit angka');
        exit;
    }
    
    // Cek duplikasi NIP
    $check_nip = $koneksi->prepare("SELECT id, nama FROM duk WHERE nip = ?");
    $check_nip->bind_param("s", $nip);
    $check_nip->execute();
    $result_nip = $check_nip->get_result();
    
    if ($result_nip->num_rows > 0) {
        $existing_nip = $result_nip->fetch_assoc();
        $check_nip->close();
        alertGagal(
            "form_tambah_duk.php", 
            "NIP sudah digunakan oleh: {$existing_nip['nama']}"
        );
        exit;
    }
    $check_nip->close();
}

// ==================== VALIDASI & FORMAT NOMOR WHATSAPP ====================
$nomor_wa_clean = preg_replace('/[^0-9]/', '', $nomor_wa);

if (strlen($nomor_wa_clean) < 10 || strlen($nomor_wa_clean) > 15) {
    alertGagal("form_tambah_duk.php", 'Nomor WhatsApp harus 10-15 digit');
    exit;
}

// Auto format: 08xxx → 628xxx
if (substr($nomor_wa_clean, 0, 1) === '0') {
    $nomor_wa_clean = '62' . substr($nomor_wa_clean, 1);
}

// Cek duplikasi Nomor WhatsApp
$check_wa = $koneksi->prepare("SELECT id, nama FROM duk WHERE nomor_wa = ?");
$check_wa->bind_param("s", $nomor_wa_clean);
$check_wa->execute();
$result_wa = $check_wa->get_result();

if ($result_wa->num_rows > 0) {
    $existing_wa = $result_wa->fetch_assoc();
    $check_wa->close();
    alertWarning(
        "form_tambah_duk.php", 
        "Nomor WhatsApp sudah digunakan oleh: {$existing_wa['nama']}"
    );
    exit;
}
$check_wa->close();

// Cek duplikasi Kartu Pegawai
$check_kartu = $koneksi->prepare("SELECT id, nama FROM duk WHERE kartu_pegawai = ?");
$check_kartu->bind_param("s", $kartu_pegawai);
$check_kartu->execute();
$result_kartu = $check_kartu->get_result();

if ($result_kartu->num_rows > 0) {
    $existing_kartu = $result_kartu->fetch_assoc();
    $check_kartu->close();
    alertGagal(
        "form_tambah_duk.php", 
        "Nomor Kartu Pegawai sudah digunakan oleh: {$existing_kartu['nama']}"
    );
    exit;
}
$check_kartu->close();

// ==================== VALIDASI ESELON & JENIS JABATAN ====================
if ($eselon === 'Non-Eselon') {
    if (empty($jenis_jabatan)) {
        alertGagal("form_tambah_duk.php", 'Jenis Jabatan wajib dipilih untuk Non-Eselon');
        exit;
    }
    
    if ($jenis_jabatan === 'JFT' && empty($jft_tingkat)) {
        alertGagal("form_tambah_duk.php", 'Tingkat JFT wajib dipilih');
        exit;
    }
    
    if ($jenis_jabatan === 'JFU' && empty($jfu_kelas)) {
        alertGagal("form_tambah_duk.php", 'Kelas JFU wajib dipilih');
        exit;
    }
} else {
    // Reset sub-fields jika bukan Non-Eselon
    $jenis_jabatan = null;
    $jft_tingkat = null;
    $jfu_kelas = null;
}

// ==================== SANITASI DATA ====================
$nip = empty($nip) ? null : $nip;

// ==================== INSERT KE DATABASE ====================
// ✅ PENTING: Ini INSERT, bukan UPDATE!
$sql = "INSERT INTO duk (
    nama,
    nip,
    kartu_pegawai,
    ttl,
    jenis_kelamin,
    pendidikan_terakhir,
    prodi,
    nomor_wa,
    pangkat_terakhir,
    golongan,
    tmt_pangkat,
    jabatan_terakhir,
    eselon,
    jenis_jabatan,
    jft_tingkat,
    jfu_kelas,
    tmt_eselon,
    id_opd,
    created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $koneksi->prepare($sql);

if (!$stmt) {
    error_log("❌ Prepare failed: " . $koneksi->error);
    alertGagal("form_tambah_duk.php", 'Gagal mempersiapkan query: ' . $koneksi->error);
    exit;
}

// ✅ Bind 18 parameter
$stmt->bind_param(
    'sssssssssssssssssi',  // 17 x string, 1 x integer (id_opd)
    $nama,
    $nip,
    $kartu_pegawai,
    $ttl,
    $jenis_kelamin,
    $pendidikan_terakhir,
    $prodi,
    $nomor_wa_clean,
    $pangkat_terakhir,
    $golongan,
    $tmt_pangkat,
    $jabatan_terakhir,
    $eselon,
    $jenis_jabatan,
    $jft_tingkat,
    $jfu_kelas,
    $tmt_eselon,
    $id_opd
);

if ($stmt->execute()) {
    // ==================== INSERT BERHASIL ====================
    $new_id = $koneksi->insert_id;
    $stmt->close();
    $koneksi->close();
    
    // ✅ Log untuk debugging
    error_log("✅ Data DUK berhasil ditambahkan - ID: $new_id | Nama: $nama | NIP: $nip");
    
    // ✅ PENTING: Gunakan alertSuksesTambah() untuk INSERT
    alertSuksesTambah('dataduk.php', "Data pegawai $nama berhasil ditambahkan!");
    exit;
    
} else {
    // ==================== INSERT GAGAL ====================
    $error_message = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    // Log error
    error_log("❌ Error insert DUK | Error: $error_message");
    
    // Redirect dengan alert error
    alertGagal("form_tambah_duk.php", 'Gagal menambahkan data: ' . $error_message);
    exit;
}
?>