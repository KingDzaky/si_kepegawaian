<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fungsi untuk cek role
function hasRole($required_roles) {
    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }
    return in_array($_SESSION['role'], $required_roles);
}

// Fungsi untuk cek apakah superadmin
function isSuperAdmin() {
    return $_SESSION['role'] === 'superadmin';
}

// Fungsi untuk cek apakah admin atau superadmin
function isAdmin() {
    return in_array($_SESSION['role'], ['superadmin', 'admin']);
}

// Fungsi untuk cek apakah kepala dinas
function isKepalaDinas() {
    return $_SESSION['role'] === 'kepala_dinas';
}
?>