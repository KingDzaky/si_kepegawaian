// Advanced Export System for DUK Data
// Requires: jsPDF, jsPDF-AutoTable, and SheetJS libraries

// Add required libraries dynamically
function loadExportLibraries() {
    return new Promise((resolve, reject) => {
        const scripts = [
            'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js'
        ];
        
        let loadedCount = 0;
        
        scripts.forEach(src => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = () => {
                loadedCount++;
                if (loadedCount === scripts.length) {
                    resolve();
                }
            };
            script.onerror = reject;
            document.head.appendChild(script);
        });
    });
}

// Enhanced Export Data function
async function exportData() {
    if (filteredData.length === 0) {
        showNotification('Tidak ada data untuk diekspor', 'warning');
        return;
    }

    // Show export modal
    showExportModal();
}

// Show export options modal
function showExportModal() {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'exportModal';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-download me-2"></i>
                        Export Data DUK
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="export-option" onclick="exportToPDF()">
                                <div class="export-icon">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                </div>
                                <div class="export-content">
                                    <h6>Export ke PDF</h6>
                                    <p class="text-muted mb-0">Format dokumen resmi dengan layout DUK standar</p>
                                    <small class="text-success">Recommended untuk laporan formal</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="export-option" onclick="exportToExcel()">
                                <div class="export-icon">
                                    <i class="fas fa-file-excel text-success"></i>
                                </div>
                                <div class="export-content">
                                    <h6>Export ke Excel</h6>
                                    <p class="text-muted mb-0">Spreadsheet untuk analisis dan editing data</p>
                                    <small class="text-info">Dapat diedit dan dianalisis lebih lanjut</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="export-settings">
                        <h6 class="mb-3">Pengaturan Export</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="includeHeader" checked>
                                    <label class="form-check-label" for="includeHeader">
                                        Sertakan header instansi
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="includeDate" checked>
                                    <label class="form-check-label" for="includeDate">
                                        Sertakan tanggal export
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="includeStats" checked>
                                    <label class="form-check-label" for="includeStats">
                                        Sertakan ringkasan statistik
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Orientasi halaman:</label>
                                    <select class="form-select form-select-sm" id="pageOrientation">
                                        <option value="landscape">Landscape (Horizontal)</option>
                                        <option value="portrait">Portrait (Vertikal)</option>
                                    </select>
                                </div>
                                <div class="form-group mt-2">
                                    <label class="form-label">Judul dokumen:</label>
                                    <input type="text" class="form-control form-control-sm" id="documentTitle" 
                                           value="DAFTAR URUSAN KEPEGAWAIAN">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="export-info mt-3 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Data yang akan diekspor:</strong>
                                <br><span class="text-primary">${filteredData.length} records</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Tanggal export:</strong>
                                <br><span class="text-muted">${new Date().toLocaleDateString('id-ID')}</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Format data:</strong>
                                <br><span class="text-success">Sesuai filter aktif</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </button>
                    <button type="button" class="btn btn-success" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Add modal styles
    const modalStyles = document.createElement('style');
    modalStyles.textContent = `
        .export-option {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            align-items: center;
        }
        
        .export-option:hover {
            border-color: #007bff;
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,123,255,0.1);
        }
        
        .export-icon {
            font-size: 2.5rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .export-content h6 {
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .export-content p {
            font-size: 0.9rem;
            line-height: 1.3;
        }
        
        .export-info {
            font-size: 0.9rem;
        }
    `;
    document.head.appendChild(modalStyles);
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Clean up when modal is hidden
    modal.addEventListener('hidden.bs.modal', () => {
        modal.remove();
        modalStyles.remove();
    });
}

