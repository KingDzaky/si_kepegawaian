<?php
session_start();
require_once 'check_session.php';

// Hanya superadmin yang bisa akses
if (!isSuperAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

require_once 'config/koneksi.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<style>
    /* ============================================================
       Semua selector di-scope ke .main-content
       agar tidak konflik dengan CSS header.php
       Class berbahaya: .btn, .btn-primary, .btn-secondary,
                        .alert, .alert-danger, .form-control
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

    /* ✅ Scope .form-control agar tidak timpa Bootstrap/header */
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

    .main-content .form-control.is-invalid {
        border-color: #ef4444;
    }

    .main-content .invalid-feedback {
        color: #ef4444;
        font-size: 12px;
        margin-top: 5px;
        display: block;
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

    /* ===== SECTION DIVIDER ===== */
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

    .main-content .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #e2e8f0;
    }

    /* ✅ Ganti nama class .btn-primary → .btn-submit agar tidak timpa Bootstrap */
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

    /* ✅ Ganti nama class .btn-secondary → .btn-cancel */
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

    /* ✅ Ganti nama class .alert-danger → .alert-error */
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
            <i class="fas fa-user-plus"></i>
            Tambah User Baru
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
        <form action="proses_tambah_user.php" method="POST" id="formTambahUser">

            <!-- ===== SECTION: INFO AKUN ===== -->
            <p class="section-title"><i class="fas fa-id-card me-1"></i> Informasi Akun</p>

            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input type="text" class="form-control" id="username" name="username"
                       required placeholder="Masukkan username" autocomplete="off">
                <small class="text-muted">Username harus unik dan tidak boleh sama</small>
            </div>

            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap <span class="required">*</span></label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                       required placeholder="Masukkan nama lengkap">
            </div>

            <hr class="section-divider">

            <!-- ===== SECTION: PASSWORD ===== -->
            <p class="section-title"><i class="fas fa-key me-1"></i> Password</p>

            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="password" name="password"
                           required placeholder="Masukkan password" autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="togglePassword('password', this)"
                            title="Tampilkan/Sembunyikan password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small class="text-muted">Minimal 6 karakter</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password <span class="required">*</span></label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                           required placeholder="Ulangi password">
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)"
                            title="Tampilkan/Sembunyikan password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <hr class="section-divider">

            <!-- ===== SECTION: PENGATURAN ===== -->
            <p class="section-title"><i class="fas fa-cog me-1"></i> Pengaturan Akun</p>

            <div class="form-group">
                <label for="role">Role <span class="required">*</span></label>
                <select class="form-control" id="role" name="role" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="superadmin">Super Admin</option>
                    <option value="admin">Admin</option>
                    <option value="kepala_dinas">Kepala Dinas</option>
                </select>
            </div>

            <div class="form-group">
                <label for="is_active">Status <span class="required">*</span></label>
                <select class="form-control" id="is_active" name="is_active" required>
                    <option value="1" selected>Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Simpan Data
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

    // ✅ Validasi form dengan SweetAlert2
    document.getElementById('formTambahUser').addEventListener('submit', function(e) {
        const username        = document.getElementById('username').value.trim();
        const password        = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const namaLengkap     = document.getElementById('nama_lengkap').value.trim();
        const role            = document.getElementById('role').value;

        if (username.length < 3) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Username minimal 3 karakter!', confirmButtonColor: '#667eea' });
            return false;
        }

        if (namaLengkap.length < 3) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Nama lengkap minimal 3 karakter!', confirmButtonColor: '#667eea' });
            return false;
        }

        if (password.length < 6) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Password minimal 6 karakter!', confirmButtonColor: '#667eea' });
            return false;
        }

        if (password !== confirmPassword) {
            e.preventDefault();
            Swal.fire({ icon: 'error', title: 'Password Tidak Cocok', text: 'Password dan konfirmasi password tidak sama!', confirmButtonColor: '#667eea' });
            return false;
        }

        if (role === '') {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Role wajib dipilih!', confirmButtonColor: '#667eea' });
            return false;
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>