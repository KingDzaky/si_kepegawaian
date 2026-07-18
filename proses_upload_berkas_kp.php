<?php
// proses_upload_berkas_kp.php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/helper_berkas.php';

if (!isAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kenaikan_pangkat.php');
    exit;
}

$id_kp = (int)($_POST['id_kenaikan_pangkat'] ?? 0);
if (!$id_kp) {
    header('Location: kenaikan_pangkat.php?error=ID usulan tidak valid');
    exit;
}

// Ambil data usulan (butuh nama untuk nama folder + data pangkat baru untuk update DUK nanti)
$stmt = $koneksi->prepare("
    SELECT id, nama, nip, status, pangkat_baru, golongan_baru, tmt_pangkat_baru, jabatan_baru
    FROM kenaikan_pangkat WHERE id = ? LIMIT 1
");
$stmt->bind_param("i", $id_kp);
$stmt->execute();
$kp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$kp) {
    header('Location: kenaikan_pangkat.php?error=Data usulan tidak ditemukan');
    exit;
}

if ($kp['status'] !== 'disetujui') {
    header('Location: kenaikan_pangkat.php?error=Usulan belum disetujui');
    exit;
}

$jenis_wajib = [
    'Daftar Usul Mutasi',
    'Surat Keterangan',
    'Surat Hukdis',
    'Surat Tidak Pidana'
];

$rootPath       = __DIR__;
$folderRelatif  = 'uploads/sk_kp/' . folderPegawaiBerkas($kp['nama'], $kp['nip']);

$berhasil = [];
$gagal    = [];

/**
 * Simpan / update satu jenis berkas ke DB + filesystem
 */
