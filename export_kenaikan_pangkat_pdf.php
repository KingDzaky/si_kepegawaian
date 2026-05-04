<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID tidak valid');
}

$id = (int) $_GET['id'];

// ✅ Query mengambil semua data dari kenaikan_pangkat + kepala_opd
$query = "SELECT 
    k.*,
    o.nama as nama_kepala_opd,
    o.nip as nip_kepala_opd,
    o.pangkat as pangkat_kepala_opd,
    o.jabatan as jabatan_kepala_opd,
    o.gelar_depan,
    o.gelar_belakang,
    o.golongan as golongan_kepala_opd
FROM kenaikan_pangkat k
LEFT JOIN kepala_opd o ON k.id_opd = o.id
WHERE k.id = ?
LIMIT 1";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Data tidak ditemukan');
}

$data = $result->fetch_assoc();
$stmt->close();

// ✅ ========================================
// FORMAT TTL dari tempat_lahir + tanggal_lahir
// ========================================
$ttl_formatted = '-';
if (!empty($data['tempat_lahir']) && !empty($data['tanggal_lahir'])) {
    $timestamp = strtotime($data['tanggal_lahir']);
    if ($timestamp !== false) {
        // Array nama bulan dalam Bahasa Indonesia
        $bulan_indo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $hari = date('d', $timestamp);
        $bulan = $bulan_indo[(int)date('m', $timestamp)];
        $tahun = date('Y', $timestamp);
        
        // Output: "Banjarmasin, 12 April 2000"
        $ttl_formatted = $data['tempat_lahir'] . ', ' . $hari . ' ' . $bulan . ' ' . $tahun;
    }
} elseif (!empty($data['tempat_lahir'])) {
    // Jika hanya ada tempat lahir tanpa tanggal
    $ttl_formatted = $data['tempat_lahir'];
}
// ========================================

$nama_kepala = $data['atasan_nama'];
$jabatan_kepala = $data['atasan_jabatan'];
$nip_kepala = $data['atasan_nip'];
$pangkat_kepala = $data['atasan_pangkat'];
$golongan_kepala = '';

if (!empty($data['nama_kepala_opd'])) {
    $gelar_depan = !empty($data['gelar_depan']) ? $data['gelar_depan'] . ' ' : '';
    $gelar_belakang = !empty($data['gelar_belakang']) ? ', ' . $data['gelar_belakang'] : '';
    $nama_kepala = $gelar_depan . $data['nama_kepala_opd'] . $gelar_belakang;
    $jabatan_kepala = $data['jabatan_kepala_opd'];
    $nip_kepala = $data['nip_kepala_opd'];
    $pangkat_kepala = $data['pangkat_kepala_opd'];
    $golongan_kepala = $data['golongan_kepala_opd'];
}

// ========================================
// PERHITUNGAN MASA KERJA YANG BENAR
// ========================================

// BARIS 1: Masa Kerja Golongan SAAT INI (dari database - mk_golongan)
$mkg_saat_ini_tahun = $data['mk_golongan_tahun'];
$mkg_saat_ini_bulan = $data['mk_golongan_bulan'];

// BARIS 2: Masa Kerja yang AKAN BERTAMBAH (dari TMT lama ke TMT baru)
$tmt_lama = new DateTime($data['tmt_pangkat_lama']);
$tmt_baru = new DateTime($data['tmt_pangkat_baru']);
$interval_tambahan = $tmt_lama->diff($tmt_baru);
$tambahan_tahun = $interval_tambahan->y;
$tambahan_bulan = $interval_tambahan->m;

// Format tanggal untuk kolom "Mulai dari sampai Dengan"
$tanggal_dari = $tmt_lama->format('d-m-Y');
$tanggal_sampai = $tmt_baru->format('d-m-Y');
$mk_dari_sampai = $tanggal_dari . ' s/d ' . $tanggal_sampai;