// Export to PDF with DUK format
async function exportToPDF() {
    try {
        showNotification('Mempersiapkan export PDF...', 'info');
        
        // Load libraries if not already loaded
        if (typeof window.jsPDF === 'undefined') {
            await loadExportLibraries();
        }
        
        const { jsPDF } = window.jsPDF;
        
        // Get settings
        const includeHeader = document.getElementById('includeHeader')?.checked ?? true;
        const includeDate = document.getElementById('includeDate')?.checked ?? true;
        const includeStats = document.getElementById('includeStats')?.checked ?? true;
        const orientation = document.getElementById('pageOrientation')?.value ?? 'landscape';
        const documentTitle = document.getElementById('documentTitle')?.value ?? 'DAFTAR URUSAN KEPEGAWAIAN';
        
        // Create PDF document
        const doc = new jsPDF({
            orientation: orientation,
            unit: 'mm',
            format: 'a4'
        });
        
        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();
        let currentY = 20;
        
        // Set font
        doc.setFont('helvetica');
        
        // Header
        if (includeHeader) {
            // Government header
            doc.setFontSize(14);
            doc.setFont('helvetica', 'bold');
            doc.text('PEMERINTAH KOTA BANJARMASIN', pageWidth / 2, currentY, { align: 'center' });
            currentY += 7;
            
            doc.text('DINAS PERTANIAN', pageWidth / 2, currentY, { align: 'center' });
            currentY += 10;
            
            // Line separator
            doc.setLineWidth(0.5);
            doc.line(20, currentY, pageWidth - 20, currentY);
            currentY += 10;
        }
        
        // Document title
        doc.setFontSize(16);
        doc.setFont('helvetica', 'bold');
        doc.text(documentTitle, pageWidth / 2, currentY, { align: 'center' });
        currentY += 15;
        
        // Date and statistics
        if (includeDate || includeStats) {
            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            
            if (includeDate) {
                doc.text(`Tanggal: ${new Date().toLocaleDateString('id-ID', { 
                    day: 'numeric', 
                    month: 'long', 
                    year: 'numeric' 
                })}`, 20, currentY);
            }
            
            if (includeStats) {
                doc.text(`Total Data: ${filteredData.length} pegawai`, pageWidth - 20, currentY, { align: 'right' });
            }
            
            currentY += 10;
        }
        
        // Prepare table data
        const tableHeaders = [
            'No',
            'Nama',
            'NIP',
            'Pangkat/Gol',
            'Jabatan',
            'TTL',
            'JK',
            'Pendidikan',
            'TMT',
            'Eselon'
        ];
        
        const tableData = filteredData.map((item, index) => [
            (index + 1).toString(),
            item.nama || '-',
            item.nip || '-',
            `${item.pangkat || '-'}/${item.golongan || '-'}`,
            item.jabatan || '-',
            item.ttl || '-',
            item.jenis_kelamin === 'Laki-laki' ? 'L' : 'P',
            item.pendidikan || '-',
            item.tmt || '-',
            item.eselon || '-'
        ]);
        
        // Create table
        doc.autoTable({
            head: [tableHeaders],
            body: tableData,
            startY: currentY,
            theme: 'grid',
            styles: {
                fontSize: 8,
                cellPadding: 2,
                lineColor: [0, 0, 0],
                lineWidth: 0.1,
            },
            headStyles: {
                fillColor: [41, 128, 185],
                textColor: 255,
                fontStyle: 'bold',
                halign: 'center'
            },
            columnStyles: {
                0: { halign: 'center', cellWidth: 10 }, // No
                1: { cellWidth: orientation === 'landscape' ? 35 : 25 }, // Nama
                2: { cellWidth: orientation === 'landscape' ? 25 : 20 }, // NIP
                3: { cellWidth: orientation === 'landscape' ? 25 : 20 }, // Pangkat/Gol
                4: { cellWidth: orientation === 'landscape' ? 30 : 25 }, // Jabatan
                5: { cellWidth: orientation === 'landscape' ? 30 : 25 }, // TTL
                6: { halign: 'center', cellWidth: 10 }, // JK
                7: { cellWidth: orientation === 'landscape' ? 20 : 15 }, // Pendidikan
                8: { cellWidth: orientation === 'landscape' ? 20 : 15 }, // TMT
                9: { cellWidth: orientation === 'landscape' ? 15 : 12 }  // Eselon
            },
            alternateRowStyles: {
                fillColor: [245, 245, 245]
            },
            margin: { top: 20, right: 20, bottom: 20, left: 20 },
        });
        
        // Footer with signature area
        const finalY = doc.lastAutoTable.finalY + 20;
        if (finalY < pageHeight - 60) {
            doc.setFontSize(10);
            doc.text('Mengetahui,', pageWidth - 60, finalY);
            doc.text('Kepala Dinas Pertanian', pageWidth - 60, finalY + 5);
            doc.text('Kota Banjarmasin', pageWidth - 60, finalY + 10);
            
            // Signature line
            doc.line(pageWidth - 80, finalY + 35, pageWidth - 20, finalY + 35);
            doc.text('NIP. ____________________', pageWidth - 60, finalY + 40, { align: 'center' });
        }
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
        if (modal) modal.hide();
        
        // Save PDF
        const fileName = `DUK_${new Date().toISOString().split('T')[0]}.pdf`;
        doc.save(fileName);
        
        showNotification('PDF berhasil diunduh!', 'success');
        
    } catch (error) {
        console.error('Export PDF error:', error);
        showNotification('Gagal mengexport PDF: ' + error.message, 'danger');
    }
}