function prosesSatuBerkas(mysqli $koneksi, int $id_kp, string $jenis, array $file, string $folderRelatif, string $rootPath, array &$berhasil, array &$gagal): void {
    $extAllowed = ['pdf', 'jpg', 'jpeg', 'png'];
    $validasi = validasiFileBerkas($file, $extAllowed, 5);

    if (!$validasi['ok']) {
        $gagal[] = $jenis . ': ' . $validasi['error'];
        return;
    }

    $namaFileTanpaExt = slugBerkas($jenis);
    $filePath = simpanFileBerkas($file, $folderRelatif, $namaFileTanpaExt, $validasi['ext'], $rootPath);

    if ($filePath === false) {
        $gagal[] = $jenis . ': gagal menyimpan file ke server (cek permission folder uploads/)';
        return;
    }

    // Cek apakah jenis berkas ini sudah ada untuk usulan ini -> UPDATE, kalau belum -> INSERT
    $stmtCek = $koneksi->prepare("SELECT id FROM berkas_kenaikan_pangkat WHERE id_kenaikan_pangkat = ? AND jenis_berkas = ? LIMIT 1");
    $stmtCek->bind_param("is", $id_kp, $jenis);
    $stmtCek->execute();
    $existing = $stmtCek->get_result()->fetch_assoc();
    $stmtCek->close();

    $namaFileAsli = $file['name'];
    $uploadedBy = $_SESSION['user_id'] ?? null;

    if ($existing) {
        $stmtUpd = $koneksi->prepare("UPDATE berkas_kenaikan_pangkat 
                                       SET nama_file = ?, file_path = ?, tanggal_upload = NOW(), uploaded_by = ?
                                       WHERE id = ?");
        $stmtUpd->bind_param("ssii", $namaFileAsli, $filePath, $uploadedBy, $existing['id']);
        $stmtUpd->execute();
        $stmtUpd->close();
    } else {
        $stmtIns = $koneksi->prepare("INSERT INTO berkas_kenaikan_pangkat 
                                       (id_kenaikan_pangkat, jenis_berkas, nama_file, file_path, tanggal_upload, uploaded_by)
                                       VALUES (?, ?, ?, ?, NOW(), ?)");
        $stmtIns->bind_param("isssi", $id_kp, $jenis, $namaFileAsli, $filePath, $uploadedBy);
        $stmtIns->execute();
        $stmtIns->close();
    }

    $berhasil[] = $jenis;
}

// 1) Proses 4 berkas wajib (yang diisi saja, yang kosong dilewati)
if (isset($_FILES['berkas']) && is_array($_FILES['berkas']['name'])) {
    foreach ($_FILES['berkas']['name'] as $jenis => $namaFile) {
        if ($namaFile === '' || $namaFile === null) {
            continue; // slot ini gak diisi, skip
        }
        if (!in_array($jenis, $jenis_wajib, true)) {
            continue; // jaga-jaga kalau ada key aneh dari luar
        }

        $file = [
            'name'     => $_FILES['berkas']['name'][$jenis],
            'type'     => $_FILES['berkas']['type'][$jenis],
            'tmp_name' => $_FILES['berkas']['tmp_name'][$jenis],
            'error'    => $_FILES['berkas']['error'][$jenis],
            'size'     => $_FILES['berkas']['size'][$jenis],
        ];

        prosesSatuBerkas($koneksi, $id_kp, $jenis, $file, $folderRelatif, $rootPath, $berhasil, $gagal);
    }
}

// 2) Proses dokumen tambahan (jenis bebas, ditulis manual oleh admin)
if (isset($_POST['tambahan_jenis']) && isset($_FILES['tambahan_file'])) {
    foreach ($_POST['tambahan_jenis'] as $i => $labelJenis) {
        $labelJenis = trim($labelJenis);
        if ($labelJenis === '' || empty($_FILES['tambahan_file']['name'][$i])) {
            continue;
        }

        $file = [
            'name'     => $_FILES['tambahan_file']['name'][$i],
            'type'     => $_FILES['tambahan_file']['type'][$i],
            'tmp_name' => $_FILES['tambahan_file']['tmp_name'][$i],
            'error'    => $_FILES['tambahan_file']['error'][$i],
            'size'     => $_FILES['tambahan_file']['size'][$i],
        ];

        // Dokumen tambahan dengan label sama akan tetap dianggap jenis berbeda kalau labelnya beda,
        // tapi kalau label PERSIS sama dengan upload sebelumnya, ini akan meng-update (replace) yang lama.
        prosesSatuBerkas($koneksi, $id_kp, $labelJenis, $file, $folderRelatif, $rootPath, $berhasil, $gagal);
    }
}

if (empty($berhasil) && empty($gagal)) {
    header('Location: form_upload_berkas_kp.php?id=' . $id_kp . '&error=' . urlencode('Tidak ada file yang dipilih untuk diupload'));
    exit;
}

// ============================================
// CEK KELENGKAPAN — apakah 4 jenis wajib sudah ada semua
// (DIKEMBALIKAN — sebelumnya hilang saat file ini dirombak ke versi multi-upload)
// ============================================
$lengkap = false;

if (!empty($berhasil)) {
    $stmt_cek_lengkap = $koneksi->prepare("
        SELECT jenis_berkas FROM berkas_kenaikan_pangkat WHERE id_kenaikan_pangkat = ?
    ");
    $stmt_cek_lengkap->bind_param("i", $id_kp);
    $stmt_cek_lengkap->execute();
    $result_berkas = $stmt_cek_lengkap->get_result();

    $jenis_terupload = [];
    while ($row = $result_berkas->fetch_assoc()) {
        $jenis_terupload[] = $row['jenis_berkas'];
    }
    $stmt_cek_lengkap->close();

    $lengkap = count(array_intersect($jenis_wajib, $jenis_terupload)) === count($jenis_wajib);

    if ($lengkap) {
        // Update status_berkas di kenaikan_pangkat
        $stmt_status = $koneksi->prepare("
            UPDATE kenaikan_pangkat SET status_berkas = 'sudah' WHERE id = ?
        ");
        $stmt_status->bind_param("i", $id_kp);
        $stmt_status->execute();
        $stmt_status->close();

        // ✅ Auto-update DUK — baru terjadi di titik ini, bukan saat approve
        if (!empty($kp['pangkat_baru']) && !empty($kp['golongan_baru'])) {
            $stmt_duk = $koneksi->prepare("
                UPDATE duk SET
                    pangkat_terakhir = ?,
                    golongan         = ?,
                    tmt_pangkat      = ?,
                    jabatan_terakhir = CASE
                        WHEN ? != '' THEN ?
                        ELSE jabatan_terakhir
                    END
                WHERE nip = ?
            ");
            $stmt_duk->bind_param("ssssss",
                $kp['pangkat_baru'],
                $kp['golongan_baru'],
                $kp['tmt_pangkat_baru'],
                $kp['jabatan_baru'],
                $kp['jabatan_baru'],
                $kp['nip']
            );
            $stmt_duk->execute();
            $stmt_duk->close();

            error_log("✅ DUK diperbarui (berkas KP lengkap) untuk NIP: {$kp['nip']}");
        } else {
            error_log("⚠️ Pangkat baru kosong untuk KP ID: $id_kp — DUK tidak diupdate meski berkas lengkap");
        }
    }
}

// Susun pesan hasil
$pesan = [];
if (!empty($berhasil)) {
    $pesan[] = count($berhasil) . ' berkas berhasil diupload (' . implode(', ', $berhasil) . ')';
}
if ($lengkap) {
    $pesan[] = 'Semua berkas wajib lengkap, data DUK telah diperbarui.';
}

if (!empty($gagal)) {
    header('Location: form_upload_berkas_kp.php?id=' . $id_kp .
        '&error=' . urlencode(implode(' | ', $gagal)) .
        (!empty($berhasil) ? '&success=' . urlencode(implode(' ', $pesan)) : ''));
    exit;
}

header('Location: form_upload_berkas_kp.php?id=' . $id_kp . '&success=' . urlencode(implode(' ', $pesan)));
exit;