<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';

if (!hasRole(['superadmin', 'admin', 'kepala_dinas'])) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

$id = $_GET['id'] ?? 0;
$jenis_surat = $_GET['jenis'] ?? 'all'; // all, pengantar, pernyataan

if ($id <= 0) {
    header('Location: usulan_pensiun.php?error=ID tidak valid');
    exit;
}

// Validasi jenis surat
$allowed_jenis = ['all', 'pengantar', 'pernyataan'];
if (!in_array($jenis_surat, $allowed_jenis)) {
    $jenis_surat = 'all';
}

// Ambil data usulan pensiun
$query = "SELECT up.*,
          kop.nama as kadis_nama,
          kop.nip as kadis_nip,
          kop.pangkat as kadis_pangkat,
          kop.golongan as kadis_golongan,
          kop.jabatan as kadis_jabatan,
          kop.gelar_depan as kadis_gelar_depan,
          kop.gelar_belakang as kadis_gelar_belakang
          FROM usulan_pensiun up
          LEFT JOIN kepala_opd kop ON kop.status = 'aktif'
          WHERE up.id = ?
          LIMIT 1";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: usulan_pensiun.php?error=Data tidak ditemukan');
    exit;
}

$data = $result->fetch_assoc();

// Function untuk format tanggal Indonesia
function tanggal_indonesia($tanggal) {
    if (empty($tanggal)) return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Generate nomor surat unik
$tahun = date('Y');
$bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
$bulan_sekarang = $bulan_romawi[(int)date('n')];

$nomor_pengantar = "800.1.6.6/" . str_pad($id, 3, '0', STR_PAD_LEFT) . "/SET-DPPKBPM/$bulan_sekarang/$tahun";
$nomor_pengantar_pkb = "800.1.11.2/DPPKBPM/$tahun";
$nomor_pernyataan = "800.1.6.6/-SEKR/DPPKBPM-BJM/$tahun";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Berkas Pensiun - <?= htmlspecialchars($data['nama']) ?></title>
    <style>
        @page {
            margin: 2cm 2.5cm;
        }
        
        @page :first {
            size: A4;
        }
        
        /* Untuk surat pernyataan (F4) */
        @page pernyataan {
            size: 215mm 330mm; /* F4 size */
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        /* KOP SURAT STYLE - Mengikuti format resmi */
        .kop-surat {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 4px solid #000;
            position: relative;
        }
        
        .kop-logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 60px;
        }
        
        .kop-logo img {
            width: 90px;
            height: 60px;
        }
        
        .kop-text {
            padding-left: 45px;
            padding-right: 0;
        }
        
        .kop-surat h2 {
            margin: 0;
            padding: 0;
            font-size: 11pt;
            font-weight: bold;
            line-height: 1.15;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .kop-surat p {
            margin: 0;
            padding: 0;
            font-size: 8pt;
            line-height: 1.2;
        }
        
        .kop-surat .alamat {
            font-size: 7.5pt;
            margin-top: 2px;
            line-height: 1.15;
        }
        
        /* Tanggal Surat */
        .tanggal-surat {
            text-align: right;
            margin: 20px 0 30px 0;
            font-size: 12pt;
        }
        
        /* Kepada/Yth */
        .kepada {
            margin: 20px 0;
            font-size: 12pt;
            line-height: 1.4;
        }
        
        .kepada p {
            margin: 0;
            padding: 0;
        }
        
        /* Nomor Surat */
        .nomor-surat {
            text-align: center;
            margin: 25px 0 20px 0;
            font-weight: bold;
        }
        
        .nomor-surat h3 {
            margin: 5px 0;
            text-decoration: underline;
            font-size: 12pt;
            font-weight: bold;
        }
        
        .nomor-surat p {
            margin: 3px 0;
            font-size: 11pt;
        }
        
        /* Tabel Surat */
        .tabel-surat {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .tabel-surat th,
        .tabel-surat td {
            padding: 8px;
            border: 1px solid #000;
            vertical-align: top;
            font-size: 11pt;
        }
        
        .tabel-surat th {
            font-weight: bold;
            text-align: center;
            background-color: transparent;
        }
        
        .tabel-surat td:first-child {
            width: 5%;
            text-align: center;
        }
        
        .tabel-surat td:nth-child(2) {
            width: 45%;
        }
        
        .tabel-surat td:nth-child(3) {
            width: 20%;
            text-align: center;
        }
        
        .tabel-surat td:last-child {
            width: 30%;
        }
        
        /* Penutup */
        .penutup {
            margin: 20px 0;
            text-align: justify;
            font-size: 12pt;
        }
        
        /* Page Break */
        .page-break {
            page-break-after: always;
        }
        
        /* No Print Elements */
        .no-print {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            max-width: 320px;
            color: white;
        }
        
        .no-print h3 {
            margin: 0 0 15px 0;
            font-size: 16pt;
            font-weight: bold;
            color: white;
            text-align: center;
            border-bottom: 2px solid rgba(255,255,255,0.3);
            padding-bottom: 10px;
        }
        
        .no-print hr {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.2);
            margin: 15px 0;
        }
        
        /* Tombol Action */
        .btn-action {
            display: inline-block;
            padding: 12px 24px;
            margin: 5px;
            border: none;
            border-radius: 8px;
            font-size: 11pt;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .btn-print {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(17, 153, 142, 0.4);
        }
        
        .btn-close {
            background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
        }
        
        .btn-close:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(238, 9, 121, 0.4);
        }
        
        /* Filter Section */
        .filter-section {
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            backdrop-filter: blur(10px);
        }
        
        .filter-section-title {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 10px;
            color: rgba(255,255,255,0.9);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .filter-tab {
            display: inline-block;
            padding: 8px 16px;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 20px;
            text-decoration: none;
            color: white;
            font-size: 9pt;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .filter-tab:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
            transform: translateY(-2px);
        }
        
        .filter-tab.active {
            background: white;
            color: #667eea;
            border-color: white;
            box-shadow: 0 4px 15px rgba(255,255,255,0.3);
        }
        
        /* Info Box */
        .info-box {
            background: rgba(255,255,255,0.95);
            border-radius: 10px;
            padding: 12px;
            margin-top: 15px;
            color: #333;
            font-size: 9pt;
            line-height: 1.6;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .info-box-title {
            font-weight: bold;
            color: #667eea;
            margin-bottom: 8px;
            font-size: 10pt;
            border-left: 4px solid #667eea;
            padding-left: 8px;
        }
        
        .info-box ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        
        .info-box li {
            margin: 5px 0;
            position: relative;
        }
        
        .info-box li:before {
            content: "✓";
            position: absolute;
            left: -15px;
            color: #11998e;
            font-weight: bold;
        }
        
        /* Icon dalam teks */
        .icon {
            display: inline-block;
            margin-right: 5px;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            .page-pernyataan {
                page: pernyataan;
            }
        }
        
        /* Tabel Data Pegawai (untuk surat pernyataan) */
        .tabel-data {
            width: 100%;
            margin: 15px 0;
            border-collapse: collapse;
        }
        
        .tabel-data td {
            padding: 4px 0;
            vertical-align: top;
            font-size: 12pt;
        }
        
        .tabel-data td:first-child {
            width: 35%;
        }
        
        .tabel-data td:nth-child(2) {
            width: 5%;
            text-align: center;
        }
        
        .tabel-data td:last-child {
            width: 60%;
        }
        
        /* ISI SURAT PERNYATAAN */
        .isi-surat {
            text-align: justify;
            margin: 20px 0;
            font-size: 12pt;
            line-height: 1.6;
        }
        
        .isi-surat p {
            margin: 10px 0;
        }
        
        .isi-surat h4 {
            text-align: center;
            margin: 20px 0;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
        }
        
        /* Styling khusus */
        strong {
            font-weight: bold;
        }
        
        .indent {
            text-indent: 50px;
        }
    </style>
</head>
<body>

<!-- Tombol Aksi -->
<div class="no-print">
    <h3><span class="icon">📄</span>Export Berkas Pensiun</h3>
    
    <!-- Tombol Aksi -->
    <div style="text-align: center; margin-bottom: 15px;">
        <button onclick="window.print()" class="btn-action btn-print">
            <span class="icon">🖨️</span> Cetak Surat
        </button>
        <button onclick="window.close()" class="btn-action btn-close">
            <span class="icon">✖️</span> Tutup
        </button>
    </div>
    
    <hr>
    
    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-section-title"><span class="icon">🔍</span> Filter Surat:</div>
        <div class="filter-tabs">
            <a href="?id=<?= $id ?>&jenis=all" class="filter-tab <?= $jenis_surat === 'all' ? 'active' : '' ?>">
                <span class="icon">📋</span> Semua
            </a>
            <a href="?id=<?= $id ?>&jenis=pengantar" class="filter-tab <?= $jenis_surat === 'pengantar' ? 'active' : '' ?>">
                <span class="icon">📨</span> Surat Pengantar
            </a>
            <?php if ($data['sumber_data'] === 'duk'): ?>
            <a href="?id=<?= $id ?>&jenis=pernyataan" class="filter-tab <?= $jenis_surat === 'pernyataan' ? 'active' : '' ?>">
                <span class="icon">📝</span> Surat Pernyataan
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Info Box -->
    <div class="info-box">
        <div class="info-box-title"><span class="icon">📌</span> Surat yang akan dicetak:</div>
        <ul>
            <?php if ($jenis_surat === 'all'): ?>
                <?php if ($data['sumber_data'] === 'penyuluh'): ?>
                    <li>Surat Pengantar PKB <small>(A4)</small></li>
                <?php else: ?>
                    <li>Surat Pengantar DUK <small>(A4)</small></li>
                    <li>Surat Pernyataan Disiplin <small>(F4)</small></li>
                <?php endif; ?>
            <?php elseif ($jenis_surat === 'pengantar'): ?>
                <li>Surat Pengantar <?= $data['sumber_data'] === 'penyuluh' ? 'PKB' : 'DUK' ?> <small>(A4)</small></li>
            <?php else: ?>
                <li>Surat Pernyataan Disiplin <small>(F4)</small></li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php if ($data['sumber_data'] === 'penyuluh' && ($jenis_surat === 'all' || $jenis_surat === 'pengantar')): ?>
<!-- ========================================
     SURAT PENGANTAR PKB (UNTUK PENYULUH)
     ======================================== -->
<div class="page-pengantar-pkb">
    <!-- KOP SURAT -->
    <div class="kop-surat">
        <div class="kop-logo">
            <img src="assets/img/logo.png" alt="Logo Banjarmasin">
        </div>
        <div class="kop-text">
            <h2>PEMERINTAH KOTA BANJARMASIN</h2>
            <h2>DINAS PENGENDALIAN PENDUDUK, KELUARGA</h2>
            <h2>BERENCANA DAN PEMBERDAYAAN MASYARAKAT</h2>
            <p class="alamat">Jalan Brigjend H. Hasan Basri – Kayutangi II RT.16 Telp (0511) 3301346 Fax (0511)3305371,</p>
            <p class="alamat">Pos-el : dppkbpm@gmail.go.id, Laman http://dppkbpm.banjarmasinkota.go.id</p>
        </div>
    </div>
    
    <!-- Tanggal -->
    <div class="tanggal-surat">
        <p>Banjarmasin, <?= tanggal_indonesia(date('Y-m-d')) ?></p>
    </div>
    
    <!-- Kepada -->
    <div class="kepada">
        <p>Yth. Kepala Perwakilan BKKBN Provinsi Kalimantan Selatan</p>
        <p style="text-indent: 27px;">Kota Banjarmasin</p>
        <p>Di-</p>
        <p style="text-indent: 27px;">Banjarmasin</p>
    </div>
    
    <!-- Nomor Surat -->
    <div class="nomor-surat">
        <h3>SURAT PENGANTAR</h3>
        <p>NOMOR : <?= $nomor_pengantar_pkb ?></p>
    </div>
    
    <!-- Tabel -->
    <table class="tabel-surat">
        <thead>
            <tr>
                <th>No.</th>
                <th>Naskah Dinas yang Dikirimkan</th>
                <th>Banyaknya</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1.</td>
                <td>
                    Permohonan Cuti<br>
                    <?= htmlspecialchars($data['nama']) ?><br>
                    Nip. <?= htmlspecialchars($data['nip']) ?>
                </td>
                <td>1 (satu) Berkas</td>
                <td>Disampaikan Sebagai Bahan Selanjutnya untuk diproses sesuai ketentuan yang berlaku</td>
            </tr>
        </tbody>
    </table>
    
    <!-- Penutup -->
    <div class="penutup">
        <p>Demikian disampaikan, atas kerjasama yang baik diucapkan terimakasih</p>
    </div>
    
    <!-- TTD -->
    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: left;">
                <p style="margin: 2px 0; line-height: 1.3;">Banjarmasin, <?= tanggal_indonesia(date('Y-m-d')) ?></p>
                <p style="margin: 2px 0; line-height: 1.3;">Pengirim,</p>
                <p style="margin: 2px 0; line-height: 1.3;"><strong>Kepala Dinas,</strong></p>
                <div style="height: 60px;"></div>
                <p style="margin: 2px 0; line-height: 1.3; text-decoration: underline; font-weight: bold;">
                    <?= htmlspecialchars($data['kadis_gelar_depan'] ?? 'Drs.') ?> 
                    <?= htmlspecialchars($data['kadis_nama'] ?? 'M. HELFIANNOOR') ?>, 
                    <?= htmlspecialchars($data['kadis_gelar_belakang'] ?? 'M.Si') ?>
                </p>
                <p style="margin: 2px 0; line-height: 1.3;">
                    <?= htmlspecialchars($data['kadis_pangkat'] ?? 'Pembina Utama Muda') ?>
                </p>
                <p style="margin: 2px 0; line-height: 1.3;">
                    NIP. <?= htmlspecialchars($data['kadis_nip'] ?? '197307191993021002') ?>
                </p>
            </td>
        </tr>
    </table>
    
</div>

<?php elseif ($data['sumber_data'] === 'duk'): ?>
    <!-- UNTUK DUK: SURAT PENGANTAR + SURAT PERNYATAAN -->
    
    <?php if ($jenis_surat === 'all' || $jenis_surat === 'pengantar'): ?>
    <!-- ========================================
         SURAT PENGANTAR DUK
         ======================================== -->
    <div class="page-pengantar-duk">
        <!-- KOP SURAT -->
        <div class="kop-surat">
            <div class="kop-logo">
                <img src="assets/img/logo.png" alt="Logo Banjarmasin">
            </div>
            <div class="kop-text">
                <h2>PEMERINTAH KOTA BANJARMASIN</h2>
                <h2>DINAS PENGENDALIAN PENDUDUK, KELUARGA</h2>
                <h2>BERENCANA DAN PEMBERDAYAAN MASYARAKAT</h2>
                <p class="alamat">Jalan Brigjend.H.Hasan Basri-Kayu Tangi II RT.16 Telp (0511) 3301346 Fax (0511)3305371,</p>
                <p class="alamat">Banjarmasin,70124</p>
            </div>
        </div>
        
        <!-- Tanggal -->
        <div class="tanggal-surat">
            <p>Banjarmasin, <?= tanggal_indonesia(date('Y-m-d')) ?></p>
        </div>
        
        <!-- Kepada -->
        <div class="kepada">
            <p>Kepada Yth.</p>
            <p>Kepala BKD, Diklat Kota Banjarmasin</p>
            <p>- Di Banjarmasin</p>
        </div>
        
        <!-- Nomor Surat -->
        <div class="nomor-surat">
            <h3>SURAT PENGANTAR</h3>
            <p>NOMOR : <?= $nomor_pengantar ?></p>
        </div>
        
        <!-- Tabel -->
        <table class="tabel-surat">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Naskah Dinas yang Dikirimkan</th>
                    <th>Banyaknya</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1.</td>
                    <td>
                        Berkas Permohonan Pensiun<br>
                        An. <?= htmlspecialchars($data['nama']) ?><br>
                        NIP. <?= htmlspecialchars($data['nip']) ?>
                    </td>
                    <td>1 (satu) Berkas</td>
                    <td>
                        Disampaikan sebagai bahan selanjutnya untuk diproses sesuai ketentuan yang berlaku
                        <br><br>
                        Terima Kasih
                    </td>
                </tr>
            </tbody>
        </table>
        
        <!-- TTD -->
        <table style="width: 100%; margin-top: 30px;">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: left;">
                    <p style="margin: 2px 0; line-height: 1.3;">Banjarmasin, <?= tanggal_indonesia(date('Y-m-d')) ?></p>
                    <p style="margin: 2px 0; line-height: 1.3;">Pengirim,</p>
                    <p style="margin: 2px 0; line-height: 1.3;"><strong>Kepala Dinas,</strong></p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 2px 0; line-height: 1.3; text-decoration: underline; font-weight: bold;">
                        <?= htmlspecialchars($data['kadis_gelar_depan'] ?? 'Drs.') ?> 
                        <?= htmlspecialchars($data['kadis_nama'] ?? 'M. Helfiannoor') ?>, 
                        <?= htmlspecialchars($data['kadis_gelar_belakang'] ?? 'M.Si') ?>
                    </p>
                    <p style="margin: 2px 0; line-height: 1.3;">
                        <?= htmlspecialchars($data['kadis_pangkat'] ?? 'Pembina Utama Muda') ?> 
                        (<?= htmlspecialchars($data['kadis_golongan'] ?? 'IV/c') ?>)
                    </p>
                    <p style="margin: 2px 0; line-height: 1.3;">
                        NIP. <?= htmlspecialchars($data['kadis_nip'] ?? '19730719 199302 1 002') ?>
                    </p>
                </td>
            </tr>
        </table>
        
    </div>
    
    <?php endif; // End pengantar ?>
    
    <?php if ($jenis_surat === 'all' || $jenis_surat === 'pernyataan'): ?>
    <!-- PAGE BREAK jika cetak semua -->
    <?php if ($jenis_surat === 'all'): ?>
    <div class="page-break"></div>
    <?php endif; ?>
    
    <!-- ========================================
         SURAT PERNYATAAN DISIPLIN (F4)
         ======================================== -->
    <div class="page-pernyataan">
        <!-- KOP SURAT -->
        <div class="kop-surat">
            <div class="kop-logo">
                <img src="assets/img/logo.png" alt="Logo Banjarmasin">
            </div>
            <div class="kop-text">
                <h2>PEMERINTAH KOTA BANJARMASIN</h2>
                <h2>DINAS PENGENDALIAN PENDUDUK, KELUARGA</h2>
                <h2>BERENCANA DAN PEMBERDAYAAN MASYARAKAT</h2>
                <p class="alamat">JL. Brigjend H. Hasan Basri – Kayutangi II RT. 16 Banjarmasin 70124</p>
                <p class="alamat">Pos-el : dppkbpm@gmail.go.id, Laman http://dppkbpm.banjarmasinkota.go.id</p>
            </div>
        </div>
        
        <!-- Judul Surat -->
        <div class="nomor-surat" style="margin-top: 30px;">
            <h3 style="margin: 5px 0;">SURAT PERNYATAN</h3>
            <h3 style="margin: 5px 0; font-size: 11pt;">TIDAK SEDANG MENJALANI PROSES PIDANA ATAU PERNAH DIPIDANA PENJARA</h3>
            <h3 style="margin: 5px 0; font-size: 11pt;">BERDASARKAN PUTUSAN PENGADILAN YANG TELAH BERKEKUATAN HUKUM TETAP</h3>
            <p style="margin-top: 10px;">NOMOR : <?= $nomor_pernyataan ?></p>
        </div>
        
        <!-- Isi Surat -->
        <div class="isi-surat">
            <p>Yang bertanda tangan dibawah ini :</p>
            
            <table class="tabel-data">
                <tr>
                    <td>N a m a</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($data['kadis_gelar_depan'] ?? 'Drs.') ?> 
                        <?= htmlspecialchars($data['kadis_nama'] ?? 'M. HELFIANNOOR') ?>, 
                        <?= htmlspecialchars($data['kadis_gelar_belakang'] ?? 'M.Si.') ?>
                    </td>
                </tr>
                <tr>
                    <td>N I P</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($data['kadis_nip'] ?? '197307191993021002') ?></td>
                </tr>
                <tr>
                    <td>Pangkat/Golongan Ruang</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($data['kadis_pangkat'] ?? 'Pembina Utama Muda') ?> 
                        (<?= htmlspecialchars($data['kadis_golongan'] ?? 'IV/c') ?>)
                    </td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($data['kadis_jabatan'] ?? 'Kepala DPPKBPM Kota Banjarmasin') ?></td>
                </tr>
            </table>
            
            <p>dengan ini menyatakan dengan sesungguhnya, bahwa Pegawai Negeri Sipil,</p>
            
            <table class="tabel-data">
                <tr>
                    <td>N a m a</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($data['nama']) ?></td>
                </tr>
                <tr>
                    <td>N I P</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($data['nip']) ?></td>
                </tr>
                <tr>
                    <td>Pangkat / Golongan</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($data['pangkat_terakhir']) ?> / 
                        (<?= htmlspecialchars($data['golongan']) ?>)
                    </td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($data['jabatan_terakhir']) ?></td>
                </tr>
            </table>
            
            <p style="margin-top: 20px; text-align: left;">tidak pernah dijatuhi hukuman disiplin tingkat sedang/berat.</p>
            
            <p style="margin-top: 20px; text-align: justify;">
                Demikian surat pernyataan ini saya buat dengan sesungguhnya dengan mengingat
                sumpah jabatan dan apabila dikemudian hari ternyata isi surat pernyataan ini tidak benar
                yang mengakibatkan kerugian bagi negara maka saya bersedia menanggung kerugian
                tersebut.
            </p>
        </div>
        
        <!-- TTD -->
        <table style="width: 100%; margin-top: 30px;">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: left;">
                    <p style="margin: 2px 0; line-height: 1.3;">Banjarmasin, <?= tanggal_indonesia(date('Y-m-d')) ?></p>
                    <p style="margin: 2px 0; line-height: 1.3;"><strong>Kepala DPPKBPM,</strong></p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 2px 0; line-height: 1.3; text-decoration: underline; font-weight: bold;">
                        <?= htmlspecialchars($data['kadis_gelar_depan'] ?? 'Drs.') ?> 
                        <?= htmlspecialchars($data['kadis_nama'] ?? 'M. HELFIANNOOR') ?>, 
                        <?= htmlspecialchars($data['kadis_gelar_belakang'] ?? 'M.Si') ?>
                    </p>
                    <p style="margin: 2px 0; line-height: 1.3;">
                        <?= htmlspecialchars($data['kadis_pangkat'] ?? 'Pembina Utama Muda') ?>
                    </p>
                    <p style="margin: 2px 0; line-height: 1.3;">
                        NIP. <?= htmlspecialchars($data['kadis_nip'] ?? '197307191993021002') ?>
                    </p>
                </td>
            </tr>
        </table>
        
    </div>

    <?php endif; // End pernyataan ?>

<?php endif; // End DUK ?>

</body>
</html>

<?php
$stmt->close();
$koneksi->close();
?>