<?php

namespace App\Traits;

trait OptimizesPdfImages
{
    /**
     * Baca file gambar dari storage lalu ubah jadi data-URI base64,
     * dengan resize otomatis kalau ukurannya besar supaya dompdf
     * tidak lama render (penyebab utama "Maximum execution time exceeded"
     * saat generate PDF Cek Harian).
     */
    protected function imageToDataUri(?string $relativePath, int $maxWidth = 1000): ?string
    {
        if (!$relativePath) {
            return null;
        }

        $full = storage_path('app/public/' . $relativePath);
        if (!file_exists($full)) {
            return null;
        }

        $type = mime_content_type($full) ?: 'image/jpeg';
        $raw  = file_get_contents($full);

        $resized = $this->resizeImageData($raw, $type, $maxWidth);
        if ($resized) {
            $raw  = $resized['data'];
            $type = $resized['type'];
        }

        return 'data:' . $type . ';base64,' . base64_encode($raw);
    }

    /**
     * Resize gambar pakai GD supaya foto dari kamera HP (bisa 3-4 MB / foto)
     * tidak dikirim mentah-mentah ke dompdf. Kalau GD tidak tersedia atau
     * gambar sudah kecil, kembalikan null (pakai data asli apa adanya).
     */
    private function resizeImageData(string $raw, string $type, int $maxWidth): ?array
    {
        if (!extension_loaded('gd')) {
            return null;
        }

        $src = @imagecreatefromstring($raw);
        if (!$src) {
            return null;
        }

        $width  = imagesx($src);
        $height = imagesy($src);

        if ($width <= $maxWidth) {
            imagedestroy($src);
            return null;
        }

        $newWidth  = $maxWidth;
        $newHeight = (int) round($height * ($maxWidth / $width));

        $dst = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, 75);
        $data = ob_get_clean();
        imagedestroy($dst);

        if ($data === false) {
            return null;
        }

        return ['data' => $data, 'type' => 'image/jpeg'];
    }
}
