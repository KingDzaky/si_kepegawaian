<?php
/**
 * File ini berisi fungsi-fungsi helper untuk set alert
 * Include di awal file PHP yang memproses data
 */

/**
 * Set alert menggunakan URL redirect
 */
function setAlertURL($type, $redirect_url, $custom_message = '') {
    $url = $redirect_url;
    $url .= (strpos($url, '?') !== false) ? '&' : '?';
    $url .= "alert=" . $type;
    
    if ($custom_message) {
        $url .= "&message=" . urlencode($custom_message);
    }
    
    header("Location: " . $url);
    exit();
}

/**
 * Set alert menggunakan Session
 */
function setAlertSession($type, $message, $redirect_url) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
    
    header("Location: " . $redirect_url);
    exit();
}

/**ma
 * Shortcut functions
 */
function alertSuksesTambah($redirect_url, $message = '') {
    setAlertURL('sukses_tambah', $redirect_url, $message);
}

function alertSuksesApproval($redirect_url, $message = '') {
    setAlertURL('sukses_approval', $redirect_url, $message);
}

function alertSuksesUbah($redirect_url, $message = '') {
    setAlertURL('sukses_ubah', $redirect_url, $message);
}

function alertSuksesHapus($redirect_url, $message = '') {
    setAlertURL('sukses_hapus', $redirect_url, $message);
}

function alertGagal($redirect_url, $message = '') {
    setAlertURL('gagal', $redirect_url, $message);
}

function alertGagalApproval($redirect_url, $message = '') {
    setAlertURL('gagal_approval', $redirect_url, $message);
}

function alertWarning($redirect_url, $message = '') {
    setAlertURL('warning', $redirect_url, $message);
}
?>