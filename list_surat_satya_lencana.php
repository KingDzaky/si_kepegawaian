<?php
session_start();
require_once 'check_session.php';
if (!isAdmin()) { 
    header('Location: dashboard.php?error=Akses ditolak'); 
    exit; 
}
require_once 'config/koneksi.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Filter tahun dari GET parameter
$filter_tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : null;

// Ambil daftar tahun yang tersedia di database
$query_tahun = "SELECT DISTINCT YEAR(tanggal_usulan) as tahun 
                FROM kenaikan_pangkat 
                WHERE tanggal_usulan IS NOT NULL 
                ORDER BY tahun DESC";
$result_tahun = $koneksi->query($query_tahun);

$tahun_list = [];
if ($result_tahun && $result_tahun->num_rows > 0) {
    while ($row_tahun = $result_tahun->fetch_assoc()) {
        $tahun_list[] = $row_tahun['tahun'];
    }
}

// Jika tidak ada tahun yang dipilih, gunakan tahun terbaru
if ($filter_tahun === null && !empty($tahun_list)) {
    $filter_tahun = $tahun_list[0]; // Tahun terbaru
}

// Jika masih null (tidak ada data sama sekali), gunakan tahun sekarang
if ($filter_tahun === null) {
    $filter_tahun = date('Y');
}

// Query untuk mengambil daftar pegawai dari kenaikan_pangkat dengan filter tahun
$query = "SELECT 
    k.id,
    k.nama,
    k.nip,
    k.pangkat_baru,
    k.golongan_baru,
    k.jabatan_baru,
    k.nomor_usulan,
    k.tanggal_usulan
FROM kenaikan_pangkat k
WHERE YEAR(k.tanggal_usulan) = ?
ORDER BY k.nama ASC";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $filter_tahun);
$stmt->execute();
$result = $stmt->get_result();

// Hitung total pegawai
$total_pegawai = $result->num_rows;

// Hitung statistik per tahun untuk info
$query_stats = "SELECT 
    COUNT(*) as total,
    MIN(tanggal_usulan) as tanggal_awal,
    MAX(tanggal_usulan) as tanggal_akhir
FROM kenaikan_pangkat 
WHERE YEAR(tanggal_usulan) = ?";
$stmt_stats = $koneksi->prepare($query_stats);
$stmt_stats->bind_param("i", $filter_tahun);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
?>

<link rel="stylesheet" href="css/dataduk.css">
<style>
.surat-options {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-surat {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    font-size: 0.85rem;
}

.btn-disiplin {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
}

.btn-pidana {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
}

.btn-surat:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    color: white;
    text-decoration: none;
}

.badge-info-custom {
    background: linear-gradient(135deg, var(--info-color), #5352ed);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.75rem;
    display: inline-block;
}

/* Filter Year Styles - DROPDOWN */
.year-filter-section {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    padding: 20px 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(44, 62, 80, 0.3);
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.year-filter-section h4 {
    color: white;
    margin: 0;
    font-size: 16px;
    white-space: nowrap;
}

.year-dropdown-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}

.year-dropdown-wrap label {
    color: rgba(255,255,255,0.85);
    font-size: 14px;
    white-space: nowrap;
    margin: 0;
}

.year-select {
    background: white;
    color: #2c3e50;
    border: none;
    padding: 9px 36px 9px 14px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    min-width: 150px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%232c3e50' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-color: white;
    transition: all 0.2s;
}

.year-select:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255,255,255,0.4);
}

.year-select:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.year-info-badge {
    background: rgba(255,255,255,0.15);
    color: white;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    white-space: nowrap;
}

.year-info-badge strong {
    color: #f9ca24;
}

.btn-export-nominatif {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    transition: all 0.3s;
    border: none;
}

.btn-export-nominatif:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
    color: white;
    text-decoration: none;
}
</style>

