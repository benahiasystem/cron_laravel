<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UsuariosActivosConVentasOffline extends Mailable
{
    use Queueable, SerializesModels;

    public $htmlTable;

    /**
     * Create a new message instance.
     *
     * @param string $htmlTable
     * @return void
     */
    public function __construct($htmlTable)
    {
        $this->htmlTable = $htmlTable;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.usuarios_activos')
                    ->subject('Usuarios Activos con Ventas Offline')
                    ->with(['htmlTable' => $this->htmlTable]);
    }
}