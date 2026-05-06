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
$filter_eselon = isset($_GET['eselon']) ? $_GET['eselon'] : '';

// Query data DUK
$query = "SELECT 
    id, nama, nip, kartu_pegawai, ttl, status_pegawai, 
    pangkat_terakhir, golongan, tmt_pangkat,
    jabatan_terakhir, eselon, jenis_jabatan, jft_tingkat, jfu_kelas,
    tmt_eselon, pendidikan_terakhir, prodi, jenis_kelamin
FROM duk WHERE 1=1";

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
if (!empty($filter_eselon)) {
    if ($filter_eselon === 'HAS_ESELON') {
        $query .= " AND (eselon IS NOT NULL AND eselon != '' AND eselon != 'Non-Eselon' AND eselon NOT LIKE '%non%')";
    } elseif ($filter_eselon === 'NO_ESELON') {
        $query .= " AND (eselon IS NULL OR eselon = '' OR eselon = 'Non-Eselon' OR eselon LIKE '%non%')";
    }
}

$query .= " ORDER BY golongan DESC, nama ASC";
$result = $koneksi->query($query);

// Hitung statistik
$total_pegawai = $result->num_rows;
$laki = $koneksi->query("SELECT COUNT(*) as total FROM duk WHERE jenis_kelamin = 'Laki-laki'")->fetch_assoc()['total'];
$perempuan = $koneksi->query("SELECT COUNT(*) as total FROM duk WHERE jenis_kelamin = 'Perempuan'")->fetch_assoc()['total'];

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
    <title>Daftar Urusan Kepegawaian</title>
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
            font-size: 9pt;
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
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .stat-value {
            font-size: 24pt;
            font-weight: bold;
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 8pt;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background: #021296;
            color: white;
            padding: 8px 5px;
            font-size: 8pt;
            font-weight: bold;
            text-align: left;
            border: 1px solid #555;
        }
        
        td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            font-size: 8pt;
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
            background: #021296;
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
            background: #5568d3;
        }

        .btn-close {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #021296;
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
            background: #5568d3;
        }
        
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            display: inline-block;
        }
        
        .badge-eselon {
            background: #4facfe;
            color: white;
        }
        
        .badge-jft {
            background: #f093fb;
            color: white;
        }
        
        .badge-jfu {
            background: #a8edea;
            color: #333;
        }
        
        .text-center {
            text-align: center;
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
        <h1>DAFTAR URUSAN KEPEGAWAIAN</h1>
        <h2>DINAS PENGENDALIAN PENDUDUK, KELUARGA BERENCANA</h2>
        <h2>DAN PEMBERDAYAAN MASYARAKAT KOTA BANJARMASIN</h2>
    </div>
    
    <!-- Statistik -->
    <div class="stats-container">
        <div class="stat-box">
            <span class="stat-value"><?= $total_pegawai ?></span>
            <span class="stat-label">Total Pegawai</span>
        </div>
        <div class="stat-box">
            <span class="stat-value" style="color: #4facfe;"><?= $laki ?></span>
            <span class="stat-label">Laki-laki</span>
        </div>
        <div class="stat-box">
            <span class="stat-value" style="color: #f093fb;"><?= $perempuan ?></span>
            <span class="stat-label">Perempuan</span>
        </div>
    </div>
    
    <!-- Tabel Data -->
    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 15%;">Nama</th>
                <th style="width: 12%;">NIP</th>
                <th style="width: 10%;">Pangkat/Gol</th>
                <th style="width: 18%;">Jabatan</th>
                <th style="width: 8%;">Eselon</th>
                <th style="width: 10%;">JFT/JFU</th>
                <th style="width: 5%;">L/P</th>
                <th style="width: 8%;">Pendidikan</th>
                <th style="width: 8%;">TMT</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if($result->num_rows > 0):
                while($row = $result->fetch_assoc()): 
                    $isNonEselon = empty($row['eselon']) || 
                                   $row['eselon'] === '-' || 
                                   $row['eselon'] === 'Non-Eselon' ||
                                   stripos($row['eselon'], 'non') !== false;
                    
                    $jftJfu = '-';
                    $badgeClass = '';
                    
                    if ($isNonEselon) {
                        if ($row['jenis_jabatan'] === 'JFT') {
                            $jftJfu = 'JFT ' . ($row['jft_tingkat'] ?: '');
                            $badgeClass = 'badge-jft';
                        } elseif ($row['jenis_jabatan'] === 'JFU') {
                            $jftJfu = 'JFU Kelas ' . ($row['jfu_kelas'] ?: '');
                            $badgeClass = 'badge-jfu';
                        }
                    }
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><strong><?= htmlspecialchars($row['nama']) ?></strong></td>
                <td><?= htmlspecialchars($row['nip']) ?></td>
                <td>
                    <?= htmlspecialchars($row['pangkat_terakhir']) ?><br>
                    <small style="color: #666;"><?= htmlspecialchars($row['golongan']) ?></small>
                </td>
                <td><?= htmlspecialchars($row['jabatan_terakhir']) ?></td>
                <td>
                    <?php if(!$isNonEselon): ?>
                        <span class="badge badge-eselon"><?= htmlspecialchars($row['eselon']) ?></span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($jftJfu !== '-'): ?>
                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($jftJfu) ?></span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="text-center"><?= $row['jenis_kelamin'] === 'Laki-laki' ? 'L' : 'P' ?></td>
                <td><?= htmlspecialchars($row['pendidikan_terakhir']) ?></td>
                <td><?= htmlspecialchars($row['tmt_pangkat']) ?></td>
                <td><?= htmlspecialchars($row['status_pegawai']) ?></td>
            </tr>
            <?php 
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="10" class="text-center" style="padding: 30px; color: #999;">
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
    $filename = "DUK_Export_" . date('YmdHis') . ".xls";
    
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
            th { background-color: #4CAF50; color: white; font-weight: bold; }
            .header { text-align: center; font-weight: bold; }
        </style>
    </head>
    <body>
        <table>
            <tr><td colspan="12" class="header">DAFTAR URUSAN KEPEGAWAIAN</td></tr>
            <tr><td colspan="12" class="header">DINAS PENGENDALIAN PENDUDUK KB DAN PEMBERDAYAAN MASYARAKAT</td></tr>
            <tr><td colspan="12" class="header">KOTA BANJARMASIN</td></tr>
            <tr><td colspan="12">&nbsp;</td></tr>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIP</th>
                <th>TTL</th>
                <th>L/P</th>
                <th>Pangkat/Gol</th>
                <th>Jabatan</th>
                <th>Pendidikan</th>
                <th>TMT Pangkat</th>
                <th>Eselon</th>
                <th>Jenis Jabatan</th>
                <th>JFT/JFU</th>
                <th>Status</th>
            </tr>
            <?php 
            $no = 1;
            while($row = $result->fetch_assoc()): 
                $isNonEselon = empty($row['eselon']) || $row['eselon'] === '-' || $row['eselon'] === 'Non-Eselon';
                $jftJfu = '-';
                if ($isNonEselon) {
                    if ($row['jenis_jabatan'] === 'JFT') {
                        $jftJfu = 'JFT ' . ($row['jft_tingkat'] ?: '');
                    } elseif ($row['jenis_jabatan'] === 'JFU') {
                        $jftJfu = 'JFU Kelas ' . ($row['jfu_kelas'] ?: '');
                    }
                }
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['nip']) ?></td>
                <td><?= htmlspecialchars($row['ttl']) ?></td>
                <td><?= $row['jenis_kelamin'] === 'Laki-laki' ? 'L' : 'P' ?></td>
                <td><?= htmlspecialchars($row['pangkat_terakhir']) ?>/<?= htmlspecialchars($row['golongan']) ?></td>
                <td><?= htmlspecialchars($row['jabatan_terakhir']) ?></td>
                <td><?= htmlspecialchars($row['pendidikan_terakhir']) ?></td>
                <td><?= htmlspecialchars($row['tmt_pangkat']) ?></td>
                <td><?= htmlspecialchars($row['eselon']) ?></td>
                <td><?= htmlspecialchars($row['jenis_jabatan']) ?></td>
                <td><?= htmlspecialchars($jftJfu) ?></td>
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