// Export to Excel with proper DUK format
async function exportToExcel() {
    try {
        showNotification('Mempersiapkan export Excel...', 'info');
        
        // Load libraries if not already loaded
        if (typeof XLSX === 'undefined') {
            await loadExportLibraries();
        }
        
        // Get settings
        const includeHeader = document.getElementById('includeHeader')?.checked ?? true;
        const includeDate = document.getElementById('includeDate')?.checked ?? true;
        const includeStats = document.getElementById('includeStats')?.checked ?? true;
        const documentTitle = document.getElementById('documentTitle')?.value ?? 'DAFTAR URUSAN KEPEGAWAIAN';
        
        // Create workbook
        const wb = XLSX.utils.book_new();
        
        // Prepare data array
        let wsData = [];
        let currentRow = 0;
        
        // Header rows
        if (includeHeader) {
            wsData.push(['PEMERINTAH KOTA BANJARMASIN']);
            wsData.push(['DINAS PERTANIAN']);
            wsData.push(['']); // Empty row
            currentRow += 3;
        }
        
        // Title
        wsData.push([documentTitle]);
        wsData.push(['']); // Empty row
        currentRow += 2;
        
        // Date and stats
        if (includeDate || includeStats) {
            const infoRow = [];
            if (includeDate) {
                infoRow.push(`Tanggal: ${new Date().toLocaleDateString('id-ID')}`);
            }
            if (includeStats) {
                infoRow.push('', '', '', '', '', '', `Total: ${filteredData.length} pegawai`);
            }
            wsData.push(infoRow);
            wsData.push(['']); // Empty row
            currentRow += 2;
        }
        
        // Table headers
        const headers = [
            'No',
            'Nama Lengkap',
            'NIP',
            'Pangkat Terakhir',
            'Golongan',
            'Jabatan Terakhir',
            'Tempat, Tanggal Lahir',
            'Jenis Kelamin',
            'Pendidikan Terakhir',
            'T.M.T Pangkat',
            'Eselon'
        ];
        wsData.push(headers);
        currentRow++;
        
        // Table data
        filteredData.forEach((item, index) => {
            wsData.push([
                index + 1,
                item.nama || '',
                item.nip || '',
                item.pangkat || '',
                item.golongan || '',
                item.jabatan || '',
                item.ttl || '',
                item.jenis_kelamin || '',
                item.pendidikan || '',
                item.tmt || '',
                item.eselon || ''
            ]);
        });
        
        // Create worksheet
        const ws = XLSX.utils.aoa_to_sheet(wsData);
        
        // Set column widths
        const colWidths = [
            { wch: 5 },  // No
            { wch: 25 }, // Nama
            { wch: 20 }, // NIP
            { wch: 18 }, // Pangkat
            { wch: 10 }, // Golongan
            { wch: 25 }, // Jabatan
            { wch: 20 }, // TTL
            { wch: 12 }, // JK
            { wch: 15 }, // Pendidikan
            { wch: 12 }, // TMT
            { wch: 12 }  // Eselon
        ];
        ws['!cols'] = colWidths;
        
        // Merge cells for header
        if (includeHeader) {
            ws['!merges'] = [
                { s: { r: 0, c: 0 }, e: { r: 0, c: 10 } }, // PEMERINTAH KOTA
                { s: { r: 1, c: 0 }, e: { r: 1, c: 10 } }, // DINAS PERTANIAN
                { s: { r: 3, c: 0 }, e: { r: 3, c: 10 } }  // Title
            ];
        }
        
        // Apply styles to specific cells
        const headerRowIndex = currentRow - 1;
        
        // Style header row
        for (let col = 0; col < headers.length; col++) {
            const cellRef = XLSX.utils.encode_cell({ r: headerRowIndex, c: col });
            if (!ws[cellRef]) ws[cellRef] = {};
            ws[cellRef].s = {
                fill: { fgColor: { rgb: "2980B9" } },
                font: { bold: true, color: { rgb: "FFFFFF" } },
                alignment: { horizontal: "center", vertical: "center" },
                border: {
                    top: { style: "thin" },
                    bottom: { style: "thin" },
                    left: { style: "thin" },
                    right: { style: "thin" }
                }
            };
        }
        
        // Add worksheet to workbook
        XLSX.utils.book_append_sheet(wb, ws, 'Data DUK');
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
        if (modal) modal.hide();
        
        // Save Excel file
        const fileName = `DUK_${new Date().toISOString().split('T')[0]}.xlsx`;
        XLSX.writeFile(wb, fileName);
        
        showNotification('Excel berhasil diunduh!', 'success');
        
    } catch (error) {
        console.error('Export Excel error:', error);
        showNotification('Gagal mengexport Excel: ' + error.message, 'danger');
    }
}

