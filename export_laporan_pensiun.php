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

// Header download Excel (.xls)
$filename = "Laporan_Pensiun_" . $filter_tahun . "_" . date('YmdHis') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Usulan Pensiun</title>
    <style>
        body  { font-family: Arial, sans-serif; font-size: 10pt; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; vertical-align: middle; }
        th { background-color: #2c3e50; color: white; font-weight: bold; text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { font-size: 14pt; margin: 4px 0; }
        .header h3 { font-size: 11pt; font-weight: normal; margin: 2px 0; }
        .header p  { font-size: 10pt; margin: 4px 0; }
        .text-center { text-align: center; }
        .status-diajukan  { background: #f093fb; color: white; padding: 2px 6px; border-radius: 3px; }
        .status-disetujui { background: #4facfe; color: white; padding: 2px 6px; border-radius: 3px; }
        .status-ditolak   { background: #fa709a; color: white; padding: 2px 6px; border-radius: 3px; }
        .status-draft     { background: #e0e0e0; color: #333;  padding: 2px 6px; border-radius: 3px; }
        .row-even { background: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN USULAN PENSIUN PEGAWAI</h2>
        <h3>DINAS PENGENDALIAN PENDUDUK, KELUARGA BERENCANA DAN PEMBERDAYAAN MASYARAKAT</h3>
        <h3>KOTA BANJARMASIN</h3>
        <p>Tahun: <strong><?= $filter_tahun ?></strong>
        <?php if ($filter_status != 'all'): ?>
            &nbsp;|&nbsp; Status: <strong><?= strtoupper($filter_status) ?></strong>
        <?php endif; ?>
        </p>
        <p>Dicetak pada: <?= date('d F Y, H:i:s') ?> WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:3%;">No</th>
                <th style="width:12%;">Nomor Usulan</th>
                <th style="width:16%;">Nama</th>
                <th style="width:13%;">NIP</th>
                <th style="width:12%;">Pangkat / Golongan</th>
                <th style="width:16%;">Jabatan Terakhir</th>
                <th style="width:8%;">Jenis Pensiun</th>
                <th style="width:8%;">Tgl Pensiun</th>
                <th style="width:6%;">Tgl Usulan</th>
                <th style="width:6%;">Status</th>
                <th style="width:10%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $status      = $row['status'] ?: 'draft';
                $status_text = 'Belum Diajukan';
                $status_class = 'status-draft';
                if ($status == 'diajukan')  { $status_text = 'Diajukan';  $status_class = 'status-diajukan'; }
                if ($status == 'disetujui') { $status_text = 'Disetujui'; $status_class = 'status-disetujui'; }
                if ($status == 'ditolak')   { $status_text = 'Ditolak';   $status_class = 'status-ditolak'; }
                $row_bg = ($no % 2 == 0) ? 'class="row-even"' : '';
        ?>
            <tr <?= $row_bg ?>>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nomor_usulan']) ?></td>
                <td><strong><?= htmlspecialchars($row['nama']) ?></strong></td>
                <td><?= htmlspecialchars($row['nip']) ?></td>
                <td>
                    <?= htmlspecialchars($row['pangkat_terakhir']) ?><br>
                    <small>(<?= htmlspecialchars($row['golongan']) ?>)</small>
                </td>
                <td><?= htmlspecialchars($row['jabatan_terakhir']) ?></td>
                <td class="text-center">
                    <strong><?= htmlspecialchars(strtoupper($row['jenis_pensiun'] ?: 'BUP')) ?></strong>
                </td>
                <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_pensiun'])) ?></td>
                <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_usulan'])) ?></td>
                <td class="text-center">
                    <span class="<?= $status_class ?>"><?= $status_text ?></span>
                </td>
                <td><?= htmlspecialchars($row['keterangan'] ?? '') ?></td>
            </tr>
        <?php
            endwhile;
        else:
        ?>
            <tr>
                <td colspan="11" class="text-center" style="padding:20px;color:#999;">
                    Tidak ada data usulan pensiun pada tahun <?= $filter_tahun ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <br>
    <p style="font-size:9pt;color:#666;">
        Total data: <strong><?= $result ? $result->num_rows : 0 ?></strong> pegawai
    </p>
</body>
</html>
<?php $koneksi->close(); ?>
