<?php
session_start();
require_once 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$filter_tahun  = isset($_GET['tahun'])  ? $_GET['tahun']  : date('Y');
$search        = isset($_GET['search']) ? $_GET['search'] : '';

// Query data usulan pensiun
$query = "SELECT 
    p.id,
    p.nomor_usulan,
    p.tanggal_usulan,
    p.nip,
    p.nama,
    p.pangkat_terakhir,
    p.golongan,
    p.jabatan_terakhir,
    p.tanggal_pensiun,
    p.jenis_pensiun,
    p.pendidikan_terakhir,
    p.jenis_kelamin,
    p.status,
    p.keterangan,
    p.created_at
FROM usulan_pensiun p
WHERE 1=1";

if ($filter_status !== 'all') {
    if ($filter_status == 'draft') {
        $query .= " AND (p.status = 'draft' OR p.status = '' OR p.status IS NULL)";
    } else {
        $query .= " AND p.status = '" . $koneksi->real_escape_string($filter_status) . "'";
    }
}

if (!empty($filter_tahun)) {
    $query .= " AND YEAR(p.tanggal_usulan) = " . (int)$filter_tahun;
}

if (!empty($search)) {
    $search_esc = $koneksi->real_escape_string($search);
    $query .= " AND (p.nama LIKE '%$search_esc%' 
                OR p.nip LIKE '%$search_esc%'
                OR p.nomor_usulan LIKE '%$search_esc%')";
}

$query .= " ORDER BY p.tanggal_pensiun ASC";
$result = $koneksi->query($query);

// Statistik
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'diajukan'  THEN 1 ELSE 0 END) as diajukan,
    SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
    SUM(CASE WHEN status = 'ditolak'   THEN 1 ELSE 0 END) as ditolak,
    SUM(CASE WHEN status = 'draft' OR status = '' OR status IS NULL THEN 1 ELSE 0 END) as belum_diajukan
FROM usulan_pensiun
WHERE YEAR(tanggal_usulan) = " . (int)$filter_tahun;

