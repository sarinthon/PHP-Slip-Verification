<?php

namespace ShuGlobal\SlipVerification\QRCodeReader;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Zxing\QrReader;

class QRCodeReader
{
    /**
     * @param UploadedFile $file
     * @return string
     */
    public static function qrcode2text(UploadedFile $file): string {
        $qrcode = new QrReader($file->path());
        $result = $qrcode->text();

        if (is_string($result)) {
            return $result;
        }

        $outputPath = Storage::path("slip-cropped.jpg");
        if (self::cropImage($file, $outputPath) === false) {
            return "";
        }

        $qrcode = new QrReader($outputPath);
        $result = $qrcode->text();
        unlink($outputPath);

        return is_string($result) ? $result : "";
    }

    /**
     * @param UploadedFile $file
     * @param string $outputPath
     * @return void|bool
     */
    private static function cropImage(UploadedFile $file, string $outputPath) {
        $im = null;

        switch ($file->getClientOriginalExtension()) {
            case "png":
                $im = imagecreatefrompng($file->path());
                break;
            case "jpg":
            case "jpeg":
                $im = imagecreatefromjpeg($file->path());
                break;
            default:
                break;
        }

        if ($im == null) {
            return false;
        }

        if (imagesy($im) < 1400) {
            return false;
        }

        $im2 = imagecrop($im,
            ['x' => 0, 'y' => (imagesy($im) / 2), 'width' => imagesx($im), 'height' => (imagesy($im) / 2)]
        );
        if ($im2 !== FALSE) {
            imagepng($im2, $outputPath);
            imagedestroy($im2);
        }
        imagedestroy($im);
    }
}