<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailLicenciasxVencer7Dias;
use App\Models\BlackList;
use App\Models\ElectronicInvoiceSender;
use App\Models\DBConfig;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
class ElectronicInvoicePropiaSenderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Artisan::call('invoices:process');
        
    }

  
}
