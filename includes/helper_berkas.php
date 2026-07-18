<?php
/**
 * helper_berkas.php
 * Kumpulan fungsi bantu untuk modul upload berkas (Kenaikan Pangkat & Pensiun)
 */

if (!function_exists('slugBerkas')) {
    /**
     * Ubah teks jadi format aman untuk nama folder/file
     * Contoh: "Nurianita, A.Md" -> "Nurianita_AMd"
     *         "Daftar Usul Mutasi" -> "Daftar_Usul_Mutasi"
     */
    function slugBerkas(string $text): string {
        $text = trim($text);
        // Buang karakter yang gak aman untuk nama folder/file (titik, koma, slash, dll)
        $text = preg_replace('/[\/\\\\\?%\*:\|"<>\.,]/', '', $text);
        // Ganti spasi berturut-turut jadi underscore tunggal
        $text = preg_replace('/\s+/', '_', $text);
        $text = trim($text, '_');
        return $text !== '' ? $text : 'berkas';
    }
}

if (!function_exists('folderPegawaiBerkas')) {
    /**
     * Nama folder pegawai berdasarkan nama (sesuai request: uploads/sk_kp/Nurianita/)
     * CATATAN: kalau ada 2 pegawai nama depan sama persis, file akan numpuk di folder
     * yang sama. Kalau mau anti-tabrakan, tinggal aktifkan baris NIP di bawah.
     */
    function folderPegawaiBerkas(string $nama, string $nip = ''): string {
        $namaSlug = slugBerkas($nama);
        // Uncomment baris ini kalau mau folder anti-tabrakan nama kembar:
        // $ekorNip = substr(preg_replace('/\D/', '', $nip), -6);
        // return $namaSlug . '-' . $ekorNip;
        return $namaSlug;
    }
}

if (!function_exists('validasiFileBerkas')) {
    /**
     * Validasi ekstensi & ukuran file upload.
     * Return: ['ok' => bool, 'error' => string|null, 'ext' => string]
     */
    function validasiFileBerkas(array $file, array $extAllowed = ['pdf', 'jpg', 'jpeg', 'png'], int $maxSizeMB = 5): array {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Upload gagal (kode error: ' . ($file['error'] ?? '-') . ')', 'ext' => ''];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $extAllowed, true)) {
            return ['ok' => false, 'error' => 'Format file "' . $ext . '" tidak diizinkan. Gunakan: ' . implode(', ', $extAllowed), 'ext' => $ext];
        }

        $maxBytes = $maxSizeMB * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            return ['ok' => false, 'error' => 'Ukuran file melebihi ' . $maxSizeMB . 'MB', 'ext' => $ext];
        }

        return ['ok' => true, 'error' => null, 'ext' => $ext];
    }
}

if (!function_exists('simpanFileBerkas')) {
    /**
     * Pindahkan file upload ke folder tujuan dengan nama file tetap (overwrite kalau sudah ada).
     * $folderRelatif contoh: 'uploads/sk_kp/Nurianita'
     * $namaFileTanpaExt contoh: hasil slugBerkas(jenis_berkas)
     * Return path relatif lengkap (untuk disimpan ke kolom file_path) atau false kalau gagal.
     */
    function simpanFileBerkas(array $file, string $folderRelatif, string $namaFileTanpaExt, string $ext, string $rootPath): string|false {
        $folderFull = rtrim($rootPath, '/') . '/' . $folderRelatif;
        if (!is_dir($folderFull)) {
            if (!mkdir($folderFull, 0755, true) && !is_dir($folderFull)) {
                return false;
            }
        }

        // Hapus file lama dengan nama dasar sama tapi ekstensi beda (kalau jenis file diganti)
        foreach (glob($folderFull . '/' . $namaFileTanpaExt . '.*') as $oldFile) {
            @unlink($oldFile);
        }

        $namaFileFinal = $namaFileTanpaExt . '.' . $ext;
        $tujuanFull = $folderFull . '/' . $namaFileFinal;

        if (!move_uploaded_file($file['tmp_name'], $tujuanFull)) {
            return false;
        }

        return $folderRelatif . '/' . $namaFileFinal;
    }
}