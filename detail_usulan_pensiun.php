<?php
session_start();
require_once 'config/koneksi.php';
require_once 'check_session.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';



// Ambil ID dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: usulan_pensiun.php?error=ID tidak valid');
    exit;
}

$id = (int)$_GET['id'];

// Query data lengkap dengan notifikasi
$query = "SELECT 
    up.*,
    DATEDIFF(up.tanggal_pensiun, CURDATE()) as hari_tersisa,
    TIMESTAMPDIFF(YEAR, up.tanggal_lahir, CURDATE()) as umur_sekarang,

    (SELECT COUNT(*) FROM notifikasi_pensiun 
     WHERE id_usulan_pensiun = up.id AND jenis_notif = 'approval' AND status = 'terkirim') as approval_terkirim,

    (SELECT tanggal_kirim FROM notifikasi_pensiun 
     WHERE id_usulan_pensiun = up.id AND jenis_notif = 'approval' AND status = 'terkirim' 
     ORDER BY tanggal_kirim DESC LIMIT 1) as tgl_approval_terkirim,

    (SELECT COUNT(*) FROM notifikasi_pensiun 
     WHERE id_usulan_pensiun = up.id AND jenis_notif = 'reminder_1_tahun' AND status = 'terkirim') as reminder_1_tahun_terkirim,

    (SELECT tanggal_kirim FROM notifikasi_pensiun 
     WHERE id_usulan_pensiun = up.id AND jenis_notif = 'reminder_1_tahun' AND status = 'terkirim' 
     ORDER BY tanggal_kirim DESC LIMIT 1) as tgl_1_tahun_terkirim,

    (SELECT COUNT(*) FROM notifikasi_pensiun 
     WHERE id_usulan_pensiun = up.id AND jenis_notif = 'reminder_1_bulan' AND status = 'terkirim') as reminder_1_bulan_terkirim,

    (SELECT tanggal_kirim FROM notifikasi_pensiun 
     WHERE id_usulan_pensiun = up.id AND jenis_notif = 'reminder_1_bulan' AND status = 'terkirim' 
     ORDER BY tanggal_kirim DESC LIMIT 1) as tgl_1_bulan_terkirim,

    (SELECT COUNT(*) FROM notifikasi_pensiun 
     WHERE id_usulan_pensiun = up.id AND jenis_notif = 'reminder_1_minggu' AND status = 'terkirim') as reminder_1_minggu_terkirim,

    (SELECT tanggal_kirim FROM notifikasi_pensiun 
     WHERE id_usulan_pensiun = up.id AND jenis_notif = 'reminder_1_minggu' AND status = 'terkirim' 
     ORDER BY tanggal_kirim DESC LIMIT 1) as tgl_1_minggu_terkirim

FROM usulan_pensiun up
WHERE up.id = ? AND up.deleted_at IS NULL
LIMIT 1";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: usulan_pensiun.php?error=Data tidak ditemukan');
    exit;
}

$data = $result->fetch_assoc();
$stmt->close();

// Hitung sisa waktu
$hari = (int)$data['hari_tersisa'];
$perlu_reminder_1_tahun = ($hari >= 350 && $hari <= 380 && $data['status'] === 'disetujui');
$perlu_reminder_1_bulan = ($hari >= 20 && $hari <= 40 && $data['status'] === 'disetujui');
$perlu_reminder_1_minggu = ($hari >= 3 && $hari <= 10 && $data['status'] === 'disetujui');

