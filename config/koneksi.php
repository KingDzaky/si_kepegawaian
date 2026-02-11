<?php
// config/koneksi.php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // ubah jika kamu pakai password
$DB_NAME = 'si_kepegawaian';

$koneksi = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}
// optional base url (sesuaikan)
$base_url = '/si_kepegawaian'; // gunakan untuk link relatif jika perlu