$stats_result = $koneksi->query($stats_query);
$stats = $stats_result ? $stats_result->fetch_assoc() : ['total'=>0,'diajukan'=>0,'disetujui'=>0,'ditolak'=>0,'belum_diajukan'=>0];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Usulan Pensiun - <?= $filter_tahun ?></title>
    <style>
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #333;
            padding: 10mm;
        }

        /* ===== TOMBOL ===== */
        .btn-bar {
            position: fixed; top: 15px; right: 15px;
            display: flex; gap: 8px; z-index: 1000;
        }
        .btn-print {
            background: #2c3e50; color: white;
            padding: 10px 20px; border: none; border-radius: 5px;
            cursor: pointer; font-size: 13px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.15);
        }
        .btn-close-w {
            background: #6c757d; color: white;
            padding: 10px 20px; border: none; border-radius: 5px;
            cursor: pointer; font-size: 13px;
        }
        .btn-print:hover { background: #34495e; }
        .btn-close-w:hover { background: #5a6268; }

        /* ===== HEADER ===== */
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #2c3e50;
        }
        .header h1 { font-size: 15pt; margin: 3px 0; color: #2c3e50; }
        .header h2 { font-size: 11pt; margin: 2px 0; color: #555; font-weight: normal; }
        .header p  { font-size: 9.5pt; margin: 5px 0 0; color: #666; }

        /* ===== STATISTIK ===== */
        .stats-container {
            display: flex; justify-content: space-between;
            margin: 15px 0; gap: 10px;
        }
        .stat-box {
            flex: 1; padding: 12px; text-align: center;
            border: 1px solid #ddd; background: #f8f9fa; border-radius: 5px;
        }
        .stat-value { font-size: 26pt; font-weight: bold; color: #2c3e50; display: block; margin-bottom: 5px; }
        .stat-label { font-size: 9pt; color: #666; }

        /* ===== TABEL ===== */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }

        th {
            background: #2c3e50; color: white;
            padding: 9px 6px; font-size: 9pt; font-weight: bold;
            text-align: center; border: 1px solid #1a252f;
        }
        td {
            padding: 7px 6px; border: 1px solid #ddd;
            font-size: 9pt; vertical-align: middle;
        }
        tr:nth-child(even) td { background: #f8f9fa; }

        /* Highlight pensiun segera (≤ 3 bulan) */
        .row-segera td { background: #fff8e1 !important; }
        .badge-segera {
            background: #ff9800; color: white;
            padding: 1px 6px; border-radius: 8px; font-size: 7.5pt;
        }

        /* ===== STATUS BADGE ===== */
        .status-badge { padding: 3px 8px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
        .status-diajukan  { background: #f093fb; color: white; }
        .status-disetujui { background: #4facfe; color: white; }
        .status-ditolak   { background: #fa709a; color: white; }
        .status-draft     { background: #e0e0e0; color: #333; }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 20px; padding-top: 10px;
            border-top: 2px solid #ddd; font-size: 9pt; color: #666;
            display: flex; justify-content: space-between;
        }

        .text-center { text-align: center; }
        small { font-size: 8pt; color: #888; }
    </style>
</head>
<body>

    <!-- Tombol Cetak -->
    <div class="btn-bar no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak / Download PDF</button>
        <button onclick="window.close()" class="btn-close-w">✖️ Tutup</button>
    </div>

    <!-- Header -->
    <div class="header">
        <h1>LAPORAN USULAN PENSIUN PEGAWAI</h1>
        <h2>DINAS PENGENDALIAN PENDUDUK, KELUARGA BERENCANA</h2>
        <h2>DAN PEMBERDAYAAN MASYARAKAT KOTA BANJARMASIN</h2>
        <p>
            Tahun: <strong><?= $filter_tahun ?></strong>
            <?php if ($filter_status != 'all'): ?>
                &nbsp;|&nbsp; Status: <strong><?= strtoupper($filter_status) ?></strong>
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
            <span class="stat-value" style="color:#f093fb;"><?= $stats['diajukan'] ?></span>
            <span class="stat-label">Diajukan</span>
        </div>
        <div class="stat-box">
            <span class="stat-value" style="color:#4facfe;"><?= $stats['disetujui'] ?></span>
            <span class="stat-label">Disetujui</span>
        </div>
        <div class="stat-box">
            <span class="stat-value" style="color:#fa709a;"><?= $stats['ditolak'] ?></span>
            <span class="stat-label">Ditolak</span>
        </div>
        <div class="stat-box">
            <span class="stat-value" style="color:#999;"><?= $stats['belum_diajukan'] ?></span>
            <span class="stat-label">Belum Diajukan</span>
        </div>
    </div>

    <!-- Tabel Data -->
    <table>
        <thead>
            <tr>
                <th style="width:3%;">No</th>
                <th style="width:11%;">Nomor Usulan</th>
                <th style="width:16%;">Nama</th>
                <th style="width:12%;">NIP</th>
                <th style="width:11%;">Pangkat / Gol</th>
                <th style="width:16%;">Jabatan Terakhir</th>
                <th style="width:7%;">Jenis Pensiun</th>
                <th style="width:8%;">Tgl Pensiun</th>
                <th style="width:7%;">Tgl Usulan</th>
                <th style="width:7%;">Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $status      = $row['status'] ?: 'draft';
                $status_class = 'status-draft';
                $status_text  = 'Belum';
                if ($status == 'diajukan')  { $status_class = 'status-diajukan';  $status_text = 'Diajukan'; }
                if ($status == 'disetujui') { $status_class = 'status-disetujui'; $status_text = 'Disetujui'; }
                if ($status == 'ditolak')   { $status_class = 'status-ditolak';   $status_text = 'Ditolak'; }

                // Highlight jika pensiun ≤ 3 bulan
                $tgl_pensiun = strtotime($row['tanggal_pensiun']);
                $bulan_lagi  = ($tgl_pensiun - time()) / (30 * 24 * 3600);
                $segera      = ($bulan_lagi >= 0 && $bulan_lagi <= 3);
                $row_class   = $segera ? 'row-segera' : '';
        ?>
            <tr class="<?= $row_class ?>">
                <td class="text-center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nomor_usulan']) ?></td>
                <td>
                    <strong><?= htmlspecialchars($row['nama']) ?></strong>
                    <?php if ($segera): ?>
                        <br><span class="badge-segera">⚠ Segera</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['nip']) ?></td>
                <td>
                    <?= htmlspecialchars($row['pangkat_terakhir']) ?><br>
                    <small>(<?= htmlspecialchars($row['golongan']) ?>)</small>
                </td>
                <td><?= htmlspecialchars($row['jabatan_terakhir']) ?></td>
                <td class="text-center">
                    <strong><?= htmlspecialchars(strtoupper($row['jenis_pensiun'] ?: 'BUP')) ?></strong>
                </td>
                <td class="text-center" style="<?= $segera ? 'color:#ff9800;font-weight:bold;' : '' ?>">
                    <?= date('d/m/Y', strtotime($row['tanggal_pensiun'])) ?>
                </td>
                <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_usulan'])) ?></td>
                <td class="text-center">
                    <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                </td>
            </tr>
        <?php
            endwhile;
        else:
        ?>
            <tr>
                <td colspan="10" class="text-center" style="padding:30px;color:#999;">
                    Tidak ada data usulan pensiun pada tahun <?= $filter_tahun ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div>
            Total: <strong><?= $result ? $result->num_rows : 0 ?></strong> pegawai &nbsp;|&nbsp;
            Dicetak pada: <?= date('d F Y, H:i:s') ?> WIB
        </div>
        <div>SI Kepegawaian – DPPKBPM Kota Banjarmasin</div>
    </div>

</body>
</html>
<?php $koneksi->close(); ?>
