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

// Hitung statistik
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'diajukan' THEN 1 ELSE 0 END) as diajukan,
    SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
    SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
    SUM(CASE WHEN status = 'draft' OR status = '' OR status IS NULL THEN 1 ELSE 0 END) as belum_diajukan
FROM kenaikan_pangkat
WHERE YEAR(created_at) = " . (int)$filter_tahun;

$stats_result = $koneksi->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kenaikan Pangkat</title>
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
            font-size: 16pt;
            margin: 3px 0;
            color: #333;
        }
        
        .header h2 {
            font-size: 12pt;
            margin: 2px 0;
            color: #555;
            font-weight: normal;
        }
        
        .header p {
            font-size: 10pt;
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
            color: #667eea;
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
            background: #667eea;
            color: white;
            padding: 10px 6px;
            font-size: 9pt;
            font-weight: bold;
            text-align: left;
            border: 1px solid #555;
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
        
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            display: inline-block;
        }
        
        .status-diajukan {
            background: #f093fb;
            color: white;
        }
        
        .status-disetujui {
            background: #4facfe;
            color: white;
        }
        
        .status-ditolak {
            background: #fa709a;
            color: white;
        }
        
        .status-draft {
            background: #e0e0e0;
            color: #333;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #ddd;
            font-size: 9pt;
            color: #666;
            display: flex;
            justify-content: space-between;
        }
        
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #667eea;
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
        <h1>LAPORAN KENAIKAN PANGKAT</h1>
        <h2>DINAS PENGENDALIAN PENDUDUK, KELUARGA BERENCANA</h2>
        <h2>DAN PEMBERDAYAAN MASYARAKAT KOTA BANJARMASIN</h2>
        <p>Tahun: <strong><?= $filter_tahun ?></strong>
        <?php if($filter_status != 'all'): ?>
            | Status: <strong><?= strtoupper($filter_status) ?></strong>
        <?php endif; ?>
        </p>
    </div>
    
    <!-- Statistik -->
    <div class="stats-container">
        <div class="stat-box">
            <span class="stat-value"><?= $stats['total'] ?></span>
            <span class="stat-label">Total Usulan</span>
        </div>
        <div class="stat-box">
            <span class="stat-value" style="color: #f093fb;"><?= $stats['diajukan'] ?></span>
            <span class="stat-label">Diajukan</span>
        </div>
        <div class="stat-box">
            <span class="stat-value" style="color: #4facfe;"><?= $stats['disetujui'] ?></span>
            <span class="stat-label">Disetujui</span>
        </div>
        <div class="stat-box">
            <span class="stat-value" style="color: #fa709a;"><?= $stats['ditolak'] ?></span>
            <span class="stat-label">Ditolak</span>
        </div>
        <div class="stat-box">
            <span class="stat-value" style="color: #999;"><?= $stats['belum_diajukan'] ?></span>
            <span class="stat-label">Belum Diajukan</span>
        </div>
    </div>
    
    <!-- Tabel Data -->
    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 11%;">Nomor Usulan</th>
                <th style="width: 15%;">Nama</th>
                <th style="width: 12%;">NIP</th>
                <th style="width: 12%;">Pangkat Lama</th>
                <th style="width: 12%;">Pangkat Baru</th>
                <th style="width: 18%;">Jabatan Baru</th>
                <th style="width: 8%;">Tanggal</th>
                <th style="width: 7%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if($result->num_rows > 0):
                while($row = $result->fetch_assoc()): 
                    $status = $row['status'] ?: 'draft';
                    $status_class = 'status-draft';
                    $status_text = 'Belum';
                    
                    if($status == 'diajukan') {
                        $status_class = 'status-diajukan';
                        $status_text = 'Diajukan';
                    } elseif($status == 'disetujui') {
                        $status_class = 'status-disetujui';
                        $status_text = 'Disetujui';
                    } elseif($status == 'ditolak') {
                        $status_class = 'status-ditolak';
                        $status_text = 'Ditolak';
                    }
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nomor_usulan']) ?></td>
                <td><strong><?= htmlspecialchars($row['nama']) ?></strong></td>
                <td><?= htmlspecialchars($row['nip']) ?></td>
                <td>
                    <?= htmlspecialchars($row['pangkat_lama']) ?><br>
                    <small><?= htmlspecialchars($row['golongan_lama']) ?></small>
                </td>
                <td>
                    <?= htmlspecialchars($row['pangkat_baru']) ?><br>
                    <small><?= htmlspecialchars($row['golongan_baru']) ?></small>
                </td>
                <td><?= htmlspecialchars($row['jabatan_baru']) ?></td>
                <td style="text-align: center;"><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                <td style="text-align: center;">
                    <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                </td>
            </tr>
            <?php 
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="9" style="text-align: center; padding: 30px; color: #999;">
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
<?php $koneksi->close(); ?>