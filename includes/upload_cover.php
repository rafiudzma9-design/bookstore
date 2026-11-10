<?php
// Memeriksa dan menyimpan gambar cover.
// Hasil: [nama_file_baru, pesan_error]
//  - tidak ada file diunggah  => [null, '']
//  - berhasil                 => ['a1b2c3.jpg', '']
//  - gagal                    => [null, 'pesan error']
function uploadCover(array $file): array
{
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, ''];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload gagal. Coba lagi dengan file yang lebih kecil.'];
    }

    // Batas ukuran: 2 MB
    if ($file['size'] > 2 * 1024 * 1024) {
        return [null, 'Ukuran gambar maksimal 2 MB.'];
    }

    // Periksa isi file yang sebenarnya, bukan hanya nama atau ekstensinya
    $info  = @getimagesize($file['tmp_name']);
    $tipe  = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];

    if (!$info || !isset($tipe[$info[2]])) {
        return [null, 'File harus berupa gambar JPG, PNG, atau WEBP.'];
    }

    // Nama baru acak, ekstensi ditentukan dari jenis gambar yang asli
    $nama   = bin2hex(random_bytes(8)) . '.' . $tipe[$info[2]];
    $folder = __DIR__ . '/../uploads/covers/';

    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $folder . $nama)) {
        return [null, 'Gagal menyimpan gambar ke folder uploads/covers.'];
    }

    return [$nama, ''];
}