<?php
session_start();
require_once 'config/koneksi.php';
require_once 'check_session.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';



// Ambil ID dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: kenaikan_pangkat.php?error=ID tidak valid');
    exit;
}

$id = (int)$_GET['id'];

// Query data lengkap
$query = "SELECT 
    k.*,
    o.nama as nama_kepala_opd,
    o.nip as nip_kepala_opd,
    o.pangkat as pangkat_kepala_opd,
    o.jabatan as jabatan_kepala_opd,
    o.gelar_depan,
    o.gelar_belakang,
    o.golongan as golongan_kepala_opd
FROM kenaikan_pangkat k
LEFT JOIN kepala_opd o ON k.id_opd = o.id
WHERE k.id = ?
LIMIT 1";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: kenaikan_pangkat.php?error=Data tidak ditemukan');
    exit;
}

$data = $result->fetch_assoc();
$stmt->close();

// Format nama kepala dengan gelar
$gelar_depan = !empty($data['gelar_depan']) ? $data['gelar_depan'] . ' ' : '';
$gelar_belakang = !empty($data['gelar_belakang']) ? ', ' . $data['gelar_belakang'] : '';
$nama_kepala_lengkap = $gelar_depan . $data['nama_kepala_opd'] . $gelar_belakang;

// Format tanggal
function formatTanggal($tanggal) {
    if (empty($tanggal)) return '-';
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

$koneksi->close();
?>

<link rel="stylesheet" href="css/dataduk.css">

<style>
/* Additional styles for detail page */
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

.section-header i {
    font-size: 2rem;
    color: var(--primary-color);
}

.section-header h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--dark-color);
    margin: 0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-label {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 1.1rem;
    color: var(--dark-color);
    font-weight: 600;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid var(--primary-color);
}

.comparison-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.comparison-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    border: 2px solid #dee2e6;
}

.comparison-card.old {
    border-left: 5px solid var(--warning-color);
}

.comparison-card.new {
    border-left: 5px solid var(--success-color);
}

