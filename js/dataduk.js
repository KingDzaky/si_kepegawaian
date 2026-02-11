let originalData = [];
let filteredData = [];
let kepalaOPDData = null; // TAMBAHKAN INI


let currentPage = 1;
let itemsPerPage = 10;


async function fetchKepalaOPD() {
  try {
    const response = await fetch('get_kepala_opd.php');
    const data = await response.json();
    if (data.success) {
      kepalaOPDData = data.data;
      console.log('Data Kepala OPD berhasil dimuat:', kepalaOPDData);
    }
  } catch (error) {
    console.error('Error fetching Kepala OPD:', error);
  }
}

document.addEventListener('DOMContentLoaded', function () {
  fetchKepalaOPD();
  storeOriginalData();
  initializeEventListeners();
  initializeTooltips();
});

function storeOriginalData() {
  const tableRows = document.querySelectorAll('#dukTable tbody tr');
  originalData = [];

  tableRows.forEach(row => {
    const cells = row.querySelectorAll('td');
    if (cells.length > 1) {
      const tmtEselonCell = cells[6].textContent;
      const tmtMatch = tmtEselonCell.match(/TMT:\s*([^\n]+)/);
      const eselonMatch = tmtEselonCell.match(/Eselon:\s*([^\n]+)/);

      const data = {
        element: row,
        nama: cells[0].querySelector('.employee-details h6')?.textContent.trim() || '',
        nip: cells[0].querySelector('.employee-details small')?.textContent.replace(/.*NIP:\s*/, '').trim() || '',
        pangkat: cells[1].querySelector('strong')?.textContent.trim() || '',
        golongan: cells[1].querySelector('.badge')?.textContent.trim() || '',
        jabatan: cells[2].textContent.trim() || '',
        ttl: cells[3].textContent.trim() || '',
        jenis_kelamin: cells[4].querySelector('.badge')?.textContent.replace(/[♂♀]/g, '').trim() || '',
        pendidikan: cells[5].querySelector('.badge')?.textContent.trim() || '',
        tmt: tmtMatch ? tmtMatch[1].trim() : '',
        eselon: row.getAttribute('data-eselon') || (eselonMatch ? eselonMatch[1].trim() : ''),
        jenis_jabatan: row.getAttribute('data-jenis-jabatan') || '',
        jft_tingkat: row.getAttribute('data-jft-tingkat') || '',
        jfu_kelas: row.getAttribute('data-jfu-kelas') || '',
        id: cells[8].querySelector('.btn-info')?.href.match(/id=(\d+)/)?.[1] || ''
      };

      originalData.push(data);
    }
  });

  filteredData = [...originalData];
  console.log('Total data loaded:', originalData.length, 'records');

  const hasEselon = originalData.filter(d => d.eselon && d.eselon !== '-' && d.eselon !== 'Non-Eselon' && d.eselon.trim() !== '').length;
  const noEselon = originalData.length - hasEselon;
  console.log('Ber-Eselon:', hasEselon, '| Non Eselon:', noEselon);
  
  // Render pertama kali dengan pagination
  renderFilteredData();
}

  function initializeEventListeners() {
    const searchName = document.getElementById('searchName');
    const quickSearch = document.getElementById('quickSearch');
  
    if (searchName) searchName.addEventListener('input', debounce(applyFilters, 300));
    if (quickSearch) quickSearch.addEventListener('input', debounce(quickFilter, 300));
  
    const filterElements = ['filterGender', 'filterPangkat', 'filterGolongan', 'filterJabatan', 'filterPendidikan'];
    filterElements.forEach(id => {
      const element = document.getElementById(id);
      if (element) element.addEventListener('change', applyFilters);
    });
  }

// TAMBAHAN: Event listener untuk items per page
const itemsPerPageSelect = document.getElementById('itemsPerPage');
if (itemsPerPageSelect) {
  itemsPerPageSelect.addEventListener('change', function() {
    itemsPerPage = parseInt(this.value);
    currentPage = 1; // Reset ke halaman pertama
    renderFilteredData();
  });
}


