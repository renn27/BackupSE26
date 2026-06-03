<?php
namespace App\Services;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

class ImageCompressionService
{
    private int $maxWidth  = 1600;
    private int $maxHeight = 1200;
    private int $targetSize = 500 * 1024;

    public function compress(string $sourcePath, string $destinationPath): array
    {
        $manager = new ImageManager(new Driver());
        $image   = $manager->decodePath($sourcePath);

        $originalSize = filesize($sourcePath);

        // Resize hanya jika lebih besar dari max dimensi (maintain aspect ratio)
        if ($image->width() > $this->maxWidth || $image->height() > $this->maxHeight) {
            $image->scaleDown($this->maxWidth, $this->maxHeight);
        }

        $qualities = [76, 68, 60, 52, 44, 36, 30, 24, 18, 12];
        $resizeAttempts = 0;

        do {
            foreach ($qualities as $quality) {
                $this->saveAsJpeg($image, $destinationPath, $quality);

                if (filesize($destinationPath) <= $this->targetSize) {
                    break 2;
                }
            }

            $nextWidth = max(480, (int) round($image->width() * 0.82));
            $nextHeight = max(360, (int) round($image->height() * 0.82));

            if ($nextWidth >= $image->width() && $nextHeight >= $image->height()) {
                break;
            }

            $image->scaleDown($nextWidth, $nextHeight);
            $resizeAttempts++;
        } while (filesize($destinationPath) > $this->targetSize && $resizeAttempts < 8);

        if (filesize($destinationPath) > $this->targetSize) {
            $this->saveAsJpeg($image, $destinationPath, 8);
        }

        $compressedSize = filesize($destinationPath);

        return [
            'original_size'    => $originalSize,
            'compressed_size'  => $compressedSize,
            'saved_bytes'      => $originalSize - $compressedSize,
            'compression_ratio'=> round((1 - $compressedSize / $originalSize) * 100, 1) . '%',
        ];
    }

    private function saveAsJpeg(ImageInterface $image, string $destinationPath, int $quality): void
    {
        $image->encode(new JpegEncoder(quality: $quality, strip: true))->save($destinationPath);
        clearstatcache(true, $destinationPath);
    }
}
