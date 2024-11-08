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

class ElectronicInvoiceSiigoSenderJob implements ShouldQueue
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
        $controller = new UserController(); // Reemplaza con el nombre correcto del controlador
     //   $this->info('Pending invoices will been processed.');
        \Log::info('Se procesan las facturas Siigo.');
        $controller->FacturasSiigoPendientesXEnviar();
    //    $this->info('Pending invoices have been processed.');
        \Log::info('Se procesaron las facturas Siigo.');
        
    }

    private function getCurlSiigo($method, $token)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://integraciones.vendty.com/api/v1/" . $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $token",
        ));

        $response = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }

        curl_close($ch);

        return json_decode($response);
    }
    
    private function getCurl($method, $token)
    {
        // Aquí va tu implementación para la llamada cURL
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://apipos.vendty.com/api/v1/" . $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $token",
        ));

        $response = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }

        curl_close($ch);

        return json_decode($response);
    }
    
    private function postCurl($method, $data, $token)
    {
        // Aquí va tu implementación para la llamada cURL con POST
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://apipos.vendty.com/api/v1/" . $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
    
        if ($method != 'login') {
            if ($token != null) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Authorization: Bearer $token",
                    "Content-Type: application/json",
                ));
            } else {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                ));
            }
        }
    
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
        $response = curl_exec($ch);
    
        curl_close($ch);
    
        return json_decode($response);
    }
    
    private function handleSiigoResponse($response, $pendiente, $venta_id, $fecha)
    {
        $Errormsg = '';
        $respuesta = 'NO PASO';
        $estado = 0;
    
        if (isset($response->status) && $response->status == 'error') {
          
            if (is_array($response->msg)) {
                $mnsg = '';
                foreach ($response->msg as $error) {
                    $mnsg .= " $error->Message,";
                }
                $Errormsg = $mnsg;
            } else {
                $Errormsg = $response->msg;
            }
    
            $estado = 0;
            $sendMail = ['id' => $venta_id, 'errores' => $Errormsg];
            $respuesta = json_encode($sendMail);
    
            if (strpos($respuesta, "The total payments must be equal to the total invoice") !== false) {
                $respuesta = @$Errormsg;
                $estado = 17; // total malo
            } elseif (strpos($respuesta, 'Error los datos del cliente son requeridos') ) {
                $respuesta = $Errormsg;
                $estado = 4; // sin cliente
            } elseif (strpos($respuesta, "No ha configurado un usuario para siigo en el sistema, por favor introduzca los datos proporcionados") ) {
                $respuesta = $Errormsg;
                $estado = 6; // sin datos
            } elseif (strpos($respuesta, "Los datos de siigo proporcionados no funcionan para generar un token, por favor verificar con Siigo que el usuario este activo y exista" )) {
                $respuesta = $Errormsg;
                $estado = 6; // sin datos
            } elseif (strpos($respuesta, "Debe configurar un documento No electronico para facturas pos siigo" )) {
                $respuesta = $Errormsg;
                $estado = 6; // sin datos
            } elseif (strpos($respuesta, "hay un error de calculos" )) {
                $respuesta = $Errormsg;
                $estado = 0; // sin datos 

            } elseif (isset($Errormsg)) {
                $respuesta = $Errormsg;
                $estado = 18; // sin datos
            }
        } else {
            if (strpos(json_encode($response), "name")) {
                echo $this->colorize(" | RESP: PASO! " . json_encode($response) . "\n","green");
                $respuesta = 'PASO';
                $estado = 1;
            } else {
                echo $this->colorize("NO PASO! " . json_encode($response),"red");
            }
        }
    
        if (is_array($respuesta)) {
            $respuesta = json_encode($respuesta);
        }
        $respuesta = json_encode($response);
    
        $pendiente->update([
            'respuesta' => $respuesta,
            'estado' => $estado,
            'ultimo_intento' => $fecha,
        ]);
        $color = 'green';
        if($estado != 1){
            $color = 'red';
        }
        echo $this->colorize(" | RESP: " . $respuesta . "\n", $color);
    }
    
    private function storeInvoice($documento, $id_factura)
    {
        $db_host_prod = "pos-main8.cnvvsgytawik.us-east-2.rds.amazonaws.com";
        $db_username_prod = "vendtyMaster";
        $db_password_prod = "ro_ar_8027*_na";
        $db_name_prod = "vendty2";
    
        $conn = new \mysqli($db_host_prod, $db_username_prod, $db_password_prod, $db_name_prod);
        $conn->set_charset("utf8");
    
        $sqlFacturaVendty = "INSERT INTO crm_factura_electronica
        (`id_factura`,`numeroDocumento`,`codigo`,`descripcion`,`id_transaccion`,`xml_firmado`,`representacion_grafica`,`cufe`) VALUES
        (?, ?, ?, ?, ?, ?, ?, ?)";
    
        $stmt = $conn->prepare($sqlFacturaVendty);
        $stmt->bind_param("isssssss", $id_factura, $documento->numeroDocumento, $documento->codigo, $documento->descripcion, $documento->idTransaccion, $documento->ubl, $documento->representacionGrafica, $documento->cufe);
        $stmt->execute();
    }
    public function EnviarCorreo($licencias, $nombres, $email, $almacen) {
        Mail::to($email)
        ->send(new MailLicenciasxVencer7Dias($licencias, $nombres, $email, $almacen));
    }
}
