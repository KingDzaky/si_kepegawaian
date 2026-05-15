<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';

// Ambil daftar usulan yang perlu approval (status = 'diajukan')
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
    p.status,
    p.created_at
FROM usulan_pensiun p
WHERE p.status = 'diajukan'
ORDER BY p.tanggal_pensiun ASC";

$result = $koneksi->query($query);

// Statistik
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM usulan_pensiun WHERE status = 'diajukan')  as pending,
    (SELECT COUNT(*) FROM usulan_pensiun WHERE status = 'disetujui') as disetujui,
    (SELECT COUNT(*) FROM usulan_pensiun WHERE status = 'ditolak')   as ditolak";
$stats_result = $koneksi->query($stats_query);
$stats = $stats_result->fetch_assoc();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>
<link rel="stylesheet" href="css/dashboard.css">
<link rel="stylesheet" href="css/dataduk.css">
<style>
    .stats-container {
        display: grid; grid-template-columns: repeat(3,1fr); gap: 20px; margin-bottom: 30px;
    }
    .stat-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border-radius: 12px; padding: 25px; color: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.3s;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-card.approved { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-card.rejected { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .stat-card h3 { font-size: 3rem; margin: 0 0 10px 0; font-weight: bold; }
    .stat-card p  { margin: 0; opacity: 0.9; font-size: 1rem; }

    .approval-card {
        background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s;
    }
    .approval-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

    .approval-info {
        display: grid; grid-template-columns: repeat(3,1fr); gap: 15px; margin-bottom: 15px;
    }
    @media(max-width:768px){ .approval-info { grid-template-columns: repeat(2,1fr); } }

    .info-item label { display: block; font-size: 0.85rem; color: #666; margin-bottom: 3px; }
    .info-item strong { display: block; color: #333; }

    .approval-actions { display: flex; gap: 10px; justify-content: flex-end; }

    .btn-approve {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: all 0.3s;
    }
    .btn-reject {
        background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
        color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: all 0.3s;
    }
    .btn-approve:hover, .btn-reject:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }

    /* Pensiun segera highlight */
    .badge-segera { background:#ff9800;color:white;padding:3px 10px;border-radius:10px;font-size:11px; }

    /* Modal */
    .modal {
        display: none; position: fixed; z-index: 1000;
        left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);
    }
    .modal.active { display: flex; align-items: center; justify-content: center; }
    .modal-content {
        background: white; padding: 30px; border-radius: 10px;
        max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;
    }
    .modal-header { margin-bottom: 20px; }
    .modal-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }

    .btn { padding: 10px 20px; border-radius: 5px; border: none; cursor: pointer; font-size: 14px; }
    .btn-secondary { background: #6c757d; color: white; }
    .btn-success   { background: linear-gradient(135deg,#11998e 0%,#38ef7d 100%); color: white; }
    .btn-danger    { background: linear-gradient(135deg,#ee0979 0%,#ff6a00 100%); color: white; }
    .btn-info      { background: #17a2b8; color: white; text-decoration: none; padding: 8px 14px; border-radius: 4px; font-size: 13px; }

    .form-control { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; margin-top: 5px; }
    .form-group   { margin-top: 15px; }
    .form-group label { font-weight: 500; color: #444; }
    .alert-warning { background:#fff3cd; border:1px solid #ffc107; padding:12px; border-radius:5px; margin-top:15px; }
</style>

<main class="main-content">
    <div class="dashboard-header">
        <div>
            <h1><i class="fas fa-check-double"></i> Approval Usulan Pensiun</h1>
            <p>Proses persetujuan usulan pensiun pegawai</p>
        </div>
    </div>

    <!-- Statistik -->
    <div class="stats-container">
        <div class="stat-card">
            <h3><?= $stats['pending'] ?></h3>
            <p><i class="fas fa-clock"></i> Menunggu Persetujuan</p>
        </div>
        <div class="stat-card approved">
            <h3><?= $stats['disetujui'] ?></h3>
            <p><i class="fas fa-check-circle"></i> Disetujui</p>
        </div>
        <div class="stat-card rejected">
            <h3><?= $stats['ditolak'] ?></h3>
            <p><i class="fas fa-times-circle"></i> Ditolak</p>
        </div>
    </div>

    <!-- Daftar Usulan -->
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()):
            $tgl_pensiun = strtotime($row['tanggal_pensiun']);
            $bulan_lagi  = ($tgl_pensiun - time()) / (30 * 24 * 3600);
            $segera      = ($bulan_lagi <= 3 && $bulan_lagi >= 0);
        ?>
        <div class="approval-card" <?= $segera ? 'style="border-left:4px solid #ff9800;"' : '' ?>>
            <h4 style="margin-bottom:15px;color:#333;">
                <i class="fas fa-user-circle" style="color:#3b82f6;"></i>
                <?= htmlspecialchars($row['nama']) ?>
                <?php if ($segera): ?>
                    <span class="badge-segera"><i class="fas fa-exclamation"></i> Pensiun Segera</span>
                <?php endif; ?>
            </h4>

            <div class="approval-info">
                <div class="info-item">
                    <label>NIP</label>
                    <strong><?= htmlspecialchars($row['nip']) ?></strong>
                </div>
                <div class="info-item">
                    <label>Nomor Usulan</label>
                    <strong><?= htmlspecialchars($row['nomor_usulan']) ?></strong>
                </div>
                <div class="info-item">
                    <label>Pangkat / Golongan</label>
                    <strong><?= htmlspecialchars($row['pangkat_terakhir']) ?> (<?= htmlspecialchars($row['golongan']) ?>)</strong>
                </div>
                <div class="info-item">
                    <label>Jabatan Terakhir</label>
                    <strong><?= htmlspecialchars($row['jabatan_terakhir']) ?></strong>
                </div>
                <div class="info-item">
                    <label>Jenis Pensiun</label>
                    <strong><?= htmlspecialchars(strtoupper($row['jenis_pensiun'] ?: 'BUP')) ?></strong>
                </div>
                <div class="info-item">
                    <label>Tanggal Pensiun</label>
                    <strong style="color:<?= $segera ? '#ff9800' : '#333' ?>;">
                        <?= date('d F Y', strtotime($row['tanggal_pensiun'])) ?>
                    </strong>
                </div>
            </div>

            <div class="approval-actions">
                <a href="detail_usulan_pensiun.php?id=<?= $row['id'] ?>" class="btn-info">
                    <i class="fas fa-eye"></i> Lihat Detail
                </a>
                <button onclick="openApproveModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['nip']) ?>')"
                        class="btn-approve">
                    <i class="fas fa-check"></i> Setujui
                </button>
                <button onclick="openRejectModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['nip']) ?>')"
                        class="btn-reject">
                    <i class="fas fa-times"></i> Tolak
                </button>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card">
            <div class="card-body" style="text-align:center;padding:60px 20px;">
                <i class="fas fa-inbox" style="font-size:4rem;color:#ccc;margin-bottom:20px;display:block;"></i>
                <h4 style="color:#999;">Tidak ada usulan yang menunggu persetujuan</h4>
                <p style="color:#999;">Semua usulan pensiun telah diproses</p>
            </div>
        </div>
    <?php endif; ?>
</main>

<!-- Modal Setujui -->
<div id="approveModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="color:#28a745;"><i class="fas fa-check-circle"></i> Setujui Usulan Pensiun</h3>
        </div>
        <form method="POST" action="proses_approval_pensiun.php">
            <p>Anda yakin ingin menyetujui usulan pensiun untuk:</p>
            <h5 id="approve-nama" style="margin:10px 0;"></h5>
            <p style="color:#666;font-size:0.9rem;" id="approve-nip"></p>

            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="keterangan" class="form-control" rows="3"
                          placeholder="Tambahkan catatan persetujuan..."></textarea>
            </div>

            <input type="hidden" name="id" id="approve-id">
            <input type="hidden" name="action" value="approve">

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('approveModal')">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Ya, Setujui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tolak -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="color:#dc3545;"><i class="fas fa-times-circle"></i> Tolak Usulan Pensiun</h3>
        </div>
        <form method="POST" action="proses_approval_pensiun.php">
            <p>Anda yakin ingin menolak usulan pensiun untuk:</p>
            <h5 id="reject-nama" style="margin:10px 0;"></h5>
            <p style="color:#666;font-size:0.9rem;" id="reject-nip"></p>

            <div class="alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Perhatian:</strong> Alasan penolakan wajib diisi!
            </div>

            <div class="form-group">
                <label>Alasan Penolakan <span style="color:red;">*</span></label>
                <textarea name="keterangan" class="form-control" rows="4"
                          placeholder="Masukkan alasan penolakan secara detail..." required></textarea>
                <small style="color:#6c757d;">
                    <i class="fas fa-info-circle"></i> Alasan ini akan dicatat pada data usulan
                </small>
            </div>

            <input type="hidden" name="id" id="reject-id">
            <input type="hidden" name="action" value="reject">

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('rejectModal')">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-ban"></i> Ya, Tolak
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
function openApproveModal(id, nama, nip) {
    document.getElementById('approve-id').value   = id;
    document.getElementById('approve-nama').textContent = nama;
    document.getElementById('approve-nip').textContent  = 'NIP: ' + nip;
    document.getElementById('approveModal').classList.add('active');
}
function openRejectModal(id, nama, nip) {
    document.getElementById('reject-id').value   = id;
    document.getElementById('reject-nama').textContent = nama;
    document.getElementById('reject-nip').textContent  = 'NIP: ' + nip;
    document.getElementById('rejectModal').classList.add('active');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}
window.onclick = function(e) {
    if (e.target.classList.contains('modal')) e.target.classList.remove('active');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.querySelectorAll('.modal').forEach(m => m.classList.remove('active'));
});
</script>
<?php $koneksi->close(); ?>