<div class="main-content">
    <!-- Dashboard Header -->
    <div class="dashboard-header fade-in">
        <div>
            <h1 class="dashboard-title">
                <i class="fas fa-file-contract"></i> Surat Satya Lencana
            </h1>
            <p class="dashboard-subtitle">Daftar Surat Pernyataan dan Dokumen Pendukung Lainnya</p>
        </div>
    </div>

    <!-- Year Filter Section - DROPDOWN -->
    <div class="year-filter-section fade-in">
        <h4><i class="fas fa-calendar-alt me-2"></i>Filter Tahun</h4>
        
        <div class="year-dropdown-wrap">
            <label for="yearSelect"><i class="fas fa-filter me-1"></i> Pilih Tahun:</label>
            <select class="year-select" id="yearSelect" onchange="gantiTahun(this.value)">
                <?php if (!empty($tahun_list)): ?>
                    <?php foreach ($tahun_list as $tahun): ?>
                        <option value="<?= $tahun ?>" <?= ($tahun == $filter_tahun) ? 'selected' : '' ?>>
                            📅 Tahun <?= $tahun ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="<?= date('Y') ?>" selected>
                        📅 Tahun <?= date('Y') ?>
                    </option>
                <?php endif; ?>
            </select>
        </div>

        <?php if ($total_pegawai > 0): ?>
        <div class="year-info-badge">
            <i class="fas fa-users me-1"></i>
            <strong><?= $total_pegawai ?></strong> pegawai
            <?php if ($stats['tanggal_awal'] && $stats['tanggal_akhir']): ?>
                &nbsp;·&nbsp; <?= date('d/m/Y', strtotime($stats['tanggal_awal'])) ?> 
                – <?= date('d/m/Y', strtotime($stats['tanggal_akhir'])) ?>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="year-info-badge">
            <i class="fas fa-inbox me-1"></i> Tidak ada data tahun <strong><?= $filter_tahun ?></strong>
        </div>
        <?php endif; ?>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container fade-in">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <h2 class="stat-number"><?= $total_pegawai ?></h2>
            <p class="stat-label">Total Pegawai (<?= $filter_tahun ?>)</p>
            <div class="stat-trend">
                <i class="fas fa-check-circle"></i>
                <span>Siap untuk diexport</span>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <h2 class="stat-number">2</h2>
            <p class="stat-label">Jenis Surat</p>
            <div class="stat-trend">
                <i class="fas fa-list"></i>
                <span>Tersedia</span>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-file-pdf"></i>
            </div>
            <h2 class="stat-number">PDF</h2>
            <p class="stat-label">Format Export</p>
            <div class="stat-trend">
                <i class="fas fa-download"></i>
                <span>Siap cetak</span>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="filter-section fade-in">
        <h4 style="margin-bottom: 10px;"><i class="fas fa-info-circle"></i> Jenis Surat Tersedia:</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
            <div style="background: #FAAC9D; padding: 15px; border-radius: 8px; border-left: 4px solid #F54927;">
                <strong>1. SPMT</strong>
                <p style="margin: 5px 0 0 0; font-size: 0.9rem;">Surat Pernyataan Melaksanakan Tugas</p>
            </div>
            <div style="background: #d1ecf1; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8;">
                <strong>2. Surat Pernyataan</strong>
                <p style="margin: 5px 0 0 0; font-size: 0.9rem;">Surat pernyataan bahwa pegawai tidak sedang menjalani proses pidana atau pernah dipidana penjara</p>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-section fade-in">
        <div class="table-header">
            <h2 class="table-title">
                <i class="fas fa-list"></i>
                Daftar Pegawai Tahun <?= $filter_tahun ?>
            </h2>
            <div class="table-controls">
                <div class="search-box">
                    <input type="text" 
                           class="search-input" 
                           id="searchInput" 
                           placeholder="Cari pegawai...">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>
            <a class="btn-export-nominatif" 
               href="export_nominatif_satya_lencana.php?tahun=<?= $filter_tahun ?>" 
               target="_blank">
                <i class="fas fa-medal"></i> 
                Export Nominatif Satya Lencana <?= $filter_tahun ?>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table" id="tableSurat">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 20%;">No Usulan</th>
                        <th style="width: 25%;">Pegawai</th>
                        <th style="width: 18%;">Pangkat / Golongan</th>
                        <th style="width: 22%;">Jabatan</th>
                        <th style="width: 10%;" class="text-center">Export Surat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result->num_rows > 0):
                        $no = 1;
                        while($row = $result->fetch_assoc()): 
                            $nama_parts = explode(' ', $row['nama']);
                            $inisial = '';
                            foreach($nama_parts as $part) {
                                if (!empty($part)) {
                                    $inisial .= strtoupper($part[0]);
                                    if (strlen($inisial) >= 2) break;
                                }
                            }
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($row['nomor_usulan']) ?></strong>
                            <br><small class="text-muted">
                                <i class="fas fa-calendar"></i> 
                                <?= date('d-m-Y', strtotime($row['tanggal_usulan'])) ?>
                            </small>
                        </td>
                        <td>
                            <div class="employee-info">
                                <div class="employee-avatar">
                                    <?= $inisial ?>
                                </div>
                                <div class="employee-details">
                                    <h6 class="employee-name"><?= htmlspecialchars($row['nama']) ?></h6>
                                    <small class="employee-nip">NIP: <?= htmlspecialchars($row['nip']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-info-custom">
                                <?= htmlspecialchars($row['pangkat_baru']) ?> (<?= htmlspecialchars($row['golongan_baru']) ?>)
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['jabatan_baru']) ?></td>
                        <td class="text-center">
                            <div class="surat-options">
                                <a href="export_surat_satya_lencana.php?id=<?= $row['id'] ?>" 
                                   target="_blank" 
                                   class="btn-surat btn-disiplin"
                                   title="Surat Pernyataan Melaksanakan Tugas">
                                    <i class="fas fa-file-alt"></i>
                                    SPMT
                                </a>
                                <a href="export_surat_pernyataan_satya.php?id=<?= $row['id'] ?>" 
                                   target="_blank" 
                                   class="btn-surat btn-pidana"
                                   title="Surat Tidak Sedang Menjalani Proses Pidana">
                                    <i class="fas fa-gavel"></i>
                                    Pernyataan
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h4>Tidak ada data</h4>
                                <p>Belum ada pegawai yang terdaftar pada tahun <?= $filter_tahun ?></p>
                                <?php if (!empty($tahun_list)): ?>
                                <p style="margin-top: 10px;">
                                    <a href="?tahun=<?= $tahun_list[0] ?>" class="btn btn-primary">
                                        <i class="fas fa-arrow-left"></i> Kembali ke Tahun <?= $tahun_list[0] ?>
                                    </a>
                                </p>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pegawai > 0): ?>
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: center;">
            <p style="margin: 0; color: #666;">
                <i class="fas fa-info-circle"></i>
                Total <strong><?= $total_pegawai ?></strong> pegawai ditampilkan dari tahun <strong><?= $filter_tahun ?></strong>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Fungsi ganti tahun via dropdown
function gantiTahun(tahun) {
    window.location.href = '?tahun=' + tahun;
}

// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('#tableSurat tbody tr');
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Add fade-in animation on scroll
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

<?php 
$stmt->close();
$stmt_stats->close();
$koneksi->close();
require_once 'includes/footer.php'; 
?>