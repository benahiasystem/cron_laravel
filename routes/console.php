<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

use App\Jobs\LicenciasxVencer0Dias;
use App\Jobs\LicenciasxVencer1Dias;
use App\Jobs\LicenciasxVencer3Dias;
use App\Jobs\LicenciasxVencer7Dias;
use App\Jobs\LicenciasxVencer15Dias;
use App\Jobs\LicenciasxVencer30Dias;
use App\Http\Controllers\UserController;
use App\Jobs\SendMails;
use App\Jobs\LicenciasVencidas;
use App\Jobs\WizardIncomplete;
use App\Jobs\VentasDiarias;

use App\Jobs\ElectronicInvoiceSiigoSenderJob;
use App\Jobs\ElectronicInvoicePropiaSenderJob;
Schedule::job(new LicenciasxVencer0Dias)
        ->name('Licencias por Vencer 0 Dias')
        ->dailyAt('04:00');

Schedule::job(new LicenciasxVencer1Dias)
        ->name('Licencias por Vencer 1 Dias')
        ->dailyAt('04:00');

Schedule::job(new LicenciasxVencer3Dias)
        ->name('Licencias por Vencer 3 Dias')
        ->dailyAt('04:00');

Schedule::job(new LicenciasxVencer7Dias)
        ->name('Licencias por Vencer 7 Dias')
        ->dailyAt('04:00');

Schedule::job(new LicenciasxVencer15Dias)
        ->name('Licencias por Vencer 15 Dias')
        ->dailyAt('04:00');

Schedule::job(new LicenciasxVencer30Dias)
        ->name('Licencias por Vencer 30 Dias')
        ->dailyAt('04:00'); 

Schedule::job(new SendMails)
        ->name('envio de emails')
         ->dailyAt('5:00');

Schedule::job(new LicenciasVencidas)
        ->name('Actualizar Licencias Vencidas')
        ->dailyAt('05:30');

Schedule::job(new WizardIncomplete)
        ->name('Wizard Incompleto')
        ->dailyAt('04:00');

Schedule::job(new VentasDiarias)
        ->name('Correo de Ventas Diarias')
        ->dailyAt('06:00');

Schedule::job(new ElectronicInvoiceSiigoSenderJob)
         ->name('Servicio de Envio de Facturas Electronicas')
         ->everyTenMinutes();
       //  ->withoutOverlapping(); 
// Artisan::command('invoices:process', function () {
//         $this->call('invoices:process')
//         ->everyTenMinutes()
//         ->withoutOverlapping()
//         ->runInBackground();
// });  

// Artisan::command('invoices:process', function () {
//         $controller = new UserController();
//         $controller->FacturasPropiasPendientesXEnviar();

// });
// Schedule::command('invoices:process')
// ->everyTenMinutes()
// ->withoutOverlapping()
// ->runInBackground();
// Schedule::job(new ElectronicInvoicePropiaSenderJob)
//          ->name('Servicio de Envio de Facturas Electronicas Propias')
//          ->everyTenMinutes()
//           ->withoutOverlapping()
//           ->runInBackground();   

/*
Artisan::command('invoices:process', function () {
        $this->call('invoices:process');
});
*/
// Artisan::command('invoices:process', function () {
//         $this->call('invoices:process');
// });

     
/* USO PARA PRUEBAS */              
        
/*Schedule::job(new LicenciasxVencer0Dias)
        ->name('Licencias por Vencer 0 Dias')
        ->everyMinute();

Schedule::job(new LicenciasxVencer1Dias)
        ->name('Licencias por Vencer 1 Dias')
        ->everyMinute();

Schedule::job(new LicenciasxVencer3Dias)
        ->name('Licencias por Vencer 3 Dias')
        ->everyMinute();

Schedule::job(new LicenciasxVencer7Dias)
        ->name('Licencias por Vencer 7 Dias')
        ->everyMinute();

Schedule::job(new LicenciasxVencer15Dias)
        ->name('Licencias por Vencer 15 Dias')
        ->everyMinute();

Schedule::job(new LicenciasxVencer30Dias)
        ->name('Licencias por Vencer 30 Dias')
        ->everyMinute(); 
*/
/*Schedule::job(new SendMails)
        ->name('envio de emails')
        ->everyMinute(); */
        
/*Schedule::job(new VentasDiarias)
        ->name('enviar email de ventas diarias')
        ->everyMinute();*/
/*
Schedule::job(new LicenciasVencidas)
        ->name('Actualizar Licencias Vencidas')
        ->everyMinute();     

Schedule::job(new WizardIncomplete)
        ->name('Wizard Incompleto')
        ->everyMinute();

*/
