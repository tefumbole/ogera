<?php

namespace App\Console\Commands;

use App\BookingProduct;
use App\Http\Controllers\BookingController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReminderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email customers once when a booked product is due back today';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // This runs every minute. Without the is_notified guard every customer
        // with a product due back today was emailed on each tick — up to 1440
        // copies of the same notice per day. BookingController::bookedproducts
        // already uses this guard; the scheduled path now matches it.
        $bookings = DB::select(
            "SELECT booking_id, product_id FROM booking_products
             WHERE is_return = false AND is_notified = false
               AND DATE_FORMAT(`end`, '%Y-%m-%d') = ?",
            [date('Y-m-d')]
        );

        $bookingController = new BookingController();
        $sent = 0;
        foreach ($bookings as $booking) {
            try {
                $bookingController->sendMailFromCommand($booking->booking_id, $booking->product_id);
                $sent++;
            } catch (\Throwable $e) {
                Log::warning('Booking end-date email failed for booking #' . $booking->booking_id
                    . ' product #' . $booking->product_id . ': ' . $e->getMessage());
            }

            // Mark regardless of the mail result: a retry every minute is far
            // worse than one missed notice, and failures are logged above.
            BookingProduct::where('booking_id', $booking->booking_id)
                ->where('product_id', $booking->product_id)
                ->update(['is_notified' => true]);
        }

        $this->info("Booking end-date reminders sent: {$sent}");

        return 0;
    }
}
