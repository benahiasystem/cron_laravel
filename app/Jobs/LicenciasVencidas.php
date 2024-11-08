<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\BlackList;

use App\Mail\MailEnviarCorreoLicenciasVencidasControl;
use App\Mail\MailActualizarLicenciasVencidas;
use Illuminate\Support\Facades\Log;
use App\Models\LicenciaEmpresa;
use App\Models\Licencias;
use Carbon\Carbon;


class LicenciasVencidas implements ShouldQueue
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
        $this->actualizarLicenciasVencidas();
    }

    public function actualizarLicenciasVencidas()
    {
        $count = 0;
        $emails = '';
        $blackListedMails = Blacklist::all()->toArray();
        
        $fecha = date('Y-m-d');
        $fecha = Carbon::parse(date("Y-m-d", strtotime($fecha . "- 1 days")));


        $sqlupdate = LicenciaEmpresa::where('fecha_vencimiento', $fecha)
                                    ->where('estado_licencia', '!=', 15)
                                    ->update(['estado_licencia' => 15]);

                                   
        log::info('Fecha: ' . substr($fecha, 0, -8) . ' Licencias ------>>' . $sqlupdate);

        if ($sqlupdate > 0) {

            $licencias = Licencias::select('v_crm_licencias.id_licencia as licencia_id', 'users.email as user_email', 'users.username as user_name')
                                ->join('users', 'v_crm_licencias.id_db_config', '=', 'users.db_config_id')
                                ->where('v_crm_licencias.fecha_vencimiento', $fecha)
                                ->where('v_crm_licencias.id_plan', '!=', 1)
                                ->where('users.is_admin', 't')
                                ->groupBy('v_crm_licencias.id_licencia')
                                ->get();
            
            
            try {
                foreach ($licencias as $key) {
                    $email = $key['user_email'];

                    $puede  = checkCanSend($blackListedMails, $email);

                    /*post_curl('baremetrics/cancel_subscription', json_encode([ 
                        'license_id' => $key['licencia_id'], 
                    ]));*/

                    if ($puede) {
                        $count++;
                        $emails .= 'Id Licencia: ' . $key['licencia_id'] . ' email: ' . $email . '\n';
                        $this->EnviarCorreoLicenciasVencidas($email, $key['user_name']);
                        log::info('Mail enviados ------>>' . $email );
                    }

                }
            } catch (Exception $e) {
                return $e;
            }
        }

        log::info('Cantidad Mail enviados ------>>' . $count );
        $this->EnviarCorreoLicenciasVencidasControl($count, $emails, substr($fecha, 0, -8));

    }

    public function EnviarCorreoLicenciasVencidas($email, $user) {
        Mail::to($email)
        ->send(new MailActualizarLicenciasVencidas($email, $user));
    }

    public function EnviarCorreoLicenciasVencidasControl($count, $emails, $fecha_ayer) {
        Mail::to('integraciones@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'nelson@vendty.com'))
        ->send(new MailEnviarCorreoLicenciasVencidasControl($count, $emails, $fecha_ayer));
    }
}
