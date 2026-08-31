<?php

namespace App\Console\Commands;

use App\Quotation;
use App\Http\Controllers\QuotationController;
use Illuminate\Console\Command;

class ResendApprovedQuotationPdfs extends Command
{
    protected $signature = 'quotations:resend-approved-pdfs
                            {--date= : Y-m-d in app timezone (default: today)}
                            {--id= : Resend a single quotation id}';

    protected $description = 'Rebuild and WhatsApp signed quotation PDFs to client + CC for approved quotes';

    public function handle()
    {
        @set_time_limit(600);

        $query = Quotation::query()->where('quotation_status', Quotation::STATUS_APPROVED);

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        } else {
            $date = $this->option('date') ?: now()->toDateString();
            $query->whereDate('client_responded_at', $date);
        }

        $quotes = $query->orderBy('client_responded_at')->get();
        if ($quotes->isEmpty()) {
            $this->warn('No approved quotations matched.');

            return 0;
        }

        $controller = app(QuotationController::class);
        $ok = 0;
        foreach ($quotes as $quotation) {
            $this->info('Sending PDF for '.$quotation->reference_no.' (id '.$quotation->id.')…');
            try {
                $controller->sendApprovedQuotationToClient($quotation);
                $ok++;
                $this->info('  done');
            } catch (\Throwable $e) {
                $this->error('  failed: '.$e->getMessage());
                \Log::error('quotations:resend-approved-pdfs failed', [
                    'id' => $quotation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Finished: {$ok}/{$quotes->count()} quotation(s).");

        return 0;
    }
}
