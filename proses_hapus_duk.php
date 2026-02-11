<?php
include "config/koneksi.php";
require_once 'includes/sweetalert.php';
require_once 'includes/alert_handler.php';
require_once 'includes/alert_functions.php';
require_once 'includes/confirm_delete.php';

$id = $_GET['id'];
$sql = "DELETE FROM duk WHERE id=$id";

if (mysqli_query($koneksi, $sql)) {
    alertSuksesHapus('dataduk.php', 'Data Berhasil Dihapus');
    exit;
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
