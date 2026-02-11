<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID tidak valid');
}

$id = (int) $_GET['id'];

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

$gelar_depan = !empty($data['gelar_depan']) ? $data['gelar_depan'] . ' ' : '';
$gelar_belakang = !empty($data['gelar_belakang']) ? ', ' . $data['gelar_belakang'] : '';
$nama_kepala = $gelar_depan . $data['nama_kepala_opd'] . $gelar_belakang;
$nip_kepala = $data['nip_kepala_opd'];
$pangkat_kepala = $data['pangkat_kepala_opd'];
$golongan_kepala = $data['golongan_kepala_opd'];
$jabatan_kepala = $data['jabatan_kepala_opd'];

$nama_pegawai = $data['nama'];
$nip_pegawai = $data['nip'];
$pangkat_lama = $data['pangkat_lama'];
$golongan_lama = $data['golongan_lama'];
$pangkat_baru = $data['pangkat_baru'];
$golongan_baru = $data['golongan_baru'];
$jabatan_baru = $data['jabatan_baru'];

$koneksi->close();

function namaBulan($bulan) {
    $bulanIndo = [
        1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    ];
    return $bulanIndo[(int)$bulan];
}

$tanggal_sekarang = date('d');
$bulan_sekarang = namaBulan(date('n'));
$tahun_sekarang = date('Y');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan PNS</title>
    <style>
        @page {
            size: A4;
            margin: 2.5cm 2cm 2cm 2cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            padding: 20px;
        }

        /* HEADER */
        .header-wrapper {
            position: relative;
            margin-bottom: 8px;
            min-height: 85px;
        }

        .header-logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 70px;
        }

        .header-logo img {
            width: 120px;
            height: 94px;
            object-fit: contain;
        }

        .header-text {
            text-align: center;
            padding: 0 100px;
        }

        .header-text p {
            margin: 0;
            line-height: 1.2;
        }

        .header-line1 {
            font-size: 11pt;
            font-weight: bold;
        }

        .header-line2 {
            font-size: 12pt;
            font-weight: bold;
        }

        .header-line3 {
            font-size: 9pt;
            margin-top: 2px;
        }

        .divider {
            border: none;
            border-top: 3px solid #000;
            margin: 8px 0;
        }

        /* TITLE */
        .title {
            text-align: center;
            margin: 20px 0 8px 0;
        }

        .title p {
            margin: 0;
            line-height: 1.2;
        }

        .title-line1 {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
        }

        .nomor-surat {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 18px;
        }

        /* CONTENT */
        .content {
            text-align: justify;
            font-size: 12pt;
            line-height: 1.5;
        }

        .content > p {
            margin: 8px 0;
        }

        /* DATA SECTION */
        .data-block {
            margin: 10px 0 10px 40px;
        }

        .data-block table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-block td {
            padding: 1px 0;
            vertical-align: top;
            line-height: 1.4;
        }

        .data-block .label {
            width: 180px;
        }

        .data-block .colon {
            width: 15px;
        }

        /* SIGNATURE AREA - Not needed anymore, using inline styles */

        /* PRINT */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                margin: 2.5cm 2cm 2cm 2cm;
            }
        }

        /* BUTTON STYLES */
        .button-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-radius: 12px;
        }

        .button-container h3 {
            color: white;
            margin-bottom: 15px;
        }

        .btn {
            padding: 12px 35px;
            font-size: 12pt;
            margin: 8px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-print {
            background: white;
            color: #3498db;
        }

        .btn-secondary {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- HEADER -->
        <div class="header-wrapper">
            <div class="header-logo">
                <img src="assets/img/logo.png" alt="Logo" onerror="this.style.display='none'">
            </div>
            <div class="header-text">
                <p class="header-line1">PEMERINTAH KOTA BANJARMASIN</p>
                <p class="header-line2">DINAS PENGENDALIAN PENDUDUK, KELUARGA BERENCANA DAN PEMBERDAYAAN MASYARAKAT</p>
                <p class="header-line3">JL. Brigjen H. Hasan Basri - Kayutangi II RT.16 Banjarmasin 70124</p>
                <p class="header-line3">Pos-el : dppkbpm@gmail.go.id, Laman http://dppkbpm.banjarmasinkota.go.id</p>
            </div>
        </div>

        <div class="divider"></div>

        <!-- TITLE -->
        <div class="title">
            <p class="title-line1">SURAT KETERANGAN</p>
        </div>

        <!-- NOMOR SURAT -->
        <p class="nomor-surat">
            Nomor : 800.1.6.6/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/DPPKBPM-BJM/<?= $tahun_sekarang ?>
        </p>

        <!-- CONTENT -->
        <div class="content">
            <p>Yang bertanda tangan di bawah ini :</p>

            <div class="data-block">
                <table>
                    <tr>
                        <td class="label">N a m a</td>
                        <td class="colon">:</td>
                        <td><?= htmlspecialchars($nama_kepala) ?></td>
                    </tr>
                    <tr>
                        <td class="label">N I P</td>
                        <td class="colon">:</td>
                        <td><?= htmlspecialchars($nip_kepala) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Pangkat / Golongan</td>
                        <td class="colon">:</td>
                        <td><?= htmlspecialchars($pangkat_kepala) ?> / <?= htmlspecialchars($golongan_kepala) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Jabatan</td>
                        <td class="colon">:</td>
                        <td><?= htmlspecialchars($jabatan_kepala) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Unit Kerja</td>
                        <td class="colon">:</td>
                        <td>Dinas Pengendalian Penduduk Keluarga Berencana dan Pemberdayaan Masyarakat</td>
                    </tr>
                </table>
            </div>

            <p>Dengan ini menerangkan bahwa</p>

            <div class="data-block">
                <table>
                    <tr>
                        <td class="label">N a m a</td>
                        <td class="colon">:</td>
                        <td><?= htmlspecialchars($nama_pegawai) ?></td>
                    </tr>
                    <tr>
                        <td class="label">N I P</td>
                        <td class="colon">:</td>
                        <td><?= htmlspecialchars($nip_pegawai) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Pangkat / Golongan</td>
                        <td class="colon">:</td>
                        <td><?= htmlspecialchars($pangkat_lama) ?> / <?= htmlspecialchars($golongan_lama) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Jabatan</td>
                        <td class="colon">:</td>
                        <td><?= htmlspecialchars($jabatan_baru) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Unit Kerja</td>
                        <td class="colon">:</td>
                        <td>Dinas Pengendalian Penduduk Keluarga Berencana dan Pemberdayaan Masyarakat</td>
                    </tr>
                </table>
            </div>

            <p>Adalah benar Pegawai Negeri Sipil (PNS) pada Dinas Pengendalian Penduduk, Keluarga Berencana dan Pemberdayaan Masyarakat yang melakukan Kegiatan sebagai <?= htmlspecialchars($jabatan_baru) ?></p>

            <p>Surat Keterangan ini diberikan untuk melengkapi usul naik pangkat dari <?= htmlspecialchars($pangkat_lama) ?> <?= htmlspecialchars($golongan_lama) ?> Ke <?= htmlspecialchars($pangkat_baru) ?> <?= htmlspecialchars($golongan_baru) ?></p>

            <p>Demikian surat keterangan ini diberikan, untuk dapat dipergunakan sebagaimana mestinya.</p>
        </div>

        <!-- SIGNATURE AREA -->
        <table style="width: 100%; font-size: 11px; margin-top: 20px;">
            <tr>
                <td style="width: 40%; vertical-align: top;">
                    <table style="width: 200px; border-collapse: collapse; border: 1px solid black;">
                        <tr>
                            <td colspan="2" style="text-align: center; border: 1px solid black; font-weight: bold;">Paraf</td>
                        </tr>
                        <tr>
                            <td style="height: 25px; border: 1px solid black; padding: 4px 6px;">Sekretaris</td>
                            <td style="width: 60px; border: 1px solid black;"></td>
                        </tr>
                        <tr>
                            <td style="height: 25px; border: 1px solid black; padding: 4px 6px;">Kasubag Umpeg</td>
                            <td style="border: 1px solid black;"></td>
                        </tr>
                    </table>
                </td>
                <td style="width: 60%; text-align: center; vertical-align: top;">
                    <p style="margin: 2px 0; line-height: 1.3;">Banjarmasin, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= $bulan_sekarang ?> <?= $tahun_sekarang ?></p>
                    <p style="margin: 2px 0; line-height: 1.3;"><strong>Kepala Dinas,</strong></p>
                    <div style="height: 50px;"></div>
                    <p style="margin: 2px 0; line-height: 1.3; text-decoration: underline; font-weight: bold;"><?= htmlspecialchars($nama_kepala) ?></p>
                    <p style="margin: 2px 0; line-height: 1.3;"><?= htmlspecialchars($pangkat_kepala) ?> / <?= htmlspecialchars($golongan_kepala) ?></p>
                    <p style="margin: 2px 0; line-height: 1.3;">NIP. <?= htmlspecialchars($nip_kepala) ?></p>
                </td>
            </tr>
        </table>
    </div>

    <!-- BUTTONS -->
    <div class="no-print button-container">
        <h3>📄 Surat Keterangan PNS</h3>
        <button onclick="window.print()" class="btn btn-print">
            🖨️ Cetak / Download PDF
        </button>
        <button onclick="window.close()" class="btn btn-secondary">✖️ Tutup</button>
    </div>

</body>
</html>