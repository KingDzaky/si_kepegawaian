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

// Query untuk mengambil daftar pegawai dari kenaikan_pangkat
$query = "SELECT 
    k.id,
    k.nama,
    k.nip,
    k.nomor_usulan,
    k.pangkat_baru,
    k.golongan_baru,
    k.jabatan_baru,
    k.nomor_usulan,
    k.tanggal_usulan
FROM kenaikan_pangkat k
ORDER BY k.nama ASC";

$result = $koneksi->query($query);

// Hitung total pegawai
$total_pegawai = $result->num_rows;
?>

<link rel="stylesheet" href="css/dataduk.css">
<style>
/* Additional Custom Styles */
.table-section {
    margin-top: 2rem;
}

.employee-name {
    font-weight: 600;
    color: var(--dark-color);
}

.employee-nip {
    font-size: 0.85rem;
    color: #6c757d;
}

.btn-export-pdf {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    padding: 0.1rem 0.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-export-pdf:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
    color: white;
    text-decoration: none;
}

.btn-export-pdf i {
    font-size: 1rem;
}

.text-center {
    text-align: center;
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
</style>

<div class="main-content">
    <!-- Dashboard Header -->
    <div class="dashboard-header fade-in">
        <div>
            <h1 class="dashboard-title">
                <i class="fas fa-file-alt"></i> Surat Keterangan
            </h1>
            <p class="dashboard-subtitle">Daftar Surat Keterangan PNS</p>
        </div>
    </div>

    
    <!-- Statistics Cards -->
    <div class="stats-container fade-in">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <h2 class="stat-number"><?= $total_pegawai ?></h2>
            <p class="stat-label">Total Pegawai</p>
            <div class="stat-trend">
                <i class="fas fa-check-circle"></i>
                <span>Siap untuk diexport</span>
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
            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;">
                <strong>Surat Keterangan</strong>
                <p style="margin: 5px 0 0 0; font-size: 0.9rem;">Surat Keterangan ini diberikan untuk melengkapi usul naik pangkat</p>
            </div>
        </div>
    </div>


    <!-- Table Section -->
    <div class="table-section fade-in">
        <div class="table-header">
            <h2 class="table-title">
                <i class="fas fa-list"></i>
                Daftar Pegawai
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
        </div>

        <div class="table-responsive">
            <table class="table" id="tableSurat">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 5%;">Nomor Usulan</th>
                        <th style="width: 30%;">Pegawai</th>
                        <th style="width: 20%;">Pangkat / Golongan </th>
                        <th style="width: 30%;">Jabatan</th>
                        <th style="width: 15%;" class="text-center">Export</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result->num_rows > 0):
                        $no = 1;
                        while($row = $result->fetch_assoc()): 
                            // Ambil inisial nama untuk avatar
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

                          <!-- Nomor Usulan -->
                        <td>
                            <strong><?= htmlspecialchars($row['nomor_usulan']) ?></strong>
                            <br><small class="text-muted"><?= date('d-m-Y', strtotime($row['tanggal_usulan'])) ?></small>
                        </td>

                        
                          <!-- Nama dan Nip -->
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

                     

                          <!-- Golongan Baru dan Export-->
                        <td>
                            <span class="badge-info-custom">
                                <?= htmlspecialchars($row['pangkat_baru']) ?> (<?= htmlspecialchars($row['golongan_baru']) ?>)
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['jabatan_baru']) ?></td>
                        <td class="text-center">
                            <a href="export_surat_keterangan.php?id=<?= $row['id'] ?>" 
                               target="_blank" 
                               class="btn-export-pdf"
                               title="Export ke PDF">
                                <i class="fas fa-file-pdf"></i>
                                Export PDF
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h4>Tidak ada data</h4>
                                <p>Belum ada pegawai yang terdaftar dalam sistem</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('#tableSurat tbody tr');
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
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

<?php require_once 'includes/footer.php'; ?>