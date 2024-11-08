<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\UserController;
class ProcessPendingInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending invoices';

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
     * @return int
     */
    public function handle()
    {
        $controller = new UserController(); // Reemplaza con el nombre correcto del controlador
        //   $this->info('Pending invoices will been processed.');
        \Log::info('Se procesan facturas propias.');
           $controller->FacturasPropiasPendientesXEnviar();
        //    $this->info('Pending invoices have been processed.');
        \Log::info('Se han procesado las facturas propias.');
        return 0;
    }
}
