<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailWizardIncomplete;
use Illuminate\Support\Facades\DB;
use App\Models\users;
use DateTime;

class WizardIncomplete implements ShouldQueue
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
        $clients = users::select(DB::raw('DISTINCT users.id'), 'db_config.fecha', 'users.username', 'users.email', 'primeros_pasos_usuarios.step', 'primeros_pasos_usuarios.type_business')
                ->join('db_config', 'users.db_config_id', '=', 'db_config.id')
                ->join('primeros_pasos_usuarios', 'db_config.id', '=', 'primeros_pasos_usuarios.db_config')
                ->where('users.is_admin', 't')
                ->where('db_config.estado', 2)
                ->get();


        $now = Date('y-m-d');
        
        $today = new DateTime($now);
       
        foreach ($clients as $client):
            if ($client['step'] > 1 && $client['step'] < 4):
                $date_create_account = new DateTime($client['fecha']);
                $diference = $today->diff($date_create_account);
                if ($diference->days > 2 && $diference->days < 7):
                    $email = $client['email'];
                    $data = array(
                        'user' => $client['username'],
                        'step' => 4 - $client['step'],
                    );
                    $this->EnviarCorreoWizardIncomplete($email, $data);
                endif;
            endif;
        endforeach;        
    }

    public function EnviarCorreoWizardIncomplete($email, $data) {
         Mail::to($email)
         ->send(new MailWizardIncomplete($data));
     }
}
