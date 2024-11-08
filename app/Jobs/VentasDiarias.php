<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Mail\MailEnviarCorreoVentasDiariasControl;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\MailVentasDiarias;
use App\Models\BlackList;
use Illuminate\Support\Facades\Log;

class VentasDiarias implements ShouldQueue
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
        $this->emailVentasDiarias();
    }

    public function emailVentasDiarias()
    {
        ini_set("memory_limit", "-1");

        $usuario = 'vendtyMaster';
        $blackListedMails = Blacklist::all()->toArray();

        $count = 0;
        $emails = '';
        $data = array();
        $fecha = date('Y-m-j 01:00:00');
        $fecha_vencimiento = date('Y-m-d');
        $nuevafecha = strtotime('-1 day', strtotime($fecha));
        $fecha_inicial = date('Y-m-j 01:00:00', $nuevafecha);
        $fecha_final = date('Y-m-j 23:59:59', $nuevafecha);
        $fecha_ayer = date('Y-m-j', $nuevafecha);
        $conection = dynamicDatabaseConnection('', '', '', '');

        $databases = DB::connection('vendty2')
                    ->table('users as u')
                    ->select('u.username', 'u.email', 'd.base_dato', 'd.servidor as servidor', 'd.usuario as usuario', 'd.clave as clave', 'l.id_almacen')
                    ->join('db_config as d', 'u.db_config_id', '=', 'd.id')
                    ->join('crm_licencias_empresa as l', 'd.id', '=', 'l.id_db_config')
                    ->where(function($query) {
                        $query->where('d.estado', 1)
                            ->orWhere('d.estado', 2);
                    })
                    ->where('l.fecha_vencimiento', '>=', $fecha_vencimiento)
                    ->whereNotIn('l.planes_id', [15, 16, 17])
                    ->where('u.is_admin', 't')
                    ->whereNotIn('d.servidor', ['0.0.0.0', '10.0.0.7'])
                    ->groupBy('u.db_config_id', 'l.id_almacen')
                    
                    ->get();
		
                    //dd(count($databases));
        if (!empty($databases)) {
            foreach ($databases as $database) {

              
                $username_admin = (string)"";
                $user_admin     = (string)"";
                $db             = (string)"";
                $id_almacen     = (int) 0;
                $usuario        = (string)"";
                $clave          = (string)"";
                $servidor       = (string)"";

               

                try {
                    log::info("##########################----------------->>>>>>>");
                    log::info("CONTADOR: " . $count . "----------------->>>>>>>");
                    $puede  = checkCanSend($blackListedMails, $database->email);
                    
                   
                    if ($puede) {
                        $username_admin = $database->username;
                        $user_admin     = strtolower($database->email);
                        $db             = $database->base_dato;
                        $id_almacen     = $database->id_almacen;
                        $usuario        = $database->usuario;
                        $clave          = $database->clave;
                        $servidor       = $database->servidor;
                        
                        //if ($count > 1040) {
                            //echo "<pre>"; print_r("base de datos " . $db . "id almacen" . $id_almacen);
                            
                        /*if (($user_admin == 'agropuli.adm@gmail.com') ||
                            ($user_admin == 'agropuliservicioalcliente@gmail.com') ||
                            ($user_admin == 'agropulicartera@gmail.com') ||
                            ($user_admin == 'alexcontador0327@gmail.com') ||
                            ($user_admin == 'nicopaez99@gmail.com') ||
                            ($user_admin == 'sergioroblescardenas@gmail.com') ||
                            ($user_admin == 'lagloriacentroturistico@gmail.com') ||
                            ($user_admin == 'brutaleatdrink@gmail.com') ||
                            ($user_admin == 'agielorena97@hotmail.com') ||
                            ($user_admin == 'juan.manrique0823@gmail.com') ||
                            ($user_admin == 'juancalderon1894@gmail.com') ||
                            ($user_admin == 'perlinaaccesorios@gmail.com') ||
                            ($user_admin == 'basilos446@gmail.com') ||
                            ($user_admin == 'luziamu72@gmail.com') ||
                            ($user_admin == 'tecnologia@maloka.org') ||
                            ($user_admin == 'alisadofrancespalmira@gmail.com') ||
                            ($user_admin == 'golosita1984sas@gmail.com') ||
                            ($user_admin == 'reservascampanario@hotmail.com'))

			                {*/
	
			                //log::info("----------------->>>>>>>");
                            log::info("----------------->>>>>>>::::::::: BASE DE DATOS:" . $database->base_dato);
                            

                            $existeBeta = "";
                            //purgar conexión dinamica
                		    DB::purge('dynamic');
                            $conection = "";
                            $conection = dynamicDatabaseConnection($db, $servidor, $usuario, $clave);
                            // reconectar
                            DB::reconnect('dynamic');
                                
			                try {
	                            $existeBeta = $conection->select("SHOW DATABASES LIKE '$db'");
			                } catch (\Throwable $th) {
                                log::info("##############################################################");
                                log::error('---- error al buscar base de datos' . $db);
                                log::info("##############################################################");
                                continue;
                            }

                            if (!empty($existeBeta)) {
                            //echo "<pre>"; print_r('---- base de datos' . $db . ' si existe' . 'en el host' . $servidor );
                            //$conection = dynamicDatabaseConnection($db, $servidor, $usuario, $clave);
                            }else{
                            //echo "<pre>"; print_r('///// base de datos' . $db . 'no existe' . 'en el host' . $servidor );
				            //purgar conexión dinamica
				                log::info("##############################################################");
				                log::info("=======> ERROR: La base de datos no existe  " . $db . "CORREO " . $user_admin);
				                log::info("##############################################################");
                                continue;
                            }


                            try {

                                /*if(($db == 'vendty2_db_73688_dr-a2024') ||
                                    ($db == 'vendty2_db_73755_pau.2024') ||
                                    ($db == 'vendty2_db_74002_ing.2024') ||
                                    ($db == 'vendty2_db_74009_sg.s2024') ||
				                    ($db == 'vendty2_db_8256_losca2017') ||
				                    ($db == 'vendty2_db_74512_la.f2024') ||
                                    ($db == 'vendty2_db_74758_Vic-2024') ||
                                    ($db == 'vendty2_db_73944_ana.2024')) {*/

                                    if (preg_match('/[.-]/', $db)) {
                                        log::info("##############################################################");
                                        log::info("=======> ERROR: Base de datos con caracteres especiales " . $user_admin );
                                        log::info("##############################################################");
                                        continue;
                                    }

                                $tableExists = $conection->select("SHOW TABLES FROM $db LIKE 'opciones'");

                                if(!empty($tableExists)) {
                                    $query = $conection->table($db.'.opciones')
                                        ->where(array('nombre_opcion' => 'simbolo'))->get();
                                }else{
                                    log::info("##############################################################");
                                    log::info("=======> ERROR: No existe la tabla opciones " . $user_admin );
                                    log::info("##############################################################");
                                    continue;
                                }
                            } catch (Exception $e) {
                                log::info("##############################################################");
                                log::info("=======> ERROR: Catch validar tabla opciones " . $user_admin);
                                log::info("##############################################################");
                                continue;
                            }

                            $simbolo = ($query == null || $query->count() == 0 || empty($query[0]->valor_opcion)) ? "$" : $query[0]->valor_opcion;
                            log::info("##############################################################");
                                log::info("=======> CONECTION:  " . $db);
                                log::info("##############################################################");

                            unset($almacenes);
                            $almacenes = [];    
                            $tableExists = $conection->select("SHOW TABLES FROM $db LIKE 'almacen'");

                            if(!empty($tableExists)) {
                                $almacenes = $conection->table('almacen')
                                                ->where('activo', 1)
                                                ->where('bodega', 0)
                                                ->where('id', $id_almacen)
                                                ->get();
                            }else{
                                log::info("##############################################################");
                                log::info("=======> ERROR: No existe la tabla almacen " . $user_admin );
                                log::info("##############################################################");
                                continue;
                            }

                            if (count($almacenes) > 0) {

                                foreach ($almacenes as $almacen) {

                                    $tableExists = $conection->select("SHOW TABLES FROM $db LIKE 'venta'");

                                    if(!empty($tableExists)) {
                                    
                                        $sql1 = $conection->table($db.'.venta as v')
                                            ->join($db.'.detalle_venta as dv', 'v.id', '=', 'dv.venta_id')
                                            ->selectRaw('
                                                SUM(dv.unidades * IFNULL(dv.descuento, 0)) AS total_descuento,
                                                SUM(((dv.precio_venta - IFNULL(dv.descuento, 0)) * IFNULL(dv.impuesto, 0)) / 100 * dv.unidades) AS impuesto,
                                                SUM(dv.precio_venta * dv.unidades) AS total_precio_venta,
                                                SUM((dv.precio_venta - IFNULL(dv.descuento, 0)) * IFNULL(dv.impuesto, 0) / 100 * dv.unidades) + SUM(dv.precio_venta * dv.unidades) AS total
                                            ')
                                            ->whereBetween('v.fecha', [$fecha_inicial, $fecha_final])
                                            ->where('v.estado', 0)
                                            ->where('v.almacen_id', $almacen->id)
                                            ->get();
                                        
                                            // Devoluciones (NC)
                                            ####todos
                                            $totaldevoluciones = 0;
                                            $total_devoluciones = $conection->table($db.'.devoluciones as d')
                                                                    ->join($db.'.venta as v', 'd.factura', '=', 'v.factura')
                                                                    ->select('v.id', 'v.factura', 'v.fecha', DB::raw('SUM(d.valor) as total_devolucion'))
                                                                    ->whereBetween('v.fecha', [$fecha_inicial, $fecha_final ])
                                                                    ->where('v.estado', '0')
                                                                    ->where('v.almacen_id', $almacen->id)
                                                                    ->groupBy('v.id', 'v.factura', 'v.fecha')
                                                                    ->get();                                                            
                                        $idFactura = "";

                                            foreach ($total_devoluciones as $key1 => $value1) {
                                                $totaldevoluciones += $value1->total_devolucion;
                                                $idFactura .= $value1->id . ",";
                                            }
                                    
                                        $result_array = $sql1->toArray();

                                        $ventas_diarias = $result_array[0]->total - $result_array[0]->total_descuento - $totaldevoluciones;
                                        $ventas_diarias = $simbolo . ' ' . number_format((float)$ventas_diarias, 2, '.', '');

                                        //Gastos
                                        $sql2 = $conection->table($db.'.proformas')
                                                        ->where('fecha', $fecha_ayer)
                                                        ->where('id_almacen', $almacen->id)
                                                        ->sum('valor');

                                        $total_gastos = (float)$sql2;
                                        
                                        $total_gastos = $simbolo . ' ' . number_format((float)$total_gastos, 2, '.', '');

                                        //Utilidad
                                        $sql3 = $conection->table($db.'.venta')
                                                        ->join('detalle_venta', 'venta.id', '=', 'detalle_venta.venta_id')
                                                        ->whereBetween('venta.fecha', [$fecha_inicial, $fecha_final])
                                                        ->where('venta.almacen_id', $almacen->id)
                                                        ->where('venta.estado', 0)
                                                        ->sum('detalle_venta.margen_utilidad');

                                        $total_utilidad = (float)$sql3;
                                        $total_utilidad = $simbolo . ' ' . number_format((float)$total_utilidad, 2, '.', '');

                                        //Ventas por formas de pago
                                        $sql4 = $conection->table($db.'.ventas_pago AS vp')
                                                        ->select('v.id AS id_venta', 
                                                        DB::raw('SUM(vp.valor_entregado) - SUM(vp.cambio) AS total_venta'), 
                                                        DB::raw('COUNT(vp.forma_pago) AS cantidad'), 'vp.forma_pago')
                                                            ->join('venta AS v', 'vp.id_venta', '=', 'v.id')
                                                            ->whereDate('v.fecha', $fecha_ayer)
                                                            ->where('v.almacen_id', $almacen->id)
                                                            ->where('v.estado', 0)
                                                            ->groupBy('v.id', 'vp.forma_pago')
                                                            ->orderBy('cantidad')
                                                            ->orderBy('id_venta')
                                                            ->orderBy('total_venta')
                                                            ->orderBy('vp.forma_pago')
                                                            ->get();

                                        $total_formas_pago = $sql4->toArray();

                                        //Productos mas vendidos
                                        $sql5 = $conection->table($db.'.detalle_venta AS dv')
                                            ->select('producto.imagen', 
                                        'producto.nombre AS nombre_producto', 
                                        DB::raw('SUM(dv.unidades) AS count_productos'), 
                                        DB::raw('SUM(dv.margen_utilidad) AS utilidad'), 'dv.precio_venta')
                                            ->join('venta AS v', 'dv.venta_id', '=', 'v.id')
                                            ->join('producto', 'dv.producto_id', '=', 'producto.id')
                                            ->whereDate('v.fecha', $fecha_ayer)
                                            ->where('v.almacen_id', $almacen->id)
                                            ->where('v.estado', 0)
                                            ->groupBy('producto.nombre', 'producto.imagen', 'dv.precio_venta')
                                            ->orderByDesc('count_productos')
                                            ->limit(3)
                                            ->get();

                                        $productos_mas_vendidos = $sql5->toArray();

                                        $data = [];
                                        $data = array(
                                            "fecha"         => $fecha_ayer,
                                            "user"          => strtoupper($username_admin),
                                            "almacen"       => (string)strtoupper($almacen->nombre),
                                            "ventas_diarias"    => $ventas_diarias,
                                            "total_utilidad"    => $total_utilidad,
                                            "devoluciones"      => $simbolo . ' ' . number_format((float)$totaldevoluciones, 2, '.', ''),
                                            "total_gastos"      => $total_gastos,
                                            "total_formas_pago" => $total_formas_pago,
                                            "productos_mas_vendidos" => $productos_mas_vendidos,
                                        );


                                     $this->EnviarCorreoEmailVentasDiarias($user_admin, $data, $fecha_ayer, (string)$almacen->nombre);
                                    log::info('El mail ' . $user_admin  . ' SE ENVIO A ALMACEN:' . $data['almacen']);

                                        $count++;
                                        $emails .= $almacen->nombre . ' - ' . $user_admin . '<br>';
                                    
                                    }else{
                                        log::info("##############################################################");
                                        log::info("=======> ERROR: La tabla venta no existe" . $user_admin);
                                        log::info("##############################################################");
                                        continue;
                                    }
                                }

                                    
                            }else {
                                log::info("##############################################################");
                                log::info("=======> ERROR: El almacen viene vacio" . $user_admin);
                                log::info("##############################################################");
                                continue;
                            }

                       /* }else{
                            $count++;
                        }*/
                    }else {
			            log::info('El mail ------>>' . $user_admin  . ' se encuentra en la lista negra');
                        //echo $this->cli->cout_color('El mail ' . $database["email"] . ' se encuentra en la lista negra', 'red') . "\n";
                    }

                        
                }catch (Exception $e) {
                    echo 'Excepción capturada: ', $database, ' - ', $e->getMessage(), "\n";
                }

            }
        }
	    log::info('Mail enviados ------>>' . $count );
        $this->EnviarCorreoVentasDiariasControl($count, $emails, $fecha_ayer);
    }

    public function EnviarCorreoEmailVentasDiarias($email, $data, $fecha_ayer, $almacenNombre) {
         Mail::to($email)
         ->send(new MailVentasDiarias($data, $fecha_ayer, $almacenNombre));
     }

     public function EnviarCorreoVentasDiariasControl($count, $emails, $fecha_ayer) {
        Mail::to('integraciones@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'nelson@vendty.com'))
        ->send(new MailEnviarCorreoVentasDiariasControl($count, $emails, $fecha_ayer));
    }
}
