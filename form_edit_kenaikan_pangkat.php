<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

if (!isAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

$error = $_GET['error'] ?? '';
$id    = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: kenaikan_pangkat.php?error=ID tidak ditemukan');
    exit;
}

$query = "SELECT * FROM kenaikan_pangkat WHERE id = ?";
$stmt  = $koneksi->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: kenaikan_pangkat.php?error=Data tidak ditemukan');
    exit;
}

$data = $result->fetch_assoc();

require_once 'includes/date_helper.php';

$ttl_preview = '';
if (!empty($data['tempat_lahir']) && !empty($data['tanggal_lahir'])) {
    $ttl_preview = formatTTL($data['tempat_lahir'], $data['tanggal_lahir']);
}

// Daftar golongan lengkap
$golongan_list = [
    'I/a','I/b','I/c','I/d',
    'II/a','II/b','II/c','II/d',
    'III/a','III/b','III/c','III/d',
    'IV/a','IV/b','IV/c','IV/d'
];
?>

<link rel="stylesheet" href="css/form_tambah_duk.css">

<main class="main-content">
  <div class="container-fluid">
    <div class="page-header fade-in">
      <h2><i class="fas fa-edit me-3"></i>Edit Usulan Kenaikan Pangkat</h2>
      <p class="mb-0">Formulir Edit Usulan <?= htmlspecialchars($data['nomor_usulan']) ?></p>
    </div>

    <div class="row justify-content-center">
      <div class="col-12 col-xl-10">

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger fade-in">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <div class="form-card fade-in">
          <div class="card-body">
            <form action="proses_edit_kenaikan_pangkat.php" method="POST">
              <input type="hidden" name="id" value="<?= $data['id'] ?>">

              <!-- ===== INFORMASI USULAN ===== -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-file-alt me-2"></i>Informasi Usulan
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-hashtag"></i> Nomor Usulan <span class="required">*</span></label>
                      <input type="text" name="nomor_usulan" class="form-control"
                             value="<?= htmlspecialchars($data['nomor_usulan']) ?>" required>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-calendar"></i> Tanggal Usulan <span class="required">*</span></label>
                      <input type="date" name="tanggal_usulan" class="form-control"
                             value="<?= $data['tanggal_usulan'] ?>" required>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-list"></i> Jenis Kenaikan <span class="required">*</span></label>
                      <select name="jenis_kenaikan" class="form-select" required>
                        <option value="Pilihan"    <?= $data['jenis_kenaikan'] == 'Pilihan'    ? 'selected' : '' ?>>1. PILIHAN</option>
                        <option value="Reguler"    <?= $data['jenis_kenaikan'] == 'Reguler'    ? 'selected' : '' ?>>2. REGULER</option>
                        <option value="Anumerta"   <?= $data['jenis_kenaikan'] == 'Anumerta'   ? 'selected' : '' ?>>3. ANUMERTA</option>
                        <option value="Pengabdian" <?= $data['jenis_kenaikan'] == 'Pengabdian' ? 'selected' : '' ?>>4. PENGABDIAN</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ===== DATA PEGAWAI (READONLY) ===== -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-user me-2"></i>Data Pegawai
                  <small class="text-muted ms-2">(Tidak dapat diubah)</small>
                </div>

                <?php if (!empty($ttl_preview)): ?>
                <div class="alert alert-info mb-3">
                  <i class="fas fa-info-circle me-2"></i>
                  <strong>TTL:</strong> <?= $ttl_preview ?>
                </div>
                <?php endif; ?>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-id-card"></i> NIP</label>
                      <input type="text" name="nip" class="form-control"
                             value="<?= htmlspecialchars($data['nip']) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-user"></i> Nama</label>
                      <input type="text" name="nama" class="form-control"
                             value="<?= htmlspecialchars($data['nama']) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-id-badge"></i> Kartu Pegawai</label>
                      <input type="text" name="kartu_pegawai" class="form-control"
                             value="<?= htmlspecialchars($data['kartu_pegawai']) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-map-marker-alt"></i> Tempat Lahir</label>
                      <input type="text" name="tempat_lahir" class="form-control"
                             value="<?= htmlspecialchars($data['tempat_lahir'] ?? '') ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-calendar-alt"></i> Tanggal Lahir</label>
                      <input type="date" name="tanggal_lahir" class="form-control"
                             value="<?= htmlspecialchars($data['tanggal_lahir'] ?? '') ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-graduation-cap"></i> Pendidikan</label>
                      <input type="text" name="pendidikan_terakhir" class="form-control"
                             value="<?= htmlspecialchars($data['pendidikan_terakhir']) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-book"></i> Program Studi</label>
                      <input type="text" name="prodi" class="form-control"
                             value="<?= htmlspecialchars($data['prodi']) ?>" readonly>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ===== PANGKAT LAMA ===== -->
              <div class="form-section">
                <div class="section-title bg-danger text-white">
                  <i class="fas fa-arrow-down me-2"></i>Pangkat/Golongan LAMA
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-medal"></i> Pangkat Lama</label>
                      <input type="text" name="pangkat_lama" class="form-control"
                             value="<?= htmlspecialchars($data['pangkat_lama']) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-layer-group"></i> Golongan Lama</label>
                      <!-- READONLY: golongan lama tidak bisa diubah -->
                      <input type="text" name="golongan_lama" class="form-control"
                             value="<?= htmlspecialchars($data['golongan_lama']) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-calendar-check"></i> TMT Pangkat Lama</label>
                      <input type="date" name="tmt_pangkat_lama" class="form-control"
                             value="<?= $data['tmt_pangkat_lama'] ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-clock"></i> MK (Tahun) <span class="required">*</span></label>
                      <input type="number" name="masa_kerja_tahun_lama" class="form-control"
                             value="<?= $data['masa_kerja_tahun_lama'] ?>" required min="0">
                      <small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Sesuaikan dengan SK jika berbeda.</small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-clock"></i> MK (Bulan) <span class="required">*</span></label>
                      <input type="number" name="masa_kerja_bulan_lama" class="form-control"
                             value="<?= $data['masa_kerja_bulan_lama'] ?>" required min="0" max="11">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-money-bill-wave"></i> Gaji Pokok Lama <span class="required">*</span></label>
                      <input type="text" name="gaji_pokok_lama" class="form-control money"
                             value="<?= number_format($data['gaji_pokok_lama'], 0, ',', '.') ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-briefcase"></i> Jabatan Lama <span class="required">*</span></label>
                      <input type="text" name="jabatan_lama" class="form-control"
                             value="<?= htmlspecialchars($data['jabatan_lama']) ?>" required>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ===== PANGKAT BARU ===== -->
              <div class="form-section">
                <div class="section-title bg-success text-white">
                  <i class="fas fa-arrow-up me-2"></i>Pangkat/Golongan BARU (Usulan)
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-medal"></i> Pangkat Baru <span class="required">*</span></label>
                      <input type="text" name="pangkat_baru" class="form-control"
                             value="<?= htmlspecialchars($data['pangkat_baru']) ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-layer-group"></i> Golongan Baru <span class="required">*</span></label>
                      <!-- name="golongan_baru" — BUKAN golongan_lama -->
                      <select name="golongan_baru" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($golongan_list as $gol): ?>
                          <option value="<?= $gol ?>" <?= $data['golongan_baru'] == $gol ? 'selected' : '' ?>>
                            <?= $gol ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-calendar-check"></i> TMT Pangkat Baru <span class="required">*</span></label>
                      <input type="date" name="tmt_pangkat_baru" class="form-control"
                             value="<?= $data['tmt_pangkat_baru'] ?>" required>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-clock"></i> MK (Tahun) <span class="required">*</span></label>
                      <input type="number" name="masa_kerja_tahun_baru" class="form-control"
                             value="<?= $data['masa_kerja_tahun_baru'] ?>" required min="0">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-clock"></i> MK (Bulan) <span class="required">*</span></label>
                      <input type="number" name="masa_kerja_bulan_baru" class="form-control"
                             value="<?= $data['masa_kerja_bulan_baru'] ?>" required min="0" max="11">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-money-bill-wave"></i> Gaji Pokok Baru <span class="required">*</span></label>
                      <input type="text" name="gaji_pokok_baru" class="form-control money"
                             value="<?= number_format($data['gaji_pokok_baru'], 0, ',', '.') ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-briefcase"></i> Jabatan Baru <span class="required">*</span></label>
                      <input type="text" name="jabatan_baru" class="form-control"
                             value="<?= htmlspecialchars($data['jabatan_baru']) ?>" required>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ===== PERHITUNGAN MASA KERJA ===== -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-calculator me-2"></i>Perhitungan Masa Kerja
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-clock"></i> MK Dalam Gol/Ruang (Tahun) <span class="required">*</span></label>
                      <input type="number" name="mk_golongan_tahun" class="form-control"
                             value="<?= $data['mk_golongan_tahun'] ?>" required min="0">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-clock"></i> MK Dalam Gol/Ruang (Bulan) <span class="required">*</span></label>
                      <input type="number" name="mk_golongan_bulan" class="form-control"
                             value="<?= $data['mk_golongan_bulan'] ?>" required min="0" max="11">
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-calendar-alt"></i> Dari s/d</label>
                      <input type="text" name="mk_dari_sampai" class="form-control"
                             value="<?= htmlspecialchars($data['mk_dari_sampai']) ?>"
                             placeholder="01-01-2007 s/d 01-01-2027">
                    </div>
                  </div>
                </div>
              </div>

              <!-- ===== ATASAN LANGSUNG ===== -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-user-tie me-2"></i>Atasan Langsung
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-user"></i> Nama Atasan</label>
                      <input type="text" name="atasan_nama" class="form-control"
                             value="<?= htmlspecialchars($data['atasan_nama']) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-id-card"></i> NIP Atasan</label>
                      <input type="text" name="atasan_nip" class="form-control"
                             value="<?= htmlspecialchars($data['atasan_nip']) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-medal"></i> Pangkat Atasan</label>
                      <input type="text" name="atasan_pangkat" class="form-control"
                             value="<?= htmlspecialchars($data['atasan_pangkat']) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-briefcase"></i> Jabatan Atasan</label>
                      <input type="text" name="atasan_jabatan" class="form-control"
                             value="<?= htmlspecialchars($data['atasan_jabatan']) ?>" readonly>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ===== WILAYAH & SKP ===== -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-map-marked-alt me-2"></i>Wilayah Pembayaran & SKP
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-map-pin"></i> Wilayah Pembayaran</label>
                      <input type="text" name="wilayah_pembayaran" class="form-control"
                             value="<?= htmlspecialchars($data['wilayah_pembayaran']) ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-calendar"></i> SKP Tahun 1</label>
                      <input type="text" name="skp_tahun_1" class="form-control"
                             value="<?= htmlspecialchars($data['skp_tahun_1']) ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-star"></i> Nilai SKP 1</label>
                      <input type="text" name="skp_nilai_1" class="form-control"
                             value="<?= htmlspecialchars($data['skp_nilai_1']) ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-calendar"></i> SKP Tahun 2</label>
                      <input type="text" name="skp_tahun_2" class="form-control"
                             value="<?= htmlspecialchars($data['skp_tahun_2']) ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-star"></i> Nilai SKP 2</label>
                      <input type="text" name="skp_nilai_2" class="form-control"
                             value="<?= htmlspecialchars($data['skp_nilai_2']) ?>">
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label"><i class="fas fa-info-circle"></i> Status <span class="required">*</span></label>
                      <select name="status" class="form-select" required>
                        <option value="draft"     <?= $data['status'] == 'draft'     ? 'selected' : '' ?>>Draft</option>
                        <option value="diajukan"  <?= $data['status'] == 'diajukan'  ? 'selected' : '' ?>>Diajukan</option>
                        <option value="disetujui" <?= $data['status'] == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                        <option value="ditolak"   <?= $data['status'] == 'ditolak'   ? 'selected' : '' ?>>Ditolak</option>
                        <option value="sk_terbit" <?= $data['status'] == 'sk_terbit' ? 'selected' : '' ?>>✓ SK Terbit</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ===== ACTION BUTTONS ===== -->
              <div class="btn-group-custom">
                <a href="kenaikan_pangkat.php" class="btn btn-secondary">
                  <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format gaji
    const gajiInputs = document.querySelectorAll('.money');
    gajiInputs.forEach(input => {
        formatMoney(input);
        input.addEventListener('input', function(e) { formatMoney(e.target); });
    });

    function formatMoney(input) {
        let value = input.value.replace(/\D/g, '');
        if (value) value = parseInt(value).toLocaleString('id-ID');
        input.value = value;
    }

    // Auto-generate dari s/d + hitung MK golongan
    const tmtLamaInput = document.querySelector('[name="tmt_pangkat_lama"]');
    const tmtBaruInput = document.querySelector('[name="tmt_pangkat_baru"]');

    function updateDariSampai() {
        const tmtLama = tmtLamaInput ? tmtLamaInput.value : '';
        const tmtBaru = tmtBaruInput ? tmtBaruInput.value : '';
        const fieldDariSampai = document.querySelector('[name="mk_dari_sampai"]');

        if (!tmtLama || !tmtBaru || !fieldDariSampai) return;

        function formatTanggal(dateStr) {
            const [y, m, d] = dateStr.split('-');
            return `${d}-${m}-${y}`;
        }

        fieldDariSampai.value = `${formatTanggal(tmtLama)} s/d ${formatTanggal(tmtBaru)}`;

        const d1 = new Date(tmtLama);
        const d2 = new Date(tmtBaru);
        let tahun = d2.getFullYear() - d1.getFullYear();
        let bulan = d2.getMonth() - d1.getMonth();
        if (bulan < 0) { tahun--; bulan += 12; }

        document.querySelector('[name="mk_golongan_tahun"]').value = tahun;
        document.querySelector('[name="mk_golongan_bulan"]').value = bulan;
    }

    if (tmtBaruInput) tmtBaruInput.addEventListener('change', updateDariSampai);
    updateDariSampai();
});
</script>

<?php require_once 'includes/footer.php'; ?>