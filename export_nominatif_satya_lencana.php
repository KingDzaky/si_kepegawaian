<?php
session_start();
require_once 'config/koneksi.php';

// Filter tahun dari GET parameter
$filter_tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Query untuk mengambil daftar pegawai berdasarkan tahun
$query = "SELECT 
    k.id,
    k.nama,
    k.nip,
    k.pangkat_baru,
    k.golongan_baru,
    k.jabatan_baru,
    k.nomor_usulan,
    k.tanggal_usulan,
    k.tempat_lahir,
    k.pendidikan_terakhir,
    k.prodi
FROM kenaikan_pangkat k
WHERE YEAR(k.tanggal_usulan) = ?
ORDER BY k.nama ASC";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $filter_tahun);
$stmt->execute();
$result = $stmt->get_result();

// Data Kepala Dinas
$query_kepala = "SELECT * FROM kepala_opd WHERE status = 'aktif' LIMIT 1";
$result_kepala = $koneksi->query($query_kepala);
$kepala = $result_kepala->fetch_assoc();

$gelar_depan = !empty($kepala['gelar_depan']) ? $kepala['gelar_depan'] . ' ' : '';
$gelar_belakang = !empty($kepala['gelar_belakang']) ? ', ' . $kepala['gelar_belakang'] : '';
$nama_kepala = $gelar_depan . $kepala['nama'] . $gelar_belakang;

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
    <title>Nominatif Pegawai Satya Lencana Tahun <?= $filter_tahun ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 2cm 1.5cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            padding: 20px;
        }

        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 12pt;
            margin: 3px 0;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* SIGNATURE */
        .signature {
            margin-top: 30px;
            text-align: right;
        }

        .signature p {
            margin: 3px 0;
            line-height: 1.3;
        }

        .signature .space {
            height: 60px;
        }

        /* PRINT */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
            }

            @page {
                margin: 2cm 1.5cm;
            }
        }

        /* BUTTON STYLES */
        .button-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #667eea;
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
        <div class="header">
            <h2>DAFTAR NOMINATIF PEGAWAI SATYA LENCANA</h2>
            <p><strong>DINAS PENGENDALIAN PENDUDUK, KELUARGA BERENCANA DAN PEMBERDAYAAN MASYARAKAT</strong></p>
            <p><strong>KOTA BANJARMASIN</strong></p>
            <p>TAHUN <?= $filter_tahun ?></p>
        </div>

        <!-- TABLE -->
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 3%;">No</th>
                    <th rowspan="2" style="width: 20%;">Nama / NIP</th>
                    <th rowspan="2" style="width: 12%;">Tempat, Tanggal Lahir</th>
                    <th colspan="2">Pangkat / Golongan</th>
                    <th rowspan="2" style="width: 20%;">Jabatan</th>
                    <th rowspan="2" style="width: 10%;">Pendidikan</th>
                    <th rowspan="2" style="width: 10%;">Tanggal Usulan</th>
                </tr>
                <tr>
                    <th style="width: 12%;">Lama</th>
                    <th style="width: 12%;">Baru</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result->num_rows > 0):
                    $no = 1;
                    while($row = $result->fetch_assoc()): 
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td>
                        <strong><?= htmlspecialchars($row['nama']) ?></strong><br>
                        <small>NIP: <?= htmlspecialchars($row['nip']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($row['tempat_lahir']) ?></td>
                    <td class="text-center">
                        <?= htmlspecialchars($row['pangkat_baru']) ?><br>
                        (<?= htmlspecialchars($row['golongan_baru']) ?>)
                    </td>
                    <td class="text-center">
                        <?= htmlspecialchars($row['pangkat_baru']) ?><br>
                        (<?= htmlspecialchars($row['golongan_baru']) ?>)
                    </td>
                    <td><?= htmlspecialchars($row['jabatan_baru']) ?></td>
                    <td class="text-center">
                        <?= htmlspecialchars($row['pendidikan_terakhir']) ?><br>
                        <?= htmlspecialchars($row['prodi']) ?>
                    </td>
                    <td class="text-center"><?= date('d-m-Y', strtotime($row['tanggal_usulan'])) ?></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="8" class="text-center">
                        <em>Tidak ada data pegawai pada tahun <?= $filter_tahun ?></em>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- SIGNATURE -->
        <div class="signature">
            <p>Banjarmasin, <?= $tanggal_sekarang ?> <?= $bulan_sekarang ?> <?= $tahun_sekarang ?></p>
            <p><strong>KEPALA DINAS,</strong></p>
            <div class="space"></div>
            <p style="text-decoration: underline; font-weight: bold;"><?= htmlspecialchars($nama_kepala) ?></p>
            <p><?= htmlspecialchars($kepala['pangkat']) ?> (<?= htmlspecialchars($kepala['golongan']) ?>)</p>
            <p>NIP. <?= htmlspecialchars($kepala['nip']) ?></p>
        </div>
    </div>

    <!-- BUTTONS -->
    <div class="no-print button-container">
        <h3>📊 Nominatif Pegawai Satya Lencana Tahun <?= $filter_tahun ?></h3>
        <button onclick="window.print()" class="btn btn-print">
            🖨️ Cetak / Download PDF
        </button>
        <button onclick="window.close()" class="btn btn-secondary">✖️ Tutup</button>
        <a href="list_surat_satya_lencana.php" class="btn btn-secondary">
            ⬅️ Kembali
        </a>
    </div>

</body>
</html>
<?php 
$stmt->close();
$koneksi->close();
?>