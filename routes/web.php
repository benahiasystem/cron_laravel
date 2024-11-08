<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('facturas-pendientes', [UserController::class, 'FacturasPendientesXEnviar']);
/*Route::get('/', function () {
    return view('welcome');
});*/

/*Route::get('00', [UserController::class, 'LicenciasxVencer0Dias']);

Route::get('1', [UserController::class, 'LicenciasxVencer1Dias']);

Route::get('3', [UserController::class, 'LicenciasxVencer3Dias']);

Route::get('7', [UserController::class, 'LicenciasxVencer7Dias']);

Route::get('15', [UserController::class, 'LicenciasxVencer15Dias']);

Route::get('30', [UserController::class, 'LicenciasxVencer30Dias']);

Route::get('first', [UserController::class, 'sendMailRegisterFirst']);

Route::get('second', [UserController::class, 'sendMailRegisterSecond']);

Route::get('third', [UserController::class, 'sendMailRegisterThird']);

Route::get('fourth', [UserController::class, 'sendMailRegisterFourth']);

Route::get('fifth', [UserController::class, 'sendMailRegisterFifth']);

Route::get('sixth', [UserController::class, 'sendMailRegisterSixth']);

Route::get('licenciasvencidas', [UserController::class, 'actualizarLicenciasVencidas']);

Route::get('wizardincomplete', [UserController::class, 'SendMailWizardIncomplete']);
*/

Route::get('ventasdiarias', [UserController::class, 'emailVentasDiarias']);
