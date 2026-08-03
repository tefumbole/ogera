<?php

namespace App\Support;

use Milon\Barcode\DNS1D;

/**
 * Safe wrapper around the 1D barcode generator.
 *
 * milon/barcode throws when a code does not satisfy the chosen symbology —
 * EAN13 wants 12/13 digits, UPC-A wants 11/12, and neither accepts letters.
 * A product saved with an alphanumeric code but an EAN symbology therefore
 * blew up the whole "print barcode" request instead of just rendering
 * something sensible. We validate first, then fall back to CODE 128, which
 * encodes any ASCII string.
 */
class BarcodeImage
{
    const FALLBACK = 'C128';

    /** Symbologies with a fixed numeric length, mapped to the digit counts they accept. */
    protected static function numericOnly()
    {
        return [
            'EAN13' => [12, 13],
            'EAN8'  => [7, 8],
            'UPCA'  => [11, 12],
            'UPCE'  => [6, 7, 8],
        ];
    }

    /**
     * Base64 PNG for a barcode, or '' when nothing could be rendered.
     * Never throws.
     */
    public static function png($code, $symbology = null, $widthFactor = 2, $height = 30)
    {
        $code = trim((string) $code);
        if ($code === '') {
            return '';
        }

        $symbology = self::resolveSymbology($code, $symbology);

        try {
            return (new DNS1D())->getBarcodePNG($code, $symbology, $widthFactor, $height);
        } catch (\Throwable $e) {
            // Last resort: CODE 128 handles anything printable.
            if ($symbology !== self::FALLBACK) {
                try {
                    return (new DNS1D())->getBarcodePNG($code, self::FALLBACK, $widthFactor, $height);
                } catch (\Throwable $inner) {
                    return '';
                }
            }

            return '';
        }
    }

    /** Pick a symbology the given code can actually be encoded with. */
    public static function resolveSymbology($code, $symbology = null)
    {
        $symbology = strtoupper(trim((string) $symbology));
        if ($symbology === '') {
            return self::FALLBACK;
        }

        $numeric = self::numericOnly();
        if (isset($numeric[$symbology])) {
            $digitsOnly = ctype_digit((string) $code);
            $lengthOk = in_array(strlen((string) $code), $numeric[$symbology], true);
            if (! $digitsOnly || ! $lengthOk) {
                return self::FALLBACK;
            }
        }

        return $symbology;
    }
}
