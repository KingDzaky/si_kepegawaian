<?php
// Template sudah dapat data dari parent ($data, $nomor_pengantar, dll)
function tanggal_indonesia($tanggal) {
    if (empty($tanggal)) return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Generate nomor surat
$tahun = date('Y');
$bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
$bulan_sekarang = $bulan_romawi[(int)date('n')];
$nomor_pengantar = "800.1.6.6/" . str_pad($id, 3, '0', STR_PAD_LEFT) . "/SET-DPPKBPM/$bulan_sekarang/$tahun";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Surat Pengantar PKB - <?= htmlspecialchars($data['nama']) ?></title>
    <link rel="stylesheet" href="css/print_surat.css">
</head>
<body>
    <div class="kop-surat">
        <h2>PEMERINTAH KOTA BANJARMASIN</h2>
        <h2>DINAS PENGENDALIAN PENDUDUK, KELUARGA</h2>
        <h2>BERENCANA DAN PEMBERDAYAAN MASYARAKAT</h2>
        <p>JL. Brigjen H. Hasan Basri – Kayutangi II RT.16 Banjarmasin 70124</p>
        <p>Pos-el : dppkbpm@gmail.banjarmasin.go.id, Laman http://dppkbpm.banjarmasinkota.go.id</p>
    </div>
    
    <p style="text-align: right; margin-top: 30px;">Banjarmasin, <?= tanggal_indonesia(date('Y-m-d')) ?></p>
    
    <p style="margin-top: 30px;">
        Yth. Kepala Perwakilan BKKBN Provinsi Kalimantan Selatan<br>
        Kota Banjarmasin<br>
        Di-<br>
        &nbsp;&nbsp;&nbsp;&nbsp;Banjarmasin
    </p>
    
    <p class="nomor-surat">
        SURAT PENGANTAR<br>
        NOMOR : <?= $nomor_pengantar ?>
    </p>
    
    <table class="tabel-surat">
        <tr>
            <td style="width: 5%; text-align: center;">No.</td>
            <td style="width: 45%;">Naskah Dinas yang Dikirimkan</td>
            <td style="width: 20%; text-align: center;">Banyaknya</td>
            <td style="width: 30%;">Keterangan</td>
        </tr>
        <tr>
            <td style="text-align: center;">1.</td>
            <td>
                Permohonan Pensiun<br>
                a.n<br>
                <strong><?= htmlspecialchars($data['nama']) ?></strong><br>
                Nip. <?= htmlspecialchars($data['nip']) ?>
            </td>
            <td style="text-align: center;">1 (satu) Berkas</td>
            <td>
                Disampaikan Sebagai Bahan Selanjutnya untuk diproses sesuai ketentuan yang berlaku
            </td>
        </tr>
    </table>
    
    <p style="margin-top: 20px;">Demikian disampaikan, atas kerjasama yang baik diucapkan terimakasih</p>
    
    <div class="ttd-section">
        <p>Pengirim,<br>Kepala Dinas,</p>
        <p class="ttd-nama">
            <?= htmlspecialchars($data['gelar_depan'] ?? 'Drs.') ?> 
            <?= htmlspecialchars($data['nama'] ?? 'M. HELFIANNOOR') ?>, 
            <?= htmlspecialchars($data['gelar_belakang'] ?? 'M.Si') ?><br>
            <?= htmlspecialchars($data['pangkat'] ?? 'Pembina Utama Muda') ?> 
            (<?= htmlspecialchars($data['golongan'] ?? 'IV/c') ?>)<br>
            NIP. <?= htmlspecialchars($data['nip'] ?? '197307191993021002') ?>
        </p>
    </div>
    
    <div class="paraf-box">
        <p><strong>Diterima tanggal :</strong></p>
        <p><strong>Paraf</strong></p>
        <p>Plh. Sekretaris</p>
        <p>Kasubag Umum dan<br>Kepegawaian</p>
    </div>
    
    <div class="no-print">
        <button onclick="window.print()">Cetak</button>
        <button onclick="window.close()">Tutup</button>
    </div>
</body>
</html>