// BARIS 3: TOTAL = Baris 1 + Baris 2
$total_bulan = $mkg_saat_ini_bulan + $tambahan_bulan;
$total_tahun = $mkg_saat_ini_tahun + $tambahan_tahun;

// Normalisasi jika bulan >= 12
if ($total_bulan >= 12) {
    $total_tahun += floor($total_bulan / 12);
    $total_bulan = $total_bulan % 12;
}

$koneksi->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Daftar Usul Mutasi Kenaikan Pangkat</title>
    <style>
        @page {
            size: A4;
            margin: 0.8cm 1cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9.5pt;
            padding: 5px;
            line-height: 1.15;
        }

        .header {
            text-align: right;
            margin-bottom: 2px;
            margin-right: 10px;
        }

        .header p {
            margin: 0;
            font-size: 8.5pt;
            line-height: 1.1;
        }

        .title {
            text-align: center;
            margin: 5px 0 2px 0;
        }

        .title h3 {
            font-size: 11pt;
            font-weight: bold;
            margin: 2px 0;
            text-decoration: underline;
        }

        .nomor {
            text-align: center;
            font-size: 9.5pt;
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th {
            background-color: #d9d9d9;
            padding: 3px;
            text-align: center;
            font-weight: bold;
            font-size: 9.5pt;
        }

        td {
            padding: 2px 4px;
            vertical-align: top;
            font-size: 9.5pt;
        }

        .no-border {
            border: none !important;
        }

        .col-no {
            width: 35px;
            text-align: center;
            font-weight: bold;
        }

        /* Inner table untuk alignment titik dua */
        .align-table {
            width: 100%;
            border: none;
        }

        .align-table td {
            border: none;
            padding: 1px 0;
        }

        .label-col {
            width: 230px;
        }

        .colon-col {
            width: 15px;
        }

        .upright-col {
            writing-mode: vertical-rl;
            text-orientation: upright;
            width: 15px;
        }

        .value-col {
            padding-left: 5px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            @page {
                margin: 0.8cm 1cm;
            }

            body {
                padding: 0;
            }
        }

        .btn-container {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background: #f5f5f5;
        }

        .btn {
            padding: 10px 25px;
            font-size: 11pt;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            margin: 0 5px;
            font-weight: bold;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }
    </style>
</head>

<body>

    <!-- Header Kanan Atas -->
    <div class="header">
        <p>KENAIKAN PANGKAT</p>
        <p>1. PILIHAN</p>
        <p>2. REGULER</p>
        <p>3. ANUMERTA</p>
        <p>4. PENGABDIAN</p>
    </div>

    <!-- Title -->
    <div class="title">
        <h3>DAFTAR USUL MUTASI KENAIKAN PANGKAT</h3>
    </div>

    <!-- Nomor -->
    <div class="nomor">
        <?php
        // Ambil tahun dari nomor_usulan (format: 800.1.3.2/012/DPPKBPM-BJM/2026)
        $nomor_parts = explode('/', $data['nomor_usulan']);
        
        // Bagian pertama (800.1.3.2)
        $nomor_prefix = isset($nomor_parts[0]) ? $nomor_parts[0] : '';
        
        // Tahun dari bagian terakhir atau dari tanggal_usulan sebagai fallback
        $tahun_usulan = '2025'; // Default
        if (isset($nomor_parts[3]) && is_numeric($nomor_parts[3])) {
            // Ambil tahun dari nomor usulan (bagian terakhir)
            $tahun_usulan = $nomor_parts[3];
        } else if (!empty($data['tanggal_usulan'])) {
            // Fallback: ambil dari tanggal usulan
            $tahun_usulan = date('Y', strtotime($data['tanggal_usulan']));
        }
        ?>
        Nomor : <?= htmlspecialchars($nomor_prefix) ?>/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/DPPKBPM-BJM/<?= htmlspecialchars($tahun_usulan) ?>
    </div>

    <!-- Main Table -->
    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th colspan="4">PEGAWAI NEGERI SIPIL YANG DIUSULKAN</th>
            </tr>
        </thead>
        <tbody>
            <!-- Row 1: Nama -->
            <tr>
                <td class="col-no">1.</td>
                <td class="label-col" colspan="2">Nama</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= htmlspecialchars($data['nama']) ?></td>
            </tr>

            <!-- Row 2: NIP -->
            <tr>
                <td class="col-no">2.</td>
                <td class="label-col" colspan="2">NIP/Seri Karpeg/Pendidikan</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <?= htmlspecialchars($data['nip']) ?> / 
                    <?= htmlspecialchars($data['kartu_pegawai']) ?> / 
                    <?= htmlspecialchars($data['pendidikan_terakhir'] . ' ' . $data['prodi']) ?>
                </td>
            </tr>

            <!-- ✅ Row 3: TTL - UPDATED dengan Format Indonesia -->
            <tr>
                <td class="col-no">3.</td>
                <td class="label-col" colspan="2">Tempat Tanggal Lahir</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= $ttl_formatted ?></td>
                <!-- ✅ JANGAN pakai htmlspecialchars() karena sudah diformat di atas -->
            </tr>

            <!-- Row 4: Pangkat LAMA -->
            <tr>
                <td class="col-no" rowspan="4">4.</td>
                <td class="upright-col" rowspan="4">LAMA</td>
                <td class="label-col">a. Pangkat / Gol. Ruang / TMT</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <?= htmlspecialchars($data['pangkat_lama']) ?> / 
                    <?= htmlspecialchars($data['golongan_lama']) ?> / 
                    <?php
                    // Format tanggal: 01 October 2021
                    $tmt_lama_obj = new DateTime($data['tmt_pangkat_lama']);
                    $bulan_inggris = [
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                    ];
                    echo $tmt_lama_obj->format('d') . ' ' . $bulan_inggris[(int)$tmt_lama_obj->format('m')] . ' ' . $tmt_lama_obj->format('Y');
                    ?>
                </td>
            </tr>

            <tr>
                <td class="label-col">b. Masa Kerja</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <?= str_pad($data['masa_kerja_tahun_lama'], 2, '0', STR_PAD_LEFT) ?> Tahun 
                    <?= str_pad($data['masa_kerja_bulan_lama'], 2, '0', STR_PAD_LEFT) ?> Bulan
                </td>
            </tr>

            <tr>
                <td class="label-col">c. Gaji Pokok</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= number_format($data['gaji_pokok_lama'], 0, ',', '.') ?>,-</td>
            </tr>

            <tr>
                <td class="label-col">d. Jabatan</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= htmlspecialchars($data['jabatan_lama']) ?></td>
            </tr>

            <!-- Row 5: Pangkat BARU -->
            <tr>
                <td class="col-no" rowspan="4">5.</td>
                <td class="upright-col" rowspan="4">BARU</td>
                <td class="label-col">a. Pangkat / Gol. Ruang / TMT</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <?= htmlspecialchars($data['pangkat_baru']) ?> / 
                    <?= htmlspecialchars($data['golongan_baru']) ?> / 
                    <?php
                    // Format tanggal: 02 December 2025
                    $tmt_baru_obj = new DateTime($data['tmt_pangkat_baru']);
                    echo $tmt_baru_obj->format('d') . ' ' . $bulan_indo[(int)$tmt_baru_obj->format('m')] . ' ' . $tmt_baru_obj->format('Y');
                    ?>
                </td>
            </tr>

            <tr>
                <td class="label-col">b. Masa Kerja</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <?= str_pad($data['masa_kerja_tahun_baru'], 2, '0', STR_PAD_LEFT) ?> Tahun 
                    <?= str_pad($data['masa_kerja_bulan_baru'], 2, '0', STR_PAD_LEFT) ?> Bulan
                </td>
            </tr>

            <tr>
                <td class="label-col">c. Gaji Pokok</td>
                <td class="colon-col">:</td>
                <td class="value-col">Rp <?= number_format($data['gaji_pokok_baru'], 0, ',', '.') ?>,-</td>
            </tr>

            <tr>
                <td class="label-col">d. Jabatan</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= htmlspecialchars($data['jabatan_baru']) ?></td>
            </tr>

            <!-- Row 6: Atasan Langsung -->
            <tr>
                <td class="col-no" rowspan="4">6.</td>
                <td class="label-col" colspan="2">Atasan Langsung</td>
                <td class="colon-col">:</td>
                <td class="value-col"></td>
            </tr>

            <tr>
                <td class="label-col" colspan="2">Nama / NIP</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= htmlspecialchars($nama_kepala) ?></td>
            </tr>

            <tr>
                <td class="label-col" colspan="2">Pangkat / Gol. Ruang</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= htmlspecialchars($nip_kepala) ?></td>
            </tr>

            <tr>
                <td class="label-col" colspan="2">Jabatan</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= htmlspecialchars($jabatan_kepala) ?></td>
            </tr>

            <!-- Row 7: Wilayah Pembayaran -->
            <tr>
                <td class="col-no">7.</td>
                <td class="label-col" colspan="2">Wilayah Pembayaran</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= htmlspecialchars($data['wilayah_pembayaran']) ?></td>
            </tr>

            <!-- Row 8: Perhitungan Masa Kerja -->
            <tr>
                <td class="col-no">8.</td>
                <td colspan="4">
                    <strong>Perhitungan Masa Kerja</strong>
                    <table style="width: 100%; margin-top: 2px; border: 1px solid #000;">
                        <tr style="background-color: #f0f0f0;">
                            <td style="width: 40%; text-align: center; padding: 2px; border-right: 1px solid #000; font-size: 8.5pt;">
                                <strong>Masa Kerja Gol. Ruang<br>Dalam Pangkat Terakhir</strong>
                            </td>
                            <td style="width: 30%; text-align: center; padding: 2px; border-right: 1px solid #000; font-size: 8.5pt;">
                                <strong>Mulai dari sampai<br>Dengan</strong>
                            </td>
                            <td style="width: 30%; text-align: center; padding: 2px; font-size: 8.5pt;" colspan="2">
                                <strong>Jumlah</strong>
                            </td>
                        </tr>
                        <tr style="background-color: #f0f0f0;">
                            <td style="border-right: 1px solid #000; border-top: 1px solid #000;"></td>
                            <td style="border-right: 1px solid #000; border-top: 1px solid #000;"></td>
                            <td style="width: 15%; text-align: center; padding: 2px; border-right: 1px solid #000; border-top: 1px solid #000; font-size: 8.5pt;">
                                <strong>Tahun</strong>
                            </td>
                            <td style="width: 15%; text-align: center; padding: 2px; border-top: 1px solid #000; font-size: 8.5pt;">
                                <strong>Bulan</strong>
                            </td>
                        </tr>
                        
                        <!-- Baris 1: MKG Saat Ini -->
                        <tr>
                            <td style="text-align: center; padding: 4px; border-right: 1px solid #000; border-top: 1px solid #000; font-size: 8.5pt;">
                                <?= htmlspecialchars($data['golongan_lama']) ?>
                            </td>
                            <td style="text-align: center; padding: 4px; border-right: 1px solid #000; border-top: 1px solid #000; font-size: 8.5pt;">
                                <?= $tanggal_dari ?>
                            </td>
                            <td style="text-align: center; padding: 4px; border-right: 1px solid #000; border-top: 1px solid #000; font-size: 8.5pt;">
                                <?= str_pad($mkg_saat_ini_tahun, 2, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td style="text-align: center; padding: 4px; border-top: 1px solid #000; font-size: 8.5pt;">
                                <?= str_pad($mkg_saat_ini_bulan, 2, '0', STR_PAD_LEFT) ?>
                            </td>
                        </tr>
                        
                        <!-- Baris 2: Tambahan MKG -->
                        <tr>
                            <td style="text-align: center; padding: 4px; border-right: 1px solid #000; border-top: 1px solid #000; font-size: 8.5pt;">
                                <?= htmlspecialchars($data['golongan_lama']) ?> ke <?= htmlspecialchars($data['golongan_baru']) ?>
                            </td>
                            <td style="text-align: center; padding: 4px; border-right: 1px solid #000; border-top: 1px solid #000; font-size: 8.5pt;">
                                <?= $mk_dari_sampai ?>
                            </td>
                            <td style="text-align: center; padding: 4px; border-right: 1px solid #000; border-top: 1px solid #000; font-size: 8.5pt;">
                                <?= str_pad($tambahan_tahun, 2, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td style="text-align: center; padding: 4px; border-top: 1px solid #000; font-size: 8.5pt;">
                                <?= str_pad($tambahan_bulan, 2, '0', STR_PAD_LEFT) ?>
                            </td>
                        </tr>
                        
                        <!-- Baris 3: TOTAL -->
                        <tr style="background-color: #f0f0f0;">
                            <td colspan="2" style="border-right: 1px solid #000; border-top: 1px solid #000;"></td>
                            <td style="text-align: center; padding: 4px; border-right: 1px solid #000; border-top: 1px solid #000; font-weight: bold; font-size: 8.5pt;">
                                <?= str_pad($total_tahun, 2, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td style="text-align: center; padding: 4px; border-top: 1px solid #000; font-weight: bold; font-size: 8.5pt;">
                                <?= str_pad($total_bulan, 2, '0', STR_PAD_LEFT) ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

        </tbody>
    </table>

    <!-- Footer dengan Kotak -->
    <table style="margin-top: 4px;">
        <tr>
            <td style="width: 60%; vertical-align: top; padding: 6px;">
                <p style="margin: 1px 0; font-size: 9.5pt;"><strong>Alasan-alasan Mutasi Kenaikan Pangkat</strong></p>
                <p style="margin: 1px 0; font-size: 9.5pt;">Memenuhi Syarat untuk diusulkan kenaikan Pangkat</p>
                <p style="margin: 1px 0; font-size: 9.5pt;">
                    SKP Tahun <?= htmlspecialchars($data['skp_tahun_1']) ?> : <?= htmlspecialchars($data['skp_nilai_1']) ?>
                </p>
                <p style="margin: 1px 0; font-size: 9.5pt;">
                    SKP Tahun <?= htmlspecialchars($data['skp_tahun_2']) ?> : <?= htmlspecialchars($data['skp_nilai_2']) ?>
                </p>
            </td>
            <td style="width: 40%; vertical-align: top; padding: 6px; text-align: center;">
                <p style="margin: 1px 0; font-size: 9.5pt;">Banjarmasin,</p>
                <p style="margin: 1px 0; font-size: 9.5pt;"><strong>KEPALA DINAS,</strong></p>
                <div style="height: 45px;"></div>
                <p style="margin: 1px 0; font-size: 9.5pt;"><strong><?= htmlspecialchars($nama_kepala) ?></strong></p>
                <p style="margin: 1px 0; font-size: 9.5pt;">
                    <strong><?= htmlspecialchars($pangkat_kepala) ?><?= !empty($golongan_kepala) ? ' / ' . htmlspecialchars($golongan_kepala) : '' ?></strong>
                </p>
                <p style="margin: 1px 0; font-size: 9.5pt;"><strong>NIP. <?= htmlspecialchars($nip_kepala) ?></strong></p>
            </td>
        </tr>
    </table>

    <!-- Buttons -->
    <div class="no-print btn-container">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak / Download PDF</button>
        <button onclick="window.close()" class="btn btn-secondary">✖️ Tutup</button>
    </div>

</body>
</html>