.comparison-title {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.comparison-title.old {
    color: var(--warning-color);
}

.comparison-title.new {
    color: var(--success-color);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    font-size: 1rem;
}

.status-badge.disetujui {
    background: var(--success-color);
    color: white;
}

.status-badge.pending {
    background: var(--warning-color);
    color: white;
}

.status-badge.ditolak {
    background: var(--danger-color);
    color: white;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
    border: none;
    cursor: pointer;
}

.btn-back {
    background: var(--dark-color);
    color: white;
}

.btn-print {
    background: var(--info-color);
    color: white;
}

.btn-edit {
    background: var(--warning-color);
    color: white;
}

.btn-delete {
    background: var(--danger-color);
    color: white;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    color: white;
    text-decoration: none;
}

@media (max-width: 768px) {
    .comparison-section {
        grid-template-columns: 1fr;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="main-content">
    <!-- Dashboard Header -->
    <div class="dashboard-header fade-in">
        <div>
            <h1 class="dashboard-title">
                <i class="fas fa-file-alt"></i> Detail Usulan Kenaikan Pangkat
            </h1>
            <p class="dashboard-subtitle">Informasi lengkap usulan kenaikan pangkat pegawai</p>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="stats-container fade-in">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-user"></i>
            </div>
            <h2 class="stat-number"><?= htmlspecialchars($data['nama']) ?></h2>
            <p class="stat-label">Nama Pegawai</p>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-id-card"></i>
            </div>
            <h2 class="stat-number"><?= htmlspecialchars($data['nip']) ?></h2>
            <p class="stat-label">NIP</p>
        </div>

        <div class="stat-card <?= $data['status'] == 'disetujui' ? 'success' : ($data['status'] == 'pending' ? 'warning' : 'danger') ?>">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="stat-number" style="text-transform: capitalize;"><?= htmlspecialchars($data['status']) ?></h2>
            <p class="stat-label">Status Usulan</p>
        </div>
    </div>

    <!-- Detail Sections -->
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
                        <span class="info-value"><?= htmlspecialchars($data['nip']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-birthday-cake"></i> Tempat, Tanggal Lahir</span>
                        <span class="info-value"><?= htmlspecialchars($data['tempat_lahir']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-graduation-cap"></i> Pendidikan</span>
                        <span class="info-value"><?= htmlspecialchars($data['pendidikan_terakhir'] . ' - ' . $data['prodi']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Perbandingan Pangkat Lama vs Baru -->
            <div class="detail-card fade-in">
                <div class="section-header">
                    <i class="fas fa-exchange-alt"></i>
                    <h3>Perbandingan Pangkat</h3>
                </div>
                
                <div class="comparison-section">
                    <!-- Pangkat Lama -->
                    <div class="comparison-card old">
                        <h4 class="comparison-title old">
                            <i class="fas fa-arrow-circle-left"></i>
                            Pangkat LAMA
                        </h4>
                        
                        <div class="info-item" style="margin-bottom: 1rem;">
                            <span class="info-label">Pangkat / Golongan</span>
                            <span class="info-value"><?= htmlspecialchars($data['pangkat_lama'] . ' / ' . $data['golongan_lama']) ?></span>
                        </div>
                        
                        <div class="info-item" style="margin-bottom: 1rem;">
                            <span class="info-label">TMT Pangkat</span>
                            <span class="info-value"><?= formatTanggal($data['tmt_pangkat_lama']) ?></span>
                        </div>
                        
                        <div class="info-item" style="margin-bottom: 1rem;">
                            <span class="info-label">Masa Kerja</span>
                            <span class="info-value"><?= $data['masa_kerja_tahun_lama'] ?> Tahun <?= $data['masa_kerja_bulan_lama'] ?> Bulan</span>
                        </div>
                        
                        <div class="info-item" style="margin-bottom: 1rem;">
                            <span class="info-label">Gaji Pokok</span>
                            <span class="info-value">Rp <?= number_format($data['gaji_pokok_lama'], 0, ',', '.') ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Jabatan</span>
                            <span class="info-value"><?= htmlspecialchars($data['jabatan_lama']) ?></span>
                        </div>
                    </div>

                    <!-- Pangkat Baru -->
                    <div class="comparison-card new">
                        <h4 class="comparison-title new">
                            <i class="fas fa-arrow-circle-right"></i>
                            Pangkat BARU
                        </h4>
                        
                        <div class="info-item" style="margin-bottom: 1rem;">
                            <span class="info-label">Pangkat / Golongan</span>
                            <span class="info-value"><?= htmlspecialchars($data['pangkat_baru'] . ' / ' . $data['golongan_baru']) ?></span>
                        </div>
                        
                        <div class="info-item" style="margin-bottom: 1rem;">
                            <span class="info-label">TMT Pangkat</span>
                            <span class="info-value"><?= formatTanggal($data['tmt_pangkat_baru']) ?></span>
                        </div>
                        
                        <div class="info-item" style="margin-bottom: 1rem;">
                            <span class="info-label">Masa Kerja</span>
                            <span class="info-value"><?= $data['masa_kerja_tahun_baru'] ?> Tahun <?= $data['masa_kerja_bulan_baru'] ?> Bulan</span>
                        </div>
                        
                        <div class="info-item" style="margin-bottom: 1rem;">
                            <span class="info-label">Gaji Pokok</span>
                            <span class="info-value">Rp <?= number_format($data['gaji_pokok_baru'], 0, ',', '.') ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Jabatan</span>
                            <span class="info-value"><?= htmlspecialchars($data['jabatan_baru']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Usulan -->
            <div class="detail-card fade-in">
                <div class="section-header">
                    <i class="fas fa-file-signature"></i>
                    <h3>Data Usulan</h3>
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
                        <span class="info-label"><i class="fas fa-map-marker-alt"></i> Wilayah Pembayaran</span>
                        <span class="info-value"><?= htmlspecialchars($data['wilayah_pembayaran']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-chart-line"></i> Jenis Kenaikan</span>
                        <span class="info-value"><?= htmlspecialchars($data['jenis_kenaikan']) ?></span>
                    </div>
                </div>

                <div class="info-grid" style="margin-top: 1.5rem;">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-star"></i> SKP Tahun <?= $data['skp_tahun_1'] ?></span>
                        <span class="info-value"><?= htmlspecialchars($data['skp_nilai_1']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-star"></i> SKP Tahun <?= $data['skp_tahun_2'] ?></span>
                        <span class="info-value"><?= htmlspecialchars($data['skp_nilai_2']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Data Atasan -->
            <div class="detail-card fade-in">
                <div class="section-header">
                    <i class="fas fa-user-tie"></i>
                    <h3>Data Kepala OPD</h3>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-user"></i> Nama</span>
                        <span class="info-value"><?= htmlspecialchars($nama_kepala_lengkap) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-id-card"></i> NIP</span>
                        <span class="info-value"><?= htmlspecialchars($data['nip_kepala_opd']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-medal"></i> Pangkat / Golongan</span>
                        <span class="info-value"><?= htmlspecialchars($data['pangkat_kepala_opd'] . ' / ' . $data['golongan_kepala_opd']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-briefcase"></i> Jabatan</span>
                        <span class="info-value"><?= htmlspecialchars($data['jabatan_kepala_opd']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons fade-in">
                <a href="laporan.php" class="btn-action btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
            </div>

        </div>
    </div>
</div>

<!-- Include SweetAlert untuk konfirmasi hapus -->
<?php include 'includes/sweetalert.php'; ?>
<?php include 'includes/confirm_delete.php'; ?>

<script>
// Fade-in animation on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

document.querySelectorAll('.fade-in').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
});
</script>

<?php include 'includes/footer.php'; ?>