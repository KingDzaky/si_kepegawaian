<?php
/**
 * ============================================================================
 * SOFT DELETE HELPER FUNCTIONS
 * ============================================================================
 */

/**
 * Soft delete record dari tabel
 * 
 * @param mysqli $koneksi Database connection
 * @param string $table Nama tabel
 * @param int $id ID record yang akan dihapus
 * @param int $user_id ID user yang menghapus
 * @param string $reason Alasan penghapusan (opsional)
 * @return array ['success' => bool, 'message' => string]
 */
function softDelete($koneksi, $table, $id, $user_id, $reason = '') {
    // Validasi tabel yang diizinkan
    $allowed_tables = ['duk', 'kenaikan_pangkat', 'usulan_pensiun', 'penyuluh'];
    if (!in_array($table, $allowed_tables)) {
        return ['success' => false, 'message' => 'Tabel tidak valid'];
    }
    
    // Cek apakah record exists dan belum dihapus
    $check_query = "SELECT id, deleted_at FROM `$table` WHERE id = ?";
    $stmt_check = $koneksi->prepare($check_query);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'Data tidak ditemukan'];
    }
    
    $row = $result->fetch_assoc();
    if ($row['deleted_at'] !== null) {
        return ['success' => false, 'message' => 'Data sudah dihapus sebelumnya'];
    }
    
    // Soft delete
    $query = "UPDATE `$table` 
              SET deleted_at = NOW(), 
                  deleted_by = ?, 
                  delete_reason = ? 
              WHERE id = ?";
    
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("isi", $user_id, $reason, $id);
    
    if ($stmt->execute()) {
        return [
            'success' => true, 
            'message' => 'Data berhasil dihapus (soft delete)'
        ];
    } else {
        return [
            'success' => false, 
            'message' => 'Gagal menghapus data: ' . $stmt->error
        ];
    }
}

/**
 * Restore soft deleted record
 * 
 * @param mysqli $koneksi Database connection
 * @param string $table Nama tabel
 * @param int $id ID record yang akan di-restore
 * @return array ['success' => bool, 'message' => string]
 */
function restoreDeleted($koneksi, $table, $id) {
    $allowed_tables = ['duk', 'kenaikan_pangkat', 'usulan_pensiun', 'penyuluh'];
    if (!in_array($table, $allowed_tables)) {
        return ['success' => false, 'message' => 'Tabel tidak valid'];
    }
    
    $query = "UPDATE `$table` 
              SET deleted_at = NULL, 
                  deleted_by = NULL,
                  delete_reason = NULL
              WHERE id = ? AND deleted_at IS NOT NULL";
    
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Update log
            $log_query = "UPDATE deleted_records_log 
                          SET is_permanently_deleted = -1 
                          WHERE table_name = ? AND record_id = ?";
            $stmt_log = $koneksi->prepare($log_query);
            $stmt_log->bind_param("si", $table, $id);
            $stmt_log->execute();
            
            return ['success' => true, 'message' => 'Data berhasil di-restore'];
        } else {
            return ['success' => false, 'message' => 'Data tidak ditemukan atau belum dihapus'];
        }
    } else {
        return ['success' => false, 'message' => 'Gagal restore data: ' . $stmt->error];
    }
}

/**
 * Hard delete permanent (Hanya untuk superadmin)
 * 
 * @param mysqli $koneksi Database connection
 * @param string $table Nama tabel
 * @param int $id ID record yang akan dihapus permanen
 * @return array ['success' => bool, 'message' => string]
 */
function hardDelete($koneksi, $table, $id) {
    $allowed_tables = ['duk', 'kenaikan_pangkat', 'usulan_pensiun', 'penyuluh'];
    if (!in_array($table, $allowed_tables)) {
        return ['success' => false, 'message' => 'Tabel tidak valid'];
    }
    
    // Hanya hapus yang sudah di-soft delete
    $query = "DELETE FROM `$table` WHERE id = ? AND deleted_at IS NOT NULL";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Update log
            $log_query = "UPDATE deleted_records_log 
                          SET is_permanently_deleted = 1 
                          WHERE table_name = ? AND record_id = ?";
            $stmt_log = $koneksi->prepare($log_query);
            $stmt_log->bind_param("si", $table, $id);
            $stmt_log->execute();
            
            return ['success' => true, 'message' => 'Data berhasil dihapus permanen'];
        } else {
            return ['success' => false, 'message' => 'Data tidak ditemukan'];
        }
    } else {
        return ['success' => false, 'message' => 'Gagal menghapus permanen: ' . $stmt->error];
    }
}

/**
 * Get deleted records (untuk recycle bin)
 * 
 * @param mysqli $koneksi Database connection
 * @param string $table Nama tabel
 * @param int $limit Batas data yang ditampilkan
 * @return array Array of deleted records
 */
function getDeletedRecords($koneksi, $table, $limit = 100) {
    $allowed_tables = ['duk', 'kenaikan_pangkat', 'usulan_pensiun', 'penyuluh'];
    if (!in_array($table, $allowed_tables)) {
        return [];
    }
    
    $query = "SELECT d.*, 
                     u.username as deleted_by_name,
                     DATEDIFF(DATE_ADD(d.deleted_at, INTERVAL 5 YEAR), NOW()) as days_remaining
              FROM `$table` d
              LEFT JOIN users u ON d.deleted_by = u.id
              WHERE d.deleted_at IS NOT NULL
              ORDER BY d.deleted_at DESC
              LIMIT ?";
    
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    
    return $records;
}

/**
 * Get count of deleted records by table
 */
function getDeletedCount($koneksi, $table) {
    $allowed_tables = ['duk', 'kenaikan_pangkat', 'usulan_pensiun', 'penyuluh'];
    if (!in_array($table, $allowed_tables)) {
        return 0;
    }
    
    $query = "SELECT COUNT(*) as total FROM `$table` WHERE deleted_at IS NOT NULL";
    $result = $koneksi->query($query);
    $row = $result->fetch_assoc();
    
    return (int)$row['total'];
}
?>