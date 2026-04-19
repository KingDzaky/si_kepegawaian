<?php
session_start();
require_once 'check_session.php';

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

$query = "SELECT * FROM users WHERE id = $id LIMIT 1";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) === 0) {
    header('Location: users.php?error=User tidak ditemukan');
    exit;
}

$user = mysqli_fetch_assoc($result);
$role_display = ucfirst(str_replace('_', ' ', $user['role']));
$status = $user['is_active'] == 1 ? 'Aktif' : 'Nonaktif';
$initials = strtoupper(substr($user['nama_lengkap'], 0, 2));

// Panggil header & sidebar SEKALI SAJA
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<style>
    /* Styling khusus halaman ini saja — TIDAK ada margin-left di sini,
       biar header.js yang handle via class .main-content */

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


    .detail-container {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .user-profile {
        display: flex;
        align-items: center;
        padding: 30px;
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
        border-radius: 15px;
        margin-bottom: 30px;
        color: white;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.2);
        border: 4px solid rgba(255, 255, 255, 0.3);
        margin-right: 25px;
        flex-shrink: 0;
    }

    .profile-info h3 {
        margin: 0 0 5px 0;
        font-size: 28px;
        font-weight: 700;
    }

    .profile-info p {
        margin: 0;
        opacity: 0.9;
        font-size: 16px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .detail-item {
        padding: 20px;
        background: #f8fafc;
        border-radius: 10px;
        border-left: 4px solid #1A34F0;
    }

    .detail-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .detail-value {
        font-size: 16px;
        color: #1e293b;
        font-weight: 600;
    }

    .badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .badge-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .badge-role {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        padding-top: 20px;
        border-top: 2px solid #e2e8f0;
    }

    /* Rename btn class agar tidak bentrok dengan Bootstrap .btn */
    .btn-detail-edit {
        padding: 12px 30px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .btn-detail-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(245, 158, 11, 0.4);
        color: white;
    }

    .btn-detail-back {
        padding: 12px 30px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        background: #64748b;
        color: white;
    }

    .btn-detail-back:hover {
        background: #475569;
        transform: translateY(-2px);
        color: white;
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .user-profile {
            flex-direction: column;
            text-align: center;
        }

        .profile-avatar {
            margin-right: 0;
            margin-bottom: 20px;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<!-- PENTING: pakai class "main-content" bukan "content-wrapper"
     agar header.js bisa otomatis atur margin saat sidebar toggle -->
<div class="main-content">
    <div class="page-header">
        <h2>
            <i class="fas fa-user"></i>
            Detail User
        </h2>
    </div>

    <div class="detail-container">
        <div class="user-profile">
            <div class="profile-avatar">
                <?= $initials ?>
            </div>
            <div class="profile-info">
                <h3><?= htmlspecialchars($user['nama_lengkap']) ?></h3>
                <p><i class="fas fa-at"></i> <?= htmlspecialchars($user['username']) ?></p>
            </div>
        </div>

        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Username</div>
                <div class="detail-value"><?= htmlspecialchars($user['username']) ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Nama Lengkap</div>
                <div class="detail-value"><?= htmlspecialchars($user['nama_lengkap']) ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Role</div>
                <div class="detail-value">
                    <span class="badge badge-role"><?= $role_display ?></span>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <span class="badge badge-<?= $user['is_active'] == 1 ? 'success' : 'danger' ?>">
                        <?= $status ?>
                    </span>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Dibuat Pada</div>
                <div class="detail-value">
                    <?= date('d F Y, H:i', strtotime($user['created_at'])) ?> WIB
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="form_edit_user.php?id=<?= $user['id'] ?>" class="btn-detail-edit">
                <i class="fas fa-edit"></i>
                Edit User
            </a>
            <a href="users.php" class="btn-detail-back">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>