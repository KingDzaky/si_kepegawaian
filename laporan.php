<?php
session_start();
require_once 'check_session.php';
// Tidak ada proteksi role, semua user yang login bisa akses

require_once 'config/koneksi.php';

// DEBUG: Cek koneksi database
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$page_title = "Laporan Kenaikan Pangkat";

// Ambil tahun terbaru dari data (bukan tahun sekarang)
$default_tahun_query = "SELECT YEAR(tanggal_usulan) as tahun 
                        FROM kenaikan_pangkat 
                        WHERE tanggal_usulan IS NOT NULL
                        ORDER BY tanggal_usulan DESC 
                        LIMIT 1";
$default_tahun_result = $koneksi->query($default_tahun_query);

// Set default tahun
$default_tahun = date('Y'); // Fallback ke tahun sekarang
if ($default_tahun_result && $default_tahun_result->num_rows > 0) {
    $row_tahun = $default_tahun_result->fetch_assoc();
    $default_tahun = $row_tahun['tahun'];
}

// Filter - PERBAIKAN: Default ke tahun data terbaru
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : $default_tahun; // GUNAKAN TAHUN DATA TERBARU
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query data kenaikan pangkat - DIPERBAIKI
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
    k.jabatan_lama,
    k.jabatan_baru,
    k.jenis_kenaikan,
    k.status,
    k.keterangan,
    k.created_at,
    k.updated_at
FROM kenaikan_pangkat k
WHERE 1=1";

// Filter status
if ($filter_status !== 'all') {
    if ($filter_status == 'draft') {
        $query .= " AND (k.status = 'draft' OR k.status = '' OR k.status IS NULL)";
    } else {
        $query .= " AND k.status = '" . $koneksi->real_escape_string($filter_status) . "'";
    }
}

// Filter tahun
if (!empty($filter_tahun)) {
    $query .= " AND YEAR(k.tanggal_usulan) = " . (int)$filter_tahun;
}

// Search
if (!empty($search)) {
    $query .= " AND (k.nama LIKE '%" . $koneksi->real_escape_string($search) . "%' 
                OR k.nip LIKE '%" . $koneksi->real_escape_string($search) . "%'
                OR k.nomor_usulan LIKE '%" . $koneksi->real_escape_string($search) . "%')";
}

$query .= " ORDER BY k.tanggal_usulan DESC, k.created_at DESC";

// DEBUG: Tampilkan query
// echo "<!-- DEBUG QUERY: " . $query . " -->";

$result = $koneksi->query($query);

if (!$result) {
    die("Error pada query: " . $koneksi->error . "<br>Query: " . $query);
}

// DEBUG: Tampilkan jumlah hasil
// echo "<!-- DEBUG: Jumlah hasil = " . $result->num_rows . " -->";

// Hitung statistik
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'diajukan' THEN 1 ELSE 0 END) as diajukan,
    SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
    SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
    SUM(CASE WHEN status = 'draft' OR status = '' OR status IS NULL THEN 1 ELSE 0 END) as belum_diajukan
FROM kenaikan_pangkat
WHERE YEAR(tanggal_usulan) = " . (int)$filter_tahun;

$stats_result = $koneksi->query($stats_query);

if (!$stats_result) {
    die("Error pada stats query: " . $koneksi->error);
}

$stats = $stats_result->fetch_assoc();

// Jika stats kosong, set default
if (!$stats) {
    $stats = [
        'total' => 0,
        'diajukan' => 0,
        'disetujui' => 0,
        'ditolak' => 0,
        'belum_diajukan' => 0
    ];
}

// Ambil daftar tahun untuk filter
$tahun_query = "SELECT DISTINCT YEAR(tanggal_usulan) as tahun 
                FROM kenaikan_pangkat 
                WHERE tanggal_usulan IS NOT NULL
                ORDER BY tahun DESC";
$tahun_result = $koneksi->query($tahun_query);

// Jika tidak ada data tahun, set default
$tahun_list = [];
if ($tahun_result && $tahun_result->num_rows > 0) {
    while ($t = $tahun_result->fetch_assoc()) {
        $tahun_list[] = $t['tahun'];
    }
}

