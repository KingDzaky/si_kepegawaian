<?php
/**
 * File ini menangani alert berdasarkan URL parameter atau Session
 * Include di bagian bawah halaman (sebelum </body>)
 */

// Cek dari URL parameter
if (isset($_GET['alert'])) {
    $alert_type = $_GET['alert'];
    $custom_message = isset($_GET['message']) ? $_GET['message'] : '';
    
    echo "<script>";
    
    switch ($alert_type) {
        case 'sukses_tambah':
            $message = $custom_message ?: 'Data berhasil ditambahkan';
            echo "
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '$message',
                showConfirmButton: true,
                confirmButtonColor: '#28a745',
                timer: 3000,
                timerProgressBar: true
            });
            ";
            break;

        case 'sukses_approval':
            $message = $custom_message ?: 'Data berhasil ditambahkan';
            echo "
            Swal.fire({
                icon: 'success',
                title: 'Usulan Disetujui!',
                text: '$message',
                showConfirmButton: true,
                confirmButtonColor: '#28a745',
                timer: 3000,
                timerProgressBar: true
            });
            ";
            break;
            
        case 'sukses_ubah':
            $message = $custom_message ?: 'Data berhasil diubah';
            echo "
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '$message',
                showConfirmButton: true,
                confirmButtonColor: '#007bff',
                timer: 3000,
                timerProgressBar: true
            });
            ";
            break;
            
        case 'sukses_hapus':
            $message = $custom_message ?: 'Data berhasil dihapus';
            echo "
            Swal.fire({
                icon: 'success',
                title: 'Terhapus!',
                text: '$message',
                showConfirmButton: true,
                confirmButtonColor: '#dc3545',
                timer: 3000,
                timerProgressBar: true
            });
            ";
            break;
            
        case 'gagal':
            $message = $custom_message ?: 'Terjadi kesalahan saat memproses data';
            echo "
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '$message',
                showConfirmButton: true,
                confirmButtonColor: '#dc3545'
            });
            ";
            break;

        case 'gagal_approval':
            $message = $custom_message ?: 'Terjadi kesalahan saat memproses data';
            echo "
            Swal.fire({
                icon: 'error',
                title: 'Ditolak!',
                text: '$message',
                showConfirmButton: true,
                confirmButtonColor: '#dc3545'
            });
            ";
            break;
            
        case 'warning':
            $message = $custom_message ?: 'Perhatian! Ada yang perlu diperhatikan';
            echo "
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: '$message',
                showConfirmButton: true,
                confirmButtonColor: '#ffc107'
            });
            ";
            break;
    }
    
    echo "</script>";
}

// Cek dari Session (alternatif)
if (isset($_SESSION['alert'])) {
    $alert_data = $_SESSION['alert'];
    $alert_type = $alert_data['type'];
    $message = $alert_data['message'];
    
    echo "<script>";
    
    switch ($alert_type) {
        case 'success':
            echo "
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '$message',
                showConfirmButton: true,
                timer: 3000,
                timerProgressBar: true
            });
            ";
            break;
            
        case 'error':
            echo "
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '$message',
                showConfirmButton: true
            });
            ";
            break;
            
        case 'warning':
            echo "
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: '$message',
                showConfirmButton: true
            });
            ";
            break;
    }
    
    echo "</script>";
    
    // Hapus session setelah ditampilkan
    unset($_SESSION['alert']);
}
?>