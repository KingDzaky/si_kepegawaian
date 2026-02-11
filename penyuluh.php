<?php
session_start();
require_once 'check_session.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';


// Hanya superadmin dan admin yang bisa akses
if (!isAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}


// Statistik dengan query yang lebih detail
$stats_query = "
    SELECT 
        COUNT(*) as total_penyuluh,
        COUNT(CASE WHEN jenis_kelamin = 'Laki-laki' THEN 1 END) as total_laki,
        COUNT(CASE WHEN jenis_kelamin = 'Perempuan' THEN 1 END) as total_perempuan,
        COUNT(CASE WHEN pangkat_terakhir IS NOT NULL AND pangkat_terakhir != '' THEN 1 END) as total_berpangkat
    FROM penyuluh
";
$stats = $koneksi->query($stats_query)->fetch_assoc();

// Query untuk filter options
$pangkat_options = $koneksi->query("SELECT DISTINCT pangkat_terakhir FROM penyuluh WHERE pangkat_terakhir != '' ORDER BY pangkat_terakhir")->fetch_all(MYSQLI_ASSOC);
$golongan_options = $koneksi->query("SELECT DISTINCT golongan FROM penyuluh WHERE golongan != '' ORDER BY golongan")->fetch_all(MYSQLI_ASSOC);
$jabatan_options = $koneksi->query("SELECT DISTINCT jabatan_terakhir FROM penyuluh WHERE jabatan_terakhir != '' ORDER BY jabatan_terakhir")->fetch_all(MYSQLI_ASSOC);
$pendidikan_options = $koneksi->query("SELECT DISTINCT pendidikan_terakhir FROM penyuluh WHERE pendidikan_terakhir != '' ORDER BY pendidikan_terakhir")->fetch_all(MYSQLI_ASSOC);
?>