// Jika tidak ada tahun di database, tambahkan tahun sekarang dan beberapa tahun sebelumnya
if (empty($tahun_list)) {
    $current_year = date('Y');
    for ($i = 0; $i < 5; $i++) {
        $tahun_list[] = $current_year - $i;
    }
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - SI Kepegawaian</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/dataduk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Dashboard Header Style */
        .page-header {
            background: linear-gradient(135deg,  #2c3e50 0%, #34495e 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .page-header .header-logo {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .page-header .logo-image {
            width: 200%;
            height: 200%;
            object-fit: contain;
            /* HAPUS filter agar logo berwarna muncul */
            /* filter: brightness(0) invert(1); */
        }
        
        .page-header .header-text h1 {
            color: white;
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .page-header .page-subtitle {
            color: rgba(255,255,255,0.9);
            margin: 5px 0 0 0;
            font-size: 0.95rem;
        }
        
        .page-header .d-flex {
            display: flex;
        }
        
        .page-header .align-items-center {
            align-items: center;
        }
        
        .page-header .me-4 {
            margin-right: 1.5rem;
        }
        
        .page-header .me-3 {
            margin-right: 1rem;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 1200px) {
            .stats-container {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .stat-card {
            background: linear-gradient(135deg,  #2c3e50 0%, #34495e 100%);
            border-radius: 12px;
            padding: 20px;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .stat-card.diajukan {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card.disetujui {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-card.ditolak {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .stat-card.belum {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        
        .stat-card p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filter-section .row {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .filter-section .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-section .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .filter-section .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .btn-export {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            color: white;
            text-decoration: none;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }
        
        .export-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-diajukan { background: #f093fb; color: white; }
        .status-disetujui { background: #4facfe; color: white; }
        .status-ditolak { background: #fa709a; color: white; }
        .status-draft { background: #e0e0e0; color: #333; }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        
        .table thead {
            background: linear-gradient(135deg,  #2c3e50 0%, #34495e 100%);
            color: white;
        }
        
        .table thead th {
            padding: 15px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
            transform: scale(1.05);
        }
        
        .text-muted {
            color: #6c757d;
            font-size: 12px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #e0e0e0;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            color: #999;
            font-size: 16px;
        }
        
        .debug-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .debug-info h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        
        .debug-info pre {
            background: #fff;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
    </style>
</head>
<body>

    <main class="main-content">
        <!-- Dashboard Header -->
        <div class="page-header">
            <div class="d-flex align-items-center">
                <div class="header-logo me-4">
                    <img src="assets/img/logo.png" 
                         alt="Logo Banjarmasin" 
                         class="logo-image"
                         onerror="this.src='https://via.placeholder.com/80x80/3b82f6/ffffff?text=Logo'">
                </div>
                <div class="header-text">
                    <h1><i class="fas fa-chart-bar me-3"></i><?= $page_title ?></h1>
                    <p class="page-subtitle">Laporan dan statistik usulan kenaikan pangkat pegawai</p>
                </div>
            </div>
        </div>

        <!-- DEBUG INFO (Hapus setelah masalah teratasi) -->
        <?php if (isset($_GET['debug'])): ?>
        <div class="debug-info">
            <h4><i class="fas fa-bug"></i> Debug Information</h4>
            <p><strong>Filter Tahun:</strong> <?= $filter_tahun ?></p>
            <p><strong>Default Tahun (dari data):</strong> <?= $default_tahun ?></p>
            <p><strong>Filter Status:</strong> <?= $filter_status ?></p>
            <p><strong>Search:</strong> <?= $search ?></p>
            <p><strong>Jumlah Hasil Query:</strong> <?= $result->num_rows ?></p>
            <p><strong>Total Data (Semua Tahun):</strong> 
            <?php 
            $count_all = $koneksi->query("SELECT COUNT(*) as total FROM kenaikan_pangkat");
            $total_all = $count_all->fetch_assoc();
            echo $total_all['total'];
            ?>
            </p>
            <pre><?= htmlspecialchars($query) ?></pre>
        </div>
        <?php endif; ?>
        
        <!-- Info: Tahun otomatis diset ke tahun data terbaru -->
        <?php if (!isset($_GET['tahun']) && $default_tahun != date('Y')): ?>
        <div class="alert alert-info" style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #0c5460;">
            <i class="fas fa-info-circle"></i>
            <strong>Info:</strong> Filter tahun otomatis diatur ke tahun <strong><?= $default_tahun ?></strong> (tahun data terbaru). 
            Anda dapat mengubah tahun di filter di bawah.
        </div>
        <?php endif; ?>

        <!-- Export Buttons -->
        <div class="export-buttons">
            <a href="export_laporan.php?tahun=<?= $filter_tahun ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>" 
               class="btn-export" target="_blank">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="export_laporan_pdf.php?tahun=<?= $filter_tahun ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>" 
               class="btn-export btn-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <a href="print_laporan.php?tahun=<?= $filter_tahun ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>" 
               class="btn-export btn-primary" target="_blank">
                <i class="fas fa-print"></i> Cetak Laporan
            </a>
        </div>
        
        <!-- Statistik Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <h3><?= number_format($stats['total']) ?></h3>
                <p><i class="fas fa-users"></i> Total Usulan</p>
            </div>
            <div class="stat-card diajukan">
                <h3><?= number_format($stats['diajukan']) ?></h3>
                <p><i class="fas fa-paper-plane"></i> Diajukan</p>
            </div>
            <div class="stat-card disetujui">
                <h3><?= number_format($stats['disetujui']) ?></h3>
                <p><i class="fas fa-check-circle"></i> Disetujui</p>
            </div>
            <div class="stat-card ditolak">
                <h3><?= number_format($stats['ditolak']) ?></h3>
                <p><i class="fas fa-times-circle"></i> Ditolak</p>
            </div>
            <div class="stat-card belum">
                <h3><?= number_format($stats['belum_diajukan']) ?></h3>
                <p><i class="fas fa-clock"></i> Belum Diajukan</p>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="">
                <div class="row">
                    <div class="form-group">
                        <label>Tahun</label>
                        <select name="tahun" class="form-control">
                            <?php foreach ($tahun_list as $tahun): ?>
                            <option value="<?= $tahun ?>" <?= $tahun == $filter_tahun ? 'selected' : '' ?>>
                                <?= $tahun ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="all" <?= $filter_status == 'all' ? 'selected' : '' ?>>Semua Status</option>
                            <option value="diajukan" <?= $filter_status == 'diajukan' ? 'selected' : '' ?>>Diajukan</option>
                            <option value="disetujui" <?= $filter_status == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                            <option value="ditolak" <?= $filter_status == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            <option value="draft" <?= $filter_status == 'draft' ? 'selected' : '' ?>>Belum Diajukan</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label>Cari Pegawai</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Nama, NIP, atau Nomor Usulan..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="form-group" style="flex: 0.5; min-width: 100px;">
                        <button type="submit" class="btn-export btn-primary" style="width: 100%;">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Tabel Data -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Usulan</th>
                                <th>Nama / NIP</th>
                                <th>Pangkat Lama</th>
                                <th>Pangkat Baru</th>
                                <th>Jabatan Baru</th>
                                <th>Jenis</th>
                                <th>Tanggal Usul</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if($result && $result->num_rows > 0):
                                while($row = $result->fetch_assoc()): 
                                    $status = $row['status'] ?: 'draft';
                                    $status_class = 'status-draft';
                                    $status_text = 'Belum Diajukan';
                                    
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
                                    
                                    $jenis_kenaikan = ucfirst($row['jenis_kenaikan'] ?: 'Reguler');
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nomor_usulan']) ?></strong><br>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($row['tanggal_usulan'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($row['nip']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['pangkat_lama']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($row['golongan_lama']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['pangkat_baru']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($row['golongan_baru']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['jabatan_baru']) ?></td>
                                <td>
                                    <span class="badge badge-secondary" style="background: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                        <?= $jenis_kenaikan ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_usulan'])) ?></td>
                                <td>
                                    <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                                    <?php if ($row['keterangan']): ?>
                                        <br><small class="text-muted" title="<?= htmlspecialchars($row['keterangan']) ?>">
                                            <i class="fas fa-info-circle"></i> Lihat keterangan
                                        </small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p>Tidak ada data yang ditampilkan</p>
                                        <small class="text-muted">
                                            <?php if (!empty($search)): ?>
                                                Tidak ditemukan data dengan kata kunci "<?= htmlspecialchars($search) ?>"
                                            <?php elseif ($filter_status != 'all'): ?>
                                                Tidak ada usulan dengan status "<?= ucfirst($filter_status) ?>" pada tahun <?= $filter_tahun ?>
                                            <?php else: ?>
                                                Belum ada data usulan kenaikan pangkat pada tahun <?= $filter_tahun ?>
                                            <?php endif; ?>
                                        </small>
                                        <br><br>
                                        <a href="?tahun=<?= date('Y') ?>&status=all" class="btn-export btn-primary">
                                            <i class="fas fa-sync"></i> Reset Filter
                                        </a>
                                        <a href="?debug=1&tahun=<?= $filter_tahun ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>" 
                                           class="btn-export" style="background: #ffc107; color: #333;">
                                            <i class="fas fa-bug"></i> Mode Debug
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div style="margin-top: 20px; padding: 15px 20px; background: linear-gradient(135deg,  #2c3e50 0%, #34495e 100%); border-radius: 8px; color: white;">
                    <p style="margin: 0; font-size: 14px;">
                        <i class="fas fa-info-circle"></i> 
                        Menampilkan <strong><?= $result->num_rows ?></strong> data dari total 
                        <strong><?= number_format($stats['total']) ?></strong> usulan kenaikan pangkat tahun <strong><?= $filter_tahun ?></strong>
                    </p>
                </div>

                </div>
                
                <?php if ($result && $result->num_rows > 0): ?>
               
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
<?php $koneksi->close(); ?>