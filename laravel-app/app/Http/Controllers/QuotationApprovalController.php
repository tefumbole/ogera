<?php

namespace App\Http\Controllers;

use App\GeneralSetting;
use App\Product;
use App\ProductQuotation;
use App\Quotation;
use App\Support\SignatureImage;
use App\Unit;
use App\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QuotationApprovalController extends Controller
{
    public function show($token)
    {
        $quotation = $this->findByToken($token);
        if (! $quotation) {
            return $this->expiredResponse();
        }

        if ($quotation->isOpenForClientApproval()) {
            $lines = $this->lineItems($quotation);
            $general_setting = GeneralSetting::first();

            return view('quotation.client_approval', compact('quotation', 'lines', 'general_setting'));
        }

        if ((int) $quotation->quotation_status === Quotation::STATUS_APPROVED) {
            return $this->statusResponse('approved', $quotation);
        }

        if ((int) $quotation->quotation_status === Quotation::STATUS_REJECTED) {
            return $this->statusResponse('rejected', $quotation);
        }

        return $this->expiredResponse($quotation);
    }

    public function approve(Request $request, $token)
    {
        $quotation = $this->findOpenByToken($token);
        if (! $quotation) {
            // Already answered — show a clear status instead of a generic expire.
            $closed = $this->findByToken($token);
            if ($closed && (int) $closed->quotation_status === Quotation::STATUS_APPROVED) {
                return $this->statusResponse('approved', $closed);
            }
            if ($closed && (int) $closed->quotation_status === Quotation::STATUS_REJECTED) {
                return $this->statusResponse('rejected', $closed);
            }

            return $this->expiredResponse($closed);
        }

        $data = $request->validate([
            'accept_agreement' => 'required|accepted',
            'client_comment' => 'nullable|string|max:2000',
            'signature_data' => 'required|string',
        ]);

        $sigPath = $this->storeSignature($quotation, $data['signature_data']);
        if (! $sigPath) {
            return back()->with('not_permitted', 'Please provide a valid signature (draw in the pad, then confirm).')->withInput();
        }

        $quotation->quotation_status = Quotation::STATUS_APPROVED;
        $quotation->client_signature_path = $sigPath;
        $quotation->client_signed_at = now();
        $quotation->client_comment = $data['client_comment'] ?? null;
        $quotation->client_responded_at = now();
        // Keep client_approval_token so the same link can report "already approved".
        $quotation->save();

        $this->notifyStakeholders($quotation->fresh(), 'approved');
        $this->sendSignedCopy($quotation->fresh());

        return view('quotation.client_responded', [
            'quotation' => $quotation->fresh(['customer', 'biller']),
            'general_setting' => GeneralSetting::first(),
        ]);
    }

    public function reject(Request $request, $token)
    {
        $quotation = $this->findOpenByToken($token);
        if (! $quotation) {
            $closed = $this->findByToken($token);
            if ($closed && (int) $closed->quotation_status === Quotation::STATUS_APPROVED) {
                return $this->statusResponse('approved', $closed);
            }
            if ($closed && (int) $closed->quotation_status === Quotation::STATUS_REJECTED) {
                return $this->statusResponse('rejected', $closed);
            }

            return $this->expiredResponse($closed);
        }

        $data = $request->validate([
            'client_comment' => 'required|string|max:2000',
        ]);

        $quotation->quotation_status = Quotation::STATUS_REJECTED;
        $quotation->client_comment = $data['client_comment'];
        $quotation->client_responded_at = now();
        // Keep token so revisit shows "already rejected".
        $quotation->save();

        $this->notifyStakeholders($quotation->fresh(), 'rejected');

        return view('quotation.client_responded', [
            'quotation' => $quotation->fresh(['customer', 'biller']),
            'general_setting' => GeneralSetting::first(),
        ]);
    }

    /**
     * The client only received a link before signing; now that they have
     * signed, hand over the full document and its verification QR code.
     *
     * Rendering the PDF and calling out to WhatsApp/SMTP takes seconds, so it
     * runs once the thank-you page has already been flushed to the client.
     */
    protected function sendSignedCopy(Quotation $quotation)
    {
        app()->terminating(function () use ($quotation) {
            try {
                app(QuotationController::class)->sendApprovedQuotationToClient($quotation);
            } catch (\Throwable $e) {
                Log::warning('Signed quotation delivery failed: '.$e->getMessage());
            }
        });
    }

    protected function notifyStakeholders(Quotation $quotation, $event)
    {
        try {
            app(QuotationController::class)->notifyQuotationStakeholders($quotation, $event);
        } catch (\Throwable $e) {
            Log::warning('Quotation client-response notify failed: '.$e->getMessage());
        }
    }

    protected function findByToken($token)
    {
        $token = trim((string) $token);
        if ($token === '') {
            return null;
        }

        return Quotation::with(['customer', 'biller', 'warehouse', 'supplier'])
            ->where('client_approval_token', $token)
            ->first();
    }

    /**
     * Only return a quotation that is still open for client signature.
     * Used / expired / already-responded links resolve to null.
     */
    protected function findOpenByToken($token)
    {
        $quotation = $this->findByToken($token);
        if (! $quotation || ! $quotation->isOpenForClientApproval()) {
            return null;
        }

        return $quotation;
    }

    protected function expiredResponse($quotation = null)
    {
        return $this->statusResponse('expired', $quotation);
    }

    protected function statusResponse($status, $quotation = null)
    {
        $company = \App\Support\SiteBrand::siteTitle(GeneralSetting::first());
        $ref = $quotation ? $quotation->reference_no : null;

        if ($status === 'approved') {
            $badge = 'Already approved';
            $headline = 'This quotation has already been approved';
            $body = 'Thank you — this quotation was signed and approved earlier. No further action is needed on this link. Contact '.$company.' if you need a fresh copy.';
        } elseif ($status === 'rejected') {
            $badge = 'Already rejected';
            $headline = 'This quotation was already rejected';
            $body = 'This quotation was rejected earlier. Contact '.$company.' if you need a revised quotation.';
        } else {
            $badge = 'Link expired';
            $headline = 'This quotation link is no longer valid';
            $body = 'This link has expired or is no longer available. If you still need to review or sign, please contact '.$company.' for a new link.';
        }

        return response()->view('quotation.client_link_status', [
            'general_setting' => GeneralSetting::first(),
            'status' => $status,
            'badge' => $badge,
            'headline' => $headline,
            'body' => $body,
            'reference' => $ref,
        ], $status === 'expired' ? 410 : 200);
    }

    protected function lineItems(Quotation $quotation)
    {
        $rows = ProductQuotation::where('quotation_id', $quotation->id)->get();
        $lines = [];
        foreach ($rows as $row) {
            $product = Product::find($row->product_id);
            $name = $product ? $product->name : 'Product';
            if ($row->variant_id) {
                $variant = Variant::find($row->variant_id);
                if ($variant) {
                    $name .= ' ['.$variant->name.']';
                }
            }
            $unit = '';
            if ($row->sale_unit_id) {
                $u = Unit::find($row->sale_unit_id);
                $unit = $u ? $u->unit_code : '';
            }
            $lines[] = [
                'name' => $name,
                'code' => $product ? $product->code : '',
                'qty' => $row->qty,
                'unit' => $unit,
                'net_unit_price' => $row->net_unit_price,
                'total' => $row->total,
            ];
        }

        return $lines;
    }

    protected function storeSignature(Quotation $quotation, $dataUrl)
    {
        if (! is_string($dataUrl) || ! preg_match('/^data:image\/(png|jpeg);base64,/', $dataUrl)) {
            return null;
        }

        $raw = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $binary = base64_decode($raw, true);
        if ($binary === false || strlen($binary) < 80) {
            return null;
        }

        // Drop the empty pad around the strokes and dissolve any white sheet,
        // so the stamp on the approved quotation is the signature and nothing
        // else. Falls back to the raw capture if GD cannot process it.
        $trimmed = SignatureImage::trimToTransparentPng($binary);
        if ($trimmed !== null) {
            $binary = $trimmed;
        }

        // Writable path under public/uploads (deploy ensures www-data ownership)
        $dir = public_path('uploads/quotations/signatures');
        try {
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0775, true);
            }
            if (! is_writable($dir)) {
                @chmod($dir, 0775);
            }
        } catch (\Throwable $e) {
            Log::error('Quotation signature dir failed: '.$e->getMessage());

            return null;
        }

        $filename = 'qsig_'.$quotation->id.'_'.Str::random(10).'.png';
        $full = $dir.DIRECTORY_SEPARATOR.$filename;
        try {
            if (File::put($full, $binary) === false) {
                return null;
            }
            @chmod($full, 0664);
        } catch (\Throwable $e) {
            Log::error('Quotation signature write failed: '.$e->getMessage());

            return null;
        }

        return 'uploads/quotations/signatures/'.$filename;
    }
}
