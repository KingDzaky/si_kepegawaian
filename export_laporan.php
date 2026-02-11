<?php
session_start();
require_once 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query data
$query = "SELECT 
    k.id,
    k.nomor_usulan,
    k.tanggal_usulan,
    k.nip,
    k.nama,
    k.pangkat_lama,
    k.golongan_lama,
    k.pangkat_baru,
    k.golongan_baru,
    k.jabatan_baru,
    k.status,
    k.keterangan,
    k.created_at
FROM kenaikan_pangkat k
WHERE 1=1";

if ($filter_status !== 'all') {
    if ($filter_status == 'draft') {
        $query .= " AND (k.status = 'draft' OR k.status = '' OR k.status IS NULL)";
    } else {
        $query .= " AND k.status = '" . $koneksi->real_escape_string($filter_status) . "'";
    }
}

if (!empty($filter_tahun)) {
    $query .= " AND YEAR(k.created_at) = " . (int)$filter_tahun;
}

if (!empty($search)) {
    $query .= " AND (k.nama LIKE '%" . $koneksi->real_escape_string($search) . "%' 
                OR k.nip LIKE '%" . $koneksi->real_escape_string($search) . "%')";
}

$query .= " ORDER BY k.created_at DESC";
$result = $koneksi->query($query);

// Set header untuk download Excel
$filename = "Laporan_Kenaikan_Pangkat_" . $filter_tahun . "_" . date('YmdHis') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kenaikan Pangkat</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN KENAIKAN PANGKAT</h2>
        <h3>DINAS PENGENDALIAN PENDUDUK, KELUARGA BERENCANA DAN PEMBERDAYAAN MASYARAKAT</h3>
        <h3>KOTA BANJARMASIN</h3>
        <p>Tahun: <?= $filter_tahun ?></p>
        <?php if($filter_status != 'all'): ?>
        <p>Status: <?= strtoupper($filter_status) ?></p>
        <?php endif; ?>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Usulan</th>
                <th>Nama</th>
                <th>NIP</th>
                <th>Pangkat Lama</th>
                <th>Golongan Lama</th>
                <th>Pangkat Baru</th>
                <th>Golongan Baru</th>
                <th>Jabatan Baru</th>
                <th>Tanggal Usul</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while($row = $result->fetch_assoc()): 
                $status = $row['status'] ?: 'draft';
                $status_text = 'Belum Diajukan';
                
                if($status == 'diajukan') $status_text = 'Diajukan';
                elseif($status == 'disetujui') $status_text = 'Disetujui';
                elseif($status == 'ditolak') $status_text = 'Ditolak';
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nomor_usulan']) ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['nip']) ?></td>
                <td><?= htmlspecialchars($row['pangkat_lama']) ?></td>
                <td><?= htmlspecialchars($row['golongan_lama']) ?></td>
                <td><?= htmlspecialchars($row['pangkat_baru']) ?></td>
                <td><?= htmlspecialchars($row['golongan_baru']) ?></td>
                <td><?= htmlspecialchars($row['jabatan_baru']) ?></td>
                <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                <td><?= $status_text ?></td>
                <td><?= htmlspecialchars($row['keterangan'] ?? '') ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <br><br>
    <p>Dicetak pada: <?= date('d F Y, H:i:s') ?></p>
</body>
</html>
<?php $koneksi->close(); ?>

// Set header untuk download Excel
$filename = "Laporan_Kenaikan_Pangkat_" . $filter_tahun . "_" . date('YmdHis') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kenaikan Pangkat</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN KENAIKAN PANGKAT</h2>
        <h3>DINAS PENGENDALIAN PENDUDUK, KELUARGA BERENCANA DAN PEMBERDAYAAN MASYARAKAT</h3>
        <h3>KOTA BANJARMASIN</h3>
        <p>Tahun: <?= $filter_tahun ?></p>
        <?php if($filter_status != 'all'): ?>
        <p>Status: <?= strtoupper($filter_status) ?></p>
        <?php endif; ?>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIP</th>
                <th>Pangkat Lama</th>
                <th>Golongan Lama</th>
                <th>Pangkat Baru</th>
                <th>Golongan Baru</th>
                <th>Jabatan Baru</th>
                <th>Tanggal Usul</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while($row = $result->fetch_assoc()): 
                $status = $row['status'] ?: 'draft';
                $status_text = 'Belum Diajukan';
                
                if($status == 'diajukan') $status_text = 'Diajukan';
                elseif($status == 'disetujui') $status_text = 'Disetujui';
                elseif($status == 'ditolak') $status_text = 'Ditolak';
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['nip']) ?></td>
                <td><?= htmlspecialchars($row['pangkat_lama']) ?></td>
                <td><?= htmlspecialchars($row['golongan_lama']) ?></td>
                <td><?= htmlspecialchars($row['pangkat_baru']) ?></td>
                <td><?= htmlspecialchars($row['golongan_baru']) ?></td>
                <td><?= htmlspecialchars($row['jabatan_baru']) ?></td>
                <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                <td><?= $status_text ?></td>
                <td><?= htmlspecialchars($row['catatan_approval'] ?? '') ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <br><br>
    <p>Dicetak pada: <?= date('d F Y, H:i:s') ?></p>
</body>
</html>
<?php $koneksi->close(); ?>