// Format tanggal
function formatTanggal($tanggal) {
    if (empty($tanggal)) return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', $tanggal);
    return (int)$split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

function formatSisaWaktu($hari) {
    if ($hari < 0) return ['label' => 'Sudah Pensiun', 'color' => 'dark'];
    if ($hari == 0) return ['label' => 'Hari Ini!', 'color' => 'danger'];
    $tahun = floor($hari / 365);
    $bulan = floor(($hari % 365) / 30);
    $hari_s = $hari % 30;
    $parts = [];
    if ($tahun > 0) $parts[] = $tahun . ' tahun';
    if ($bulan > 0) $parts[] = $bulan . ' bulan';
    if ($hari_s > 0) $parts[] = $hari_s . ' hari';
    $color = $hari <= 7 ? 'danger' : ($hari <= 30 ? 'warning' : ($hari <= 365 ? 'info' : 'success'));
    return ['label' => implode(' ', $parts), 'color' => $color];
}

$sisa = formatSisaWaktu($hari);
$statusColors = ['draft' => 'secondary', 'diajukan' => 'info', 'disetujui' => 'success', 'ditolak' => 'danger'];
$statusColor = $statusColors[$data['status']] ?? 'secondary';

$koneksi->close();
?>

<link rel="stylesheet" href="css/dataduk.css">

<style>
.detail-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
}
.detail-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}
.section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid var(--primary-color);
}
.section-header i { font-size: 2rem; color: var(--primary-color); }
.section-header h3 { font-size: 1.5rem; font-weight: 700; color: var(--dark-color); margin: 0; }
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.2rem;
}
.info-item { display: flex; flex-direction: column; gap: 0.4rem; }
.info-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.info-value {
    font-size: 1rem;
    color: var(--dark-color);
    font-weight: 600;
    padding: 0.65rem 0.9rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid var(--primary-color);
}
.info-value.highlight-red { border-left-color: var(--danger-color); background: #fff5f5; }
.info-value.highlight-green { border-left-color: var(--success-color); background: #f0fff4; }

/* Timeline notifikasi */
.notif-timeline { display: flex; flex-direction: column; gap: 0.8rem; }
.notif-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.9rem 1.2rem;
    border-radius: 10px;
    border: 1px solid #dee2e6;
}
.notif-item.terkirim { background: #f0fff4; border-color: #28a745; }
.notif-item.belum   { background: #fff8e1; border-color: #ffc107; }
.notif-item.pending { background: #f8f9fa; border-color: #dee2e6; }
.notif-dot {
    width: 40px; height: 40px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 1.1rem;
}
.notif-dot.green { background: #28a745; color: white; }
.notif-dot.yellow { background: #ffc107; color: #333; }
.notif-dot.gray  { background: #adb5bd; color: white; }
.notif-info { flex: 1; }
.notif-title { font-weight: 700; font-size: 0.95rem; margin-bottom: 0.2rem; }
.notif-date  { font-size: 0.82rem; color: #6c757d; }

/* Countdown visual */
.countdown-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 1.5rem 2rem;
    color: white;
    text-align: center;
    margin-bottom: 1.5rem;
}
.countdown-box.danger-bg  { background: linear-gradient(135deg, #f5365c 0%, #f56036 100%); }
.countdown-box.warning-bg { background: linear-gradient(135deg, #fb6340 0%, #fbb140 100%); }
.countdown-box.success-bg { background: linear-gradient(135deg, #2dce89 0%, #2dcecc 100%); }
.countdown-box.dark-bg    { background: linear-gradient(135deg, #525f7f 0%, #32325d 100%); }
.countdown-number { font-size: 3rem; font-weight: 800; line-height: 1; }
.countdown-label  { font-size: 1rem; opacity: 0.9; margin-top: 0.3rem; }
.countdown-sub    { font-size: 0.85rem; opacity: 0.75; margin-top: 0.5rem; }

/* Action buttons */
.action-buttons { display: flex; gap: 1rem; margin-top: 2rem; flex-wrap: wrap; }
.btn-action {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.75rem 2rem; border-radius: 8px;
    font-weight: 600; text-decoration: none;
    transition: var(--transition); border: none; cursor: pointer;
}
.btn-back   { background: var(--dark-color); color: white; }
.btn-print  { background: var(--info-color); color: white; }
.btn-edit   { background: var(--warning-color); color: white; }
.btn-delete { background: var(--danger-color); color: white; }
.btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.2); color: white; text-decoration: none; }

@media (max-width: 768px) {
    .info-grid { grid-template-columns: 1fr; }
    .countdown-number { font-size: 2rem; }
}
</style>

<div class="main-content">
    <!-- Header -->
    <div class="dashboard-header fade-in">
        <div>
            <h1 class="dashboard-title">
                <i class="fas fa-user-clock"></i> Detail Usulan Pensiun
            </h1>
            <p class="dashboard-subtitle">Informasi lengkap usulan pensiun pegawai ASN &amp; Penyuluh</p>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="stats-container fade-in">
        <div class="stat-card primary">
            <div class="stat-icon"><i class="fas fa-user"></i></div>
            <h2 class="stat-number" style="font-size:1.1rem;"><?= htmlspecialchars($data['nama']) ?></h2>
            <p class="stat-label">Nama Pegawai</p>
        </div>
        <div class="stat-card info">
            <div class="stat-icon"><i class="fas fa-id-card"></i></div>
            <h2 class="stat-number" style="font-size:1rem;"><?= htmlspecialchars($data['nip'] ?? '-') ?></h2>
            <p class="stat-label">NIP</p>
        </div>
        <div class="stat-card <?= $data['status'] == 'disetujui' ? 'success' : ($data['status'] == 'diajukan' ? 'info' : ($data['status'] == 'ditolak' ? 'danger' : 'warning')) ?>">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <h2 class="stat-number" style="text-transform:capitalize;"><?= htmlspecialchars($data['status']) ?></h2>
            <p class="stat-label">Status Usulan</p>
        </div>
        <div class="stat-card <?= $hari < 0 ? 'danger' : ($hari <= 30 ? 'warning' : 'success') ?>">
            <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
            <h2 class="stat-number" style="font-size:1rem;"><?= $sisa['label'] ?></h2>
            <p class="stat-label">Sisa Waktu Pensiun</p>
        </div>
    </div>

    <!-- Countdown Visual -->
    <?php
    $cbClass = $hari < 0 ? 'dark-bg' : ($hari <= 7 ? 'danger-bg' : ($hari <= 30 ? 'warning-bg' : 'success-bg'));
    ?>
    <div class="countdown-box <?= $cbClass ?> fade-in">
        <div class="countdown-number">
            <?php if ($hari < 0): ?>
                <i class="fas fa-check-circle"></i>
            <?php elseif ($hari == 0): ?>
                <i class="fas fa-bell"></i>
            <?php else: ?>
                <?= $hari ?>
            <?php endif; ?>
        </div>
        <div class="countdown-label">
            <?= $hari < 0 ? 'Pegawai Sudah Pensiun' : ($hari == 0 ? 'Hari Pensiun — Hari Ini!' : 'Hari Lagi Menuju Pensiun') ?>
        </div>
        <div class="countdown-sub">
            Tanggal Pensiun: <?= formatTanggal($data['tanggal_pensiun']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">

            <!-- Data Personal -->
            <div class="detail-card fade-in">
                <div class="section-header">
                    <i class="fas fa-user-circle"></i>
                    <h3>Data Personal</h3>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-user"></i> Nama Lengkap</span>
                        <span class="info-value"><?= htmlspecialchars($data['nama']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-id-card"></i> NIP</span>
                        <span class="info-value"><?= htmlspecialchars($data['nip'] ?? '-') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-id-badge"></i> Kartu Pegawai</span>
                        <span class="info-value"><?= htmlspecialchars($data['kartu_pegawai'] ?? '-') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-birthday-cake"></i> Tempat, Tanggal Lahir</span>
                        <span class="info-value"><?= htmlspecialchars($data['ttl']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-calendar"></i> Tanggal Lahir</span>
                        <span class="info-value"><?= formatTanggal($data['tanggal_lahir']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-user-clock"></i> Umur Sekarang</span>
                        <span class="info-value"><?= $data['umur_sekarang'] ?> Tahun</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-venus-mars"></i> Jenis Kelamin</span>
                        <span class="info-value"><?= htmlspecialchars($data['jenis_kelamin'] ?? '-') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fab fa-whatsapp"></i> Nomor WhatsApp</span>
                        <span class="info-value"><?= !empty($data['nomor_wa']) ? htmlspecialchars($data['nomor_wa']) : '<span class="text-muted">Tidak ada</span>' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-graduation-cap"></i> Pendidikan Terakhir</span>
                        <span class="info-value">
                            <?= htmlspecialchars($data['pendidikan_terakhir'] ?? '-') ?>
                            <?php if (!empty($data['prodi'])): ?>
                                — <?= htmlspecialchars($data['prodi']) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-database"></i> Sumber Data</span>
                        <span class="info-value">
                            <?php if ($data['sumber_data'] === 'penyuluh'): ?>
                                <span class="badge bg-info"><i class="fas fa-chalkboard-teacher"></i> Penyuluh</span>
                            <?php else: ?>
                                <span class="badge bg-primary"><i class="fas fa-users"></i> DUK</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Data Kepegawaian -->
            <div class="detail-card fade-in">
                <div class="section-header">
                    <i class="fas fa-briefcase"></i>
                    <h3>Data Kepegawaian</h3>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-medal"></i> Pangkat Terakhir</span>
                        <span class="info-value"><?= htmlspecialchars($data['pangkat_terakhir'] ?? '-') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-layer-group"></i> Golongan</span>
                        <span class="info-value"><?= htmlspecialchars($data['golongan'] ?? '-') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-calendar-check"></i> TMT Pangkat</span>
                        <span class="info-value"><?= formatTanggal($data['tmt_pangkat']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-briefcase"></i> Jabatan Terakhir</span>
                        <span class="info-value"><?= htmlspecialchars($data['jabatan_terakhir'] ?? '-') ?></span>
                    </div>
                </div>
            </div>

            <!-- Data Usulan Pensiun -->
            <div class="detail-card fade-in">
                <div class="section-header">
                    <i class="fas fa-file-signature"></i>
                    <h3>Data Usulan Pensiun</h3>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-barcode"></i> Nomor Usulan</span>
                        <span class="info-value"><?= htmlspecialchars($data['nomor_usulan']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-calendar-alt"></i> Tanggal Usulan</span>
                        <span class="info-value"><?= formatTanggal($data['tanggal_usulan']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-sign-out-alt"></i> Jenis Pensiun</span>
                        <span class="info-value"><?= htmlspecialchars($data['jenis_pensiun']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-calendar-times"></i> Tanggal Pensiun</span>
                        <span class="info-value highlight-red"><?= formatTanggal($data['tanggal_pensiun']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-hourglass-half"></i> Sisa Waktu</span>
                        <span class="info-value <?= $hari <= 30 && $hari >= 0 ? 'highlight-red' : ($hari < 0 ? 'highlight-red' : 'highlight-green') ?>">
                            <?= $sisa['label'] ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-info-circle"></i> Status</span>
                        <span class="info-value">
                            <span class="badge bg-<?= $statusColor ?>" style="font-size:1rem; padding:0.5rem 1.2rem;">
                                <?= ucfirst($data['status']) ?>
                            </span>
                        </span>
                    </div>
                    <?php if (!empty($data['alasan'])): ?>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <span class="info-label"><i class="fas fa-comment-alt"></i> Alasan Pensiun</span>
                        <span class="info-value"><?= nl2br(htmlspecialchars($data['alasan'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($data['keterangan'])): ?>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <span class="info-label"><i class="fas fa-sticky-note"></i> Keterangan</span>
                        <span class="info-value"><?= nl2br(htmlspecialchars($data['keterangan'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status Notifikasi WhatsApp -->
            <div class="detail-card fade-in">
                <div class="section-header">
                    <i class="fab fa-whatsapp"></i>
                    <h3>Riwayat Notifikasi WhatsApp</h3>
                </div>

                <?php if (empty($data['nomor_wa'])): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Nomor WhatsApp pegawai belum diisi. Notifikasi tidak bisa dikirim.
                    </div>
                <?php else: ?>
                <div class="notif-timeline">
                    <!-- Approval -->
                    <div class="notif-item <?= $data['approval_terkirim'] > 0 ? 'terkirim' : 'pending' ?>">
                        <div class="notif-dot <?= $data['approval_terkirim'] > 0 ? 'green' : 'gray' ?>">
                            <i class="fas <?= $data['approval_terkirim'] > 0 ? 'fa-check' : 'fa-clock' ?>"></i>
                        </div>
                        <div class="notif-info">
                            <div class="notif-title">Notifikasi Approval / Persetujuan</div>
                            <div class="notif-date">
                                <?php if ($data['approval_terkirim'] > 0): ?>
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Terkirim</span>
                                    <?php if (!empty($data['tgl_approval_terkirim'])): ?>
                                        — <?= date('d-m-Y H:i', strtotime($data['tgl_approval_terkirim'])) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Belum Terkirim</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Reminder 1 Tahun -->
                    <?php
                    $r1t_class = $data['reminder_1_tahun_terkirim'] > 0 ? 'terkirim' : ($perlu_reminder_1_tahun ? 'belum' : 'pending');
                    $r1t_dot   = $data['reminder_1_tahun_terkirim'] > 0 ? 'green' : ($perlu_reminder_1_tahun ? 'yellow' : 'gray');
                    $r1t_icon  = $data['reminder_1_tahun_terkirim'] > 0 ? 'fa-check' : ($perlu_reminder_1_tahun ? 'fa-bell' : 'fa-clock');
                    ?>
                    <div class="notif-item <?= $r1t_class ?>">
                        <div class="notif-dot <?= $r1t_dot ?>">
                            <i class="fas <?= $r1t_icon ?>"></i>
                        </div>
                        <div class="notif-info">
                            <div class="notif-title">Reminder 1 Tahun Sebelum Pensiun</div>
                            <div class="notif-date">
                                <?php if ($data['reminder_1_tahun_terkirim'] > 0): ?>
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Terkirim</span>
                                    <?php if (!empty($data['tgl_1_tahun_terkirim'])): ?>
                                        — <?= date('d-m-Y H:i', strtotime($data['tgl_1_tahun_terkirim'])) ?>
                                    <?php endif; ?>
                                <?php elseif ($perlu_reminder_1_tahun): ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Waktunya dikirim sekarang!</span>
                                <?php elseif ($hari > 380): ?>
                                    <span class="badge bg-secondary">Dijadwalkan ~<?= round($hari/365, 1) ?> tahun lagi</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Terlewat / Tidak Berlaku</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($perlu_reminder_1_tahun && $data['reminder_1_tahun_terkirim'] == 0 && !empty($data['nomor_wa'])): ?>
                        <button onclick="kirimReminder(<?= $data['id'] ?>, '1_tahun')" class="btn btn-info btn-sm">
                            <i class="fas fa-paper-plane"></i> Kirim
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Reminder 1 Bulan -->
                    <?php
                    $r1b_class = $data['reminder_1_bulan_terkirim'] > 0 ? 'terkirim' : ($perlu_reminder_1_bulan ? 'belum' : 'pending');
                    $r1b_dot   = $data['reminder_1_bulan_terkirim'] > 0 ? 'green' : ($perlu_reminder_1_bulan ? 'yellow' : 'gray');
                    $r1b_icon  = $data['reminder_1_bulan_terkirim'] > 0 ? 'fa-check' : ($perlu_reminder_1_bulan ? 'fa-bell' : 'fa-clock');
                    ?>
                    <div class="notif-item <?= $r1b_class ?>">
                        <div class="notif-dot <?= $r1b_dot ?>">
                            <i class="fas <?= $r1b_icon ?>"></i>
                        </div>
                        <div class="notif-info">
                            <div class="notif-title">Reminder 1 Bulan Sebelum Pensiun</div>
                            <div class="notif-date">
                                <?php if ($data['reminder_1_bulan_terkirim'] > 0): ?>
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Terkirim</span>
                                    <?php if (!empty($data['tgl_1_bulan_terkirim'])): ?>
                                        — <?= date('d-m-Y H:i', strtotime($data['tgl_1_bulan_terkirim'])) ?>
                                    <?php endif; ?>
                                <?php elseif ($perlu_reminder_1_bulan): ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Waktunya dikirim sekarang!</span>
                                <?php elseif ($hari > 40): ?>
                                    <span class="badge bg-secondary">Dijadwalkan ~<?= round($hari/30) ?> bulan lagi</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Terlewat / Tidak Berlaku</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($perlu_reminder_1_bulan && $data['reminder_1_bulan_terkirim'] == 0 && !empty($data['nomor_wa'])): ?>
                        <button onclick="kirimReminder(<?= $data['id'] ?>, '1_bulan')" class="btn btn-warning btn-sm">
                            <i class="fas fa-paper-plane"></i> Kirim
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Reminder 1 Minggu -->
                    <?php
                    $r1m_class = $data['reminder_1_minggu_terkirim'] > 0 ? 'terkirim' : ($perlu_reminder_1_minggu ? 'belum' : 'pending');
                    $r1m_dot   = $data['reminder_1_minggu_terkirim'] > 0 ? 'green' : ($perlu_reminder_1_minggu ? 'yellow' : 'gray');
                    $r1m_icon  = $data['reminder_1_minggu_terkirim'] > 0 ? 'fa-check' : ($perlu_reminder_1_minggu ? 'fa-bell' : 'fa-clock');
                    ?>
                    <div class="notif-item <?= $r1m_class ?>">
                        <div class="notif-dot <?= $r1m_dot ?>">
                            <i class="fas <?= $r1m_icon ?>"></i>
                        </div>
                        <div class="notif-info">
                            <div class="notif-title">Reminder 1 Minggu Sebelum Pensiun</div>
                            <div class="notif-date">
                                <?php if ($data['reminder_1_minggu_terkirim'] > 0): ?>
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Terkirim</span>
                                    <?php if (!empty($data['tgl_1_minggu_terkirim'])): ?>
                                        — <?= date('d-m-Y H:i', strtotime($data['tgl_1_minggu_terkirim'])) ?>
                                    <?php endif; ?>
                                <?php elseif ($perlu_reminder_1_minggu): ?>
                                    <span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Mendesak! Kirim sekarang!</span>
                                <?php elseif ($hari > 10): ?>
                                    <span class="badge bg-secondary">Dijadwalkan ~<?= $hari ?> hari lagi</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Terlewat / Tidak Berlaku</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($perlu_reminder_1_minggu && $data['reminder_1_minggu_terkirim'] == 0 && !empty($data['nomor_wa'])): ?>
                        <button onclick="kirimReminder(<?= $data['id'] ?>, '1_minggu')" class="btn btn-danger btn-sm">
                            <i class="fas fa-paper-plane"></i> Kirim
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info Tambahan -->
            <div class="detail-card fade-in">
                <div class="section-header">
                    <i class="fas fa-clock"></i>
                    <h3>Info Pencatatan</h3>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-plus-circle"></i> Dibuat Pada</span>
                        <span class="info-value"><?= !empty($data['created_at']) ? date('d-m-Y H:i', strtotime($data['created_at'])) : '-' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-edit"></i> Terakhir Diperbarui</span>
                        <span class="info-value"><?= !empty($data['updated_at']) ? date('d-m-Y H:i', strtotime($data['updated_at'])) : '-' ?></span>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>

<?php include 'includes/sweetalert.php'; ?>

<script>
// Fade-in animation
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

document.querySelectorAll('.fade-in').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
});

function confirmDelete(id, nomor) {
    Swal.fire({
        title: 'Hapus Data?',
        text: `Yakin ingin menghapus usulan "${nomor}"? Data tidak dapat dikembalikan.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `proses_hapus_usulan_pensiun.php?id=${id}`;
        }
    });
}

function kirimReminder(id, jenis) {
    const jenisLabel = { '1_tahun': '1 Tahun', '1_bulan': '1 Bulan', '1_minggu': '1 Minggu' };

    Swal.fire({
        title: 'Kirim Reminder?',
        text: `Kirim reminder ${jenisLabel[jenis]} sebelum pensiun ke pegawai ini via WhatsApp?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-paper-plane"></i> Ya, Kirim!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Mengirim...',
            text: 'Sedang mengirim reminder WhatsApp',
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        fetch('api/kirim_reminder_pensiun.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_usulan_pensiun: id, jenis_reminder: jenis })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Terkirim!',
                    text: data.message,
                    confirmButtonColor: '#28a745',
                    timer: 3000,
                    timerProgressBar: true
                }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message, confirmButtonColor: '#dc3545' });
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error!', text: 'Terjadi kesalahan koneksi, coba lagi.', confirmButtonColor: '#dc3545' });
        });
    });
}
</script>

<?php include 'includes/footer.php'; ?>