<?php
namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageCompressionService
{
    private int $maxWidth  = 1920;
    private int $maxHeight = 1080;
    private int $quality   = 75;

    public function compress(string $sourcePath, string $destinationPath): array
    {
        $manager = new ImageManager(new Driver());
        $image   = $manager->decodePath($sourcePath);

        $originalSize = filesize($sourcePath);

        // Resize hanya jika lebih besar dari max dimensi (maintain aspect ratio)
        if ($image->width() > $this->maxWidth || $image->height() > $this->maxHeight) {
            $image->scaleDown($this->maxWidth, $this->maxHeight);
        }

        // Strip EXIF data & simpan awal
        $currentQuality = $this->quality;
        $image->save($destinationPath, quality: $currentQuality);
        clearstatcache(true, $destinationPath);

        $targetSize = 500 * 1024; // Maksimal 500KB

        // Jika masih di atas 500KB, lakukan loop kompresi agresif
        while (filesize($destinationPath) > $targetSize && $currentQuality >= 10) {
            $currentQuality -= 10;

            // Jika kualitas sudah sangat rendah tapi file masih besar, pangkas dimensi 20%
            if ($currentQuality <= 30) {
                $image->scaleDown(
                    (int)($image->width() * 0.8),
                    (int)($image->height() * 0.8)
                );
            }

            $image->save($destinationPath, quality: $currentQuality);
            clearstatcache(true, $destinationPath);
        }

        $compressedSize = filesize($destinationPath);

        return [
            'original_size'    => $originalSize,
            'compressed_size'  => $compressedSize,
            'saved_bytes'      => $originalSize - $compressedSize,
            'compression_ratio'=> round((1 - $compressedSize / $originalSize) * 100, 1) . '%',
        ];
    }
}
