<?php
/**
 * JavaScript function untuk konfirmasi hapus
 * Include sekali saja di halaman yang butuh konfirmasi hapus
 */
?>
<script>
/**
 * Fungsi untuk konfirmasi hapus data
 * @param {string} url - URL untuk proses hapus
 * @param {string} nama - Nama data yang akan dihapus (opsional)
 */
function konfirmasiHapus(url, nama = '') {
    let text = "Data yang dihapus tidak dapat dikembalikan!";
    if (nama) {
        text = "Data '" + nama + "' akan dihapus dan tidak dapat dikembalikan!";
    }
    
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

/**
 * Fungsi untuk konfirmasi dengan custom message
 */
function konfirmasiCustom(url, title, text) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>