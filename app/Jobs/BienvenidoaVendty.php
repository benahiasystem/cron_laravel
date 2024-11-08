<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BienvenidoaVendty implements ShouldQueue
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
        Log::info("dsdfgsdfgsdgsdfg");
    }

   /*public function BienvenidoaVendty($idbd)
    {
        $this->load->library('email');
        $this->email->initialize();
        $this->email->from('no-responder@vendty.net', 'Vendty POS y Tienda Virtual');
        $this->email->to('no-responder@vendty.net');
        $this->email->bcc(array('arnulfo@vendty.com', 'desarrollo@vendty.com', 'roxanna.vergara@gmail.com', 'soporte@vendty.com', 'asesor@vendty.com'));
        $this->email->subject('Bienvenido a Vendty - Agenda Tú Capacitación');

        $sql = "SELECT username, email, phone FROM users
			WHERE db_config_id=$idbd
			AND is_admin='t' LIMIT 1";
        $sql = $this->db->query($sql)->result();
        $destination = array();
        $userParams = array();
        $message = '[welcome]';
        $globalParams = rawurlencode('{"welcome":"Bienvenido(a) a Vendty, Si necesitas ayuda con tu prueba Gratis puedes agendar una demo en https://app.hubspot.com/meetings/capacitacion/resolucion-dudas o Chatear por WhatsApp http://bit.ly/2RSQeAg"}');

        foreach ($sql as $key => $value) {
            if (!array_search($value->phone, $destination) && strlen($value->phone) >= 10 && $value->phone == '3015262684') {
                array_push($destination, $value->phone);
                array_push($userParams, '"' . $value->phone . '":{"name":"' . $key['first_name'] . '"}');
            }

            $this->email->to($value->email);
            $data = array(
                'name' => $value->username,
            );
            $message = $this->load->view('email/welcome_to_vendty', $data, true);
            $this->email->message($message);

            if (!$this->email->send()) {
                echo '<br>No se pudo enviar el mensaje';
                var_dump($this->email->print_debugger());
            }
        }
    }*/


}
