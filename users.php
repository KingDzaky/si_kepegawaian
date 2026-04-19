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
       Semua selector di-scope agar tidak konflik dengan header.php
       Class yang bentrok: .user-avatar, .user-name, .user-info, .user-details
    ============================================================ */

    .main-content {
        padding: 20px;
        margin-left: 250px;
        transition: margin-left 0.3s ease;
    }

    /* ===== PAGE HEADER ===== */
    .main-content .page-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 3px 12px rgba(102, 126, 234, 0.2);
    }

    .main-content .page-header h2 {
        color: white;
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        display: flex;
        align-items: center;
    }

    .main-content .page-header h2 i {
        margin-right: 10px;
        font-size: 22px;
    }

    .main-content .header-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    /* ===== SEARCH BOX ===== */
    .main-content .page-header .search-box {
        position: relative;
    }

    .main-content .page-header .search-box input {
        padding: 8px 35px 8px 12px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        width: 250px;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .main-content .page-header .search-box input::placeholder {
        color: rgba(255, 255, 255, 0.8);
    }

    .main-content .page-header .search-box input:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
        width: 280px;
    }

    .main-content .page-header .search-box i {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: white;
        font-size: 14px;
    }

    /* ===== TOMBOL TAMBAH ===== */
    .main-content .btn-add {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 8px 18px;
        border-radius: 20px;
        border: none;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(16, 185, 129, 0.3);
        text-decoration: none;
    }

    .main-content .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
        color: white;
    }

    /* ===== USER CARD ===== */
    .users-container .user-card {
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .users-container .user-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* ===== USER AVATAR - scope ke .users-container agar tidak bentrok header ===== */
    .users-container .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
        position: relative;
    }

    .users-container .user-avatar.superadmin {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .users-container .user-avatar.admin {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .users-container .user-avatar.kepala_dinas {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    /* ===== USER INFO - scope ke .users-container agar tidak bentrok header ===== */
    .users-container .user-info {
        flex: 1;
        display: block; /* override flex dari header.php */
    }

    /* ===== USER NAME - scope ke .users-container agar tidak bentrok header ===== */
    .users-container .user-name {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .users-container .user-username {
        color: #64748b;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .users-container .user-username i {
        font-size: 12px;
    }

    /* ===== USER DETAILS - scope ke .users-container agar tidak bentrok header ===== */
    .users-container .user-details {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        flex: 2;
    }

    .users-container .detail-item {
        display: flex;
        flex-direction: column;
    }

    .users-container .detail-label {
        font-size: 10px;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 3px;
    }

    .users-container .detail-value {
        font-size: 13px;
        color: #1e293b;
        font-weight: 600;
    }

    /* ===== BADGE ===== */
    .users-container .badge {
        padding: 5px 12px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .users-container .badge-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .users-container .badge-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .users-container .badge-role {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .users-container .badge-role.superadmin {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .users-container .badge-role.admin {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .users-container .badge-role.kepala_dinas {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    /* ===== ACTION BUTTONS ===== */
    .users-container .action-buttons {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
    }

    .users-container .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        text-decoration: none;
    }

    .users-container .btn-view {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .users-container .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        color: white;
    }

    .users-container .btn-edit {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .users-container .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        color: white;
    }

    .users-container .btn-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .users-container .btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        color: white;
    }

    /* ===== NO DATA STATE ===== */
    .main-content .no-data {
        text-align: center;
        padding: 50px 20px;
        color: #94a3b8;
    }

    .main-content .no-data i {
        font-size: 56px;
        margin-bottom: 15px;
        opacity: 0.3;
        display: block;
    }

    .main-content .no-data h3 {
        font-size: 18px;
        margin-bottom: 8px;
    }

    .main-content .no-data p {
        font-size: 14px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 15px;
        }

        .main-content .page-header {
            flex-direction: column;
            gap: 12px;
            padding: 12px 15px;
        }

        .main-content .page-header h2 {
            font-size: 18px;
        }

        .main-content .header-actions {
            width: 100%;
            flex-direction: column;
        }

        .main-content .page-header .search-box input {
            width: 100%;
        }

        .main-content .page-header .search-box input:focus {
            width: 100%;
        }

        .users-container .user-card {
            flex-direction: column;
            text-align: center;
        }

        .users-container .user-details {
            justify-content: center;
        }

        .users-container .action-buttons {
            justify-content: center;
        }
    }
</style>

<div class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <h2>
            <i class="fas fa-users"></i>
            Data User
        </h2>
        <div class="header-actions">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Pencarian cepat..." onkeyup="searchUsers()">
                <i class="fas fa-search"></i>
            </div>
            <a href="form_tambah_user.php" class="btn-add">
                <i class="fas fa-plus"></i>
                Tambah Data
            </a>
        </div>
    </div>

    <!-- Users Container -->
    <div class="users-container" id="usersContainer">
        <?php
        // ✅ Gunakan prepared statement
        $stmt = $koneksi->prepare("SELECT * FROM users ORDER BY id DESC");
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $status       = $row['is_active'] == 1 ? 'Aktif' : 'Nonaktif';
                $badge_class  = $row['is_active'] == 1 ? 'success' : 'danger';
                $role_display = ucfirst(str_replace('_', ' ', $row['role']));
                $initials     = strtoupper(substr($row['nama_lengkap'], 0, 2));
                $created_date = date('d M Y', strtotime($row['created_at']));
        ?>

        <div class="user-card"
             data-username="<?= strtolower($row['username']) ?>"
             data-name="<?= strtolower($row['nama_lengkap']) ?>"
             data-role="<?= strtolower($row['role']) ?>">

            <!-- Avatar -->
            <div class="user-avatar <?= $row['role'] ?>">
                <?= $initials ?>
            </div>

            <!-- Info Nama & Username -->
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                <div class="user-username">
                    <i class="fas fa-user"></i>
                    <?= htmlspecialchars($row['username']) ?>
                </div>
            </div>

            <!-- Detail: Role, Status, Tanggal -->
            <div class="user-details">
                <div class="detail-item">
                    <span class="detail-label">Role</span>
                    <span class="badge badge-role <?= $row['role'] ?>"><?= $role_display ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="badge badge-<?= $badge_class ?>">
                        <i class="fas fa-circle" style="font-size: 6px;"></i>
                        <?= $status ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Dibuat</span>
                    <span class="detail-value"><?= $created_date ?></span>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="action-buttons">
                <button class="btn-action btn-view" title="Lihat Detail"
                        onclick="viewUser(<?= $row['id'] ?>)">
                    <i class="fas fa-eye"></i>
                </button>
                <a href="form_edit_user.php?id=<?= $row['id'] ?>" class="btn-action btn-edit" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                <button class="btn-action btn-delete" title="Hapus"
                        onclick="deleteUser(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_lengkap'], ENT_QUOTES) ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>

        <?php
            endwhile;
            $stmt->close();
        else:
        ?>

        <div class="no-data">
            <i class="fas fa-users-slash"></i>
            <h3>Belum ada data user</h3>
            <p>Klik tombol "Tambah Data" untuk menambahkan user baru</p>
        </div>

        <?php endif; ?>
    </div>
</div>

<script>
    // ✅ Fungsi pencarian cepat
    function searchUsers() {
        const filter    = document.getElementById('searchInput').value.toLowerCase();
        const container = document.getElementById('usersContainer');
        const cards     = container.getElementsByClassName('user-card');

        Array.from(cards).forEach(card => {
            const username = card.getAttribute('data-username');
            const name     = card.getAttribute('data-name');
            const role     = card.getAttribute('data-role');

            card.style.display = (
                username.includes(filter) ||
                name.includes(filter)     ||
                role.includes(filter)
            ) ? '' : 'none';
        });
    }

    // ✅ Lihat detail user
    function viewUser(id) {
        window.location.href = 'detail_user.php?id=' + id;
    }

    // ✅ Hapus user dengan SweetAlert2
    function deleteUser(id, name) {
        Swal.fire({
            title: 'Hapus User?',
            html: `Anda yakin ingin menghapus user <strong>${name}</strong>?<br><small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus!',
            cancelButtonText: '<i class="fas fa-times me-1"></i> Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'proses_hapus_user.php?id=' + id;
            }
        });
    }
</script>

<?php require_once 'includes/footer.php'; ?>