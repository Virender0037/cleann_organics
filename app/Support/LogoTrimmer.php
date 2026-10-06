<?php

namespace App\Support;

/**
 * Trims the empty margins around an uploaded logo so the artwork fills the header/footer box.
 *
 * Why: logos are often exported on a large canvas (the live one was 1600×1067 with the artwork only 25% of the
 * height), and every logo slot is height-capped — so the visible artwork rendered ~18px tall. CSS cannot crop
 * transparent padding; trimming the file can.
 *
 * Safe by design: PNG/WebP only (the formats that carry transparency); trims fully transparent (or pure-white, for
 * opaque exports) margins; keeps a small breathing margin; leaves the file untouched when GD is unavailable, the
 * format is unsupported, the image is huge, or there is nothing meaningful to trim. Never upscales or distorts.
 */
class LogoTrimmer
{
    /** Keep this fraction of the artwork size as margin on every side. */
    private const MARGIN_RATIO = 0.03;

    /** Only rewrite the file when at least this share of the canvas would be removed. */
    private const MIN_SAVING = 0.10;

    /** Refuse to scan images larger than this many pixels (memory/time guard). */
    private const MAX_PIXELS = 16_000_000;

    /** @return bool true when the file was rewritten */
    public static function trim(string $path): bool
    {
        if (! extension_loaded('gd') || ! is_file($path)) {
            return false;
        }

        $info = @getimagesize($path);
        if (! $info || $info[0] * $info[1] > self::MAX_PIXELS) {
            return false;
        }

        [$width, $height, $type] = $info;
        $image = match ($type) {
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (! $image) {
            return false;
        }

        $box = self::contentBox($image, $width, $height);

        if ($box === null) {
            imagedestroy($image);

            return false;
        }

        [$minX, $minY, $maxX, $maxY] = $box;
        $artW = $maxX - $minX + 1;
        $artH = $maxY - $minY + 1;
        $margin = (int) ceil(max($artW, $artH) * self::MARGIN_RATIO);

        $x = max(0, $minX - $margin);
        $y = max(0, $minY - $margin);
        $newW = min($width, $maxX + $margin + 1) - $x;
        $newH = min($height, $maxY + $margin + 1) - $y;

        if ($newW * $newH > (1 - self::MIN_SAVING) * $width * $height) {
            imagedestroy($image);

            return false; // already tight enough
        }

        $cropped = imagecreatetruecolor($newW, $newH);
        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);
        imagefill($cropped, 0, 0, imagecolorallocatealpha($cropped, 0, 0, 0, 127));
        imagecopy($cropped, $image, 0, 0, $x, $y, $newW, $newH);

        $saved = $type === IMAGETYPE_PNG ? imagepng($cropped, $path, 9) : imagewebp($cropped, $path, 90);

        imagedestroy($image);
        imagedestroy($cropped);

        return (bool) $saved;
    }

    /**
     * Bounding box of the visible artwork: pixels that are neither (almost) fully transparent nor pure white.
     *
     * @return array{int, int, int, int}|null [minX, minY, maxX, maxY], or null when the image is blank
     */
    private static function contentBox(\GdImage $image, int $width, int $height): ?array
    {
        $minX = $width;
        $minY = $height;
        $maxX = -1;
        $maxY = -1;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;           // 127 = fully transparent
                if ($alpha >= 120) {
                    continue;
                }
                if ((($rgba >> 16) & 0xFF) > 250 && (($rgba >> 8) & 0xFF) > 250 && ($rgba & 0xFF) > 250) {
                    continue;                              // pure-white background of an opaque export
                }
                if ($x < $minX) {
                    $minX = $x;
                }
                if ($x > $maxX) {
                    $maxX = $x;
                }
                if ($y < $minY) {
                    $minY = $y;
                }
                if ($y > $maxY) {
                    $maxY = $y;
                }
            }
        }

        return $maxX < 0 ? null : [$minX, $minY, $maxX, $maxY];
    }
}
