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
    .content-wrapper {
        padding: 20px;
        margin-left: 250px;
        transition: margin-left 0.3s ease;
    }

    .page-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
        padding: 25px 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
    }

    .page-header h2 {
        color: white;
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        display: flex;
        align-items: center;
    }

    .page-header h2 i {
        margin-right: 12px;
    }

    .form-container {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group label span {
        color: #ef4444;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .form-control.is-invalid {
        border-color: #ef4444;
    }

    .invalid-feedback {
        color: #ef4444;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #e2e8f0;
    }

    .btn {
        padding: 12px 30px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary {
        background: #64748b;
        color: white;
    }

    .btn-secondary:hover {
        background: #475569;
        transform: translateY(-2px);
    }

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-danger {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    @media (max-width: 768px) {
        .content-wrapper {
            margin-left: 0;
            padding: 15px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="content-wrapper">
    <div class="page-header">
        <h2>
            <i class="fas fa-user-plus"></i>
            Tambah User Baru
        </h2>
    </div>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
    <?php endif; ?>

    <div class="form-container">
        <form action="proses_tambah_user.php" method="POST" id="formTambahUser">
            <div class="form-group">
                <label for="username">Username <span>*</span></label>
                <input type="text" class="form-control" id="username" name="username" required 
                       placeholder="Masukkan username" autocomplete="off">
                <small class="text-muted">Username harus unik dan tidak boleh sama</small>
            </div>

            <div class="form-group">
                <label for="password">Password <span>*</span></label>
                <input type="password" class="form-control" id="password" name="password" required 
                       placeholder="Masukkan password" autocomplete="new-password">
                <small class="text-muted">Minimal 6 karakter</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password <span>*</span></label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required 
                       placeholder="Ulangi password">
            </div>

            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap <span>*</span></label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required 
                       placeholder="Masukkan nama lengkap">
            </div>

            <div class="form-group">
                <label for="role">Role <span>*</span></label>
                <select class="form-control" id="role" name="role" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="superadmin">Super Admin</option>
                    <option value="admin">Admin</option>
                    <option value="kepala_dinas">Kepala Dinas</option>
                </select>
            </div>

            <div class="form-group">
                <label for="is_active">Status <span>*</span></label>
                <select class="form-control" id="is_active" name="is_active" required>
                    <option value="1" selected>Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Simpan Data
                </button>
                <a href="users.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Validasi form
document.getElementById('formTambahUser').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password.length < 6) {
        e.preventDefault();
        alert('Password minimal 6 karakter!');
        return false;
    }
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Password dan Konfirmasi Password tidak sama!');
        return false;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>