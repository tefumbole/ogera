<?php

namespace App\Support;

/**
 * Cleans up a signature captured from an HTML canvas so it can be stamped onto
 * a document.
 *
 * A raw capture is the whole pad — usually several hundred pixels of empty
 * space around a few strokes, and on some browsers the strokes are flattened
 * onto an opaque white sheet. Both make the signature sit in a visible white
 * box once it is placed on a PDF, so the background is dissolved and the image
 * cropped down to the ink itself.
 */
class SignatureImage
{
    /** Below this ink strength a pixel counts as background. */
    const INK_THRESHOLD = 0.10;

    /** Pixels above this alpha (0 = opaque, 127 = clear) are ignored by the crop. */
    const CROP_ALPHA_CUTOFF = 100;

    /**
     * @param  string  $binary  raw image bytes (PNG or JPEG)
     * @param  int  $padding  breathing room kept around the strokes, in pixels
     * @param  int  $maxWidth  cropped result is scaled down to at most this wide
     * @return string|null processed PNG bytes, or null when the image cannot be
     *                     improved (no GD, unreadable bytes, or a blank pad)
     */
    public static function trimToTransparentPng($binary, $padding = 12, $maxWidth = 900)
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagepng')) {
            return null;
        }

        $src = @imagecreatefromstring($binary);
        if (! $src) {
            return null;
        }

        try {
            $src = self::capResolution($src);
            $width = imagesx($src);
            $height = imagesy($src);

            $work = imagecreatetruecolor($width, $height);
            imagealphablending($work, false);
            imagesavealpha($work, true);
            imagefilledrectangle($work, 0, 0, $width - 1, $height - 1, imagecolorallocatealpha($work, 255, 255, 255, 127));

            $minX = $width;
            $minY = $height;
            $maxX = -1;
            $maxY = -1;

            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $pixel = imagecolorat($src, $x, $y);
                    $alpha = ($pixel >> 24) & 0x7F;
                    if ($alpha === 127) {
                        continue;
                    }

                    $r = ($pixel >> 16) & 0xFF;
                    $g = ($pixel >> 8) & 0xFF;
                    $b = $pixel & 0xFF;

                    // Perceived darkness of the pixel, weighted by whatever
                    // opacity it already carried.
                    $luma = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
                    $ink = (1 - $luma) * ((127 - $alpha) / 127);
                    if ($ink < self::INK_THRESHOLD) {
                        continue;
                    }

                    $outAlpha = (int) max(0, min(127, round(127 - ($ink * 127))));
                    imagesetpixel($work, $x, $y, imagecolorallocatealpha($work, $r, $g, $b, $outAlpha));

                    if ($outAlpha < self::CROP_ALPHA_CUTOFF) {
                        $minX = min($minX, $x);
                        $maxX = max($maxX, $x);
                        $minY = min($minY, $y);
                        $maxY = max($maxY, $y);
                    }
                }
            }

            if ($maxX < 0 || $maxY < 0) {
                imagedestroy($work);

                return null;
            }

            $minX = max(0, $minX - $padding);
            $minY = max(0, $minY - $padding);
            $maxX = min($width - 1, $maxX + $padding);
            $maxY = min($height - 1, $maxY + $padding);

            $cropW = $maxX - $minX + 1;
            $cropH = $maxY - $minY + 1;

            $targetW = $cropW > $maxWidth ? $maxWidth : $cropW;
            $targetH = (int) max(1, round($cropH * ($targetW / $cropW)));

            $out = imagecreatetruecolor($targetW, $targetH);
            imagealphablending($out, false);
            imagesavealpha($out, true);
            imagefilledrectangle($out, 0, 0, $targetW - 1, $targetH - 1, imagecolorallocatealpha($out, 255, 255, 255, 127));
            imagecopyresampled($out, $work, 0, 0, $minX, $minY, $targetW, $targetH, $cropW, $cropH);

            ob_start();
            imagepng($out, null, 9);
            $bytes = ob_get_clean();

            imagedestroy($work);
            imagedestroy($out);

            return $bytes !== '' ? $bytes : null;
        } catch (\Throwable $e) {
            return null;
        } finally {
            if (is_resource($src) || $src instanceof \GdImage) {
                imagedestroy($src);
            }
        }
    }

    /**
     * Retina pads hand back images at 2–3x, which makes the per-pixel scan
     * needlessly slow without adding any legibility to a stamp.
     */
    protected static function capResolution($image, $maxWidth = 1400)
    {
        $width = imagesx($image);
        if ($width <= $maxWidth) {
            return $image;
        }

        $height = imagesy($image);
        $targetH = (int) max(1, round($height * ($maxWidth / $width)));

        $scaled = imagecreatetruecolor($maxWidth, $targetH);
        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);
        imagefilledrectangle($scaled, 0, 0, $maxWidth - 1, $targetH - 1, imagecolorallocatealpha($scaled, 255, 255, 255, 127));
        imagecopyresampled($scaled, $image, 0, 0, 0, 0, $maxWidth, $targetH, $width, $height);
        imagedestroy($image);

        return $scaled;
    }
}
