<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailLicenciasxVencer0Dias;
use Illuminate\Support\Facades\Log;
use App\Models\BlackList;


class LicenciasxVencer0Dias implements ShouldQueue
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
        $blackListedMails = Blacklist::all()->toArray();
       
        //busqueda licencias en app/Helpers.php
         $licencias = json_decode(ConsultaLicencias(0),TRUE); 
 
        if (!empty($licencias)) {
            
             foreach ($licencias as $key) {
                 $email          = $key['email'];
                 $usersnombres   = $key['first_name'] . ' ' . $key['last_name'];
                 $servidor       = $key['servidor'];
                 $base_dato      = $key['base_dato'];
                 $usuario        = $key['usuario'];
                 $clave          = $key['clave'];
                 $idAlmacen      = $key['id_almacen']; 

                 try {
                    $almacen = (string)ConsultaAlmacen($base_dato, $servidor, $idAlmacen, $usuario, $clave);
                } catch (\PDOException $e) {
                    Log::info('Database connection failed: ' . $e->getMessage());
                    Log::info($base_dato);
                    continue;
                }

                 $puede  = checkCanSend($blackListedMails, $email);
 
                 if (isset($puede)) {
                     $this->EnviarCorreo($licencias, strtoupper($usersnombres), $email, $almacen);
                 }
  
             }
          
        }
    }

    public function EnviarCorreo($licencias, $nombres, $email, $almacen) {
        Mail::to($email)
        ->send(new MailLicenciasxVencer0Dias($licencias, $nombres, $email, $almacen));
    }
}
