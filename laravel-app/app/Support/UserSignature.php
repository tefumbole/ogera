<?php

namespace App\Support;

use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Stores the signature, stamp and approval images that belong to a user.
 *
 * Everything is normalised to a cropped, transparent PNG before it is written,
 * whether it was drawn on a pad, pasted from the clipboard or uploaded as a
 * JPEG. A signature stamped onto an invoice must not arrive with a white box
 * around it, and callers should not each have to remember that.
 */
class UserSignature
{
    /** The user columns this class manages, in the order they appear on the form. */
    const FIELDS = ['sign', 'stemp', 'approve'];

    const DIRECTORY = 'images/user';

    public static function isField($field)
    {
        return in_array($field, self::FIELDS, true);
    }

    /**
     * Write image bytes as the user's $field, replacing whatever was there.
     *
     * @return string|null the stored filename, or null if the bytes were unusable
     */
    public static function store(User $user, $field, $binary)
    {
        if (! self::isField($field) || ! is_string($binary) || strlen($binary) < 80) {
            return null;
        }

        $png = SignatureImage::trimToTransparentPng($binary);
        if ($png === null) {
            // A blank pad, or GD could not read it. Re-encoding still beats
            // storing a JPEG the PDF would draw a white rectangle for.
            $png = self::toPng($binary);
        }
        if ($png === null) {
            return null;
        }

        $dir = public_path(self::DIRECTORY);
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return null;
        }

        $filename = 'usig_'.$field.'_'.$user->id.'_'.Str::lower(Str::random(8)).'.png';
        if (@file_put_contents($dir.DIRECTORY_SEPARATOR.$filename, $png) === false) {
            return null;
        }

        // Only now that the replacement is safely written. The attribute is set
        // but not saved, so cleanup stays correct however the caller persists.
        self::forget($user, $field);
        $user->{$field} = $filename;

        return $filename;
    }

    /** Accepts the `data:image/png;base64,...` a signature pad produces. */
    public static function storeFromDataUrl(User $user, $field, $dataUrl)
    {
        if (! is_string($dataUrl) || ! preg_match('/^data:image\/(png|jpeg|jpg|gif|webp);base64,/i', $dataUrl)) {
            return null;
        }

        $binary = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);

        return $binary === false ? null : self::store($user, $field, $binary);
    }

    public static function storeFromUpload(User $user, $field, UploadedFile $file)
    {
        if (! $file->isValid()) {
            return null;
        }

        $binary = @file_get_contents($file->getRealPath());

        return $binary === false ? null : self::store($user, $field, $binary);
    }

    /** Delete the file currently recorded in $field. The column is left alone. */
    public static function forget(User $user, $field)
    {
        if (! self::isField($field) || empty($user->{$field})) {
            return;
        }

        $path = self::path($user->{$field});
        if ($path !== null) {
            @unlink($path);
        }
    }

    /** Absolute path of a stored filename, or null when it is not on disk. */
    public static function path($filename)
    {
        if (empty($filename)) {
            return null;
        }

        $path = public_path(self::DIRECTORY.'/'.basename((string) $filename));

        return is_file($path) ? $path : null;
    }

    public static function url($filename)
    {
        return empty($filename) ? null : url('public/'.self::DIRECTORY.'/'.basename((string) $filename));
    }

    /**
     * Inline bytes for dompdf, which cannot resolve a URL at render time.
     */
    public static function dataUri($filename)
    {
        $path = self::path($filename);
        if ($path === null) {
            return null;
        }

        $bytes = @file_get_contents($path);
        if ($bytes === false) {
            return null;
        }

        $mime = self::isPng($bytes) ? 'image/png' : 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($bytes);
    }

    /**
     * The signature to stamp on a document created by $user, ready for dompdf.
     */
    public static function documentSignature($user)
    {
        if (! $user || empty($user->sign)) {
            return null;
        }

        return self::dataUri($user->sign);
    }

    protected static function isPng($bytes)
    {
        return strncmp($bytes, "\x89PNG\r\n\x1a\n", 8) === 0;
    }

    protected static function toPng($binary)
    {
        if (self::isPng($binary)) {
            return $binary;
        }
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagepng')) {
            return null;
        }

        $src = @imagecreatefromstring($binary);
        if (! $src) {
            return null;
        }

        imagealphablending($src, false);
        imagesavealpha($src, true);
        ob_start();
        imagepng($src, null, 9);
        $bytes = ob_get_clean();
        imagedestroy($src);

        return $bytes !== '' ? $bytes : null;
    }
}