// Print report function
function printReport() {
    const printWindow = window.open('', '_blank');
    
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Laporan Data DUK</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 12px; 
                    line-height: 1.4;
                    margin: 20px;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 30px;
                    border-bottom: 2px solid #000;
                    padding-bottom: 15px;
                }
                .header h1 { 
                    margin: 0; 
                    font-size: 16px; 
                    font-weight: bold;
                }
                .header h2 { 
                    margin: 5px 0; 
                    font-size: 14px;
                }
                .info { 
                    display: flex; 
                    justify-content: space-between; 
                    margin: 20px 0;
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-top: 20px;
                }
                th, td { 
                    border: 1px solid #000; 
                    padding: 8px; 
                    text-align: left;
                    vertical-align: top;
                }
                th { 
                    background-color: #f0f0f0; 
                    font-weight: bold;
                    text-align: center;
                }
                .number { 
                    text-align: center; 
                    width: 30px;
                }
                .signature {
                    margin-top: 40px;
                    float: right;
                    text-align: center;
                    width: 200px;
                }
                .signature-line {
                    border-bottom: 1px solid #000;
                    margin: 40px 0 5px 0;
                }
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>PEMERINTAH KOTA BANJARMASIN</h1>
                <h2>DINAS PERTANIAN</h2>
                <h2>DAFTAR URUSAN KEPEGAWAIAN</h2>
            </div>
            
            <div class="info">
                <div>Tanggal: ${new Date().toLocaleDateString('id-ID')}</div>
                <div>Total Data: ${filteredData.length} pegawai</div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th class="number">No</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Pangkat/Gol</th>
                        <th>Jabatan</th>
                        <th>TTL</th>
                        <th>JK</th>
                        <th>Pendidikan</th>
                        <th>TMT</th>
                        <th>Eselon</th>
                    </tr>
                </thead>
                <tbody>
                    ${filteredData.map((item, index) => `
                        <tr>
                            <td class="number">${index + 1}</td>
                            <td>${item.nama || '-'}</td>
                            <td>${item.nip || '-'}</td>
                            <td>${item.pangkat || '-'}/${item.golongan || '-'}</td>
                            <td>${item.jabatan || '-'}</td>
                            <td>${item.ttl || '-'}</td>
                            <td>${item.jenis_kelamin === 'Laki-laki' ? 'L' : 'P'}</td>
                            <td>${item.pendidikan || '-'}</td>
                            <td>${item.tmt || '-'}</td>
                            <td>${item.eselon || '-'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
            
            <div class="signature">
                <div>Mengetahui,</div>
                <div>Kepala Dinas Pertanian</div>
                <div>Kota Banjarmasin</div>
                <div class="signature-line"></div>
                <div>NIP. ____________________</div>
            </div>
            
            <script>
                window.onload = function() {
                    window.print();
                    window.onafterprint = function() {
                        window.close();
                    }
                }
            </script>
        </body>
        </html>
    `;
    
    printWindow.document.write(printContent);
    printWindow.document.close();
}

console.log('Advanced Export System loaded successfully');