<link rel="stylesheet" href="css/penyuluh.css">
<main class="main-content">
  <!-- Dashboard Header -->
  <div class="dashboard-header fade-in">
    <div class="header-content">
      <div class="header-icon">
        <i class="fas fa-chalkboard-teacher"></i>
      </div>
      <div class="header-text">
        <h1 class="dashboard-title">Data Penyuluh</h1>
        <p class="dashboard-subtitle">Sistem Informasi Manajemen Data Penyuluh DPPKBPM</p>
      </div>
    </div>
  </div>

  <!-- Enhanced Statistics -->
  <div class="stats-container fade-in">
    <div class="stat-card primary" data-aos="fade-up" data-aos-delay="100">
      <div class="stat-background">
        <i class="fas fa-users"></i>
      </div>
      <div class="stat-content">
        <div class="stat-icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <h3 class="stat-number"><?= $stats['total_penyuluh'] ?></h3>
        <p class="stat-label">Total Penyuluh</p>
        <div class="stat-trend trend-up">
          <i class="fas fa-arrow-up me-1"></i>
          <span>Data terkini</span>
        </div>
      </div>
    </div>

    <div class="stat-card success" data-aos="fade-up" data-aos-delay="200">
      <div class="stat-background">
        <i class="fas fa-male"></i>
      </div>
      <div class="stat-content">
        <div class="stat-icon">
          <i class="fas fa-mars"></i>
        </div>
        <h3 class="stat-number"><?= $stats['total_laki'] ?></h3>
        <p class="stat-label">Penyuluh Laki-laki</p>
        <div class="stat-trend">
          <span><?= $stats['total_penyuluh'] > 0 ? round(($stats['total_laki'] / $stats['total_penyuluh']) * 100, 1) : 0 ?>% dari total</span>
        </div>
      </div>
    </div>

    <div class="stat-card warning" data-aos="fade-up" data-aos-delay="300">
      <div class="stat-background">
        <i class="fas fa-female"></i>
      </div>
      <div class="stat-content">
        <div class="stat-icon">
          <i class="fas fa-venus"></i>
        </div>
        <h3 class="stat-number"><?= $stats['total_perempuan'] ?></h3>
        <p class="stat-label">Penyuluh Perempuan</p>
        <div class="stat-trend">
          <span><?= $stats['total_penyuluh'] > 0 ? round(($stats['total_perempuan'] / $stats['total_penyuluh']) * 100, 1) : 0 ?>% dari total</span>
        </div>
      </div>
    </div>

  </div>

  <!-- Advanced Filter Section -->
  <div class="filter-section fade-in" data-aos="fade-up">
    <div class="filter-card">
      <div class="filter-header">
        <h5 class="filter-title">
          <i class="fas fa-filter me-2"></i>
          Filter & Pencarian Data Penyuluh
        </h5>
        <button class="filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#filterContent" aria-expanded="false">
          <span>Tampilkan Filter</span>
          <i class="fas fa-chevron-down ms-2"></i>
        </button>
      </div>
      
      <div class="collapse filter-content" id="filterContent">
        <form id="filterForm" class="filter-form">
          <div class="filter-grid">
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-search me-2"></i>Pencarian Nama/NIP
              </label>
              <input type="text" class="form-control filter-input" id="searchName" 
                     placeholder="Masukkan nama atau NIP...">
            </div>
            
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-venus-mars me-2"></i>Jenis Kelamin
              </label>
              <select class="form-select filter-select" id="filterGender">
                <option value="">Semua Jenis Kelamin</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>
            
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-medal me-2"></i>Pangkat
              </label>
              <select class="form-select filter-select" id="filterPangkat">
                <option value="">Semua Pangkat</option>
                <?php foreach($pangkat_options as $pangkat): ?>
                  <option value="<?= htmlspecialchars($pangkat['pangkat_terakhir']) ?>">
                    <?= htmlspecialchars($pangkat['pangkat_terakhir']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-layer-group me-2"></i>Golongan
              </label>
              <select class="form-select filter-select" id="filterGolongan">
                <option value="">Semua Golongan</option>
                <?php foreach($golongan_options as $golongan): ?>
                  <option value="<?= htmlspecialchars($golongan['golongan']) ?>">
                    <?= htmlspecialchars($golongan['golongan']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-briefcase me-2"></i>Jabatan
              </label>
              <select class="form-select filter-select" id="filterJabatan">
                <option value="">Semua Jabatan</option>
                <?php foreach($jabatan_options as $jabatan): ?>
                  <option value="<?= htmlspecialchars($jabatan['jabatan_terakhir']) ?>">
                    <?= htmlspecialchars($jabatan['jabatan_terakhir']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-graduation-cap me-2"></i>Pendidikan
              </label>
              <select class="form-select filter-select" id="filterPendidikan">
                <option value="">Semua Pendidikan</option>
                <?php foreach($pendidikan_options as $pendidikan): ?>
                  <option value="<?= htmlspecialchars($pendidikan['pendidikan_terakhir']) ?>">
                    <?= htmlspecialchars($pendidikan['pendidikan_terakhir']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="filter-actions">
    <div class="action-buttons">
        <button type="button" class="btn btn-primary" id="applyFilter">
            <i class="fas fa-search me-2"></i>Terapkan Filter
        </button>
        <button type="button" class="btn btn-secondary" id="resetFilter">
            <i class="fas fa-refresh me-2"></i>Reset Filter
        </button>
        <button type="button" class="btn btn-success" onclick="exportDataBtn()">
            <i class="fas fa-download me-2"></i>Export Data
          </button>
          <a class="btn btn-info" href="export_semua_data_penyuluh.php?format=pdf" target="_blank">
              <i class="fas fa-file-pdf"></i> Export PDF
          </a>
    </div>
    
    <div class="filter-info">
        <span class="result-count" id="resultCount">Menampilkan semua data</span>
    </div>
</div>
            
            <div class="filter-info">
              <span class="result-count" id="resultCount">Menampilkan semua data</span>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Enhanced Table Section -->
      <div class="table-header">
        <h5 class="table-title">
          <i class="fas fa-table me-2"></i>
          Data Penyuluh DPPKBPM Kota Banjarmasin
        </h5>
        <div class="table-controls">
  <div class="search-box">
    <input type="text" class="search-input" id="quickSearch" placeholder="Pencarian cepat...">
    <i class="fas fa-search search-icon"></i>
  </div>
  <a href="form_tambah_penyuluh.php" class="btn btn-success btn-sm">
    <i class="fas fa-plus me-3"></i>Tambah Data
  </a>
</div>
</div>
      

        <!-- Table View -->
        <div class="table-content">
  <div class="table-responsive">
    <table class="table table-hover" id="penyuluhTable">
      <thead>
        <tr>
          <th>Penyuluh</th>
          <th>Pangkat/Gol</th>
          <th>Jabatan</th>
          <th>TTL</th>
          <th>Jenis Kelamin</th>
          <th>Pendidikan</th>
          <th>TMT</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <!-- Data will be populated by JavaScript -->
      </tbody>
    </table>
  </div>
</div>
      <!-- Pagination -->
      <div class="table-footer">
        <div class="pagination-info">
          <span id="paginationInfo">Menampilkan 1-10 dari 50 data</span>
        </div>
        <div class="pagination-controls">
          <nav aria-label="Pagination">
            <ul class="pagination" id="paginationNav">
              <!-- Pagination will be populated by JavaScript -->
            </ul>
          </nav>
        </div>
      </div>
    
  </div>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>

// ===================================
// GLOBAL VARIABLES
// ===================================
let allData = [];
let filteredData = [];
let currentPage = 1;
let itemsPerPage = 10;

// ===================================
// FETCH DATA FROM DATABASE
// ===================================
async function fetchData() {
    try {
        const response = await fetch('api/get_penyuluh.php');
        const data = await response.json();
        
        if (data.success) {
            allData = data.data;
            filteredData = allData;
            renderTable();
            updatePagination();
            updateResultCount();
        } else {
            console.error('Error fetching data:', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// ===================================
// RENDER TABLE
// ===================================
function renderTable() {
    const tbody = document.getElementById('tableBody');
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const paginatedData = filteredData.slice(start, end);
    
    if (paginatedData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Tidak ada data yang ditemukan</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = paginatedData.map(item => `
        <tr>
            <td>
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-details">
                        <strong>${item.nama || '-'}</strong>
                        <small class="text-muted d-block">NIP: ${item.nip || '-'}</small>
                    </div>
                </div>
            </td>
            <td>
                <div class="rank-info">
                    <span class="badge bg-primary">${item.pangkat_terakhir || '-'}</span>
                    <small class="d-block text-muted mt-1">${item.golongan || '-'}</small>
                </div>
            </td>
            <td>${item.jabatan_terakhir || '-'}</td>
            <td>
                <small>${item.ttl || '-'}</small>
            </td>
            <td>
                <span class="badge ${item.jenis_kelamin === 'Laki-laki' ? 'bg-info' : 'bg-warning'}">
                    <i class="fas ${item.jenis_kelamin === 'Laki-laki' ? 'fa-mars' : 'fa-venus'} me-1"></i>
                    ${item.jenis_kelamin || '-'}
                </span>
            </td>
            <td>
                <span class="badge bg-success">${item.pendidikan_terakhir || '-'}</span>
            </td>
            <td>
                <small>${item.tmt_pangkat || '-'}</small>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-info" onclick="viewDetail(${item.id})" title="Detail">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editData(${item.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteData(${item.id}, '${item.nama}')" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ===================================
// PAGINATION
// ===================================
function updatePagination() {
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    const paginationNav = document.getElementById('paginationNav');
    
    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            paginationHTML += `
                <li class="page-item ${currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    `;
    
    paginationNav.innerHTML = paginationHTML;
    
    // Update pagination info
    const start = (currentPage - 1) * itemsPerPage + 1;
    const end = Math.min(currentPage * itemsPerPage, filteredData.length);
    document.getElementById('paginationInfo').textContent = 
        `Menampilkan ${start}-${end} dari ${filteredData.length} data`;
}

function changePage(page) {
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        renderTable();
        updatePagination();
    }
}

// ===================================
// FILTER FUNCTIONALITY
// ===================================
function applyFilters() {
    const searchName = document.getElementById('searchName').value.toLowerCase();
    const filterGender = document.getElementById('filterGender').value;
    const filterPangkat = document.getElementById('filterPangkat').value;
    const filterGolongan = document.getElementById('filterGolongan').value;
    const filterJabatan = document.getElementById('filterJabatan').value;
    const filterPendidikan = document.getElementById('filterPendidikan').value;
    
    filteredData = allData.filter(item => {
        const matchName = !searchName || 
            (item.nama && item.nama.toLowerCase().includes(searchName)) ||
            (item.nip && item.nip.toLowerCase().includes(searchName));
        const matchGender = !filterGender || item.jenis_kelamin === filterGender;
        const matchPangkat = !filterPangkat || item.pangkat_terakhir === filterPangkat;
        const matchGolongan = !filterGolongan || item.golongan === filterGolongan;
        const matchJabatan = !filterJabatan || item.jabatan_terakhir === filterJabatan;
        const matchPendidikan = !filterPendidikan || item.pendidikan_terakhir === filterPendidikan;
        
        return matchName && matchGender && matchPangkat && matchGolongan && matchJabatan && matchPendidikan;
    });
    
    currentPage = 1;
    renderTable();
    updatePagination();
    updateResultCount();
}

function resetFilters() {
    document.getElementById('filterForm').reset();
    filteredData = allData;
    currentPage = 1;
    renderTable();
    updatePagination();
    updateResultCount();
}

function updateResultCount() {
    const resultCount = document.getElementById('resultCount');
    if (filteredData.length === allData.length) {
        resultCount.textContent = `Menampilkan semua data (${allData.length})`;
    } else {
        resultCount.textContent = `Ditemukan ${filteredData.length} dari ${allData.length} data`;
    }
}

// ===================================
// EXPORT FUNCTIONS
// ===================================
function exportDataBtn() {
    // Create modal for export options
    const modalHTML = `
        <div class="modal fade" id="exportModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-download me-2"></i>Export Data Penyuluh
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6 class="mb-3">Pilih Template Export:</h6>
                        <div class="list-group">
                            <button class="list-group-item list-group-item-action" onclick="exportTemplate1()">
                                <i class="fas fa-file-alt text-primary me-2"></i>
                                <strong>Template 1:</strong> Data Lengkap Semua Penyuluh
                                <small class="d-block text-muted">Export semua data dengan informasi lengkap</small>
                            </button>
                            <button class="list-group-item list-group-item-action" onclick="exportTemplate2()">
                                <i class="fas fa-venus-mars text-info me-2"></i>
                                <strong>Template 2:</strong> Berdasarkan Jenis Kelamin
                                <small class="d-block text-muted">Dikelompokkan berdasarkan Jenis Kelamin</small>
                            </button>
                            <button class="list-group-item list-group-item-action" onclick="exportTemplate3()">
                                <i class="fas fa-graduation-cap text-success me-2"></i>
                                <strong>Template 3:</strong> Berdasarkan Pendidikan Terakhir
                                <small class="d-block text-muted">Dikelompokkan berdasarkan tingkat pendidikan</small>
                            </button>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3">Format Export:</h6>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-danger" id="exportPDF">
                                <i class="fas fa-file-pdf me-2"></i>PDF
                            </button>
                            <button type="button" class="btn btn-outline-success" id="exportExcel">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('exportModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('exportModal'));
    modal.show();
}

// ===================================
// TEMPLATE 1: DATA LENGKAP
// ===================================
function exportTemplate1() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');
    
    // Header
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    doc.text('DAFTAR PENYULUH PERTANIAN', 148, 15, { align: 'center' });
    doc.setFontSize(12);
    doc.setFont(undefined, 'normal');
    doc.text('DINAS PENGENDALIAN PENDUDUK, KELUARGA BERENCANA', 148, 22, { align: 'center' });
    doc.text('DAN PEMBERDAYAAN MASYARAKAT', 148, 28, { align: 'center' });
    
    // Date
    doc.setFontSize(10);
    const today = new Date().toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    });
    doc.text(`Per Tanggal: ${today}`, 148, 35, { align: 'center' });
    
    // Table
    doc.autoTable({
        startY: 45,
        head: [['No', 'Nama', 'NIP', 'TTL', 'Jenis Kelamin', 'Pangkat', 'Golongan', 'Jabatan', 'Pendidikan', 'TMT']],
        body: filteredData.map((item, index) => [
            index + 1,
            item.nama || '-',
            item.nip || '-',
            item.ttl || '-',
            item.jenis_kelamin || '-',
            item.pangkat_terakhir || '-',
            item.golongan || '-',
            item.jabatan_terakhir || '-',
            item.pendidikan_terakhir || '-',
            item.tmt_pangkat || '-'
        ]),
        headStyles: {
            fillColor: [41, 128, 185],
            textColor: 255,
            fontSize: 8,
            fontStyle: 'bold',
            halign: 'center'
        },
        bodyStyles: {
            fontSize: 7,
            cellPadding: 2
        },
        alternateRowStyles: {
            fillColor: [245, 245, 245]
        },
        margin: { left: 10, right: 10 }
    });
    
    // Footer
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.text(`Halaman ${i} dari ${pageCount}`, 148, 200, { align: 'center' });
    }
    
    doc.save('Data_Penyuluh_Lengkap.pdf');
    bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
}

// ===================================
// TEMPLATE 2: BERDASARKAN GENDER
// ===================================
function exportTemplate2() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');
    
    // Group by gender
    const lakiLaki = filteredData.filter(item => item.jenis_kelamin === 'Laki-laki');
    const perempuan = filteredData.filter(item => item.jenis_kelamin === 'Perempuan');
    
    let startY = 15;
    
    // Header
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    doc.text('DAFTAR PENYULUH BERDASARKAN JENIS KELAMIN', 148, startY, { align: 'center' });
    startY += 10;
    
    // Laki-laki Section
    doc.setFontSize(14);
    doc.setTextColor(41, 128, 185);
    doc.text(`PENYULUH LAKI-LAKI (${lakiLaki.length} Orang)`, 14, startY);
    startY += 5;
    
    if (lakiLaki.length > 0) {
        doc.autoTable({
            startY: startY,
            head: [['No', 'Nama', 'NIP', 'Pangkat/Gol', 'Jabatan', 'Pendidikan']],
            body: lakiLaki.map((item, index) => [
                index + 1,
                item.nama || '-',
                item.nip || '-',
                `${item.pangkat_terakhir || '-'} (${item.golongan || '-'})`,
                item.jabatan_terakhir || '-',
                item.pendidikan_terakhir || '-'
            ]),
            headStyles: {
                fillColor: [52, 152, 219],
                textColor: 255,
                fontSize: 9,
                fontStyle: 'bold'
            },
            bodyStyles: { fontSize: 8 },
            alternateRowStyles: { fillColor: [235, 245, 251] },
            margin: { left: 14, right: 14 }
        });
        startY = doc.lastAutoTable.finalY + 10;
    }
    
    // Perempuan Section
    if (startY > 160) {
        doc.addPage();
        startY = 15;
    }
    
    doc.setFontSize(14);
    doc.setTextColor(233, 30, 99);
    doc.text(`PENYULUH PEREMPUAN (${perempuan.length} Orang)`, 14, startY);
    startY += 5;
    
    if (perempuan.length > 0) {
        doc.autoTable({
            startY: startY,
            head: [['No', 'Nama', 'NIP', 'Pangkat/Gol', 'Jabatan', 'Pendidikan']],
            body: perempuan.map((item, index) => [
                index + 1,
                item.nama || '-',
                item.nip || '-',
                `${item.pangkat_terakhir || '-'} (${item.golongan || '-'})`,
                item.jabatan_terakhir || '-',
                item.pendidikan_terakhir || '-'
            ]),
            headStyles: {
                fillColor: [233, 30, 99],
                textColor: 255,
                fontSize: 9,
                fontStyle: 'bold'
            },
            bodyStyles: { fontSize: 8 },
            alternateRowStyles: { fillColor: [252, 228, 236] },
            margin: { left: 14, right: 14 }
        });
    }
    
    doc.save('Data_Penyuluh_Gender.pdf');
    bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
}

// ===================================
// TEMPLATE 3: BERDASARKAN PENDIDIKAN
// ===================================
function exportTemplate3() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');
    
    // Group by education
    const educationGroups = {};
    filteredData.forEach(item => {
        const edu = item.pendidikan_terakhir || 'Tidak Diketahui';
        if (!educationGroups[edu]) {
            educationGroups[edu] = [];
        }
        educationGroups[edu].push(item);
    });
    
    // Sort education levels
    const eduOrder = ['S3', 'S2', 'S1', 'D4', 'D3', 'D2', 'D1', 'SMA/SMK', 'SMP', 'SD'];
    const sortedEdu = Object.keys(educationGroups).sort((a, b) => {
        const indexA = eduOrder.indexOf(a);
        const indexB = eduOrder.indexOf(b);
        if (indexA === -1 && indexB === -1) return a.localeCompare(b);
        if (indexA === -1) return 1;
        if (indexB === -1) return -1;
        return indexA - indexB;
    });
    
    let startY = 15;
    
    // Header
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    doc.text('DAFTAR PENYULUH BERDASARKAN PENDIDIKAN TERAKHIR', 148, startY, { align: 'center' });
    startY += 10;
    
    // Loop through education groups
    sortedEdu.forEach((edu, groupIndex) => {
        const group = educationGroups[edu];
        
        if (startY > 160 && groupIndex > 0) {
            doc.addPage();
            startY = 15;
        }
        
        doc.setFontSize(12);
        doc.setTextColor(76, 175, 80);
        doc.setFont(undefined, 'bold');
        doc.text(`${edu} (${group.length} Orang)`, 14, startY);
        startY += 5;
        
        doc.autoTable({
            startY: startY,
            head: [['No', 'Nama', 'NIP', 'Gender', 'Pangkat', 'Golongan', 'Jabatan']],
            body: group.map((item, index) => [
                index + 1,
                item.nama || '-',
                item.nip || '-',
                item.jenis_kelamin || '-',
                item.pangkat_terakhir || '-',
                item.golongan || '-',
                item.jabatan_terakhir || '-'
            ]),
            headStyles: {
                fillColor: [76, 175, 80],
                textColor: 255,
                fontSize: 9,
                fontStyle: 'bold'
            },
            bodyStyles: { fontSize: 8 },
            alternateRowStyles: { fillColor: [240, 248, 241] },
            margin: { left: 14, right: 14 }
        });
        
        startY = doc.lastAutoTable.finalY + 10;
    });
    
    doc.save('Data_Penyuluh_Pendidikan.pdf');
    bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
}

// ===================================
// PRINT FUNCTION
// ===================================
function printData() {
    window.print();
}

// ===================================
// CRUD FUNCTIONS
// ===================================
function viewDetail(id) {
    window.location.href = `detail_penyuluh.php?id=${id}`;
}

function editData(id) {
    window.location.href = `form_edit_penyuluh.php?id=${id}`;
}

function deleteData(id, nama) {
    // Gunakan SweetAlert2 untuk konfirmasi
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: `Data ${nama} akan dihapus dan tidak dapat dikembalikan!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect ke proses hapus dengan GET parameter
            window.location.href = `proses_hapus_penyuluh.php?id=${id}`;
        }
    });
}

// ===================================
// EVENT LISTENERS
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    fetchData();
    
    // Filter toggle
    const filterToggle = document.querySelector('.filter-toggle');
    if (filterToggle) {
        filterToggle.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const text = this.querySelector('span');
            
            setTimeout(() => {
                if (document.getElementById('filterContent').classList.contains('show')) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                    text.textContent = 'Sembunyikan Filter';
                } else {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                    text.textContent = 'Tampilkan Filter';
                }
            }, 350);
        });
    }
    
    // Apply filter button
    document.getElementById('applyFilter')?.addEventListener('click', applyFilters);
    
    // Reset filter button
    document.getElementById('resetFilter')?.addEventListener('click', resetFilters);
    
    // Quick search
    document.getElementById('quickSearch')?.addEventListener('input', function(e) {
        document.getElementById('searchName').value = e.target.value;
        applyFilters();
    });
    
    // Search name filter
    document.getElementById('searchName')?.addEventListener('input', applyFilters);
    
    // Print button
    document.getElementById('printData')?.addEventListener('click', printData);
});
</script>

<!-- ✅ PENTING: Include confirm_delete.php -->
<?php include 'includes/confirm_delete.php'; ?>

<!-- ✅ PENTING: Include alert_handler.php -->
<?php include 'includes/alert_handler.php'; ?>
<!-- ============================================
     TAMBAHKAN SCRIPT INI DI penyuluh.php
     SEBELUM tag penutup </main> atau sebelum jQuery
     ============================================ -->

<!-- jsPDF Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- jsPDF AutoTable Plugin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<!-- XLSX Library untuk Excel Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- Bootstrap JS (pastikan sudah ada) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (pastikan sudah ada) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<?php require_once 'includes/footer.php'; ?>