function initializeTooltips() {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

  function applyFilters() {
    const filters = {
      name: document.getElementById('searchName')?.value.toLowerCase().trim() || '',
      gender: document.getElementById('filterGender')?.value || '',
      pangkat: document.getElementById('filterPangkat')?.value || '',
      golongan: document.getElementById('filterGolongan')?.value || '',
      jabatan: document.getElementById('filterJabatan')?.value || '',
      pendidikan: document.getElementById('filterPendidikan')?.value || ''
    };
  
    filteredData = originalData.filter(item => {
      if (filters.name) {
        const nameMatch = item.nama.toLowerCase().includes(filters.name);
        const nipMatch = item.nip.toLowerCase().includes(filters.name);
        if (!nameMatch && !nipMatch) return false;
      }
      if (filters.gender && item.jenis_kelamin !== filters.gender) return false;
      if (filters.pangkat && item.pangkat !== filters.pangkat) return false;
      if (filters.golongan && item.golongan !== filters.golongan) return false;
      if (filters.jabatan && item.jabatan !== filters.jabatan) return false;
      if (filters.pendidikan && item.pendidikan !== filters.pendidikan) return false;
      return true;
    });
  
    currentPage = 1; // Reset ke halaman pertama saat filter
    renderFilteredData();
    updateResultCount();
  
    const hasActiveFilters = Object.values(filters).some(value => value !== '');
    if (hasActiveFilters) {
      const filterContent = document.getElementById('filterContent');
      if (filterContent && !filterContent.classList.contains('show')) {
        new bootstrap.Collapse(filterContent, { show: true });
      }
    }
  }

  function quickFilter() {
    const searchTerm = document.getElementById('quickSearch')?.value.toLowerCase().trim() || '';
    if (searchTerm === '') {
      filteredData = [...originalData];
    } else {
      filteredData = originalData.filter(item => {
        return item.nama.toLowerCase().includes(searchTerm) ||
          item.nip.toLowerCase().includes(searchTerm) ||
          item.pangkat.toLowerCase().includes(searchTerm) ||
          item.jabatan.toLowerCase().includes(searchTerm) ||
          item.pendidikan.toLowerCase().includes(searchTerm);
      });
    }
    
    currentPage = 1; // Reset ke halaman pertama
    renderFilteredData();
    updateResultCount();
  }

  function filterByCard(type) {
    resetFilter();
    const filterGender = document.getElementById('filterGender');
    switch (type) {
      case 'laki':
        if (filterGender) filterGender.value = 'Laki-laki';
        break;
      case 'perempuan':
        if (filterGender) filterGender.value = 'Perempuan';
        break;
      case 'eselon':
        filteredData = originalData.filter(item => item.eselon && item.eselon !== '-' && item.eselon !== 'Non-Eselon' && item.eselon.trim() !== '');
        currentPage = 1;
        renderFilteredData();
        updateResultCount();
        return;
      case 'all':
      default:
        filteredData = [...originalData];
        currentPage = 1;
        renderFilteredData();
        updateResultCount();
        return;
    }
    applyFilters();
  }

// FUNGSI RENDER DENGAN PAGINATION
function renderFilteredData() {
  const tbody = document.getElementById('tableBody');
  if (!tbody) return;

  // Sembunyikan semua data asli
  originalData.forEach(item => item.element.style.display = 'none');

  if (filteredData.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="9" class="empty-state">
          <i class="fas fa-search"></i>
          <h5>Tidak ada data yang cocok</h5>
          <p>Coba ubah kriteria pencarian atau filter Anda</p>
          <button class="btn btn-secondary btn-sm" onclick="resetFilter()">
            <i class="fas fa-refresh me-2"></i>Reset Filter
          </button>
        </td>
      </tr>`;
    
    // Hapus pagination jika tidak ada data
    removePagination();
    return;
  }

  // Hapus empty state jika ada
  const emptyState = tbody.querySelector('.empty-state');
  if (emptyState) emptyState.closest('tr').remove();

  // HITUNG PAGINATION
  const totalPages = Math.ceil(filteredData.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const paginatedData = filteredData.slice(startIndex, endIndex);

  // Tampilkan hanya data untuk halaman ini
  paginatedData.forEach((item, index) => {
    item.element.style.display = '';
    item.element.style.opacity = '0';
    item.element.style.transform = 'translateY(20px)';
    setTimeout(() => {
      item.element.style.transition = 'all 0.3s ease';
      item.element.style.opacity = '1';
      item.element.style.transform = 'translateY(0)';
    }, index * 50);
  });

  setTimeout(() => initializeTooltips(), 100);
  
  // Render pagination controls
  renderPagination(totalPages);
}

// FUNGSI RENDER PAGINATION CONTROLS
function renderPagination(totalPages) {
  // Hapus pagination lama jika ada
  removePagination();
  
  if (totalPages <= 1) return; // Tidak perlu pagination jika hanya 1 halaman
  
  const tableSection = document.querySelector('.table-section');
  if (!tableSection) return;
  
  const paginationWrapper = document.createElement('div');
  paginationWrapper.className = 'pagination-wrapper';
  paginationWrapper.id = 'paginationWrapper';
  
  let paginationHTML = `
    <div class="pagination-container">
      <div class="pagination-info">
        Menampilkan <strong>${((currentPage - 1) * itemsPerPage) + 1}</strong> - 
        <strong>${Math.min(currentPage * itemsPerPage, filteredData.length)}</strong> dari 
        <strong>${filteredData.length}</strong> data
      </div>
      
      <div class="pagination-controls">
        <label class="me-2">Data per halaman:</label>
        <select class="form-select form-select-sm d-inline-block w-auto me-3" id="itemsPerPage">
          <option value="10" ${itemsPerPage === 10 ? 'selected' : ''}>10</option>
          <option value="25" ${itemsPerPage === 25 ? 'selected' : ''}>25</option>
          <option value="50" ${itemsPerPage === 50 ? 'selected' : ''}>50</option>
          <option value="100" ${itemsPerPage === 100 ? 'selected' : ''}>100</option>
        </select>
        
        <nav>
          <ul class="pagination pagination-sm mb-0">`;
  
  // Tombol Previous
  paginationHTML += `
    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
      <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">
        <i class="fas fa-chevron-left"></i>
      </a>
    </li>`;
  
  // Tombol halaman
  const maxButtons = 5; // Maksimal tombol yang ditampilkan
  let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
  let endPage = Math.min(totalPages, startPage + maxButtons - 1);
  
  if (endPage - startPage < maxButtons - 1) {
    startPage = Math.max(1, endPage - maxButtons + 1);
  }
  
  // Tombol halaman pertama
  if (startPage > 1) {
    paginationHTML += `
      <li class="page-item">
        <a class="page-link" href="#" onclick="changePage(1); return false;">1</a>
      </li>`;
    if (startPage > 2) {
      paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
  }
  
  // Tombol halaman tengah
  for (let i = startPage; i <= endPage; i++) {
    paginationHTML += `
      <li class="page-item ${i === currentPage ? 'active' : ''}">
        <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
      </li>`;
  }
  
  // Tombol halaman terakhir
  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
    paginationHTML += `
      <li class="page-item">
        <a class="page-link" href="#" onclick="changePage(${totalPages}); return false;">${totalPages}</a>
      </li>`;
  }
  
  // Tombol Next
  paginationHTML += `
    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
      <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">
        <i class="fas fa-chevron-right"></i>
      </a>
    </li>
          </ul>
        </nav>
      </div>
    </div>`;
  
  paginationWrapper.innerHTML = paginationHTML;
  tableSection.appendChild(paginationWrapper);
  
  // Re-attach event listener untuk items per page
  const itemsPerPageSelect = document.getElementById('itemsPerPage');
  if (itemsPerPageSelect) {
    itemsPerPageSelect.addEventListener('change', function() {
      itemsPerPage = parseInt(this.value);
      currentPage = 1;
      renderFilteredData();
    });
  }
}

// FUNGSI HAPUS PAGINATION
function removePagination() {
  const existingPagination = document.getElementById('paginationWrapper');
  if (existingPagination) {
    existingPagination.remove();
  }
}

// FUNGSI GANTI HALAMAN
function changePage(page) {
  const totalPages = Math.ceil(filteredData.length / itemsPerPage);
  if (page < 1 || page > totalPages) return;
  
  currentPage = page;
  renderFilteredData();
  
  // Scroll ke atas table
  const tableSection = document.querySelector('.table-section');
  if (tableSection) {
    tableSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

function resetFilter() {
  const searchName = document.getElementById('searchName');
  const quickSearch = document.getElementById('quickSearch');
  if (searchName) searchName.value = '';
  if (quickSearch) quickSearch.value = '';

  const filterSelects = ['filterGender', 'filterPangkat', 'filterGolongan', 'filterJabatan', 'filterPendidikan'];
  filterSelects.forEach(id => {
    const element = document.getElementById(id);
    if (element) element.value = '';
  });

  filteredData = [...originalData];
  currentPage = 1; // Reset ke halaman pertama
  renderFilteredData();
  updateResultCount();

  const filterContent = document.getElementById('filterContent');
  if (filterContent && filterContent.classList.contains('show')) {
    new bootstrap.Collapse(filterContent, { hide: true });
  }
}

function updateResultCount() {
  const resultCount = document.getElementById('resultCount');
  if (resultCount) {
    const total = originalData.length;
    const filtered = filteredData.length;
    if (filtered === total) {
      resultCount.textContent = `Menampilkan ${total} data`;
    } else {
      resultCount.textContent = `Menampilkan ${filtered} dari ${total} data`;
    }
  }
}

  async function exportData() {
    if (filteredData.length === 0) {
      showNotification('Tidak ada data untuk diekspor', 'warning');
      return;
    }
    showExportModal();
  }

  function showExportModal() {
    const existingModal = document.getElementById('exportModal');
    if (existingModal) existingModal.remove();

    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'exportModal';
    modal.innerHTML = `
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">
            <i class="fas fa-download me-2"></i>Export Data DUK ke PDF
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          
          <!-- Filter Umum -->
          <div class="card mb-3">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Data Export</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3 mb-3">
                  <label class="form-label">Jenis Kelamin</label>
                  <select class="form-select form-select-sm" id="exportFilterGender">
                    <option value="">Semua</option>
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                  </select>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Pendidikan</label>
                  <select class="form-select form-select-sm" id="exportFilterPendidikan">
                    <option value="">Semua</option>
                  </select>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Golongan</label>
                  <select class="form-select form-select-sm" id="exportFilterGolongan">
                    <option value="">Semua</option>
                  </select>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Status Eselon</label>
                  <select class="form-select form-select-sm" id="exportFilterEselon">
                    <option value="">Semua</option>
                    <option value="HAS_ESELON">Ber-Eselon</option>
                    <option value="NO_ESELON">Non Eselon</option>
                  </select>
                </div>
              </div>
              <div class="alert alert-info mb-0">
                <small><i class="fas fa-info-circle me-1"></i>Data yang akan diekspor: <strong id="exportDataCount">${filteredData.length}</strong> pegawai</small>
              </div>
            </div>
          </div>

          <!-- Template Export PDF -->
          <div class="card">
            <div class="card-header bg-secondary text-white">
              <h6 class="mb-0"><i class="fas fa-file-pdf me-2"></i>Pilih Template PDF</h6>
            </div>
            <div class="card-body">
              <div class="row g-3">
                
                <!--1: Semua Data -->
                <div class="col-md-6">
                  <div class="export-template-card" onclick="exportPDFTemplate(1)" style="cursor: pointer; border: 2px solid #e9ecef; border-radius: 12px; padding: 1.5rem; transition: all 0.3s ease;">
                    <div class="d-flex align-items-start">
                      <div class="template-icon me-3" style="font-size: 2.5rem; color: #007bff;">
                        <i class="fas fa-list-alt"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-2 fw-bold">1: Semua Data</h6>
                        <p class="text-muted mb-2" style="font-size: 0.85rem;">
                          Menampilkan semua pegawai dengan kolom: Eselon (I-V) + Non-Eselon (JFT/JFU) + Jenis Kelamin + Pendidikan
                        </p>
                        <div class="badge bg-primary">Lengkap</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 2: Berdasarkan Status Eselon -->
                <div class="col-md-6">
                  <div class="export-template-card" onclick="exportPDFTemplate(2)" style="cursor: pointer; border: 2px solid #e9ecef; border-radius: 12px; padding: 1.5rem; transition: all 0.3s ease;">
                    <div class="d-flex align-items-start">
                      <div class="template-icon me-3" style="font-size: 2.5rem; color: #28a745;">
                        <i class="fas fa-sitemap"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-2 fw-bold">2: Eselon dan Non-Eselon</h6>
                        <p class="text-muted mb-2" style="font-size: 0.85rem;">
                          Pemisahan antara pegawai Eselon (I-V) dan Non-Eselon (JFT Pratama/Muda/Madya & JFU Kelas V-VII)
                        </p>
                        <div class="badge bg-success">Detail Status Jabatan</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 3: Berdasarkan Jenis Kelamin -->
                <div class="col-md-6">
                  <div class="export-template-card" onclick="exportPDFTemplate(3)" style="cursor: pointer; border: 2px solid #e9ecef; border-radius: 12px; padding: 1.5rem; transition: all 0.3s ease;">
                    <div class="d-flex align-items-start">
                      <div class="template-icon me-3" style="font-size: 2.5rem; color: #ff6b6b;">
                        <i class="fas fa-venus-mars"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-2 fw-bold">3: Jenis Kelamin</h6>
                        <p class="text-muted mb-2" style="font-size: 0.85rem;">
                          Fokus pada distribusi pegawai berdasarkan gender (Laki-laki & Perempuan) per golongan
                        </p>
                        <div class="badge bg-danger">Jenis Kelamin</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!--4: Berdasarkan Pendidikan -->
                <div class="col-md-6">
                  <div class="export-template-card" onclick="exportPDFTemplate(4)" style="cursor: pointer; border: 2px solid #e9ecef; border-radius: 12px; padding: 1.5rem; transition: all 0.3s ease;">
                    <div class="d-flex align-items-start">
                      <div class="template-icon me-3" style="font-size: 2.5rem; color: #f39c12;">
                        <i class="fas fa-graduation-cap"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-2 fw-bold">4: Pendidikan Terakhir</h6>
                        <p class="text-muted mb-2" style="font-size: 0.85rem;">
                          Distribusi pegawai berdasarkan tingkat pendidikan (SMA, D3, D4, S1, S2, S3) per golongan
                        </p>
                        <div class="badge bg-warning text-dark">Kompetensi SDM</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 5: Berdasarkan Golongan -->
                <div class="col-md-6">
                  <div class="export-template-card" onclick="exportPDFTemplate(5)" style="cursor: pointer; border: 2px solid #e9ecef; border-radius: 12px; padding: 1.5rem; transition: all 0.3s ease;">
                    <div class="d-flex align-items-start">
                      <div class="template-icon me-3" style="font-size: 2.5rem; color: #9b59b6;">
                        <i class="fas fa-layer-group"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-2 fw-bold">5: Detail Per Golongan</h6>
                        <p class="text-muted mb-2" style="font-size: 0.85rem;">
                          Analisis mendalam per golongan (I, II, III, IV) dengan breakdown lengkap jabatan & jenis kelamin
                        </p>
                        <div class="badge bg-info">Statistik Kepangkatan</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Template Excel -->
                <div class="col-md-6">
                  <div class="export-template-card" onclick="startExcelExport()" style="cursor: pointer; border: 2px solid #e9ecef; border-radius: 12px; padding: 1.5rem; transition: all 0.3s ease;">
                    <div class="d-flex align-items-start">
                      <div class="template-icon me-3" style="font-size: 2.5rem; color: #28a745;">
                        <i class="fas fa-file-excel"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-2 fw-bold">Export ke Excel</h6>
                        <p class="text-muted mb-2" style="font-size: 0.85rem;">
                          Format DUK dalam spreadsheet untuk editing dan analisis lanjutan
                        </p>
                        <div class="badge bg-success">Dapat Diedit</div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>`;

    const style = document.createElement('style');
    style.textContent = `
    .export-template-card:hover { 
      border-color: #007bff !important; 
      background: #f8f9fa !important; 
      transform: translateY(-2px); 
      box-shadow: 0 4px 15px rgba(0,123,255,0.2); 
    }
  `;
    document.head.appendChild(style);

    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();

    populateExportFilters();

    ['exportFilterEselon', 'exportFilterGender', 'exportFilterPendidikan', 'exportFilterGolongan'].forEach(id => {
      const element = document.getElementById(id);
      if (element) {
        element.addEventListener('change', updateExportDataCount);
      }
    });

    modal.addEventListener('hidden.bs.modal', () => {
      modal.remove();
      style.remove();
    });
  }

  // Fungsi untuk populate filter options
  function populateExportFilters() {
    const pendidikanSet = new Set();
    filteredData.forEach(item => {
      if (item.pendidikan && item.pendidikan !== '-') pendidikanSet.add(item.pendidikan);
    });
    const pendidikanSelect = document.getElementById('exportFilterPendidikan');
    if (pendidikanSelect) {
      Array.from(pendidikanSet).sort().forEach(pendidikan => {
        const option = document.createElement('option');
        option.value = pendidikan;
        option.textContent = pendidikan;
        pendidikanSelect.appendChild(option);
      });
    }

    const golonganSet = new Set();
    filteredData.forEach(item => {
      if (item.golongan && item.golongan !== '-') golonganSet.add(item.golongan);
    });
    const golonganSelect = document.getElementById('exportFilterGolongan');
    if (golonganSelect) {
      Array.from(golonganSet).sort().forEach(golongan => {
        const option = document.createElement('option');
        option.value = golongan;
        option.textContent = golongan;
        golonganSelect.appendChild(option);
      });
    }
  }

  // Update count data yang akan diexport
  function updateExportDataCount() {
    const filtered = getFilteredExportData();
    const countElement = document.getElementById('exportDataCount');
    if (countElement) {
      countElement.textContent = filtered.length;
    }
  }

  // Fungsi untuk mendapatkan data yang sudah difilter
  function getFilteredExportData() {
    const eselon = document.getElementById('exportFilterEselon')?.value || '';
    const gender = document.getElementById('exportFilterGender')?.value || '';
    const pendidikan = document.getElementById('exportFilterPendidikan')?.value || '';
    const golongan = document.getElementById('exportFilterGolongan')?.value || '';

    return filteredData.filter(item => {
      if (eselon === 'HAS_ESELON') {
        const hasEselon = item.eselon &&
          item.eselon !== '-' &&
          item.eselon.trim() !== '' &&
          item.eselon !== 'Non-Eselon' &&
          !item.eselon.toLowerCase().includes('non');
        if (!hasEselon) return false;
      }

      if (eselon === 'NO_ESELON') {
        const isNonEselon = !item.eselon ||
          item.eselon === '-' ||
          item.eselon.trim() === '' ||
          item.eselon === 'Non-Eselon' ||
          item.eselon.toLowerCase().includes('non');
        if (!isNonEselon) return false;
      }

      if (gender && item.jenis_kelamin !== gender) return false;
      if (pendidikan && item.pendidikan !== pendidikan) return false;
      if (golongan && item.golongan !== golongan) return false;

      return true;
    });
  }

  // ============================================
  // FUNGSI EXPORT PDF BERDASARKAN TEMPLATE
  // ============================================

  function exportPDFTemplate(templateNumber) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
    if (modal) modal.hide();

    showNotification(`Membuat PDF Template ${templateNumber}...`, 'info');

    setTimeout(() => {
      switch (templateNumber) {
        case 1:
          generatePDFTemplate1_SemuaData();
          break;
        case 2:
          generatePDFTemplate2_EselonNonEselon();
          break;
        case 3:
          generatePDFTemplate3_JenisKelamin();
          break;
        case 4:
          generatePDFTemplate4_Pendidikan();
          break;
        case 5:
          generatePDFTemplate5_PerGolongan();
          break;
      }
    }, 300);
  }



 // ============================================
// TEMPLATE 1: SEMUA DATA (FIXED VERSION)
// Taruh code ini DI ATAS generatePDFTemplate2_EselonNonEselon()
// ============================================

function generatePDFTemplate1_SemuaData() {
  try {
    const { jsPDF } = window.jspdf;
    if (!jsPDF) {
      showNotification('Library jsPDF tidak tersedia!', 'danger');
      return;
    }

    // Ambil data yang sudah difilter
    const exportData = getFilteredExportData();
    
    // Debug: cek data
    console.log('Export Data:', exportData);
    console.log('Jumlah data:', exportData.length);
    
    if (!exportData || exportData.length === 0) {
      showNotification('Tidak ada data untuk diekspor!', 'warning');
      return;
    }

    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    const pageWidth = doc.internal.pageSize.getWidth();
    
    // Langsung buat PDF tanpa tunggu logo
    buatPDFSemuaData(doc, exportData, pageWidth);

  } catch (error) {
    console.error('Error di generatePDFTemplate1:', error);
    showNotification(`Error: ${error.message}`, 'danger');
  }
}

// Fungsi untuk membuat PDF dengan semua data
function buatPDFSemuaData(doc, exportData, pageWidth) {
  let currentY = 8;

  try {
    // Coba load logo, tapi jangan tunggu
    try {
      const logo = new Image();
      logo.src = 'assets/img/logo.png';
      doc.addImage(logo, 'PNG', 15, 8, 20, 20);
    } catch (e) {
      console.warn('Logo tidak dimuat:', e);
    }

    // KOP SURAT
    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.text('PEMERINTAH KOTA BANJARMASIN', pageWidth / 2, currentY + 5, { align: 'center' });

    doc.setFontSize(13);
    doc.text('DINAS PENGENDALIAN PENDUDUK, KELUARGA', pageWidth / 2, currentY + 11, { align: 'center' });
    doc.text('BERENCANA DAN PEMBERDAYAAN MASYARAKAT', pageWidth / 2, currentY + 17, { align: 'center' });

    doc.setFontSize(8);
    doc.setFont('helvetica', 'normal');
    doc.text('JL. Brigjen H. Hasan Basri – Kayutangi II RT.16 Banjarmasin 70124', pageWidth / 2, currentY + 22, { align: 'center' });

    doc.setFontSize(7);
    doc.setTextColor(0, 0, 255);
    doc.text('Pos-el : dppkbpm@gmail.banjarmasin.go.id, Laman http://dppkbpm.banjarmasinkota.go.id', pageWidth / 2, currentY + 26, { align: 'center' });
    doc.setTextColor(0, 0, 0);

    currentY += 28;
    doc.setLineWidth(1);
    doc.line(15, currentY, pageWidth - 15, currentY);
    currentY += 1;
    doc.setLineWidth(0.5);
    doc.line(15, currentY, pageWidth - 15, currentY);
    currentY += 6;

    // JUDUL
    doc.setFontSize(10);
    doc.setFont('helvetica', 'bold');
    doc.text('DAFTAR JUMLAH PEGAWAI BERDASARKAN GOLONGAN DAN JABATAN', pageWidth / 2, currentY, { align: 'center' });
    currentY += 5;
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.text('(Per 1 Agustus 2025)', pageWidth / 2, currentY, { align: 'center' });
    currentY += 6;

    doc.setFontSize(7);
    doc.text('SKPD : DPPKBPM KOTA BANJARMASIN', 15, currentY);
    currentY += 4;
    doc.text('GOLONGAN/ RUANG', 15, currentY);
    currentY += 6;

    // Buat tabel
    buatTabelPDF(doc, exportData, currentY, pageWidth);

  } catch (error) {
    console.error('Error di buatPDFSemuaData:', error);
    showNotification(`Error membuat PDF: ${error.message}`, 'danger');
  }
}

// Fungsi untuk membuat tabel PDF
function buatTabelPDF(doc, exportData, currentY, pageWidth) {
  try {
    console.log('Mulai buat tabel...');
    
    const tableData = [];

    // HEADER BARIS 1 - Kategori Utama
    const headerRow1 = [
      { content: 'GOLONGAN/\nRUANG', rowSpan: 2, styles: { halign: 'center', valign: 'middle', fontStyle: 'bold', cellWidth: 20 } },
      { content: 'ESELON', colSpan: 5, styles: { halign: 'center', fontStyle: 'bold', fillColor: [220, 230, 241] } },
      { content: 'NON-ESELON', colSpan: 5, styles: { halign: 'center', fontStyle: 'bold', fillColor: [252, 228, 214] } },
      { content: 'JENIS KELAMIN', colSpan: 2, styles: { halign: 'center', fontStyle: 'bold', fillColor: [217, 234, 211] } },
      { content: 'PENDIDIKAN TERAKHIR', colSpan: 6, styles: { halign: 'center', fontStyle: 'bold', fillColor: [255, 242, 204] } },
      { content: 'JUMLAH', rowSpan: 2, styles: { valign: 'middle', halign: 'center', fontStyle: 'bold' } }
    ];
    tableData.push(headerRow1);

    // HEADER BARIS 2 - Detail Sub Kolom
    const headerRow2 = [];
    headerRow2.push('I', 'II', 'III', 'IV', 'V'); // Eselon
    headerRow2.push('JFT Pratama', 'JFT Muda', 'JFT Madya', 'JFU-5', 'JFU-6/7'); // Non-Eselon
    headerRow2.push('L', 'P'); // Gender
    headerRow2.push('SMA', 'D3', 'D4', 'S1', 'S2', 'S3'); // Pendidikan

    tableData.push(headerRow2.map(txt => ({ 
      content: txt, 
      styles: { fontSize: 6, halign: 'center', fontStyle: 'bold' } 
    })));

    // Total 18 kolom
    const totalCols = 18;

    // Proses data
    console.log('Memproses data per golongan...');
    const dataByGolongan = prosesDataPerGolongan(exportData);
    console.log('Data per golongan:', dataByGolongan);

    // List golongan
    const golonganList = [
      { label: 'IV/d', gol: 'IV/d' },
      { label: 'IV/c', gol: 'IV/c' },
      { label: 'IV/b', gol: 'IV/b' },
      { label: 'IV/a', gol: 'IV/a' },
      { label: 'JUMLAH GOLONGAN IV', isTotal: true, section: 'IV' },
      { label: 'III/d', gol: 'III/d' },
      { label: 'III/c', gol: 'III/c' },
      { label: 'III/b', gol: 'III/b' },
      { label: 'III/a', gol: 'III/a' },
      { label: 'JUMLAH GOLONGAN III', isTotal: true, section: 'III' },
      { label: 'II/d', gol: 'II/d' },
      { label: 'II/c', gol: 'II/c' },
      { label: 'II/b', gol: 'II/b' },
      { label: 'II/a', gol: 'II/a' },
      { label: 'JUMLAH GOLONGAN II', isTotal: true, section: 'II' },
      { label: 'I/d', gol: 'I/d' },
      { label: 'I/c', gol: 'I/c' },
      { label: 'I/b', gol: 'I/b' },
      { label: 'I/a', gol: 'I/a' },
      { label: 'JUMLAH GOLONGAN I', isTotal: true, section: 'I' },
      { label: 'TOTAL', isGrandTotal: true }
    ];

    // Array untuk total
    const columnTotals = Array(totalCols).fill(0);
    const sectionTotals = {
      'IV': Array(totalCols).fill(0),
      'III': Array(totalCols).fill(0),
      'II': Array(totalCols).fill(0),
      'I': Array(totalCols).fill(0)
    };

    // Isi data tabel
    golonganList.forEach(({ label, gol, isTotal, isGrandTotal, section }) => {
      const row = [label];

      if (isTotal || isGrandTotal) {
        // Baris Total
        if (isGrandTotal) {
          for (let i = 0; i < totalCols; i++) {
            row.push(columnTotals[i] > 0 ? columnTotals[i] : '-');
          }
        } else {
          for (let i = 0; i < totalCols; i++) {
            const val = sectionTotals[section][i];
            row.push(val > 0 ? val : '-');
          }
        }

        const rowTotal = row.slice(1).reduce((sum, val) => sum + (typeof val === 'number' ? val : 0), 0);
        row.push(rowTotal);

        tableData.push(row.map(val => ({
          content: val,
          styles: { fontStyle: 'bold', fillColor: [240, 240, 240], fontSize: 6, halign: 'center' }
        })));
      } else {
        // Baris data normal
        const counts = dataByGolongan[gol] || {};
        let rowTotal = 0;

        for (let i = 0; i < totalCols; i++) {
          const count = counts[i] || 0;
          row.push(count > 0 ? count : '-');
          rowTotal += count;
          columnTotals[i] += count;

          if (gol.startsWith('IV/')) sectionTotals['IV'][i] += count;
          else if (gol.startsWith('III/')) sectionTotals['III'][i] += count;
          else if (gol.startsWith('II/')) sectionTotals['II'][i] += count;
          else if (gol.startsWith('I/')) sectionTotals['I'][i] += count;
        }

        row.push(rowTotal);
        tableData.push(row.map(val => ({ 
          content: val, 
          styles: { fontSize: 6, halign: 'center' } 
        })));
      }
    });

    console.log('Membuat autoTable...');

    // Buat tabel
    doc.autoTable({
      body: tableData,
      startY: currentY,
      theme: 'grid',
      styles: {
        fontSize: 6,
        cellPadding: 0.5,
        lineColor: [0, 0, 0],
        lineWidth: 0.2,
        textColor: [0, 0, 0],
        halign: 'center',
        valign: 'middle'
      },
      columnStyles: {
        0: { cellWidth: 20, halign: 'left', fontStyle: 'bold' }
      },
      margin: { left: 10, right: 10 }
    });

  
  // Footer TTD
  addPDFFooter(doc, doc.lastAutoTable.finalY, pageWidth);

    // Save PDF
    const fileName = `DUK_Golongan_Jabatan_${new Date().toISOString().split('T')[0]}.pdf`;
    doc.save(fileName);
    
    console.log('PDF berhasil disimpan!');
    showNotification('PDF Template 1 berhasil diunduh!', 'success');

  } catch (error) {
    console.error('Error di buatTabelPDF:', error);
    showNotification(`Error: ${error.message}`, 'danger');
  }
}

// Fungsi untuk proses data per golongan
function prosesDataPerGolongan(data) {
  const result = {};

  console.log('Memproses', data.length, 'data pegawai...');

  data.forEach((item, index) => {
    const gol = item.golongan || '';
    if (!gol) {
      console.warn('Data ke-' + index + ' tidak punya golongan:', item);
      return;
    }

    if (!result[gol]) {
      result[gol] = {};
    }

    // Cek Non-Eselon
    const isNonEselon = !item.eselon || 
                        item.eselon === '-' || 
                        item.eselon === 'Non-Eselon' || 
                        item.eselon.toLowerCase().includes('non');

    // KOLOM 0-4: ESELON
    if (!isNonEselon) {
      const eselon = item.eselon;
      
      if (eselon.includes('I') && !eselon.includes('II') && !eselon.includes('III') && !eselon.includes('IV')) {
        result[gol][0] = (result[gol][0] || 0) + 1; // Eselon I
      } else if (eselon.includes('II')) {
        result[gol][1] = (result[gol][1] || 0) + 1; // Eselon II
      } else if (eselon.includes('III')) {
        result[gol][2] = (result[gol][2] || 0) + 1; // Eselon III
      } else if (eselon.includes('IV')) {
        result[gol][3] = (result[gol][3] || 0) + 1; // Eselon IV
      } else if (eselon.includes('V')) {
        result[gol][4] = (result[gol][4] || 0) + 1; // Eselon V
      }
    }

    // KOLOM 5-9: NON-ESELON
    if (isNonEselon) {
      if (item.jenis_jabatan === 'JFT') {
        const tingkat = (item.jft_tingkat || '').toLowerCase();
        
        if (tingkat.includes('pratama')) {
          result[gol][5] = (result[gol][5] || 0) + 1;
        } else if (tingkat.includes('muda')) {
          result[gol][6] = (result[gol][6] || 0) + 1;
        } else if (tingkat.includes('madya')) {
          result[gol][7] = (result[gol][7] || 0) + 1;
        }
      } else if (item.jenis_jabatan === 'JFU') {
        const kelas = item.jfu_kelas || '';
        
        if (kelas === '5') {
          result[gol][8] = (result[gol][8] || 0) + 1;
        } else if (kelas === '6' || kelas === '7') {
          result[gol][9] = (result[gol][9] || 0) + 1;
        }
      }
    }

    // KOLOM 10-11: JENIS KELAMIN
    if (item.jenis_kelamin === 'Laki-laki') {
      result[gol][10] = (result[gol][10] || 0) + 1;
    } else if (item.jenis_kelamin === 'Perempuan') {
      result[gol][11] = (result[gol][11] || 0) + 1;
    }

    // KOLOM 12-17: PENDIDIKAN
    const pend = item.pendidikan || '';
    
    if (pend === 'SMA') {
      result[gol][12] = (result[gol][12] || 0) + 1;
    } else if (pend === 'D3') {
      result[gol][13] = (result[gol][13] || 0) + 1;
    } else if (pend === 'D4') {
      result[gol][14] = (result[gol][14] || 0) + 1;
    } else if (pend === 'S1') {
      result[gol][15] = (result[gol][15] || 0) + 1;
    } else if (pend === 'S2') {
      result[gol][16] = (result[gol][16] || 0) + 1;
    } else if (pend === 'S3') {
      result[gol][17] = (result[gol][17] || 0) + 1;
    }
  });

  console.log('Hasil proses data:', result);
  return result;
}

  // ============================================
  // FUNGSI HELPER UNTUK PDF
  // ============================================

  function createPDFHeader(doc, pageWidth, title) {
    let currentY = 8;

    // Logo (opsional - jika ada)
    const logo = new Image();
    logo.src = 'assets/img/logo.png';

    try {
      doc.addImage(logo, 'PNG', 15, 8, 20, 20);
    } catch (e) {
      console.warn('Logo tidak tersedia');
    }

    // KOP SURAT
    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.text('PEMERINTAH KOTA BANJARMASIN', pageWidth / 2, currentY + 5, { align: 'center' });

    doc.setFontSize(13);
    doc.text('DINAS PENGENDALIAN PENDUDUK, KELUARGA', pageWidth / 2, currentY + 11, { align: 'center' });
    doc.text('BERENCANA DAN PEMBERDAYAAN MASYARAKAT', pageWidth / 2, currentY + 17, { align: 'center' });

    doc.setFontSize(8);
    doc.setFont('helvetica', 'normal');
    doc.text('JL. Brigjen H. Hasan Basri – Kayutangi II RT.16 Banjarmasin 70124', pageWidth / 2, currentY + 22, { align: 'center' });

    doc.setFontSize(7);
    doc.setTextColor(0, 0, 255);
    doc.text('Pos-el : dppkbpm@gmail.banjarmasin.go.id, Laman http://dppkbpm.banjarmasinkota.go.id', pageWidth / 2, currentY + 26, { align: 'center' });
    doc.setTextColor(0, 0, 0);

    currentY += 28;
    doc.setLineWidth(1);
    doc.line(15, currentY, pageWidth - 15, currentY);
    currentY += 1;
    doc.setLineWidth(0.5);
    doc.line(15, currentY, pageWidth - 15, currentY);
    currentY += 6;

    // JUDUL
    doc.setFontSize(10);
    doc.setFont('helvetica', 'bold');
    const titleLines = title.split('\n');
    titleLines.forEach((line, index) => {
      doc.text(line, pageWidth / 2, currentY + (index * 5), { align: 'center' });
    });
    currentY += (titleLines.length * 5);

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.text('(Per 1 Agustus 2025)', pageWidth / 2, currentY, { align: 'center' });
    currentY += 6;

    doc.setFontSize(7);
    doc.text('SKPD : DPPKBPM KOTA BANJARMASIN', 15, currentY);
    currentY += 6;

    return currentY;
  }

  function addPDFFooter(doc, finalY, pageWidth) {
  const footerY = finalY + 8;

  doc.setFontSize(7);
  doc.text('Banjarmasin, Agustus 2025', pageWidth - 50, footerY);
  
  // Gunakan data dari kepalaOPDData
  if (kepalaOPDData) {
    doc.text(kepalaOPDData.jabatan, pageWidth - 50, footerY + 4);
    
    // Garis TTD
    doc.line(pageWidth - 65, footerY + 18, pageWidth - 20, footerY + 18);
    
    // Nama lengkap dengan gelar
    doc.text(kepalaOPDData.nama_lengkap, pageWidth - 50, footerY + 22);
    doc.text('NIP. ' + kepalaOPDData.nip, pageWidth - 50, footerY + 26);
  } else {
    // Fallback jika data belum dimuat
    doc.text('Pengguna Anggaran', pageWidth - 50, footerY + 4);
    doc.line(pageWidth - 65, footerY + 18, pageWidth - 20, footerY + 18);
    doc.text('(Nama Kepala Dinas)', pageWidth - 50, footerY + 22);
    doc.text('NIP. __________________', pageWidth - 50, footerY + 26);
  }
}

  function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification-toast`;
    notification.innerHTML = `
    <i class="fas fa-${getNotificationIcon(type)} me-2"></i>${message}
    <button type="button" class="btn-close ms-auto" onclick="this.parentElement.remove()"></button>`;
    notification.style.cssText = `position: fixed; top: 100px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-radius: 8px; animation: slideInRight 0.3s ease;`;
    document.body.appendChild(notification);

    if (type !== 'info') {
      setTimeout(() => {
        if (notification.parentElement) {
          notification.style.animation = 'slideOutRight 0.3s ease';
          setTimeout(() => notification.remove(), 300);
        }
      }, 5000);
    }
  }

  function getNotificationIcon(type) {
    const icons = {
      'success': 'check-circle',
      'danger': 'exclamation-circle',
      'warning': 'exclamation-triangle',
      'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
  }

  // CSS animations
  const notificationStyles = document.createElement('style');
  notificationStyles.textContent = `
  @keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }
  @keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
  }
  .notification-toast {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .notification-toast .btn-close {
    margin-left: 1rem;
  }
`;
  document.head.appendChild(notificationStyles);

  function generatePDFTable(doc, exportData, currentY, pageWidth) {
    try {
      const uniqueGender = [...new Set(exportData.map(item => item.jenis_kelamin).filter(j => j))].sort();
      const uniquePendidikan = [...new Set(exportData.map(item => item.pendidikan).filter(p => p))].sort();

      const displayGender = uniqueGender.slice(0, 2);
      const displayPendidikan = uniquePendidikan.slice(0, 8);

      const tableData = [];

      // Header Row 1
      const headerRow1 = [
        { content: 'GOLONGAN/\nRUANG', rowSpan: 2, styles: { halign: 'center', valign: 'middle', fontStyle: 'bold' } }
      ];

      headerRow1.push({ content: 'ESELON', colSpan: 5, styles: { halign: 'center', fontStyle: 'bold', fillColor: [240, 240, 240] } });
      headerRow1.push({ content: 'JENIS KELAMIN', colSpan: displayGender.length, styles: { halign: 'center', fontStyle: 'bold', fillColor: [240, 240, 240] } });
      headerRow1.push({ content: 'PENDIDIKAN TERAKHIR', colSpan: displayPendidikan.length, styles: { halign: 'center', fontStyle: 'bold', fillColor: [240, 240, 240] } });
      headerRow1.push({ content: 'JUMLAH', rowSpan: 2, styles: { valign: 'middle', halign: 'center', fontStyle: 'bold' } });

      tableData.push(headerRow1);

      // Header Row 2
      const headerRow2 = [];
      headerRow2.push('I', 'II', 'III', 'IV', 'V');
      displayGender.forEach(g => headerRow2.push(g === 'Laki-laki' ? 'L' : 'P'));
      displayPendidikan.forEach(pend => headerRow2.push(pend.substring(0, 10)));

      tableData.push(headerRow2.map(txt => ({ content: txt, styles: { fontSize: 6, halign: 'center' } })));

      const totalCols = 5 + displayGender.length + displayPendidikan.length;

      const dataByGolongan = processDataByGolonganAndCategories(exportData, displayGender, displayPendidikan);

      const golonganList = [
        { label: 'IV/d', gol: 'IV/d' },
        { label: 'IV/c', gol: 'IV/c' },
        { label: 'IV/b', gol: 'IV/b' },
        { label: 'IV/a', gol: 'IV/a' },
        { label: 'JUMLAH GOLONGAN IV', isTotal: true, section: 'IV' },
        { label: 'III/d', gol: 'III/d' },
        { label: 'III/c', gol: 'III/c' },
        { label: 'III/b', gol: 'III/b' },
        { label: 'III/a', gol: 'III/a' },
        { label: 'JUMLAH GOLONGAN III', isTotal: true, section: 'III' },
        { label: 'II/d', gol: 'II/d' },
        { label: 'II/c', gol: 'II/c' },
        { label: 'II/b', gol: 'II/b' },
        { label: 'II/a', gol: 'II/a' },
        { label: 'JUMLAH GOLONGAN II', isTotal: true, section: 'II' },
        { label: 'I/d', gol: 'I/d' },
        { label: 'I/c', gol: 'I/c' },
        { label: 'I/b', gol: 'I/b' },
        { label: 'I/a', gol: 'I/a' },
        { label: 'JUMLAH GOLONGAN I', isTotal: true, section: 'I' },
        { label: 'TOTAL', isGrandTotal: true }
      ];

      const columnTotals = Array(totalCols).fill(0);
      const sectionTotals = {
        'IV': Array(totalCols).fill(0),
        'III': Array(totalCols).fill(0),
        'II': Array(totalCols).fill(0),
        'I': Array(totalCols).fill(0)
      };

      golonganList.forEach(({ label, gol, isTotal, isGrandTotal, section }) => {
        const row = [label];

        if (isTotal || isGrandTotal) {
          if (isGrandTotal) {
            for (let i = 0; i < totalCols; i++) {
              row.push(columnTotals[i] > 0 ? columnTotals[i] : '-');
            }
          } else {
            for (let i = 0; i < totalCols; i++) {
              const val = sectionTotals[section][i];
              row.push(val > 0 ? val : '-');
            }
          }

          const rowTotal = row.slice(1).reduce((sum, val) => sum + (typeof val === 'number' ? val : 0), 0);
          row.push(rowTotal);

          tableData.push(row.map(val => ({
            content: val,
            styles: { fontStyle: 'bold', fillColor: [240, 240, 240], fontSize: 6, halign: 'center' }
          })));
        } else {
          const counts = dataByGolongan[gol] || {};
          let rowTotal = 0;

          for (let i = 0; i < totalCols; i++) {
            const count = counts[i] || 0;
            row.push(count > 0 ? count : '-');
            rowTotal += count;
            columnTotals[i] += count;

            if (gol.startsWith('IV/')) sectionTotals['IV'][i] += count;
            else if (gol.startsWith('III/')) sectionTotals['III'][i] += count;
            else if (gol.startsWith('II/')) sectionTotals['II'][i] += count;
            else if (gol.startsWith('I/')) sectionTotals['I'][i] += count;
          }

          row.push(rowTotal);
          tableData.push(row.map(val => ({ content: val, styles: { fontSize: 6, halign: 'center' } })));
        }
      });

      doc.autoTable({
        body: tableData,
        startY: currentY,
        theme: 'grid',
        styles: {
          fontSize: 6,
          cellPadding: 0.5,
          lineColor: [0, 0, 0],
          lineWidth: 0.2,
          textColor: [0, 0, 0],
          halign: 'center',
          valign: 'middle'
        },
        columnStyles: {
          0: { cellWidth: 25, halign: 'left', fontStyle: 'bold' }
        },
        margin: { left: 10, right: 10 }
      });

      addPDFFooter(doc, doc.lastAutoTable.finalY, pageWidth);

      const fileName = `DUK_Golongan_Jabatan_${new Date().toISOString().split('T')[0]}.pdf`;
      doc.save(fileName);
      showNotification('PDF berhasil diunduh!', 'success');

    } catch (error) {
      console.error('PDF Generation Error:', error);
      showNotification(`Error PDF: ${error.message}`, 'danger');
    }
  }

  function processDataByGolonganAndCategories(data, displayGender, displayPendidikan) {
    const result = {};

    data.forEach(item => {
      const gol = item.golongan || '';
      if (!gol) return;

      if (!result[gol]) {
        result[gol] = {};
      }

      let colIndex = 0;

      // Eselon columns (5)
      const eselonLevels = ['I', 'II', 'III', 'IV', 'V'];
      eselonLevels.forEach((level, idx) => {
        if (item.eselon && item.eselon.includes(level)) {
          result[gol][idx] = (result[gol][idx] || 0) + 1;
        }
      });
      colIndex += 5;

      // Gender columns
      displayGender.forEach((gender, idx) => {
        if (item.jenis_kelamin === gender) {
          result[gol][colIndex + idx] = (result[gol][colIndex + idx] || 0) + 1;
        }
      });
      colIndex += displayGender.length;

      // Pendidikan columns
      displayPendidikan.forEach((pend, idx) => {
        if (item.pendidikan === pend) {
          result[gol][colIndex + idx] = (result[gol][colIndex + idx] || 0) + 1;
        }
      });
    });

    return result;
  }

  // ============================================
// TEMPLATE 2: ESELON DAN NON-ESELON (DIPERBAIKI)
// ============================================
function generatePDFTemplate2_EselonNonEselon() {
  try {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    const pageWidth = doc.internal.pageSize.getWidth();

    const exportData = getFilteredExportData();
    let currentY = createPDFHeader(doc, pageWidth, 'DAFTAR PEGAWAI BERDASARKAN STATUS ESELON DAN NON-ESELON');

    doc.setFontSize(7);
    doc.setTextColor(100, 100, 100);
    doc.text(`Total Pegawai: ${exportData.length}`, 15, currentY);
    currentY += 4;
    doc.setTextColor(0, 0, 0);

    const tableData = [];

    // Header Row 1
    const headerRow1 = [
      { content: 'GOLONGAN/\nRUANG', rowSpan: 2, styles: { halign: 'center', valign: 'middle', fontStyle: 'bold', cellWidth: 20 } },
      { content: 'ESELON', colSpan: 5, styles: { halign: 'center', fontStyle: 'bold', fillColor: [220, 230, 241] } },
      { content: 'NON-ESELON', colSpan: 5, styles: { halign: 'center', fontStyle: 'bold', fillColor: [252, 228, 214] } },
      { content: 'JUMLAH', rowSpan: 2, styles: { valign: 'middle', halign: 'center', fontStyle: 'bold' } }
    ];
    tableData.push(headerRow1);

    // Header Row 2
    const headerRow2 = [
      'I', 'II', 'III', 'IV', 'V', // Eselon
      'JFT Pratama', 'JFT Muda', 'JFT Madya', 'JFU-5', 'JFU-6/7', // Non-Eselon
      'L', 'P' // Gender
    ];
    tableData.push(headerRow2.map(txt => ({ content: txt, styles: { fontSize: 6, halign: 'center', fontStyle: 'bold' } })));

    // Process data
    const dataByGolongan = processTemplate2Data(exportData);

    const golonganList = [
      { label: 'IV/d', gol: 'IV/d' },
      { label: 'IV/c', gol: 'IV/c' },
      { label: 'IV/b', gol: 'IV/b' },
      { label: 'IV/a', gol: 'IV/a' },
      { label: 'JUMLAH GOLONGAN IV', isTotal: true, section: 'IV' },
      { label: 'III/d', gol: 'III/d' },
      { label: 'III/c', gol: 'III/c' },
      { label: 'III/b', gol: 'III/b' },
      { label: 'III/a', gol: 'III/a' },
      { label: 'JUMLAH GOLONGAN III', isTotal: true, section: 'III' },
      { label: 'II/d', gol: 'II/d' },
      { label: 'II/c', gol: 'II/c' },
      { label: 'II/b', gol: 'II/b' },
      { label: 'II/a', gol: 'II/a' },
      { label: 'JUMLAH GOLONGAN II', isTotal: true, section: 'II' },
      { label: 'I/d', gol: 'I/d' },
      { label: 'I/c', gol: 'I/c' },
      { label: 'I/b', gol: 'I/b' },
      { label: 'I/a', gol: 'I/a' },
      { label: 'JUMLAH GOLONGAN I', isTotal: true, section: 'I' },
      { label: 'TOTAL', isGrandTotal: true }
    ];

    const totalCols = 12; // 5 eselon + 5 non-eselon + 2 gender
    const columnTotals = Array(totalCols).fill(0);
    const sectionTotals = {
      'IV': Array(totalCols).fill(0),
      'III': Array(totalCols).fill(0),
      'II': Array(totalCols).fill(0),
      'I': Array(totalCols).fill(0)
    };

    golonganList.forEach(({ label, gol, isTotal, isGrandTotal, section }) => {
      const row = [label];

      if (isTotal || isGrandTotal) {
        if (isGrandTotal) {
          for (let i = 0; i < totalCols; i++) {
            row.push(columnTotals[i] > 0 ? columnTotals[i] : '-');
          }
        } else {
          for (let i = 0; i < totalCols; i++) {
            const val = sectionTotals[section][i];
            row.push(val > 0 ? val : '-');
          }
        }

        const rowTotal = row.slice(1).reduce((sum, val) => sum + (typeof val === 'number' ? val : 0), 0);
        row.push(rowTotal);

        tableData.push(row.map(val => ({
          content: val,
          styles: { fontStyle: 'bold', fillColor: [240, 240, 240], fontSize: 6, halign: 'center' }
        })));
      } else {
        const counts = dataByGolongan[gol] || {};
        let rowTotal = 0;

        for (let i = 0; i < totalCols; i++) {
          const count = counts[i] || 0;
          row.push(count > 0 ? count : '-');
          rowTotal += count;
          columnTotals[i] += count;

          if (gol.startsWith('IV/')) sectionTotals['IV'][i] += count;
          else if (gol.startsWith('III/')) sectionTotals['III'][i] += count;
          else if (gol.startsWith('II/')) sectionTotals['II'][i] += count;
          else if (gol.startsWith('I/')) sectionTotals['I'][i] += count;
        }

        row.push(rowTotal);
        tableData.push(row.map(val => ({ content: val, styles: { fontSize: 6, halign: 'center' } })));
      }
    });

    doc.autoTable({
      body: tableData,
      startY: currentY,
      theme: 'grid',
      styles: {
        fontSize: 6,
        cellPadding: 0.5,
        lineColor: [0, 0, 0],
        lineWidth: 0.2,
        halign: 'center',
        valign: 'middle'
      },
      columnStyles: {
        0: { cellWidth: 20, halign: 'left', fontStyle: 'bold' }
      },
      margin: { left: 12, right: 12 }
    });

    addPDFFooter(doc, doc.lastAutoTable.finalY, pageWidth);

    const fileName = `DUK_Eselon_NonEselon_${new Date().toISOString().split('T')[0]}.pdf`;
    doc.save(fileName);
    showNotification('PDF Template 2 berhasil diunduh!', 'success');

  } catch (error) {
    console.error('PDF Template 2 Error:', error);
    showNotification(`Error: ${error.message}`, 'danger');
  }
}

// Fungsi untuk memproses data Template 2
function processTemplate2Data(data) {
  const result = {};

  data.forEach(item => {
    const gol = item.golongan || '';
    if (!gol) return;

    if (!result[gol]) result[gol] = {};

    // Cek apakah Non-Eselon
    const isNonEselon = !item.eselon || 
                        item.eselon === '-' || 
                        item.eselon === 'Non-Eselon' || 
                        item.eselon.toLowerCase().includes('non');

    // === ESELON (5 kolom: I, II, III, IV, V) ===
    if (!isNonEselon) {
      if (item.eselon.includes('I') && !item.eselon.includes('II') && !item.eselon.includes('III') && !item.eselon.includes('IV')) {
        result[gol][0] = (result[gol][0] || 0) + 1; // Eselon I
      } else if (item.eselon.includes('II')) {
        result[gol][1] = (result[gol][1] || 0) + 1; // Eselon II
      } else if (item.eselon.includes('III')) {
        result[gol][2] = (result[gol][2] || 0) + 1; // Eselon III
      } else if (item.eselon.includes('IV')) {
        result[gol][3] = (result[gol][3] || 0) + 1; // Eselon IV
      } else if (item.eselon.includes('V')) {
        result[gol][4] = (result[gol][4] || 0) + 1; // Eselon V
      }
    }

    // === NON-ESELON (5 kolom: JFT Pratama, JFT Muda, JFT Madya, JFU-5, JFU-6/7) ===
    if (isNonEselon) {
      if (item.jenis_jabatan === 'JFT') {
        const tingkat = (item.jft_tingkat || '').toLowerCase();
        if (tingkat.includes('pratama')) {
          result[gol][5] = (result[gol][5] || 0) + 1; // JFT Pratama
        } else if (tingkat.includes('muda')) {
          result[gol][6] = (result[gol][6] || 0) + 1; // JFT Muda
        } else if (tingkat.includes('madya')) {
          result[gol][7] = (result[gol][7] || 0) + 1; // JFT Madya
        }
      } else if (item.jenis_jabatan === 'JFU') {
        const kelas = item.jfu_kelas || '';
        if (kelas === '5') {
          result[gol][8] = (result[gol][8] || 0) + 1; // JFU Kelas 5
        } else if (kelas === '6' || kelas === '7') {
          result[gol][9] = (result[gol][9] || 0) + 1; // JFU Kelas 6/7
        }
      }
    }

    // === JENIS KELAMIN (2 kolom: L, P) ===
    if (item.jenis_kelamin === 'Laki-laki') {
      result[gol][10] = (result[gol][10] || 0) + 1; // Laki-laki
    } else if (item.jenis_kelamin === 'Perempuan') {
      result[gol][11] = (result[gol][11] || 0) + 1; // Perempuan
    }
  });

  return result;
}

// ============================================
// TEMPLATE 3: JENIS KELAMIN (DIPERBAIKI)
// ============================================
function generatePDFTemplate3_JenisKelamin() {
  try {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    const pageWidth = doc.internal.pageSize.getWidth();

    const exportData = getFilteredExportData();
    let currentY = createPDFHeader(doc, pageWidth, 'DAFTAR PEGAWAI BERDASARKAN JENIS KELAMIN');

    doc.setFontSize(7);
    doc.setTextColor(100, 100, 100);
    doc.text(`Total Pegawai: ${exportData.length}`, 15, currentY);
    currentY += 4;
    doc.setTextColor(0, 0, 0);

    const tableData = [];

    // Header Row 1
    const headerRow1 = [
      { content: 'GOLONGAN/\nRUANG', rowSpan: 2, styles: { halign: 'center', valign: 'middle', fontStyle: 'bold', cellWidth: 20 } },
      { content: 'LAKI-LAKI', colSpan: 3, styles: { halign: 'center', fontStyle: 'bold', fillColor: [220, 230, 241] } },
      { content: 'PEREMPUAN', colSpan: 3, styles: { halign: 'center', fontStyle: 'bold', fillColor: [252, 228, 214] } },
      { content: 'JUMLAH', rowSpan: 2, styles: { valign: 'middle', halign: 'center', fontStyle: 'bold' } }
    ];
    tableData.push(headerRow1);

    // Header Row 2
    const headerRow2 = [
      'Eselon', 'JFT', 'JFU', // Laki-laki
      'Eselon', 'JFT', 'JFU'  // Perempuan
    ];
    tableData.push(headerRow2.map(txt => ({ content: txt, styles: { fontSize: 6, halign: 'center', fontStyle: 'bold' } })));

    const dataByGolongan = processTemplate3Data(exportData);

    const golonganList = [
      { label: 'IV/d', gol: 'IV/d' },
      { label: 'IV/c', gol: 'IV/c' },
      { label: 'IV/b', gol: 'IV/b' },
      { label: 'IV/a', gol: 'IV/a' },
      { label: 'JUMLAH GOLONGAN IV', isTotal: true, section: 'IV' },
      { label: 'III/d', gol: 'III/d' },
      { label: 'III/c', gol: 'III/c' },
      { label: 'III/b', gol: 'III/b' },
      { label: 'III/a', gol: 'III/a' },
      { label: 'JUMLAH GOLONGAN III', isTotal: true, section: 'III' },
      { label: 'II/d', gol: 'II/d' },
      { label: 'II/c', gol: 'II/c' },
      { label: 'II/b', gol: 'II/b' },
      { label: 'II/a', gol: 'II/a' },
      { label: 'JUMLAH GOLONGAN II', isTotal: true, section: 'II' },
      { label: 'I/d', gol: 'I/d' },
      { label: 'I/c', gol: 'I/c' },
      { label: 'I/b', gol: 'I/b' },
      { label: 'I/a', gol: 'I/a' },
      { label: 'JUMLAH GOLONGAN I', isTotal: true, section: 'I' },
      { label: 'TOTAL', isGrandTotal: true }
    ];

    const totalCols = 6; // 3 laki + 3 perempuan
    const columnTotals = Array(totalCols).fill(0);
    const sectionTotals = {
      'IV': Array(totalCols).fill(0),
      'III': Array(totalCols).fill(0),
      'II': Array(totalCols).fill(0),
      'I': Array(totalCols).fill(0)
    };

    golonganList.forEach(({ label, gol, isTotal, isGrandTotal, section }) => {
      const row = [label];

      if (isTotal || isGrandTotal) {
        if (isGrandTotal) {
          for (let i = 0; i < totalCols; i++) {
            row.push(columnTotals[i] > 0 ? columnTotals[i] : '-');
          }
        } else {
          for (let i = 0; i < totalCols; i++) {
            const val = sectionTotals[section][i];
            row.push(val > 0 ? val : '-');
          }
        }

        const rowTotal = row.slice(1).reduce((sum, val) => sum + (typeof val === 'number' ? val : 0), 0);
        row.push(rowTotal);

        tableData.push(row.map(val => ({
          content: val,
          styles: { fontStyle: 'bold', fillColor: [240, 240, 240], fontSize: 6, halign: 'center' }
        })));
      } else {
        const counts = dataByGolongan[gol] || {};
        let rowTotal = 0;

        for (let i = 0; i < totalCols; i++) {
          const count = counts[i] || 0;
          row.push(count > 0 ? count : '-');
          rowTotal += count;
          columnTotals[i] += count;

          if (gol.startsWith('IV/')) sectionTotals['IV'][i] += count;
          else if (gol.startsWith('III/')) sectionTotals['III'][i] += count;
          else if (gol.startsWith('II/')) sectionTotals['II'][i] += count;
          else if (gol.startsWith('I/')) sectionTotals['I'][i] += count;
        }

        row.push(rowTotal);
        tableData.push(row.map(val => ({ content: val, styles: { fontSize: 6, halign: 'center' } })));
      }
    });

    doc.autoTable({
      body: tableData,
      startY: currentY,
      theme: 'grid',
      styles: {
        fontSize: 6,
        cellPadding: 0.5,
        lineColor: [0, 0, 0],
        lineWidth: 0.2,
        halign: 'center',
        valign: 'middle'
      },
      columnStyles: {
        0: { cellWidth: 20, halign: 'left', fontStyle: 'bold' }
      },
      margin: { left: 12, right: 12 }
    });

    addPDFFooter(doc, doc.lastAutoTable.finalY, pageWidth);

    const fileName = `DUK_JenisKelamin_${new Date().toISOString().split('T')[0]}.pdf`;
    doc.save(fileName);
    showNotification('PDF Template 3 berhasil diunduh!', 'success');

  } catch (error) {
    console.error('PDF Template 3 Error:', error);
    showNotification(`Error: ${error.message}`, 'danger');
  }
}

// Proses data untuk Template 3
function processTemplate3Data(data) {
  const result = {};

  data.forEach(item => {
    const gol = item.golongan || '';
    if (!gol) return;

    if (!result[gol]) result[gol] = {};

    const isNonEselon = !item.eselon || 
                        item.eselon === '-' || 
                        item.eselon === 'Non-Eselon' || 
                        item.eselon.toLowerCase().includes('non');

    // LAKI-LAKI (kolom 0-2)
    if (item.jenis_kelamin === 'Laki-laki') {
      if (!isNonEselon) {
        result[gol][0] = (result[gol][0] || 0) + 1; // L - Eselon
      } else if (item.jenis_jabatan === 'JFT') {
        result[gol][1] = (result[gol][1] || 0) + 1; // L - JFT
      } else if (item.jenis_jabatan === 'JFU') {
        result[gol][2] = (result[gol][2] || 0) + 1; // L - JFU
      }
    }

    // PEREMPUAN (kolom 3-5)
    if (item.jenis_kelamin === 'Perempuan') {
      if (!isNonEselon) {
        result[gol][3] = (result[gol][3] || 0) + 1; // P - Eselon
      } else if (item.jenis_jabatan === 'JFT') {
        result[gol][4] = (result[gol][4] || 0) + 1; // P - JFT
      } else if (item.jenis_jabatan === 'JFU') {
        result[gol][5] = (result[gol][5] || 0) + 1; // P - JFU
      }
    }
  });

  return result;
}

// ============================================
// TEMPLATE 4: PENDIDIKAN TERAKHIR
// ============================================
function generatePDFTemplate4_Pendidikan() {
  try {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    const pageWidth = doc.internal.pageSize.getWidth();

    const exportData = getFilteredExportData();
    let currentY = createPDFHeader(doc, pageWidth, 'DAFTAR PEGAWAI BERDASARKAN PENDIDIKAN TERAKHIR');

    doc.setFontSize(7);
    doc.setTextColor(100, 100, 100);
    doc.text(`Total Pegawai: ${exportData.length}`, 15, currentY);
    currentY += 4;
    doc.setTextColor(0, 0, 0);

    const tableData = [];

    // Header Row 1
    const headerRow1 = [
      { content: 'GOLONGAN/\nRUANG', rowSpan: 2, styles: { halign: 'center', valign: 'middle', fontStyle: 'bold', cellWidth: 20 } },
      { content: 'PENDIDIKAN TERAKHIR', colSpan: 9, styles: { halign: 'center', fontStyle: 'bold', fillColor: [220, 230, 241] } },
      { content: 'JUMLAH', rowSpan: 2, styles: { valign: 'middle', halign: 'center', fontStyle: 'bold' } }
    ];
    tableData.push(headerRow1);

    // Header Row 2
    const headerRow2 = ['SD', 'SMP', 'SMA', 'D1', 'D3', 'D4', 'S1', 'S2', 'S3'];
    tableData.push(headerRow2.map(txt => ({ content: txt, styles: { fontSize: 6, halign: 'center', fontStyle: 'bold' } })));

    const dataByGolongan = processTemplate4Data(exportData);

    const golonganList = [
      { label: 'IV/d', gol: 'IV/d' },
      { label: 'IV/c', gol: 'IV/c' },
      { label: 'IV/b', gol: 'IV/b' },
      { label: 'IV/a', gol: 'IV/a' },
      { label: 'JUMLAH GOLONGAN IV', isTotal: true, section: 'IV' },
      { label: 'III/d', gol: 'III/d' },
      { label: 'III/c', gol: 'III/c' },
      { label: 'III/b', gol: 'III/b' },
      { label: 'III/a', gol: 'III/a' },
      { label: 'JUMLAH GOLONGAN III', isTotal: true, section: 'III' },
      { label: 'II/d', gol: 'II/d' },
      { label: 'II/c', gol: 'II/c' },
      { label: 'II/b', gol: 'II/b' },
      { label: 'II/a', gol: 'II/a' },
      { label: 'JUMLAH GOLONGAN II', isTotal: true, section: 'II' },
      { label: 'I/d', gol: 'I/d' },
      { label: 'I/c', gol: 'I/c' },
      { label: 'I/b', gol: 'I/b' },
      { label: 'I/a', gol: 'I/a' },
      { label: 'JUMLAH GOLONGAN I', isTotal: true, section: 'I' },
      { label: 'TOTAL', isGrandTotal: true }
    ];

    const totalCols = 9;
    const columnTotals = Array(totalCols).fill(0);
    const sectionTotals = {
      'IV': Array(totalCols).fill(0),
      'III': Array(totalCols).fill(0),
      'II': Array(totalCols).fill(0),
      'I': Array(totalCols).fill(0)
    };

    golonganList.forEach(({ label, gol, isTotal, isGrandTotal, section }) => {
      const row = [label];

      if (isTotal || isGrandTotal) {
        if (isGrandTotal) {
          for (let i = 0; i < totalCols; i++) {
            row.push(columnTotals[i] > 0 ? columnTotals[i] : '-');
          }
        } else {
          for (let i = 0; i < totalCols; i++) {
            const val = sectionTotals[section][i];
            row.push(val > 0 ? val : '-');
          }
        }

        const rowTotal = row.slice(1).reduce((sum, val) => sum + (typeof val === 'number' ? val : 0), 0);
        row.push(rowTotal);

        tableData.push(row.map(val => ({
          content: val,
          styles: { fontStyle: 'bold', fillColor: [240, 240, 240], fontSize: 6, halign: 'center' }
        })));
      } else {
        const counts = dataByGolongan[gol] || {};
        let rowTotal = 0;

        for (let i = 0; i < totalCols; i++) {
          const count = counts[i] || 0;
          row.push(count > 0 ? count : '-');
          rowTotal += count;
          columnTotals[i] += count;

          if (gol.startsWith('IV/')) sectionTotals['IV'][i] += count;
          else if (gol.startsWith('III/')) sectionTotals['III'][i] += count;
          else if (gol.startsWith('II/')) sectionTotals['II'][i] += count;
          else if (gol.startsWith('I/')) sectionTotals['I'][i] += count;
        }

        row.push(rowTotal);
        tableData.push(row.map(val => ({ content: val, styles: { fontSize: 6, halign: 'center' } })));
      }
    });

    doc.autoTable({
      body: tableData,
      startY: currentY,
      theme: 'grid',
      styles: {
        fontSize: 6,
        cellPadding: 0.5,
        lineColor: [0, 0, 0],
        lineWidth: 0.2,
        halign: 'center',
        valign: 'middle'
      },
      columnStyles: {
        0: { cellWidth: 20, halign: 'left', fontStyle: 'bold' }
      },
      margin: { left: 12, right: 12 }
    });

    addPDFFooter(doc, doc.lastAutoTable.finalY, pageWidth);

    const fileName = `DUK_Pendidikan_${new Date().toISOString().split('T')[0]}.pdf`;
    doc.save(fileName);
    showNotification('PDF Template 4 berhasil diunduh!', 'success');

  } catch (error) {
    console.error('PDF Template 4 Error:', error);
    showNotification(`Error: ${error.message}`, 'danger');
  }
}

// Proses data untuk Template 4
function processTemplate4Data(data) {
  const result = {};

  data.forEach(item => {
    const gol = item.golongan || '';
    if (!gol) return;

    if (!result[gol]) result[gol] = {};

    const pendidikanMap = {
      'SD': 0, 'SMP': 1, 'SMA': 2, 'D1': 3, 'D3': 4, 'D4': 5, 'S1': 6, 'S2': 7, 'S3': 8
    };

    const pend = item.pendidikan || '';
    if (pendidikanMap[pend] !== undefined) {
      result[gol][pendidikanMap[pend]] = (result[gol][pendidikanMap[pend]] || 0) + 1;
    }
  });

  return result;
}

// ============================================
// TEMPLATE 5: DETAIL PER GOLONGAN (LENGKAP)
// Letakkan kode ini setelah Template 4
// ============================================

function generatePDFTemplate5_PerGolongan() {
  try {
    const { jsPDF } = window.jspdf;
    if (!jsPDF) {
      showNotification('Library jsPDF tidak tersedia!', 'danger');
      return;
    }

    const exportData = getFilteredExportData();
    
    if (!exportData || exportData.length === 0) {
      showNotification('Tidak ada data untuk diekspor!', 'warning');
      return;
    }

    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    const pageWidth = doc.internal.pageSize.getWidth();
    
    let currentY = createPDFHeader(doc, pageWidth, 'DAFTAR PEGAWAI PER GOLONGAN LENGKAP\n(JABATAN, JENIS KELAMIN, DAN PENDIDIKAN)');
    
    doc.setFontSize(7);
    doc.setTextColor(100, 100, 100);
    doc.text(`Total Pegawai: ${exportData.length}`, 15, currentY);
    currentY += 4;
    doc.setTextColor(0, 0, 0);
    
    const tableData = [];
    
    // ===== HEADER BARIS 1 - Kategori Utama =====
    const headerRow1 = [
      { content: 'GOLONGAN/\nRUANG', rowSpan: 2, styles: { halign: 'center', valign: 'middle', fontStyle: 'bold', cellWidth: 20 } },
      { content: 'STATUS JABATAN', colSpan: 3, styles: { halign: 'center', fontStyle: 'bold', fillColor: [220, 230, 241] } },
      { content: 'JENIS KELAMIN', colSpan: 2, styles: { halign: 'center', fontStyle: 'bold', fillColor: [252, 228, 214] } },
      { content: 'PENDIDIKAN TERAKHIR', colSpan: 6, styles: { halign: 'center', fontStyle: 'bold', fillColor: [217, 234, 211] } },
      { content: 'JUMLAH', rowSpan: 2, styles: { valign: 'middle', halign: 'center', fontStyle: 'bold' } }
    ];
    tableData.push(headerRow1);
    
    // ===== HEADER BARIS 2 - Detail Sub Kolom =====
    const headerRow2 = [
      'Eselon', 'JFT', 'JFU',  // Status Jabatan (3 kolom)
      'L', 'P',                 // Jenis Kelamin (2 kolom)
      'SMA', 'D3', 'D4', 'S1', 'S2', 'S3'  // Pendidikan (6 kolom)
    ];
    tableData.push(headerRow2.map(txt => ({ 
      content: txt, 
      styles: { fontSize: 6, halign: 'center', fontStyle: 'bold' } 
    })));
    
    // Proses data per golongan
    const dataByGolongan = processTemplate5Data(exportData);
    
    // List golongan
    const golonganList = [
      { label: 'IV/d', gol: 'IV/d' },
      { label: 'IV/c', gol: 'IV/c' },
      { label: 'IV/b', gol: 'IV/b' },
      { label: 'IV/a', gol: 'IV/a' },
      { label: 'JUMLAH GOLONGAN IV', isTotal: true, section: 'IV' },
      { label: 'III/d', gol: 'III/d' },
      { label: 'III/c', gol: 'III/c' },
      { label: 'III/b', gol: 'III/b' },
      { label: 'III/a', gol: 'III/a' },
      { label: 'JUMLAH GOLONGAN III', isTotal: true, section: 'III' },
      { label: 'II/d', gol: 'II/d' },
      { label: 'II/c', gol: 'II/c' },
      { label: 'II/b', gol: 'II/b' },
      { label: 'II/a', gol: 'II/a' },
      { label: 'JUMLAH GOLONGAN II', isTotal: true, section: 'II' },
      { label: 'I/d', gol: 'I/d' },
      { label: 'I/c', gol: 'I/c' },
      { label: 'I/b', gol: 'I/b' },
      { label: 'I/a', gol: 'I/a' },
      { label: 'JUMLAH GOLONGAN I', isTotal: true, section: 'I' },
      { label: 'TOTAL', isGrandTotal: true }
    ];
    
    // Total 11 kolom data (3 jabatan + 2 gender + 6 pendidikan)
    const totalCols = 11;
    const columnTotals = Array(totalCols).fill(0);
    const sectionTotals = {
      'IV': Array(totalCols).fill(0),
      'III': Array(totalCols).fill(0),
      'II': Array(totalCols).fill(0),
      'I': Array(totalCols).fill(0)
    };
    
    // ===== ISI DATA TABEL =====
    golonganList.forEach(({ label, gol, isTotal, isGrandTotal, section }) => {
      const row = [label];
      
      if (isTotal || isGrandTotal) {
        // BARIS TOTAL
        if (isGrandTotal) {
          // Grand Total (semua golongan)
          for (let i = 0; i < totalCols; i++) {
            row.push(columnTotals[i] > 0 ? columnTotals[i] : '-');
          }
        } else {
          // Total per section (IV, III, II, I)
          for (let i = 0; i < totalCols; i++) {
            const val = sectionTotals[section][i];
            row.push(val > 0 ? val : '-');
          }
        }
        
        const rowTotal = row.slice(1).reduce((sum, val) => sum + (typeof val === 'number' ? val : 0), 0);
        row.push(rowTotal);
        
        tableData.push(row.map(val => ({ 
          content: val, 
          styles: { fontStyle: 'bold', fillColor: [240, 240, 240], fontSize: 6, halign: 'center' }
        })));
      } else {
        // BARIS DATA NORMAL
        const counts = dataByGolongan[gol] || {};
        let rowTotal = 0;
        
        for (let i = 0; i < totalCols; i++) {
          const count = counts[i] || 0;
          row.push(count > 0 ? count : '-');
          rowTotal += count;
          columnTotals[i] += count;
          
          // Akumulasi ke section totals
          if (gol.startsWith('IV/')) sectionTotals['IV'][i] += count;
          else if (gol.startsWith('III/')) sectionTotals['III'][i] += count;
          else if (gol.startsWith('II/')) sectionTotals['II'][i] += count;
          else if (gol.startsWith('I/')) sectionTotals['I'][i] += count;
        }
        
        row.push(rowTotal);
        tableData.push(row.map(val => ({ 
          content: val, 
          styles: { fontSize: 6, halign: 'center' } 
        })));
      }
    });

    // ===== BUAT TABEL PDF =====
    doc.autoTable({
      body: tableData,
      startY: currentY,
      theme: 'grid',
      styles: {
        fontSize: 6,
        cellPadding: 0.5,
        lineColor: [0, 0, 0],
        lineWidth: 0.2,
        halign: 'center',
        valign: 'middle'
      },
      columnStyles: {
        0: { cellWidth: 20, halign: 'left', fontStyle: 'bold' }
      },
      margin: { left: 12, right: 12 }
    });

    // ===== FOOTER TTD =====
    addPDFFooter(doc, doc.lastAutoTable.finalY, pageWidth);
    
    // ===== SAVE PDF =====
    const fileName = `DUK_PerGolongan_${new Date().toISOString().split('T')[0]}.pdf`;
    doc.save(fileName);
    showNotification('PDF Template 5 berhasil diunduh!', 'success');

  } catch (error) {
    console.error('PDF Template 5 Error:', error);
    showNotification(`Error: ${error.message}`, 'danger');
  }
}

// ============================================
// FUNGSI PROSES DATA UNTUK TEMPLATE 5
// ============================================
function processTemplate5Data(data) {
  const result = {};
  
  console.log('Memproses Template 5 untuk', data.length, 'pegawai...');
  
  data.forEach((item, index) => {
    const gol = item.golongan || '';
    if (!gol) {
      console.warn('Data ke-' + index + ' tidak punya golongan:', item);
      return;
    }
    
    if (!result[gol]) {
      result[gol] = {};
    }
    
    // Cek apakah Non-Eselon
    const isNonEselon = !item.eselon || 
                        item.eselon === '-' || 
                        item.eselon === 'Non-Eselon' || 
                        item.eselon.toLowerCase().includes('non');
    
    // ===== KOLOM 0-2: STATUS JABATAN (Eselon, JFT, JFU) =====
    if (!isNonEselon) {
      // Pegawai Ber-Eselon
      result[gol][0] = (result[gol][0] || 0) + 1; // Kolom Eselon
    } else {
      // Pegawai Non-Eselon
      if (item.jenis_jabatan === 'JFT') {
        result[gol][1] = (result[gol][1] || 0) + 1; // Kolom JFT
      } else if (item.jenis_jabatan === 'JFU') {
        result[gol][2] = (result[gol][2] || 0) + 1; // Kolom JFU
      }
    }
    
    // ===== KOLOM 3-4: JENIS KELAMIN (L, P) =====
    if (item.jenis_kelamin === 'Laki-laki') {
      result[gol][3] = (result[gol][3] || 0) + 1; // Kolom L
    } else if (item.jenis_kelamin === 'Perempuan') {
      result[gol][4] = (result[gol][4] || 0) + 1; // Kolom P
    }
    
    // ===== KOLOM 5-10: PENDIDIKAN TERAKHIR (SMA, D3, D4, S1, S2, S3) =====
    const pendidikanMap = { 
      'SMA': 5, 
      'D3': 6, 
      'D4': 7, 
      'S1': 8, 
      'S2': 9, 
      'S3': 10 
    };
    
    const pend = item.pendidikan || '';
    if (pendidikanMap[pend] !== undefined) {
      const colIndex = pendidikanMap[pend];
      result[gol][colIndex] = (result[gol][colIndex] || 0) + 1;
    }
  });
  
  console.log('Hasil proses Template 5:', result);
  return result;
}

console.log('✅ Template 5 - Detail Per Golongan berhasil dimuat!');

  // ============================================
  // FUNGSI EXPORT EXCEL (TETAP)
  // ============================================
  function startExcelExport() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
    if (modal) modal.hide();

    showNotification('Membuat Excel...', 'info');
    setTimeout(() => exportToExcel(), 300);
  }

  function exportToExcel() {
    try {
      if (typeof XLSX === 'undefined') throw new Error('XLSX not loaded');

      const exportData = getFilteredExportData();
      const wb = XLSX.utils.book_new();
      let wsData = [];

      wsData.push(['PEMERINTAH KOTA BANJARMASIN']);
      wsData.push(['DINAS PENGENDALIAN PENDUDUK, KELUARGA BERENCANA DAN PEMBERDAYAAN MASYARAKAT']);
      wsData.push(['']);
      wsData.push(['DAFTAR URUSAN KEPEGAWAIAN']);
      wsData.push(['']);
      wsData.push([`Tanggal: ${new Date().toLocaleDateString('id-ID')}`, '', '', '', '', '', '', '', '', '', '', `Jumlah: ${exportData.length} orang`]);
      wsData.push(['']);
      wsData.push(['No', 'NAMA', 'NIP', 'TEMPAT/TGL LAHIR', 'L/P', 'PANGKAT/GOL', 'JABATAN', 'PENDIDIKAN', 'TMT PANGKAT', 'ESELON', 'JENIS JAB', 'JFT/JFU']);

      exportData.forEach((item, index) => {
        let jftJfu = '-';
        const isNonEselon = !item.eselon || item.eselon === '-' || item.eselon === 'Non-Eselon' || item.eselon.toLowerCase().includes('non');

        if (isNonEselon) {
          if (item.jenis_jabatan === 'JFT') {
            jftJfu = `JFT ${item.jft_tingkat || ''}`;
          } else if (item.jenis_jabatan === 'JFU') {
            jftJfu = `JFU Kelas ${item.jfu_kelas || ''}`;
          }
        }

        wsData.push([
          index + 1,
          (item.nama || '').toUpperCase(),
          item.nip || '',
          item.ttl || '',
          item.jenis_kelamin === 'Laki-laki' ? 'L' : item.jenis_kelamin === 'Perempuan' ? 'P' : '',
          `${item.pangkat || ''}${item.golongan ? '/' + item.golongan : ''}`,
          item.jabatan || '',
          item.pendidikan || '',
          item.tmt || '',
          item.eselon || '',
          item.jenis_jabatan || '',
          jftJfu
        ]);
      });

      const ws = XLSX.utils.aoa_to_sheet(wsData);
      ws['!cols'] = [
        { wch: 5 }, { wch: 25 }, { wch: 20 }, { wch: 25 }, { wch: 5 },
        { wch: 18 }, { wch: 30 }, { wch: 15 }, { wch: 15 }, { wch: 12 }, { wch: 10 }, { wch: 15 }
      ];
      ws['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: 11 } },
        { s: { r: 1, c: 0 }, e: { r: 1, c: 11 } },
        { s: { r: 3, c: 0 }, e: { r: 3, c: 11 } }
      ];

      XLSX.utils.book_append_sheet(wb, ws, 'DUK');
      const fileName = `DUK_${new Date().toISOString().split('T')[0]}.xlsx`;
      XLSX.writeFile(wb, fileName);
      showNotification('Excel berhasil diunduh!', 'success');

    } catch (error) {
      console.error('Excel Generation Error:', error);
      showNotification(`Error Excel: ${error.message}`, 'danger');
    }
  }

  
