<?php
session_start();
require_once 'check_session.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Hanya superadmin yang bisa akses
if (!isSuperAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

require_once 'config/koneksi.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: users.php?error=ID tidak valid');
    exit;
}

$id = (int)$_GET['id'];

// ✅ Gunakan prepared statement untuk keamanan
$stmt = $koneksi->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: users.php?error=User tidak ditemukan');
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();
?>

<style>
    /* ============================================================
       Semua selector di-scope ke .main-content
       agar tidak konflik dengan CSS header/navbar
    ============================================================ */

    .main-content {
        padding: 20px;
        margin-left: 250px;
        transition: margin-left 0.3s ease;
    }

    .main-content .page-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
        padding: 25px 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
    }

    .main-content .page-header h2 {
        color: white;
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        display: flex;
        align-items: center;
    }

    .main-content .page-header h2 i {
        margin-right: 12px;
    }

    .main-content .form-container {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .main-content .form-group {
        margin-bottom: 20px;
    }

    .main-content .form-group label {
        display: block;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .main-content .form-group label span.required {
        color: #ef4444;
    }

    .main-content .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .main-content .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .main-content .form-control[readonly] {
        background-color: #f8fafc;
        color: #94a3b8;
        cursor: not-allowed;
    }

    .main-content .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #e2e8f0;
    }

    .main-content .btn-submit {
        padding: 12px 30px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .main-content .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .main-content .btn-cancel {
        padding: 12px 30px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        background: #64748b;
        color: white;
    }

    .main-content .btn-cancel:hover {
        background: #475569;
        transform: translateY(-2px);
        color: white;
    }

    .main-content .alert-error {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    /* ===== PASSWORD TOGGLE ===== */
    .main-content .password-wrapper {
        position: relative;
    }

    .main-content .password-wrapper .form-control {
        padding-right: 45px;
    }

    .main-content .toggle-password {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #94a3b8;
        transition: color 0.2s ease;
        background: none;
        border: none;
        padding: 0;
        font-size: 15px;
        z-index: 2;
    }

    .main-content .toggle-password:hover {
        color: #667eea;
    }

    /* ===== DIVIDER SECTION ===== */
    .main-content .section-divider {
        border: none;
        border-top: 1px dashed #e2e8f0;
        margin: 25px 0;
    }

    .main-content .section-title {
        font-size: 13px;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 15px;
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 15px;
        }

        .main-content .form-actions {
            flex-direction: column;
        }

        .main-content .btn-submit,
        .main-content .btn-cancel {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <h2>
            <i class="fas fa-user-edit"></i>
            Edit User
        </h2>
    </div>

    <!-- Alert Error -->
    <?php if (isset($_GET['error'])): ?>
    <div class="alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="form-container">
        <form action="proses_edit_user.php" method="POST" id="formEditUser">
            <input type="hidden" name="id" value="<?= $user['id'] ?>">

            <!-- ===== SECTION: INFO AKUN ===== -->
            <p class="section-title"><i class="fas fa-id-card me-1"></i> Informasi Akun</p>

            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= htmlspecialchars($user['username']) ?>" required readonly>
                <small class="text-muted"><i class="fas fa-lock me-1"></i>Username tidak dapat diubah</small>
            </div>

            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap <span class="required">*</span></label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                       value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required
                       placeholder="Masukkan nama lengkap">
            </div>

            <hr class="section-divider">

            <!-- ===== SECTION: GANTI PASSWORD ===== -->
            <p class="section-title"><i class="fas fa-key me-1"></i> Ganti Password</p>

            <div class="form-group">
                <label for="password">Password Baru</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Kosongkan jika tidak ingin mengubah password">
                    <button type="button" class="toggle-password" onclick="togglePassword('password', this)"
                            title="Tampilkan/Sembunyikan password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small class="text-muted">Minimal 6 karakter. Kosongkan jika tidak ingin mengubah.</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password Baru</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                           placeholder="Ulangi password baru">
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)"
                            title="Tampilkan/Sembunyikan password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <hr class="section-divider">

            <!-- ===== SECTION: PENGATURAN AKUN ===== -->
            <p class="section-title"><i class="fas fa-cog me-1"></i> Pengaturan Akun</p>

            <div class="form-group">
                <label for="role">Role <span class="required">*</span></label>
                <select class="form-control" id="role" name="role" required>
                    <option value="superadmin" <?= $user['role'] == 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                    <option value="admin"       <?= $user['role'] == 'admin'       ? 'selected' : '' ?>>Admin</option>
                    <option value="kepala_dinas" <?= $user['role'] == 'kepala_dinas' ? 'selected' : '' ?>>Kepala Dinas</option>
                </select>
            </div>

            <div class="form-group">
                <label for="is_active">Status <span class="required">*</span></label>
                <select class="form-control" id="is_active" name="is_active" required>
                    <option value="1" <?= $user['is_active'] == 1 ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= $user['is_active'] == 0 ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>

            <!-- ===== TOMBOL AKSI ===== -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Simpan Perubahan
                </button>
                <a href="users.php" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<script>
    // ✅ Toggle show/hide password
    function togglePassword(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const icon  = btn.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // ✅ Validasi form sebelum submit
    document.getElementById('formEditUser').addEventListener('submit', function(e) {
        const password        = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const namaLengkap     = document.getElementById('nama_lengkap').value.trim();

        // Validasi nama lengkap
        if (namaLengkap.length < 3) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Nama lengkap minimal 3 karakter!',
                confirmButtonColor: '#667eea'
            });
            return false;
        }

        // Validasi password jika diisi
        if (password.length > 0) {
            if (password.length < 6) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Password baru minimal 6 karakter!',
                    confirmButtonColor: '#667eea'
                });
                return false;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Password Tidak Cocok',
                    text: 'Password baru dan konfirmasi password tidak sama!',
                    confirmButtonColor: '#667eea'
                });
                return false;
            }
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>