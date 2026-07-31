<?php

use App\Support\SignatureImage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Signatures captured before the canvas trim was introduced are stored as the
 * full pad — a wide, mostly empty image that shrinks to nothing when placed on
 * a document. Re-process them in place so previously approved quotations show
 * the same tight, transparent stamp as new ones.
 */
class TrimExistingQuotationSignatures extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('quotations') || ! Schema::hasColumn('quotations', 'client_signature_path')) {
            return;
        }

        $rows = DB::table('quotations')
            ->select('id', 'client_signature_path')
            ->whereNotNull('client_signature_path')
            ->where('client_signature_path', '!=', '')
            ->get();

        foreach ($rows as $row) {
            $path = public_path(ltrim(str_replace('\\', '/', $row->client_signature_path), '/'));
            if (! is_file($path) || ! is_readable($path) || ! is_writable($path)) {
                continue;
            }

            $bytes = @file_get_contents($path);
            if ($bytes === false) {
                continue;
            }

            $trimmed = SignatureImage::trimToTransparentPng($bytes);
            if ($trimmed !== null) {
                @file_put_contents($path, $trimmed);
            }
        }
    }

    public function down()
    {
        // Cropping is not reversible; the originals were only ever padding.
    }
}
