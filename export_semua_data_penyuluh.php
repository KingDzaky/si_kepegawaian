<?php
session_start();
require_once 'config/koneksi.php';
require_once 'check_session.php';

if (!isAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

// Ambil parameter
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf'; // pdf atau excel
$filter_gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$filter_pendidikan = isset($_GET['pendidikan']) ? $_GET['pendidikan'] : '';
$filter_golongan = isset($_GET['golongan']) ? $_GET['golongan'] : '';

// Query data Penyuluh
$query = "SELECT 
    id, nama, nip, ttl, 
    pangkat_terakhir, golongan, tmt_pangkat,
    jabatan_terakhir, pendidikan_terakhir, jenis_kelamin, created_at
FROM penyuluh WHERE 1=1";

// Apply filters
if (!empty($filter_gender)) {
    $query .= " AND jenis_kelamin = '" . $koneksi->real_escape_string($filter_gender) . "'";
}
if (!empty($filter_pendidikan)) {
    $query .= " AND pendidikan_terakhir = '" . $koneksi->real_escape_string($filter_pendidikan) . "'";
}
if (!empty($filter_golongan)) {
    $query .= " AND golongan = '" . $koneksi->real_escape_string($filter_golongan) . "'";
}

$query .= " ORDER BY golongan DESC, nama ASC";
$result = $koneksi->query($query);

// Hitung statistik
$total_penyuluh = $result->num_rows;
$laki = $koneksi->query("SELECT COUNT(*) as total FROM penyuluh WHERE jenis_kelamin = 'Laki-laki'")->fetch_assoc()['total'];
$perempuan = $koneksi->query("SELECT COUNT(*) as total FROM penyuluh WHERE jenis_kelamin = 'Perempuan'")->fetch_assoc()['total'];

// Export berdasarkan format
if ($format === 'excel') {
    exportToExcel($result);
    exit;
}

// Export PDF
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Penyuluh KB</title>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
            
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .no-print {
                display: none;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #333;
            padding: 10mm;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #333;
        }
        
        .header h1 {
            font-size: 14pt;
            margin: 3px 0;
            color: #333;
        }
        
        .header h2 {
            font-size: 11pt;
            margin: 2px 0;
            color: #555;
            font-weight: normal;
        }
        
        .header p {
            font-size: 9pt;
            margin: 5px 0 0 0;
            color: #666;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            gap: 10px;
        }
        
        .stat-box {
            flex: 1;
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .stat-value {
            font-size: 28pt;
            font-weight: bold;
            color: #28a745;
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 9pt;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background: #28a745;
            color: white;
            padding: 10px 6px;
            font-size: 9pt;
            font-weight: bold;
            text-align: left;
            border: 1px solid #1e7e34;
        }
        
        td {
            padding: 8px 6px;
            border: 1px solid #ddd;
            font-size: 9pt;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #ddd;
            font-size: 8pt;
            color: #666;
            display: flex;
            justify-content: space-between;
        }
        
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .btn-print:hover {
            background: #218838;
        }

        .btn-close {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #28a745;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .btn-close:hover {
            background: #218838;
        }
        
        
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            display: inline-block;
        }
        
        .badge-gender-l {
            background: #007bff;
            color: white;
        }
        
        .badge-gender-p {
            background: #e83e8c;
            color: white;
        }
        
        .text-center {
            text-align: center;
        }
        
        small {
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <button onclick="window.print()" class="btn-print no-print">
        🖨️ Cetak PDF
    </button>
    <button onclick="window.close()" class="btn btn-close no-print">
        ✖️ Tutup
    </button>
    <!-- Header -->
    <div class="header">
        <h1>DAFTAR PENYULUH KELUARGA BERENCANA</h1>
        <h2>DINAS PENGENDALIAN PENDUDUK, KELUARGA BERENCANA</h2>
        <h2>DAN PEMBERDAYAAN MASYARAKAT KOTA BANJARMASIN</h2>
    </div>
    
    <!-- Statistik -->
    <div class="stats-container">
        <div class="stat-box">
            <span class="stat-value"><?= $total_penyuluh ?></span>
            <span class="stat-label">Total Penyuluh</span>
        </div>
        <div class="stat-box">
            <span class="stat-value" style="color: #007bff;"><?= $laki ?></span>
            <span class="stat-label">Laki-laki</span>
        </div>
        <div class="stat-box">
            <span class="stat-value" style="color: #e83e8c;"><?= $perempuan ?></span>
            <span class="stat-label">Perempuan</span>
        </div>
    </div>
    
    <!-- Tabel Data -->
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 18%;">Nama</th>
                <th style="width: 14%;">NIP</th>
                <th style="width: 15%;">Tempat/Tanggal Lahir</th>
                <th style="width: 12%;">Pangkat/Gol</th>
                <th style="width: 15%;">Jabatan</th>
                <th style="width: 10%;">TMT Pangkat</th>
                <th style="width: 7%;">L/P</th>
                <th style="width: 10%;">Pendidikan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if($result->num_rows > 0):
                while($row = $result->fetch_assoc()): 
                    $genderBadge = $row['jenis_kelamin'] === 'Laki-laki' ? 'badge-gender-l' : 'badge-gender-p';
                    $genderText = $row['jenis_kelamin'] === 'Laki-laki' ? 'L' : 'P';
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><strong><?= htmlspecialchars($row['nama']) ?></strong></td>
                <td><?= htmlspecialchars($row['nip'] ?: '-') ?></td>
                <td><?= htmlspecialchars($row['ttl'] ?: '-') ?></td>
                <td>
                    <?= htmlspecialchars($row['pangkat_terakhir'] ?: '-') ?><br>
                    <small><?= htmlspecialchars($row['golongan'] ?: '-') ?></small>
                </td>
                <td><?= htmlspecialchars($row['jabatan_terakhir'] ?: '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['tmt_pangkat'] ?: '-') ?></td>
                <td class="text-center">
                    <span class="badge <?= $genderBadge ?>"><?= $genderText ?></span>
                </td>
                <td><?= htmlspecialchars($row['pendidikan_terakhir'] ?: '-') ?></td>
            </tr>
            <?php 
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="9" class="text-center" style="padding: 30px; color: #999;">
                    Tidak ada data yang ditampilkan
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Footer -->
    <div class="footer">
        <div>Dicetak pada: <?= date('d F Y, H:i:s') ?> WIB</div>
        <div>Halaman 1 dari 1</div>
    </div>
</body>
</html>
<?php
$koneksi->close();

// ============================================
// FUNGSI EXPORT EXCEL
// ============================================
function exportToExcel($result) {
    $filename = "Penyuluh_Export_" . date('YmdHis') . ".xls";
    
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid black; padding: 5px; text-align: left; }
            th { background-color: #28a745; color: white; font-weight: bold; }
            .header { text-align: center; font-weight: bold; }
        </style>
    </head>
    <body>
        <table>
            <tr><td colspan="10" class="header">DAFTAR PENYULUH KELUARGA BERENCANA</td></tr>
            <tr><td colspan="10" class="header">DINAS PENGENDALIAN PENDUDUK KB DAN PEMBERDAYAAN MASYARAKAT</td></tr>
            <tr><td colspan="10" class="header">KOTA BANJARMASIN</td></tr>
            <tr><td colspan="10">&nbsp;</td></tr>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIP</th>
                <th>Tempat/Tanggal Lahir</th>
                <th>Jenis Kelamin</th>
                <th>Pangkat Terakhir</th>
                <th>Golongan</th>
                <th>TMT Pangkat</th>
                <th>Jabatan Terakhir</th>
                <th>Pendidikan Terakhir</th>
            </tr>
            <?php 
            $no = 1;
            while($row = $result->fetch_assoc()): 
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['nip'] ?: '-') ?></td>
                <td><?= htmlspecialchars($row['ttl'] ?: '-') ?></td>
                <td><?= htmlspecialchars($row['jenis_kelamin']) ?></td>
                <td><?= htmlspecialchars($row['pangkat_terakhir'] ?: '-') ?></td>
                <td><?= htmlspecialchars($row['golongan'] ?: '-') ?></td>
                <td><?= htmlspecialchars($row['tmt_pangkat'] ?: '-') ?></td>
                <td><?= htmlspecialchars($row['jabatan_terakhir'] ?: '-') ?></td>
                <td><?= htmlspecialchars($row['pendidikan_terakhir'] ?: '-') ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <p>Dicetak pada: <?= date('d F Y, H:i:s') ?> WIB</p>
    </body>
    </html>
    <?php
}
?>