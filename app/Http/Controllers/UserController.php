<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Mail\MailLicenciasxVencer0Dias;
use App\Mail\MailLicenciasxVencer1Dias;
use App\Mail\MailLicenciasxVencer3Dias;
use App\Mail\MailLicenciasxVencer7Dias;
use App\Mail\MailLicenciasxVencer15Dias;
use App\Mail\MailLicenciasxVencer30Dias;

use App\Mail\MailRegisterFirst;
use App\Mail\MailEnviarCorreoFirstControl;

use App\Mail\MailRegisterSecond;
use App\Mail\MailRegisterSecond2;
use App\Mail\MailEnviarCorreoSecondControl;

use App\Mail\MailRegisterThird;
use App\Mail\MailEnviarCorreoThirdControl;

use App\Mail\MailRegisterFourth;
use App\Mail\MailEnviarCorreoFourthControl;

use App\Mail\MailRegisterFifth;
use App\Mail\MailRegisterFifth2;
use App\Mail\MailEnviarCorreoFifthControl;

use App\Mail\MailRegisterSixth;
use App\Mail\MailEnviarCorreoSixthControl;
use App\Mail\UsuariosActivosConVentasOffline;
use App\Mail\MailActualizarLicenciasVencidas;
use App\Models\LicenciaEmpresa;
use App\Models\Licencias;
use Carbon\Carbon;

use App\Mail\MailWizardIncomplete;
use App\Models\users;
use DateTime;

use App\Mail\MailVentasDiarias;
use App\Mail\MailEnviarCorreoVentasDiariasControl;
use Illuminate\Support\Facades\Schema;

use View;
use App\Models\BlackList;
use App\Models\ElectronicInvoiceSender;
use App\Models\DBConfig;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    private $appliedChanges = [];
    public $servers = ['pos5.cnvvsgytawik.us-east-2.rds.amazonaws.com', 'pos3.cnvvsgytawik.us-east-2.rds.amazonaws.com', 'demo-db-cluster.cluster-cnvvsgytawik.us-east-2.rds.amazonaws.com', 'demos.cnvvsgytawik.us-east-2.rds.amazonaws.com'];

    public function EnviarCorreo($licencias, $nombres, $email, $almacen) {
            //dd($nombres);
             Mail::to($email)
             ->send(new MailLicenciasxVencer0Dias($licencias, $nombres, $email, $almacen));
    }

    public function EnviarCorreoHtml($htmlTable, $email) {
        // Enviar el correo
        Mail::to($email)
            ->send(new UsuariosActivosConVentasOffline($htmlTable));
    }

    public function activosConOffline() {
      
        
        //busqueda licencias en app/Helpers.php
         $usuariosActivos = json_decode(ConsultaActivos(),TRUE); 
       
        $datos = [];
        if (!empty($usuariosActivos)) {

            $htmlTable = '<h3>Usuarios Activos y Ventas Offline</h3>';
            $htmlTable .= '<table border="1" cellpadding="10" cellspacing="0">';
            $htmlTable .= '<thead>
                              <tr>
                                  <th>Email</th>
                                  <th>Nombre Completo</th>
                                  <th>Almacen</th>
                                  <th>Venta Offline</th>
                                  <th>Ultima Factura Offline</th>
                              </tr>
                           </thead>';
            $htmlTable .= '<tbody>';
            $index = 0;
             foreach ($usuariosActivos as $key) {

                 $email          = $key['email'];
                 $usersnombres   = $key['first_name'] . ' ' . $key['last_name'];
                 $servidor       = $key['servidor'];
                 $base_dato      = $key['base_dato'];
                 $usuario        = $key['usuario'];
                 $clave          = $key['clave'];
                 $idAlmacen      = $key['id_almacen']; 
 
                 //dd($email);
                if (strpos($base_dato, '.') !== false) {
                  
                    continue; // Retornar null o manejar el error de la manera que prefieras
                }

                 $venta = ConsultaVentaOffline($base_dato, $servidor, $idAlmacen, $usuario, $clave);
                 $almacen = (string)ConsultaAlmacen($base_dato, $servidor, $idAlmacen, $usuario, $clave);
              
              
                 $usaOffline = 'No';
                 $fecha = "----";
                 if(!is_null($venta)&&isset($venta->id)){
                    $fecha = $venta->fecha;
                    $index++;
                    $usaOffline = 'Si';
                    echo "$index || Email: $email || Almacen: $almacen || Ultima Venta: $fecha \n";
                 }
                 $htmlTable .= "<tr>
                    <td>{$email}</td>
                    <td>{$usersnombres}</td>
                    <td>{$almacen}</td>
                    <td>{$usaOffline}</td>
                    <td>{$fecha}</td>
                </tr>";
                
            
                
                
             }
             $htmlTable .= '</tbody></table>';
             $this->EnviarCorreoCesar($htmlTable);
          
    
            return response()->json(['message' => 'Correo enviado correctamente.']);
        }
    }
    public function activosConFacturaPropia() {
      
        
        //busqueda licencias en app/Helpers.php
         $usuariosActivos = json_decode(ConsultaActivos(),TRUE); 
       
        $datos = [];
        if (!empty($usuariosActivos)) {

            $htmlTable = '<h3>Usuarios Activos y Ventas Offline</h3>';
            $htmlTable .= '<table border="1" cellpadding="10" cellspacing="0">';
            $htmlTable .= '<thead>
                              <tr>
                                  <th>Email</th>
                                  <th>Nombre Completo</th>
                                  <th>Almacen</th>
                                  <th>token</th>
                                  <th>identificador FE</th>
                                  <th>identificador POSE</th>
                                  <th>identificador DOCSO</th>
                                  <th>Datos Factura Electronica</th>
                                  <th>Datos POS Electronico</th>
                                  <th>Datos Documento Soporte</th>
                              
                              </tr>
                           </thead>';
            $htmlTable .= '<tbody>';
            $index = 0;
             foreach ($usuariosActivos as $key) {

                 $email          = $key['email'];
                 $usersnombres   = $key['first_name'] . ' ' . $key['last_name'];
                 $servidor       = $key['servidor'];
                 $base_dato      = $key['base_dato'];
                 $usuario        = $key['usuario'];
                 $clave          = $key['clave'];
                 $idAlmacen      = $key['id_almacen']; 
 
                 //dd($email);
                if (strpos($base_dato, '.') !== false) {
                    continue; // Retornar null o manejar el error de la manera que prefieras
                }

              //   $venta = ConsultaVentaOffline($base_dato, $servidor, $idAlmacen, $usuario, $clave);
                 $almacen = ConsultaAlmacenData($base_dato, $servidor, $idAlmacen, $usuario, $clave);
              
              
                 $usaOffline = 'No';
                 $fecha = "----";
                 if(isset($almacen)&&isset($almacen->aliado)&&$almacen->aliado=='2'){
                 
                    $index++;
                    $usaOffline = 'Si';
                    $nombre =  @$almacen->nombre ?? "Sin Nombre";
                    echo "$index || Email: $email || Almacen: $nombre ---------- o ------------- \n";
                 
                    $datosFacturaElectronica = "";
                    $datosPosElectronico = "";
                    $datosDocumentoSoporte = "";
                 
                    if(isset($almacen->resolution_felatam) && !is_null($almacen->resolution_felatam)&&$almacen->resolution_felatam != ""){
                       $activo = isset($almacen->activo_fe_propia) && $almacen->activo_fe_propia == 1 ? "SI" : "NO";
                       $datosFacturaElectronica = "
                       <b>Factura Electronica: </b>  <br> <br>
                       Activo?: $activo
                       Token: $almacen->token_felatam <br>
                       Resolucion: $almacen->resolution_felatam <br>
                       Prefijo: $almacen->prefix_felatam <br>
                       Actual: $almacen->actual_felatam <br>
                       Fecha Desde: $almacen->from_felatam <br>
                       Fecha Hasta: $almacen->date_to_felatam <br>";                    
                    }
                  
                    if(isset($almacen->resolution_eposlatam) && !is_null($almacen->resolution_eposlatam)&&$almacen->resolution_eposlatam != ""){
                       $activo = isset($almacen->activo_epos_propia) && $almacen->activo_epos_propia == 1 ? "SI" : "NO";
                       $datosPosElectronico = "
                           <b>POS Electronico: </b> <br> <br>
                           Activo?: $activo
                           Token: $almacen->token_felatam <br>
                           Resolucion: $almacen->resolution_eposlatam <br>
                           Prefijo: $almacen->prefix_eposlatam <br>
                           Actual: $almacen->actual_eposlatam <br>
                           Fecha Desde: $almacen->from_eposlatam <br>
                           Fecha Hasta: $almacen->date_to_eposlatam <br>";
                    }
                    if(isset($almacen->resolution_docsolatam) && !is_null($almacen->resolution_docsolatam)&&$almacen->resolution_docsolatam != ""){
                       $activo =isset($almacen->activo_docso_propia) &&  $almacen->activo_docso_propia == 1 ? "SI" : "NO";
                       $datosDocumentoSoporte ="
                       <b>Documento Soporte: </b> <br> <br>
                       Activo?: $activo
                           Token: $almacen->token_felatam <br>
                           Resolucion: $almacen->resolution_docsolatam <br>
                           Prefijo: $almacen->prefix_docsolatam <br>
                           Actual: $almacen->actual_docsolatam <br>
                           Fecha Desde: $almacen->from_docsolatam <br>
                           Fecha Hasta: $almacen->date_to_docsolatam <br>";
                    }
                    $key = $almacen->key_felatam  ??  '';
                    $key_epos = $almacen->key_eposlatam  ??  ''; 
                    $key_dosco = $almacen->key_docsolatam  ??  ''; 
                    $htmlTable .= "<tr>
                        <td>{$email}</td>
                        <td>{$usersnombres}</td>
                        <td>{$almacen->nombre}</td>
                        <td>{$almacen->token_felatam}</td>
                        <td>{$key}</td>
                        <td>{$key_epos }</td>
                        <td>{$key_dosco}</td>
                        <td>{$datosFacturaElectronica}</td>
                        <td>{$datosPosElectronico}</td>
                        <td>{$datosDocumentoSoporte}</td>
                        
                    </tr>";
                 }
             }
             $htmlTable .= '</tbody></table>';
             $this->EnviarCorreoCesar($htmlTable);
          
    
            return response()->json(['message' => 'Correo enviado correctamente.']);
        }
    }

    public function activosConFacturaxion() {
      
        
        //busqueda licencias en app/Helpers.php
         $usuariosActivos = json_decode(ConsultaActivos(),TRUE); 
       
        $datos = [];
        if (!empty($usuariosActivos)) {

            $htmlTable = '<h3>Usuarios Con Facturaxion</h3>';
            $htmlTable .= '<table border="1" cellpadding="10" cellspacing="0">';
            $htmlTable .= '<thead>
                              <tr>
                                  <th>Email</th>
                                  <th>Nombre Completo</th>
                                  <th>Almacen</th>
                                  <th>Total Ventas</th>
                                  <th>Ultima Venta</th>
                              </tr>
                           </thead>';
            $htmlTable .= '<tbody>';
            $index = 0;
             foreach ($usuariosActivos as $key) {

                 $email          = $key['email'];
                 $usersnombres   = $key['first_name'] . ' ' . $key['last_name'];
                 $servidor       = $key['servidor'];
                 $base_dato      = $key['base_dato'];
                 $usuario        = $key['usuario'];
                 $clave          = $key['clave'];
                 $idAlmacen      = $key['id_almacen']; 
 
                 //dd($email);
                if (strpos($base_dato, '.') !== false) {
                    continue; // Retornar null o manejar el error de la manera que prefieras
                }

              //   $venta = ConsultaVentaOffline($base_dato, $servidor, $idAlmacen, $usuario, $clave);
                 $almacen = ConsultaAlmacenData($base_dato, $servidor, $idAlmacen, $usuario, $clave);
                 $numero_ventas = CuentaVentas($base_dato, $servidor, $idAlmacen, $usuario, $clave, 'cuenta_facturas_electronicas');
                 $ultima_venta = CuentaVentas($base_dato, $servidor, $idAlmacen, $usuario, $clave, 'ultima_factura_electronica');
                 $fecha = @$ultima_venta->fecha;
                 $esb2b = 'No';
                 $fecha = "----";
                 if(isset($almacen)&&is_null(@$almacen->aliado)&&$almacen->facturacion_electronica==1){
                 
                    $index++;
                    $esb2b = 'Si';
                    $nombre =  @$almacen->nombre ?? "Sin Nombre";
                    echo "$index || Email: $email || Almacen: $nombre ---------- o ------------- \n";
              
                    $htmlTable .= "<tr>
                        <td>{$email}</td>
                        <td>{$usersnombres}</td>
                        <td>{$almacen->nombre}</td>
                        <td>{$numero_ventas}</td>
                        <td>{$fecha}</td>
                        
                    </tr>";
                 }
             }
             $htmlTable .= '</tbody></table>';
             $this->EnviarCorreoCesar($htmlTable);
          
    
            return response()->json(['message' => 'Correo enviado correctamente.']);
        }
    }
    public function EnviarCorreoCesar($htmlTable) {
        Mail::to("cesarmarduk@gmail.com")
       // ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'desarrollo@vendty.com'))
        ->send(new UsuariosActivosConVentasOffline($htmlTable));
    }
 
    private function colorize($text, $color)
    {
        $colors = [
            'black' => '0;30',
            'red' => '0;31',
            'green' => '0;32',
            'yellow' => '1;33',
            'blue' => '0;34',
            'magenta' => '0;35',
            'cyan' => '0;36',
            'white' => '1;37',
        ];
    
        $colorCode = $colors[$color] ?? '0'; // Si no se encuentra el color, usa el código por defecto
        return "\033[" . $colorCode . "m" . $text . "\033[0m";
    }
    public function encuentraFactura()
    {
        DB::connection('vendty2')->statement("SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode, 'ONLY_FULL_GROUP_BY', ''))");

        $sql = "SELECT username, email, base_dato, d.servidor AS servidor, d.usuario AS usuario, d.clave AS clave, l.id_almacen
                FROM vendty2.users AS u
                INNER JOIN vendty2.db_config AS d ON u.db_config_id = d.id
                INNER JOIN vendty2.crm_licencias_empresa AS l ON d.id = l.id_db_config
                WHERE u.is_admin = 't'
                AND servidor IN ('pos5v8.cnvvsgytawik.us-east-2.rds.amazonaws.com', 'pos3v8.cnvvsgytawik.us-east-2.rds.amazonaws.com','demo24.cnvvsgytawik.us-east-2.rds.amazonaws.com')
                GROUP BY u.db_config_id";

        // Ejecuta la consulta directamente sin DB::raw()
        $databases = DB::connection('vendty2')->select($sql);
        $nDatabases = count($databases);
        $index = 0;
        $dump = "SELECT * FROM venta WHERE factura = 'DPE3437' LIMIT 1 ";
        
        $indicesAgregados = 0;
        if (!empty($databases)) {
            foreach ($databases as $database) {
                $index++;
                if ($index) {
                    $errors = '';
                    $username_admin = $database->username;
                    $user_admin = $database->email;
                    $db = $database->base_dato;
                    $id_almacen = $database->id_almacen;
                    $usuario = $database->usuario;
                    $clave = $database->clave;
                    $servidor = $database->servidor;

                    // dump($servidor);
                    // dump($db);
                    // dump($user_admin);
                    echo $this->colorize('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> BASE DE DATO Numero ' . $index . ' de ' . $nDatabases . ' : ' . $db . ' en proceso <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<  \n','blue');
                    \Log::info('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> BASE DE DATO Numero ' . $index . ' de ' . $nDatabases . ' : ' . $db . ' en proceso <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
                    DB::purge('temp');
                    if (in_array($servidor, [
                        '0.0.0.0',
                        '10.0.0.7',
                        'demo-db-cluster.cluster-cnvvsgytawik.us-east-2.rds.amazonaws.com-mal',
                        'ec2-35-163-242-38.us-west-2.compute.amazonaws.com',
                        'demos1.cnvvsgytawik.us-east-2.rds.amazonaws.com',
                        'produccion.cgog1qhbqtxl.us-west-2.rds.amazonaws.com',
                        "pos3.cnvvsgytawik.us-east-2.rds.amazonaws.com",
                        "pos5.cnvvsgytawik.us-east-2.rds.amazonaws.com"
                    ])) {
                        continue;
                    }
                    echo "Intentando COnectar \n";
                    // Conexión temporal a mysql para verificar la existencia de la base de datos
                    config(['database.connections.temp_check' => [
                        'driver' => 'mysql',
                        'host' => $servidor,
                        'database' => null, // No se especifica la base de datos
                        'username' => $usuario,
                        'password' => $clave,
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                        'strict' => false,
                    ]]);

                    // Forzar reconexión
                    DB::purge('temp_check');

                    $existeBeta = @DB::connection('temp_check')->select("SHOW DATABASES LIKE '" . $db . "'");

                    if (count($existeBeta) == 0) {
                        echo $this->colorize("No EXISTE LA BASE \n",'yellow');
                        echo "\n";
                        continue;
                    }

                    config(['database.connections.temp' => [
                        'driver' => 'mysql',
                        'host' => $servidor,
                        'database' => $db,
                        'username' => $usuario,
                        'password' => $clave,
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                        'strict' => false,
                    ]]);

                    // Forzar reconexión
                    DB::purge('temp');


                    if (isset($dump)) {
                        $sql_array = explode(';', $dump);
                        DB::connection('temp')->statement("SET FOREIGN_KEY_CHECKS = 0");

                        foreach ($sql_array as $query) {
                            $query = trim($query);
                            if ($query != "") {
                                try {
                                    $venta = DB::connection('temp')->select($query);
                                    if (!empty($venta)) {
                                        echo $this->colorize("Se encontro la factura en la base \n \n",'green');
                                        // Si hay registros, entra aquí
                                        echo $this->colorize("$servidor \n",'green');
                                        echo $this->colorize("$db \n",'green');
                                        echo $this->colorize("$user_admin \n",'green');
                                          // Detener la ejecución del ciclo y de la función completa
                                        return;
                                    } else {
                                        // No se encontraron registros
                                        echo $this->colorize("No se encontró la factura DPE3437 en la base de datos $db \n", 'red');
                                    }
                                   // echo  $this->colorize($index . ' de ' . $nDatabases . ' | ' . $query . " Se ejecuto \n",'green');
                                  
                                    $indicesAgregados++;
                                    \Log::info($index . ' de ' . $nDatabases . ' | ' . $query . ' Se ejecuto');
                                    
                                  //  sleep(1);
                                } catch (\Exception $e) {

                                    \Log::error($index . ' de ' . $nDatabases . ' | ' . $query);
                                    echo $this->colorize("ERROR EN ".$index . ' de ' . $nDatabases . ' | ' . $query, 'red');
                                    echo "\n";
                                    $errors .= 'error >>>>>>>>>>>>> ' . $e->getMessage() . ' <<<<<<<<<<<<<';
                                    echo  $this->colorize('error >>>>>>>>>>>>> ' . $e->getMessage() . ' <<<<<<<<<<<<<','red');
                                    echo "\n";
                                 //   sleep(1);
                                }
                            }
                        }

                        DB::connection('temp')->statement("SET FOREIGN_KEY_CHECKS = 1");
                    }

                   
                }

              
            }
            echo "\n";
            echo $this->colorize("Se completaron $indicesAgregados INDICES", 'green');
        }
    }

    public function processDatabases()
    {
        DB::connection('vendty2')->statement("SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode, 'ONLY_FULL_GROUP_BY', ''))");

        $sql = "SELECT username, email, base_dato, d.servidor AS servidor, d.usuario AS usuario, d.clave AS clave, l.id_almacen
                FROM vendty2.users AS u
                INNER JOIN vendty2.db_config AS d ON u.db_config_id = d.id
                INNER JOIN vendty2.crm_licencias_empresa AS l ON d.id = l.id_db_config
                WHERE u.is_admin = 't'
                AND servidor IN ('pos5v8.cnvvsgytawik.us-east-2.rds.amazonaws.com', 'pos3v8.cnvvsgytawik.us-east-2.rds.amazonaws.com','demo24.cnvvsgytawik.us-east-2.rds.amazonaws.com')
                GROUP BY u.db_config_id";

        // Ejecuta la consulta directamente sin DB::raw()
        $databases = DB::connection('vendty2')->select($sql);
        $nDatabases = count($databases);
        $index = 0;
        $dump = "
        ALTER TABLE `devoluciones` DROP FOREIGN KEY `fk_devoluciones_venta`;
        ALTER TABLE `devoluciones` 
        ADD CONSTRAINT `fk_devoluciones_venta`
        FOREIGN KEY (`factura`)
        REFERENCES `venta` (`factura`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
      
            
       

        ";
       
        /*

          CREATE INDEX idx_proformas_fecha_almacen_notas
        ON proformas (fecha_crea_gasto, id_almacen, notas(11));
        ALTER TABLE proformas ADD FULLTEXT idx_proformas_notas (notas);

        0b3ffb0d5a92381e3cf12ed1a16923f6bc216e94 fix nota 

         012ea7d6939165192fc51700f99edb8f81be9441 optimiza fe serial
        d0ace89a4e5d54a915f179a0bbea71c106d15410 cache infocontroller
        9b0e3e8efc194dc2832b160734122b9557dbe91b consultas dashboard

                CREATE INDEX idx_factura_factura_electronica
        ON venta (factura, factura_electronica);
        CREATE INDEX idx_venta_fecha_estado_almacen ON venta (fecha, estado, almacen_id);
        CREATE INDEX idx_ventas_pago_id_venta_forma_pago ON ventas_pago (id_venta, forma_pago);
        CREATE INDEX idx_producto_categoria_id_activo ON producto (categoria_id, activo);
        CREATE INDEX idx_stock_actual_producto_id ON stock_actual (producto_id);
        CREATE INDEX idx_usuario_almacen_almacen_id_usuario_id ON usuario_almacen (almacen_id, usuario_id);
        CREATE INDEX idx_producto_material_subrecetas_activo ON producto (material, subrecetas, activo);

        CREATE INDEX idx_notacredito_factura_id ON notacredito(factura_id);
        CREATE INDEX idx_venta_estado_almacen_factura ON venta(estado, almacen_id, factura_electronica, fecha);
        CREATE INDEX idx_venta_fecha_estado_almacen ON venta(fecha, estado, almacen_id);
        CREATE INDEX idx_ventas_pago_id_venta_forma_pago ON ventas_pago(id_venta, forma_pago);
        CREATE UNIQUE INDEX idx_impuesto_id_impuesto ON impuesto(id_impuesto);
        CREATE INDEX idx_detalle_venta_venta_id_nombre_producto 
        ON detalle_venta(venta_id, nombre_producto);
        CREATE INDEX idx_producto_ref_atributo_id_detalle_id 
        ON producto_referencia_atributo_detalle(producto_referencia_atributo_id, id);
        CREATE INDEX idx_prod_ref_atributo_detalle_prod 
        ON producto_referencia_atributo_detalle_producto(producto_referencia_atributo_detalle1_id, producto_id);
        CREATE INDEX idx_producto_condiciones 
        ON producto(tienda, activo, material, nombre, precio_venta);
         CREATE INDEX idx_producto_material_subrecetas_activo ON producto(material, subrecetas, activo);
        CREATE INDEX idx_categoria_activo ON categoria(activo);
        CREATE INDEX idx_stock_actual_producto_almacen ON stock_actual(producto_id, almacen_id);
        CREATE INDEX idx_usuario_almacen_usuario_almacen ON usuario_almacen(usuario_id, almacen_id);


        CREATE INDEX idx_nombre_opcion ON opciones (nombre_opcion);
        CREATE INDEX idx_producto_seriales_producto_almacen 
        ON producto_seriales (id_producto, almacen_id, serial);
        CREATE INDEX idx_producto_nombre_codigo_activo 
        ON producto (activo, nombre, codigo);

        */
        $indicesAgregados = 0;
        if (!empty($databases)) {
            $reached = false;
            foreach ($databases as $database) {
                $index++;
                if ($index) {
                    /*if($index > 9300 && $reached === false){
                        $ya = true;
                        $dump .= "
                        CREATE INDEX idx_producto_material_subrecetas_activo ON producto(material, subrecetas, activo);
                        CREATE INDEX idx_categoria_activo ON categoria(activo);
                        CREATE INDEX idx_stock_actual_producto_almacen ON stock_actual(producto_id, almacen_id);
                        CREATE INDEX idx_usuario_almacen_usuario_almacen ON usuario_almacen(usuario_id, almacen_id);
                        CREATE INDEX idx_venta_fecha_almacen_estado 
                            ON venta(fecha, almacen_id, estado);
                       CREATE INDEX idx_venta_factura_factura_antes 
                        ON venta(factura, factura_antes);
                        CREATE INDEX idx_movimientos_cierre_caja_id_mov_tip_id_cierre 
                        ON movimientos_cierre_caja(id_mov_tip, id_cierre);
                        

                         ";
                    }*/
                    $errors = '';
                    $username_admin = $database->username;
                    $user_admin = $database->email;
                    $db = $database->base_dato;
                    $id_almacen = $database->id_almacen;
                    $usuario = $database->usuario;
                    $clave = $database->clave;
                    $servidor = $database->servidor;

                    dump($servidor);
                    dump($db);
                    dump($user_admin);
                    echo $this->colorize('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> BASE DE DATO Numero ' . $index . ' de ' . $nDatabases . ' : ' . $db . ' en proceso <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<  \n','blue');
                    \Log::info('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> BASE DE DATO Numero ' . $index . ' de ' . $nDatabases . ' : ' . $db . ' en proceso <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
                    DB::purge('temp');
                    if (in_array($servidor, [
                        '0.0.0.0',
                        '10.0.0.7',
                        'demo-db-cluster.cluster-cnvvsgytawik.us-east-2.rds.amazonaws.com-mal',
                        'ec2-35-163-242-38.us-west-2.compute.amazonaws.com',
                        'demos1.cnvvsgytawik.us-east-2.rds.amazonaws.com',
                        'produccion.cgog1qhbqtxl.us-west-2.rds.amazonaws.com',
                        "pos3.cnvvsgytawik.us-east-2.rds.amazonaws.com",
                        "pos5.cnvvsgytawik.us-east-2.rds.amazonaws.com"
                    ])) {
                        continue;
                    }
                    echo "Intentando COnectar \n";
                    // Conexión temporal a mysql para verificar la existencia de la base de datos
                    config(['database.connections.temp_check' => [
                        'driver' => 'mysql',
                        'host' => $servidor,
                        'database' => null, // No se especifica la base de datos
                        'username' => $usuario,
                        'password' => $clave,
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                        'strict' => false,
                    ]]);

                    // Forzar reconexión
                    DB::purge('temp_check');

                    $existeBeta = @DB::connection('temp_check')->select("SHOW DATABASES LIKE '" . $db . "'");

                    if (count($existeBeta) == 0) {
                        echo $this->colorize("No EXISTE LA BASE \n",'yellow');
                        echo "\n";
                        continue;
                    }

                    config(['database.connections.temp' => [
                        'driver' => 'mysql',
                        'host' => $servidor,
                        'database' => $db,
                        'username' => $usuario,
                        'password' => $clave,
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                        'strict' => false,
                    ]]);

                    // Forzar reconexión
                    DB::purge('temp');


                    if (isset($dump)) {
                        $sql_array = explode(';', $dump);
                        DB::connection('temp')->statement("SET FOREIGN_KEY_CHECKS = 0");

                        foreach ($sql_array as $query) {
                            $query = trim($query);
                            if ($query != "") {
                                try {
                                    DB::connection('temp')->statement($query);
                                    echo  $this->colorize($index . ' de ' . $nDatabases . ' | ' . $query . " Se ejecuto \n",'green');
                                    echo "\n";
                                    $indicesAgregados++;
                                    \Log::info($index . ' de ' . $nDatabases . ' | ' . $query . ' Se ejecuto');
                                  //  sleep(1);
                                } catch (\Exception $e) {

                                    \Log::error($index . ' de ' . $nDatabases . ' | ' . $query);
                                    echo $this->colorize("ERROR EN ".$index . ' de ' . $nDatabases . ' | ' . $query, 'red');
                                    echo "\n";
                                    $errors .= 'error >>>>>>>>>>>>> ' . $e->getMessage() . ' <<<<<<<<<<<<<';
                                    echo  $this->colorize('error >>>>>>>>>>>>> ' . $e->getMessage() . ' <<<<<<<<<<<<<','red');
                                    echo "\n";
                                 //   sleep(1);
                                }
                            }
                        }

                        DB::connection('temp')->statement("SET FOREIGN_KEY_CHECKS = 1");
                    }

                    if ($errors == '') {
                        echo 'La base de datos se le aplico indexes completos ' . $database->base_dato . ' EXISTE'.' \n';
                        echo "\n";
                        \Log::info('La base de datos se le aplico indexes completos ' . $database->base_dato . ' EXISTE'.' \n');
                    } else {
                        \Log::error('El script tuvo algunos errores: ' . $errors);
                    }
                }

              
            }
            echo "\n";
            echo $this->colorize("Se completaron $indicesAgregados INDICES", 'green');
        }
    }
    public function applyForeignKeyChange($recordId,$conexion) {
        
    
        if (!isset($this->appliedChanges)) {
            $this->appliedChanges = [];
        }
    
        // Crea una clave única para cada registro basado en el ID
        $key = $recordId;
    
        // Verificar si el cambio ya se aplicó
        if (isset($this->appliedChanges[$key])) {
            echo "El cambio de clave foránea ya fue aplicado para el registro $recordId en la tabla.\n";
            return;
        }
    
       // Verificar si la clave foránea ya existe en la tabla `devoluciones`
        $query = "SELECT COUNT(*) AS count 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_TYPE = 'FOREIGN KEY' 
        AND TABLE_NAME = 'devoluciones' 
        AND CONSTRAINT_NAME = 'fk_devoluciones_venta'";

        $stmt = $conexion->query($query);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row['count'] > 0) {
            echo "La clave foránea `fk_devoluciones_venta` ya existe. Procediendo a eliminarla.\n";
            $conexion->exec("ALTER TABLE `devoluciones` DROP FOREIGN KEY `fk_devoluciones_venta`;");
        } else {
            echo "La clave foránea `fk_devoluciones_venta` no existe en la tabla `devoluciones`. No se realizó la eliminación.\n";
        }
    
        // Agregar la clave foránea nuevamente
        $conexion->query("ALTER TABLE `devoluciones` 
            ADD CONSTRAINT `fk_devoluciones_venta`
            FOREIGN KEY (`factura`)
            REFERENCES `venta` (`factura`)
            ON DELETE CASCADE
            ON UPDATE CASCADE;");
    
        // Registrar el cambio en el arreglo
        $this->appliedChanges[$key] = true;
    
        echo "Cambio registrado en el arreglo de cambios aplicados.\n";
    }
      public function borrarRegistrosviejos() { // esta se ejecuta atualmente en el cron
        $conerror = 0;
        $hoy = date('Y-m-d');
     
        $arrayToMail = [];
        //eliminar duplicadas
     
      
      

        // Eliminar los 10,000 registros más antiguos de hace más de 2 meses
        $fechaLimite = date('Y-m-d', strtotime('-10 days'));

        // Ejecutar la eliminación
        DB::connection('VendtyServices')->statement("
            DELETE FROM electronic_invoice_sender
            ORDER BY creacion ASC
            LIMIT 10000
        ");
    
        // Reenviar con la emision alternativa las que fallaron
        // Reparar Facturas con precios decimales que no pasan
        \Log::info("Finalizado el envio correctamente");
        \Log::info("---------------------------------------------------------");
        echo $this->colorize("Finalizado el envio correctamente","green"); echo "\n";
        echo "\n";
        // Intentar pasar las facturas
        // $this->reenviarFacturasDecimalesMalosReparaas();
        
      
    }
    public function FacturasPendientesXEnviar() { // esta se ejecuta atualmente en el cron
        $conerror = 0;
        $hoy = date('Y-m-d');
     
        $arrayToMail = [];
        //eliminar duplicadas
        DB::connection('VendtyServices')->statement("DELETE FROM electronic_invoice_sender
        WHERE id NOT IN (
            SELECT id FROM (
                SELECT MIN(id) AS id
                FROM electronic_invoice_sender
                GROUP BY base_dato, venta_id
            ) AS subquery
        )");
      
        $porEnviar = ElectronicInvoiceSender::query()
            ->where('base_dato', '!=', 23560)
            ->where('estado', 12)
          //  ->where('tipo_facturacion', 'siigo')
            ->where('respuesta'," El campo number es obligatorio.,")
            ->where('enviando',  0)
            ->where(function ($query) {
                $hasta = strtotime('-20 days');
                $query->where('creacion', '>=', date('Y-m-d', $hasta))
                        ->orWhere('updated_at', '>=', date('Y-m-d', $hasta));
            })
            
            ->limit(5000)
            ->get();

        // Actualiza en bloque el campo 'enviando' a 1
        ElectronicInvoiceSender::query()
        ->whereIn('id', $porEnviar->pluck('id'))
        ->update(['enviando' => 1]);
        $hasta = strtotime('-20 days');
        \Log::info("Comienza el Envio de Facturas");
        $total= count($porEnviar);
       //    \Log::info("-----------------------" . count($porEnviar)." Fecha: ".$hasta."----------------------------------");
        echo $this->colorize( "\n o------------------------o----------------------------o \n",'green') ;
        echo $this->colorize( " SIN LIMITE | Num facts " .  $total." Fecha: ".$hasta,'green') ;
        echo $this->colorize( "\n o------------------------o----------------------------o \n",'green') ;
        \Log::info(date('Y-m-d H:i:s')." ----->  SIN LIMITE | Num facts " .  $total." Fecha: ".$hasta);
       
        if ($porEnviar->count() > 0) {
            foreach ($porEnviar as $index => $pendiente) {
                if($pendiente->creacion <= '2024-06-18'){
                    $tokenNuevo = @ElectronicInvoiceSender::query()->where('base_dato',  $pendiente->base_dato)->where('creacion', '>=', '2024-06-19')->first()->token;
                    if($tokenNuevo){
                        $pendiente->token = $tokenNuevo;
                        $pendiente->save();
                        $token = $tokenNuevo;
                    }else{
                        continue;
                    }
                   

                }

                // if($index <= 1420){
                //     continue;
                // }
                $fecha = date('Y-m-d H:i:s');
                echo $this->colorize( "\n " . $index. " de: ".count($porEnviar),'yellow');
                echo "\n";

                if (!is_null($pendiente->respuesta)&&strpos($pendiente->respuesta, "The id is inactive") !== false) {
                    echo $this->colorize("ID INACTIVO \n ",'red');
                    $pendiente->update(['estado' => 12]);
                    continue;
                }
                $token = null;
                if ($pendiente->tipo_facturacion != 'vendty') {
                    if ($pendiente->token == null || $pendiente->token == '') {
                        $TokenDB = ElectronicInvoiceSender::where('token', '!=', null)
                            ->where('base_dato', $pendiente->base_dato)
                            ->where('tipo_facturacion', $pendiente->tipo_facturacion)
                            ->orderBy('id', 'DESC')
                            ->first();
                        $token = $TokenDB->token;
                        ElectronicInvoiceSender::where('token', null)
                            ->where('base_dato', $pendiente->base_dato)
                            ->where('tipo_facturacion', $pendiente->tipo_facturacion)
                            ->update(['token' => $token]);
                        echo "\n";
                        echo $this->colorize(" Token Actualizado ",'magenta');
                        echo "\n";
                     
                    } else {
                        $token = $pendiente->token;
                    }
    
                    $venta_id = $pendiente->venta_id;
                    $db_config_id = $pendiente->base_dato;
                    $tipo_facturacion = $pendiente->tipo_facturacion;
                    $db_config = DBConfig::find($db_config_id);
    
                    // CONEXION CON BASE DE DATOS CLIENTE
                    $servidor = $db_config->servidor;
                    $base_dato = $db_config->base_dato;
                    $usuario = $db_config->usuario;
                    $clave = $db_config->clave;
    
                    if(true){ // quite la validacion para q no consultara demo24
                        $dns = "mysql:host=$servidor;dbname=$base_dato;charset=utf8";
                        echo "\n $dns \n";
                        
                        // Usar PDO para conectar a la base de datos del cliente
                        try {
                            $dbConnection = new \PDO($dns, $usuario, $clave);
                        } catch (\PDOException $e) {
                            echo "Connection failed: " . $e->getMessage(); echo "\n";
                            continue;
                        }

                     //   $this->applyForeignKeyChange($pendiente->base_dato,$dbConnection);
                        $checkAliado = $dbConnection->query("SELECT * FROM almacen ORDER BY id ASC")->fetch(\PDO::FETCH_OBJ);
    
                        echo "\n";
    
                        echo "$fecha | CONSULTANDO : DB-CONFIG: $db_config_id | VENTA-ID $venta_id | Identificador $pendiente->id";
                   
                        if($pendiente->tipo_facturacion == 'docsoporte'){
                            $documento = $dbConnection->query("SELECT * FROM support_document WHERE id = ".$venta_id." AND `status` != -1 AND ((id_transaccion IS NULL OR id_transaccion = '') OR resp_electronic_invoice = 'ERROR')" )->fetch(\PDO::FETCH_OBJ);
                            echo $this->colorize($index." de ".  $total. "| FECHA documento: ".@$documento->fecha,'green'); echo "\n";
                            echo $this->colorize($index." de ".  $total. "| Resp Documento: ".@$documento->resp_electronic_invoice,'green'); echo "\n";
                          
                            if (isset($documento->id) && $documento->id_transaccion == null || isset($documento->id) && $documento->resp_electronic_invoice == 'ERROR' ) {
                                
                                $response = $this->postCurl('emite-docso-propia/' . $venta_id,[], $token);
                                echo $this->colorize($index." de ".  $total. " | RESP: PASO! " . json_encode($response) . "\n","green");
                                $pendiente->update([
                                    'respuesta' => 'SE ENVIO',
                                    'estado' => 1,
                                    'comentario' => 'Se envio',
                                    'ultimo_intento' => $fecha,
                                ]);
                                echo $this->colorize($index." de ".  $total. " Se envo la factura PROPIA","green");
                                \Log::info($index." de ".  $total. " Enviada Factura Propia");
                            } else {
                                $pendiente->update([
                                    'respuesta' => 'YA FUE ENVIADO O ESTA ANULADA',
                                    'estado' => 10,
                                    'comentario' => 'YA FUE ENVIADO O ESTA ANULADA',
                                    'ultimo_intento' => $fecha,
                                ]);
                                echo $this->colorize($index." de ".  $total. " | RESP: " . 'YA FUE ENVIADO O ESTA ANULADA' . "\n",'red');
                                \Log::error($index." de ".  $total. " YA FUE ENVIADA");
                            }
                           
                        }else{
                            $venta = $dbConnection->query("SELECT * FROM venta WHERE id = ".$venta_id." AND estado != -1 AND (id_transaccion IS NULL OR resp_electronic_invoice = 'ERROR')" )->fetch(\PDO::FETCH_OBJ);
                      

                            echo $this->colorize($index." de ".  $total. "| FECHA VENTA: ".@$venta->fecha,'green'); echo "\n";
                            echo $this->colorize($index." de ".  $total. "| Resp VENTA: ".@$venta->resp_electronic_invoice,'green'); echo "\n";
                            echo $this->colorize($index." de ".  $total. "| ALIADO VENTA: ".@$venta->aliado,'green'); echo "\n";
                            if (isset($venta->id) && $venta->id_transaccion == null || isset($venta->id) && $venta->resp_electronic_invoice == 'ERROR' || $pendiente->tipo_facturacion == 'guatemala') {
                                
                                if ($pendiente->tipo_facturacion == 'siigo') {
                                    $response = $this->getCurlSiigo('reemite/' . $venta_id, $token);
                                    $this->handleSiigoResponse($response, $pendiente, $venta_id, $fecha);
                                    \Log::info($index." de ".  $total. " Enviada Siigo");
                                } elseif ($pendiente->tipo_facturacion == 'fpropia') {
                                    $response = $this->postBetaCurl('emite-propia-directo/' . $venta_id,[], $token);
                                 

                                    if( isset($response->error)&&$response->error==true ){
                                 
                                        if(is_array($response->validation_errors)){
                                            $mnsg='';
                                            foreach($response->validation_errors as $error){
                                                if($error->field == "invoice_lines"){
                                                    $mnsg.=" El campo de productos es obligatorio,";
                                                }else{
                                                    $mnsg.=" $error->error,";
                                                }
                                            
                                            }
                                            $pendiente->update([
                                                'respuesta' => $mnsg,
                                                'estado' => 12,
                                                'comentario' => 'error envio',
                                                'ultimo_intento' => $fecha,
                                            ]);
                                            \Log::info($index." de ".  $total. " ".$mnsg. " Estado 12");
                                        }else{
                                            $pendiente->update([
                                                'respuesta' => $response->msg,
                                                'estado' => 0,
                                                'comentario' => 'error envio el servicio esta caido',
                                                'ultimo_intento' => $fecha,
                                            ]);
                                            \Log::info($index." de ".  $total. " ".$response->msg. " en cola todavia");
                                        }
                                       
                                      
                                    }else{
                                     
                                        if(isset($response->response->success)&& $response->response->success == false){
                                            if($response->response->message){
                                                $pendiente->update([
                                                    'respuesta' => $response->response->message,
                                                    'estado' => 0,
                                                    'comentario' => 'SE ENVIO',
                                                    'ultimo_intento' => $fecha,
                                                ]);
                                              //  echo $this->colorize($index." de ".  $total. " | RESP: PASO! " . json_encode($response) . "\n","green");
                                              \Log::info($index." de ".  $total. " ".$response->response->message);
                                            }
                                           
                                        }else{
                                            $pendiente->update([
                                                'respuesta' => 'SE ENVIO',
                                                'estado' => 1,
                                                'comentario' => 'SE ENVIO',
                                                'ultimo_intento' => $fecha,
                                            ]);
                                            echo $this->colorize($index." de ".  $total. " | RESP: PASO! " . json_encode($response) . "\n","green");
                                            \Log::info($index." de ".  $total. " SE ENVIO");
                                        }
                                     
                                    }


                                 
                                    echo $this->colorize($index." de ".  $total. " Se envo la factura PROPIA","green");
                                    \Log::info($index." de ".  $total. " Enviada Factura Propia");
                                } elseif ($pendiente->tipo_facturacion == 'guatemala') {
                                     $response = $this->getCurlGuatemala('emite/' . $venta_id, $token);
                                     $pendiente->update([
                                        'respuesta' => 'SE ENVIO',
                                        'estado' => 1,
                                        'comentario' => 'SE ENVIO',
                                        'ultimo_intento' => $fecha,
                                    ]);
                                    \Log::info($index." de ".  $total. " Enviada Guatemala");
                                } else {
                                    $response = $this->getCurl('generate/' . $venta_id, $token);
                                    $pendiente->update([
                                        'respuesta' => 'SE ENVIO',
                                        'estado' => 1,
                                        'comentario' => 'SE ENVIO',
                                        'ultimo_intento' => $fecha,
                                    ]);
                                    echo $this->colorize($index." de ".  $total. "Se envo la factura B2B","green");
                                    \Log::info($index." Enviada Factura Facturaxion");
                                }
                            } else {
                                $pendiente->update([
                                    'respuesta' => 'YA FUE ENVIADO O ESTA ANULADA',
                                    'estado' => 10,
                                    'comentario' => 'YA FUE ENVIADO O ESTA ANULADA 2',
                                    'ultimo_intento' => $fecha,
                                ]);
                                echo $this->colorize($index." de ".  $total. " | RESP: " . 'YA FUE ENVIADO O ESTA ANULADA' . "\n",'red');
                                \Log::error($index." de ".  $total. " YA FUE ENVIADA");
                            }
                            
                        }
                        sleep(2);
                    } else {
                        $pendiente->update([
                            'respuesta' => "esta en demo",
                            'estado' => 22,
                            'ultimo_intento' => $fecha,
                        ]);
                    }
    
                } else {
                    $response = $this->postCurl('generateVendty', $pendiente->data_to_send, $token);
                    if ($response->codigoRespuesta == '01') {
                        echo $this->colorize($index." de ".  $total. " | RESP: " . 'Se envio la factura Vendty, se esperan 25 segundos para la respuesta' . "\n",'green');
                        sleep(25);
                        $response2 = $this->postCurl('requestVendty', json_encode($response));
                        if ($response2->RWS->codigoRespuesta == '01') {
                            $documento = $response2->RWS->Documento;
                            $this->storeInvoice($documento, $pendiente->id);
                        }
                    } else {
                        $pendiente->update([
                            'respuesta' => json_encode($response),
                            'estado' => 0,
                            'ultimo_intento' => $fecha,
                        ]);
                    }
                }
            }
        } else {
            echo $this->colorize("NO HAY FACTURAS POR ENVIAR","green"); echo "\n";
        }

        ElectronicInvoiceSender::query()
        ->whereIn('id', $porEnviar->pluck('id'))
        ->update(['enviando' => 0,'comentario' => "Update 0"]);

        // Eliminar los 10,000 registros más antiguos de hace más de 2 meses
        $fechaLimite = date('Y-m-d', strtotime('-2 months'));

        // Ejecutar la eliminación
        DB::connection('VendtyServices')->statement("
            DELETE FROM electronic_invoice_sender
            WHERE creacion < ? 
            ORDER BY creacion ASC
            LIMIT 10000
        ", [$fechaLimite]);
    
        // Reenviar con la emision alternativa las que fallaron
        // Reparar Facturas con precios decimales que no pasan
        \Log::info("Finalizado el envio correctamente");
        \Log::info("---------------------------------------------------------");
        echo $this->colorize("Finalizado el envio correctamente","green"); echo "\n";
        echo "\n";
        // Intentar pasar las facturas
        // $this->reenviarFacturasDecimalesMalosReparaas();
        
      
    }
    public function FacturasSiigoPendientesXEnviar() { // esta se ejecuta atualmente en el cron actua
        $conerror = 0;
        $hoy = date('Y-m-d');
     
        $arrayToMail = [];
        //eliminar duplicadas
        // DB::connection('VendtyServices')->statement("DELETE FROM electronic_invoice_sender
        // WHERE id NOT IN (
        //     SELECT id FROM (
        //         SELECT MIN(id) AS id
        //         FROM electronic_invoice_sender
        //         GROUP BY base_dato, venta_id
        //     ) AS subquery
        // )");
      
        $porEnviar = ElectronicInvoiceSender::query()
            ->where('base_dato', '!=', 23560)
            ->where('estado',  0)
         //   ->where('tipo_facturacion','!=', 'fpropia')
            ->where('enviando',  0)
            ->where(function ($query) {
                $hasta = strtotime('-90 days');
                $query->where('creacion', '>=', date('Y-m-d', $hasta))
                        ->orWhere('updated_at', '>=', date('Y-m-d', $hasta));
            })
            
            ->limit(1000)
            ->get();

        // Actualiza en bloque el campo 'enviando' a 1
        ElectronicInvoiceSender::query()
        ->whereIn('id', $porEnviar->pluck('id'))
        ->update(['enviando' => 1]);
        $hasta = strtotime('-90 days');
        \Log::info("Comienza el Envio de Facturas");
        $total= count($porEnviar);
       //    \Log::info("-----------------------" . count($porEnviar)." Fecha: ".$hasta."----------------------------------");
        echo $this->colorize( "\n o------------------------o----------------------------o \n",'green') ;
        echo $this->colorize( " SIN LIMITE | Num facts " .  $total." Fecha: ".$hasta,'green') ;
        echo $this->colorize( "\n o------------------------o----------------------------o \n",'green') ;
        \Log::info(date('Y-m-d H:i:s')." ----->  SIN LIMITE | Num facts " .  $total." Fecha: ".$hasta);
       
        if ($porEnviar->count() > 0) {
            foreach ($porEnviar as $index => $pendiente) {
                if($pendiente->creacion <= '2024-06-18'){
                    $tokenNuevo = @ElectronicInvoiceSender::query()->where('base_dato',  $pendiente->base_dato)->where('creacion', '>=', '2024-06-19')->first()->token;
                    if($tokenNuevo){
                        $pendiente->token = $tokenNuevo;
                        $pendiente->save();
                        $token = $tokenNuevo;
                    }else{
                        continue;
                    }
                   

                }
                $fecha = date('Y-m-d H:i:s');
                echo $this->colorize( "\n " . $index. " de: ".count($porEnviar),'yellow');
                echo "\n";

                if (!is_null($pendiente->respuesta)&&strpos($pendiente->respuesta, "The id is inactive") !== false) {
                    echo $this->colorize("ID INACTIVO \n ",'red');
                    $pendiente->update(['estado' => 12]);
                    continue;
                }
                $token = null;
                if ($pendiente->tipo_facturacion != 'vendty') {
                    if ($pendiente->token == null || $pendiente->token == '') {
                        $TokenDB = ElectronicInvoiceSender::where('token', '!=', null)
                            ->where('base_dato', $pendiente->base_dato)
                            ->where('tipo_facturacion', $pendiente->tipo_facturacion)
                            ->orderBy('id', 'DESC')
                            ->first();
                        $token = $TokenDB->token;
                        ElectronicInvoiceSender::where('token', null)
                            ->where('base_dato', $pendiente->base_dato)
                            ->where('tipo_facturacion', $pendiente->tipo_facturacion)
                            ->update(['token' => $token]);
                        echo "\n";
                        echo $this->colorize(" Token Actualizado ",'magenta');
                        echo "\n";
                     
                    } else {
                        $token = $pendiente->token;
                    }
    
                    $venta_id = $pendiente->venta_id;
                    $db_config_id = $pendiente->base_dato;
                    $tipo_facturacion = $pendiente->tipo_facturacion;
                    $db_config = DBConfig::find($db_config_id);
    
                    // CONEXION CON BASE DE DATOS CLIENTE
                    $servidor = $db_config->servidor;
                    $base_dato = $db_config->base_dato;
                    $usuario = $db_config->usuario;
                    $clave = $db_config->clave;
    
                    if(true){ // quite la validacion para q no consultara demo24
                        $dns = "mysql:host=$servidor;dbname=$base_dato;charset=utf8";
                        echo "\n $dns \n";
                        
                        // Usar PDO para conectar a la base de datos del cliente
                        try {
                            $dbConnection = new \PDO($dns, $usuario, $clave);
                        } catch (\PDOException $e) {
                            echo "Connection failed: " . $e->getMessage(); echo "\n";
                            continue;
                        }
        
                        $checkAliado = $dbConnection->query("SELECT * FROM almacen ORDER BY id ASC")->fetch(\PDO::FETCH_OBJ);
    
                        echo "\n";
    
                        echo "$fecha | CONSULTANDO : DB-CONFIG: $db_config_id | VENTA-ID $venta_id | Identificador $pendiente->id";
                   
                        if($pendiente->tipo_facturacion == 'docsoporte'){
                            $documento = $dbConnection->query("SELECT * FROM support_document WHERE id = ".$venta_id." AND `status` != -1 AND ((id_transaccion IS NULL OR id_transaccion = '') OR resp_electronic_invoice = 'ERROR')" )->fetch(\PDO::FETCH_OBJ);
                            echo $this->colorize($index." de ".  $total. "| FECHA documento: ".@$documento->fecha,'green'); echo "\n";
                            echo $this->colorize($index." de ".  $total. "| Resp Documento: ".@$documento->resp_electronic_invoice,'green'); echo "\n";
                          
                            if (isset($documento->id) && $documento->id_transaccion == null || isset($documento->id) && $documento->resp_electronic_invoice == 'ERROR' ) {
                                
                                $response = $this->postCurl('emite-docso-propia/' . $venta_id,[], $token);
                                echo $this->colorize($index." de ".  $total. " | RESP: PASO! " . json_encode($response) . "\n","green");
                                $pendiente->update([
                                    'respuesta' => 'SE ENVIO',
                                    'estado' => 1,
                                    'comentario' => 'Se envio',
                                    'ultimo_intento' => $fecha,
                                ]);
                                echo $this->colorize($index." de ".  $total. " Se envo la factura PROPIA","green");
                                \Log::info($index." de ".  $total. " Enviada Factura Propia");
                            } else {
                                $pendiente->update([
                                    'respuesta' => 'YA FUE ENVIADO O ESTA ANULADA',
                                    'estado' => 10,
                                    'comentario' => 'YA FUE ENVIADO O ESTA ANULADA',
                                    'ultimo_intento' => $fecha,
                                ]);
                                echo $this->colorize($index." de ".  $total. " | RESP: " . 'YA FUE ENVIADO O ESTA ANULADA' . "\n",'red');
                                \Log::error($index." de ".  $total. " YA FUE ENVIADA");
                            }
                           
                        }else{
                            $venta = $dbConnection->query("SELECT * FROM venta WHERE id = ".$venta_id." AND estado != -1 AND (id_transaccion IS NULL OR resp_electronic_invoice = 'ERROR')" )->fetch(\PDO::FETCH_OBJ);
                      

                            echo $this->colorize($index." de ".  $total. "| FECHA VENTA: ".@$venta->fecha,'green'); echo "\n";
                            echo $this->colorize($index." de ".  $total. "| Resp VENTA: ".@$venta->resp_electronic_invoice,'green'); echo "\n";
                            echo $this->colorize($index." de ".  $total. "| ALIADO VENTA: ".@$venta->aliado,'green'); echo "\n";
                            if (isset($venta->id) && $venta->id_transaccion == null || isset($venta->id) && $venta->resp_electronic_invoice == 'ERROR' || $pendiente->tipo_facturacion == 'guatemala') {
                                
                                if ($pendiente->tipo_facturacion == 'siigo') {
                                    $response = $this->getCurlSiigo('reemite/' . $venta_id, $token);
                                    $this->handleSiigoResponse($response, $pendiente, $venta_id, $fecha);
                                    \Log::info($index." de ".  $total. " Enviada Siigo");
                                } elseif ($pendiente->tipo_facturacion == 'fpropia') {
                                    $response = $this->postCurl('emite-propia-directo/' . $venta_id,[], $token);
                                 

                                    if( isset($response->error)&&$response->error==true ){
                                 
                                        if(is_array($response->validation_errors)){
                                            $mnsg='';
                                            foreach($response->validation_errors as $error){
                                                if($error->field == "invoice_lines"){
                                                    $mnsg.=" El campo de productos es obligatorio,";
                                                }else{
                                                    $mnsg.=" $error->error,";
                                                }
                                            
                                            }
                                            $pendiente->update([
                                                'respuesta' => $mnsg,
                                                'estado' => 12,
                                                'comentario' => 'error envio',
                                                'ultimo_intento' => $fecha,
                                            ]);
                                            \Log::info($index." de ".  $total. " ".$mnsg. " Estado 12");
                                        }else{
                                            $pendiente->update([
                                                'respuesta' => $response->msg,
                                                'estado' => 0,
                                                'comentario' => 'error envio el servicio esta caido',
                                                'ultimo_intento' => $fecha,
                                            ]);
                                            \Log::info($index." de ".  $total. " ".$response->msg. " en cola todavia");
                                        }
                                       
                                      
                                    }else{
                                     
                                        if(isset($response->response->success)&& $response->response->success == false){
                                            if($response->response->message){
                                                $pendiente->update([
                                                    'respuesta' => $response->response->message,
                                                    'estado' => 0,
                                                    'comentario' => 'SE ENVIO',
                                                    'ultimo_intento' => $fecha,
                                                ]);
                                              //  echo $this->colorize($index." de ".  $total. " | RESP: PASO! " . json_encode($response) . "\n","green");
                                              \Log::info($index." de ".  $total. " ".$response->response->message);
                                            }
                                           
                                        }else{
                                            $pendiente->update([
                                                'respuesta' => 'SE ENVIO',
                                                'estado' => 1,
                                                'comentario' => 'SE ENVIO',
                                                'ultimo_intento' => $fecha,
                                            ]);
                                            echo $this->colorize($index." de ".  $total. " | RESP: PASO! " . json_encode($response) . "\n","green");
                                            \Log::info($index." de ".  $total. " SE ENVIO");
                                        }
                                     
                                    }


                                 
                                    echo $this->colorize($index." de ".  $total. " Se envo la factura PROPIA","green");
                                    \Log::info($index." de ".  $total. " Enviada Factura Propia");
                                } elseif ($pendiente->tipo_facturacion == 'guatemala') {
                                     $response = $this->getCurlGuatemala('emite/' . $venta_id, $token);
                                     $pendiente->update([
                                        'respuesta' => 'SE ENVIO',
                                        'estado' => 1,
                                        'comentario' => 'SE ENVIO',
                                        'ultimo_intento' => $fecha,
                                    ]);
                                    \Log::info($index." de ".  $total. " Enviada Guatemala");
                                } else {
                                    $response = $this->getCurl('generate/' . $venta_id, $token);
                                    $pendiente->update([
                                        'respuesta' => 'SE ENVIO',
                                        'estado' => 1,
                                        'comentario' => 'SE ENVIO',
                                        'ultimo_intento' => $fecha,
                                    ]);
                                    echo $this->colorize($index." de ".  $total. "Se envo la factura B2B","green");
                                    \Log::info($index." Enviada Factura Facturaxion");
                                }
                            } else {
                                $pendiente->update([
                                    'respuesta' => 'YA FUE ENVIADO O ESTA ANULADA',
                                    'estado' => 10,
                                    'comentario' => 'YA FUE ENVIADO O ESTA ANULADA 2',
                                    'ultimo_intento' => $fecha,
                                ]);
                                echo $this->colorize($index." de ".  $total. " | RESP: " . 'YA FUE ENVIADO O ESTA ANULADA' . "\n",'red');
                                \Log::error($index." de ".  $total. " YA FUE ENVIADA");
                            }
                            
                        }
                        sleep(2);
                    } else {
                        $pendiente->update([
                            'respuesta' => "esta en demo",
                            'estado' => 22,
                            'ultimo_intento' => $fecha,
                        ]);
                    }
    
                } else {
                    $response = $this->postCurl('generateVendty', $pendiente->data_to_send, $token);
                    if ($response->codigoRespuesta == '01') {
                        echo $this->colorize($index." de ".  $total. " | RESP: " . 'Se envio la factura Vendty, se esperan 25 segundos para la respuesta' . "\n",'green');
                        sleep(25);
                        $response2 = $this->postCurl('requestVendty', json_encode($response));
                        if ($response2->RWS->codigoRespuesta == '01') {
                            $documento = $response2->RWS->Documento;
                            $this->storeInvoice($documento, $pendiente->id);
                        }
                    } else {
                        $pendiente->update([
                            'respuesta' => json_encode($response),
                            'estado' => 0,
                            'ultimo_intento' => $fecha,
                        ]);
                    }
                }
            }
        } else {
            echo $this->colorize("NO HAY FACTURAS POR ENVIAR","green"); echo "\n";
        }

        ElectronicInvoiceSender::query()
        ->whereIn('id', $porEnviar->pluck('id'))
        ->update(['enviando' => 0,'comentario' => "Update 0"]);

        // Eliminar los 10,000 registros más antiguos de hace más de 2 meses
        $fechaLimite = date('Y-m-d', strtotime('-10 days'));

        // Ejecutar la eliminación
        DB::connection('VendtyServices')->statement("
            DELETE FROM electronic_invoice_sender
            WHERE creacion < ? 
            ORDER BY creacion ASC
            LIMIT 10000
        ", [$fechaLimite]);
    
        // Reenviar con la emision alternativa las que fallaron
        // Reparar Facturas con precios decimales que no pasan
        \Log::info("Finalizado el envio correctamente");
        \Log::info("---------------------------------------------------------");
        echo $this->colorize("Finalizado el envio correctamente","green"); echo "\n";
        echo "\n";
        // Intentar pasar las facturas
        // $this->reenviarFacturasDecimalesMalosReparaas();
        
      
    }
    public function FacturasPropiasPendientesXEnviar() { // esta se ejecuta atualmente en el cron
        $conerror = 0;
        $hoy = date('Y-m-d');
     
        $arrayToMail = [];
        //eliminar duplicadas
        DB::connection('VendtyServices')->statement("DELETE FROM electronic_invoice_sender
        WHERE id NOT IN (
            SELECT id FROM (
                SELECT MIN(id) AS id
                FROM electronic_invoice_sender
                GROUP BY base_dato, venta_id
            ) AS subquery
        )");
      
        $porEnviar = ElectronicInvoiceSender::query()
            ->where('base_dato', '!=', 23560)
            ->where('estado',  0)
            ->where('tipo_facturacion', 'fpropia')
            ->where('enviando',  0)
            ->where(function ($query) {
                $hasta = strtotime('-20 days');
                $query->where('creacion', '>=', date('Y-m-d', $hasta))
                        ->orWhere('updated_at', '>=', date('Y-m-d', $hasta));
            })
            
            ->limit(5000)
            ->get();

        // Actualiza en bloque el campo 'enviando' a 1
        ElectronicInvoiceSender::query()
        ->whereIn('id', $porEnviar->pluck('id'))
        ->update(['enviando' => 1]);
        $hasta = strtotime('-20 days');
        \Log::info("Comienza el Envio de Facturas");
        $total= count($porEnviar);
       //    \Log::info("-----------------------" . count($porEnviar)." Fecha: ".$hasta."----------------------------------");
        echo $this->colorize( "\n o------------------------o----------------------------o \n",'green') ;
        echo $this->colorize( " SIN LIMITE | Num facts " .  $total." Fecha: ".$hasta,'green') ;
        echo $this->colorize( "\n o------------------------o----------------------------o \n",'green') ;
        \Log::info(date('Y-m-d H:i:s')." ----->  SIN LIMITE | Num facts " .  $total." Fecha: ".$hasta);
       
        if ($porEnviar->count() > 0) {
            foreach ($porEnviar as $index => $pendiente) {
                if($pendiente->creacion <= '2024-06-18'){
                    $tokenNuevo = @ElectronicInvoiceSender::query()->where('base_dato',  $pendiente->base_dato)->where('creacion', '>=', '2024-06-19')->first()->token;
                    if($tokenNuevo){
                        $pendiente->token = $tokenNuevo;
                        $pendiente->save();
                        $token = $tokenNuevo;
                    }else{
                        continue;
                    }
                   

                }
                $fecha = date('Y-m-d H:i:s');
                echo $this->colorize( "\n " . $index. " de: ".count($porEnviar),'yellow');
                echo "\n";

                if (!is_null($pendiente->respuesta)&&strpos($pendiente->respuesta, "The id is inactive") !== false) {
                    echo $this->colorize("ID INACTIVO \n ",'red');
                    $pendiente->update(['estado' => 12]);
                    continue;
                }
                $token = null;
                if ($pendiente->tipo_facturacion != 'vendty') {
                    if ($pendiente->token == null || $pendiente->token == '') {
                        $TokenDB = ElectronicInvoiceSender::where('token', '!=', null)
                            ->where('base_dato', $pendiente->base_dato)
                            ->where('tipo_facturacion', $pendiente->tipo_facturacion)
                            ->orderBy('id', 'DESC')
                            ->first();
                        $token = $TokenDB->token;
                        ElectronicInvoiceSender::where('token', null)
                            ->where('base_dato', $pendiente->base_dato)
                            ->where('tipo_facturacion', $pendiente->tipo_facturacion)
                            ->update(['token' => $token]);
                        echo "\n";
                        echo $this->colorize(" Token Actualizado ",'magenta');
                        echo "\n";
                     
                    } else {
                        $token = $pendiente->token;
                    }
    
                    $venta_id = $pendiente->venta_id;
                    $db_config_id = $pendiente->base_dato;
                    $tipo_facturacion = $pendiente->tipo_facturacion;
                    $db_config = DBConfig::find($db_config_id);
    
                    // CONEXION CON BASE DE DATOS CLIENTE
                    $servidor = $db_config->servidor;
                    $base_dato = $db_config->base_dato;
                    $usuario = $db_config->usuario;
                    $clave = $db_config->clave;
    
                    if(true){ // quite la validacion para q no consultara demo24
                        $dns = "mysql:host=$servidor;dbname=$base_dato;charset=utf8";
                        echo "\n $dns \n";
                        
                        // Usar PDO para conectar a la base de datos del cliente
                        try {
                            $dbConnection = new \PDO($dns, $usuario, $clave);
                        } catch (\PDOException $e) {
                            echo "Connection failed: " . $e->getMessage(); echo "\n";
                            continue;
                        }
        
                        $checkAliado = $dbConnection->query("SELECT * FROM almacen ORDER BY id ASC")->fetch(\PDO::FETCH_OBJ);
    
                        echo "\n";
    
                        echo "$fecha | CONSULTANDO : DB-CONFIG: $db_config_id | VENTA-ID $venta_id | Identificador $pendiente->id";
                   
                        if($pendiente->tipo_facturacion == 'docsoporte'){
                            $documento = $dbConnection->query("SELECT * FROM support_document WHERE id = ".$venta_id." AND `status` != -1 AND ((id_transaccion IS NULL OR id_transaccion = '') OR resp_electronic_invoice = 'ERROR')" )->fetch(\PDO::FETCH_OBJ);
                            echo $this->colorize($index." de ".  $total. "| FECHA documento: ".@$documento->fecha,'green'); echo "\n";
                            echo $this->colorize($index." de ".  $total. "| Resp Documento: ".@$documento->resp_electronic_invoice,'green'); echo "\n";
                          
                            if (isset($documento->id) && $documento->id_transaccion == null || isset($documento->id) && $documento->resp_electronic_invoice == 'ERROR' ) {
                                
                                $response = $this->postCurl('emite-docso-propia/' . $venta_id,[], $token);
                                echo $this->colorize($index." de ".  $total. " | RESP: PASO! " . json_encode($response) . "\n","green");
                                $pendiente->update([
                                    'respuesta' => 'SE ENVIO',
                                    'estado' => 1,
                                    'comentario' => 'Se envio',
                                    'ultimo_intento' => $fecha,
                                ]);
                                echo $this->colorize($index." de ".  $total. " Se envo la factura PROPIA","green");
                                \Log::info($index." de ".  $total. " Enviada Factura Propia");
                            } else {
                                $pendiente->update([
                                    'respuesta' => 'YA FUE ENVIADO O ESTA ANULADA',
                                    'estado' => 10,
                                    'comentario' => 'YA FUE ENVIADO O ESTA ANULADA',
                                    'ultimo_intento' => $fecha,
                                ]);
                                echo $this->colorize($index." de ".  $total. " | RESP: " . 'YA FUE ENVIADO O ESTA ANULADA' . "\n",'red');
                                \Log::error($index." de ".  $total. " YA FUE ENVIADA");
                            }
                           
                        }else{
                            $venta = $dbConnection->query("SELECT * FROM venta WHERE id = ".$venta_id." AND estado != -1 AND (id_transaccion IS NULL OR resp_electronic_invoice = 'ERROR')" )->fetch(\PDO::FETCH_OBJ);
                      

                            echo $this->colorize($index." de ".  $total. "| FECHA VENTA: ".@$venta->fecha,'green'); echo "\n";
                            echo $this->colorize($index." de ".  $total. "| Resp VENTA: ".@$venta->resp_electronic_invoice,'green'); echo "\n";
                            echo $this->colorize($index." de ".  $total. "| ALIADO VENTA: ".@$venta->aliado,'green'); echo "\n";
                            if (isset($venta->id) && $venta->id_transaccion == null || isset($venta->id) && $venta->resp_electronic_invoice == 'ERROR' || $pendiente->tipo_facturacion == 'guatemala') {
                                
                                if ($pendiente->tipo_facturacion == 'siigo') {
                                    $response = $this->getCurlSiigo('reemite/' . $venta_id, $token);
                                    $this->handleSiigoResponse($response, $pendiente, $venta_id, $fecha);
                                    \Log::info($index." de ".  $total. " Enviada Siigo");
                                } elseif ($pendiente->tipo_facturacion == 'fpropia') {
                                    $response = $this->postCurl('emite-propia-directo/' . $venta_id,[], $token);
                                 

                                    if( isset($response->error)&&$response->error==true ){
                                 
                                        if(is_array($response->validation_errors)){
                                            $mnsg='';
                                            foreach($response->validation_errors as $error){
                                                if($error->field == "invoice_lines"){
                                                    $mnsg.=" El campo de productos es obligatorio,";
                                                }else{
                                                    $mnsg.=" $error->error,";
                                                }
                                            
                                            }
                                            $pendiente->update([
                                                'respuesta' => $mnsg,
                                                'estado' => 12,
                                                'comentario' => 'error envio',
                                                'ultimo_intento' => $fecha,
                                            ]);
                                            \Log::info($index." de ".  $total. " ".$mnsg. " Estado 12");
                                        }else{
                                            $pendiente->update([
                                                'respuesta' => $response->msg,
                                                'estado' => 0,
                                                'comentario' => 'error envio el servicio esta caido',
                                                'ultimo_intento' => $fecha,
                                            ]);
                                            \Log::info($index." de ".  $total. " ".$response->msg. " en cola todavia");
                                        }
                                       
                                      
                                    }else{
                                     
                                        if(isset($response->response->success)&& $response->response->success == false){
                                            if($response->response->message){
                                                $pendiente->update([
                                                    'respuesta' => $response->response->message,
                                                    'estado' => 0,
                                                    'comentario' => 'SE ENVIO',
                                                    'ultimo_intento' => $fecha,
                                                ]);
                                              //  echo $this->colorize($index." de ".  $total. " | RESP: PASO! " . json_encode($response) . "\n","green");
                                              \Log::info($index." de ".  $total. " ".$response->response->message);
                                            }
                                           
                                        }else{
                                            $pendiente->update([
                                                'respuesta' => 'SE ENVIO',
                                                'estado' => 1,
                                                'comentario' => 'SE ENVIO',
                                                'ultimo_intento' => $fecha,
                                            ]);
                                            echo $this->colorize($index." de ".  $total. " | RESP: PASO! " . json_encode($response) . "\n","green");
                                            \Log::info($index." de ".  $total. " SE ENVIO");
                                        }
                                     
                                    }


                                 
                                    echo $this->colorize($index." de ".  $total. " Se envo la factura PROPIA","green");
                                    \Log::info($index." de ".  $total. " Enviada Factura Propia");
                                } elseif ($pendiente->tipo_facturacion == 'guatemala') {
                                     $response = $this->getCurlGuatemala('emite/' . $venta_id, $token);
                                     $pendiente->update([
                                        'respuesta' => 'SE ENVIO',
                                        'estado' => 1,
                                        'comentario' => 'SE ENVIO',
                                        'ultimo_intento' => $fecha,
                                    ]);
                                    \Log::info($index." de ".  $total. " Enviada Guatemala");
                                } else {
                                    $response = $this->getCurl('generate/' . $venta_id, $token);
                                    $pendiente->update([
                                        'respuesta' => 'SE ENVIO',
                                        'estado' => 1,
                                        'comentario' => 'SE ENVIO',
                                        'ultimo_intento' => $fecha,
                                    ]);
                                    echo $this->colorize($index." de ".  $total. "Se envo la factura B2B","green");
                                    \Log::info($index." Enviada Factura Facturaxion");
                                }
                            } else {
                                $pendiente->update([
                                    'respuesta' => 'YA FUE ENVIADO O ESTA ANULADA',
                                    'estado' => 10,
                                    'comentario' => 'YA FUE ENVIADO O ESTA ANULADA 2',
                                    'ultimo_intento' => $fecha,
                                ]);
                                echo $this->colorize($index." de ".  $total. " | RESP: " . 'YA FUE ENVIADO O ESTA ANULADA' . "\n",'red');
                                \Log::error($index." de ".  $total. " YA FUE ENVIADA");
                            }
                            
                        }
                        sleep(2);
                    } else {
                        $pendiente->update([
                            'respuesta' => "esta en demo",
                            'estado' => 22,
                            'ultimo_intento' => $fecha,
                        ]);
                    }
    
                } else {
                    $response = $this->postCurl('generateVendty', $pendiente->data_to_send, $token);
                    if ($response->codigoRespuesta == '01') {
                        echo $this->colorize($index." de ".  $total. " | RESP: " . 'Se envio la factura Vendty, se esperan 25 segundos para la respuesta' . "\n",'green');
                        sleep(25);
                        $response2 = $this->postCurl('requestVendty', json_encode($response));
                        if ($response2->RWS->codigoRespuesta == '01') {
                            $documento = $response2->RWS->Documento;
                            $this->storeInvoice($documento, $pendiente->id);
                        }
                    } else {
                        $pendiente->update([
                            'respuesta' => json_encode($response),
                            'estado' => 0,
                            'ultimo_intento' => $fecha,
                        ]);
                    }
                }
            }
        } else {
            echo $this->colorize("NO HAY FACTURAS POR ENVIAR","green"); echo "\n";
        }

        ElectronicInvoiceSender::query()
        ->whereIn('id', $porEnviar->pluck('id'))
        ->update(['enviando' => 0,'comentario' => "Update 0"]);

        // Eliminar los 10,000 registros más antiguos de hace más de 2 meses
        $fechaLimite = date('Y-m-d', strtotime('-2 months'));

        // Ejecutar la eliminación
        DB::connection('VendtyServices')->statement("
            DELETE FROM electronic_invoice_sender
            WHERE creacion < ? 
            ORDER BY creacion ASC
            LIMIT 10000
        ", [$fechaLimite]);
    
        // Reenviar con la emision alternativa las que fallaron
        // Reparar Facturas con precios decimales que no pasan
        \Log::info("Finalizado el envio correctamente");
        \Log::info("---------------------------------------------------------");
        echo $this->colorize("Finalizado el envio correctamente","green"); echo "\n";
        echo "\n";
        // Intentar pasar las facturas
        // $this->reenviarFacturasDecimalesMalosReparaas();
        
      
    }
    public function FacturasObleaPendientesXEnviar() { // esta se ejecuta atualmente en el cron
        $conerror = 0;
        $hoy = date('Y-m-d');
     
        $arrayToMail = [];
        //eliminar duplicadas
        DB::connection('VendtyServices')->statement("DELETE FROM electronic_invoice_sender
        WHERE id NOT IN (
            SELECT id FROM (
                SELECT MIN(id) AS id
                FROM electronic_invoice_sender
                GROUP BY base_dato, venta_id
            ) AS subquery
        )");
      
        $porEnviar = ElectronicInvoiceSender::query()
            ->where('base_dato', 22045)
            ->where('estado',  0)
         //   ->where('tipo_facturacion', 'fpropia')
            ->where('enviando',  0)
            ->where(function ($query) {
                $hasta = strtotime('-40 days');
                $query->where('creacion', '>=', date('Y-m-d', $hasta))
                        ->orWhere('updated_at', '>=', date('Y-m-d', $hasta));
            })
            
            ->limit(1000)
            ->get();

        // Actualiza en bloque el campo 'enviando' a 1
        ElectronicInvoiceSender::query()
        ->whereIn('id', $porEnviar->pluck('id'))
        ->update(['enviando' => 1]);
        $hasta = strtotime('-20 days');
        \Log::info("Comienza el Envio de Facturas");
        $total= count($porEnviar);
       //    \Log::info("-----------------------" . count($porEnviar)." Fecha: ".$hasta."----------------------------------");
        echo $this->colorize( "\n o------------------------o----------------------------o \n",'green') ;
        echo $this->colorize( " SIN LIMITE | Num facts " .  $total." Fecha: ".$hasta,'green') ;
        echo $this->colorize( "\n o------------------------o----------------------------o \n",'green') ;
        \Log::info(date('Y-m-d H:i:s')." ----->  SIN LIMITE | Num facts " .  $total." Fecha: ".$hasta);
       
        if ($porEnviar->count() > 0) {
            foreach ($porEnviar as $index => $pendiente) {
                if($pendiente->creacion <= '2024-06-18'){
                    $tokenNuevo = @ElectronicInvoiceSender::query()->where('base_dato',  $pendiente->base_dato)->where('creacion', '>=', '2024-06-19')->first()->token;
                    if($tokenNuevo){
                        $pendiente->token = $tokenNuevo;
                        $pendiente->save();
                        $token = $tokenNuevo;
                    }else{
                        continue;
                    }
                   

                }
                $fecha = date('Y-m-d H:i:s');
                echo $this->colorize( "\n " . $index. " de: ".count($porEnviar),'yellow');
                echo "\n";

                if (!is_null($pendiente->respuesta)&&strpos($pendiente->respuesta, "The id is inactive") !== false) {
                    echo $this->colorize("ID INACTIVO \n ",'red');
                    $pendiente->update(['estado' => 12]);
                    continue;
                }
                $token = null;
                if ($pendiente->tipo_facturacion != 'vendty') {
                    if ($pendiente->token == null || $pendiente->token == '') {
                        $TokenDB = ElectronicInvoiceSender::where('token', '!=', null)
                            ->where('base_dato', $pendiente->base_dato)
                            ->where('tipo_facturacion', $pendiente->tipo_facturacion)
                            ->orderBy('id', 'DESC')
                            ->first();
                        $token = $TokenDB->token;
                        ElectronicInvoiceSender::where('token', null)
                            ->where('base_dato', $pendiente->base_dato)
                            ->where('tipo_facturacion', $pendiente->tipo_facturacion)
                            ->update(['token' => $token]);
                        echo "\n";
                        echo $this->colorize(" Token Actualizado ",'magenta');
                        echo "\n";
                     
                    } else {
                        $token = $pendiente->token;
                    }
    
                    $venta_id = $pendiente->venta_id;
                    $db_config_id = $pendiente->base_dato;
                    $tipo_facturacion = $pendiente->tipo_facturacion;
                    $db_config = DBConfig::find($db_config_id);
    
                    // CONEXION CON BASE DE DATOS CLIENTE
                    $servidor = $db_config->servidor;
                    $base_dato = $db_config->base_dato;
                    $usuario = $db_config->usuario;
                    $clave = $db_config->clave;
    
                    if(true){ // quite la validacion para q no consultara demo24
                        $dns = "mysql:host=$servidor;dbname=$base_dato;charset=utf8";
                        echo "\n $dns \n";
                        
                        // Usar PDO para conectar a la base de datos del cliente
                        try {
                            $dbConnection = new \PDO($dns, $usuario, $clave);
                        } catch (\PDOException $e) {
                            echo "Connection failed: " . $e->getMessage(); echo "\n";
                            continue;
                        }
        
                        $checkAliado = $dbConnection->query("SELECT * FROM almacen ORDER BY id ASC")->fetch(\PDO::FETCH_OBJ);
    
                        echo "\n";
    
                        echo "$fecha | CONSULTANDO : DB-CONFIG: $db_config_id | VENTA-ID $venta_id | Identificador $pendiente->id";
                   
                        if($pendiente->tipo_facturacion == 'docsoporte'){
                            $documento = $dbConnection->query("SELECT * FROM support_document WHERE id = ".$venta_id." AND `status` != -1 AND ((id_transaccion IS NULL OR id_transaccion = '') OR resp_electronic_invoice = 'ERROR')" )->fetch(\PDO::FETCH_OBJ);
                            echo $this->colorize($index." de ".  $total. "| FECHA documento: ".@$documento->fecha,'green'); echo "\n";
                            echo $this->colorize($index." de ".  $total. "| Resp Documento: ".@$documento->resp_electronic_invoice,'green'); echo "\n";
                          
                            if (isset($documento->id) && $documento->id_transaccion == null || isset($documento->id) && $documento->resp_electronic_invoice == 'ERROR' ) {
                                
                                $response = $this->postCurl('emite-docso-propia/' . $venta_id,[], $token);
                                echo $this->colorize($index." de ".  $total. " | RESP: PASO! " . json_encode($response) . "\n","green");
                                $pendiente->update([
                                    'respuesta' => 'SE ENVIO',
                                    'estado' => 1,
                                    'comentario' => 'Se envio',
                                    'ultimo_intento' => $fecha,
                                ]);
                                echo $this->colorize($index." de ".  $total. " Se envo la factura PROPIA","green");
                                \Log::info($index." de ".  $total. " Enviada Factura Propia");
                            } else {
                                $pendiente->update([
                                    'respuesta' => 'YA FUE ENVIADO O ESTA ANULADA',
                                    'estado' => 10,
                                    'comentario' => 'YA FUE ENVIADO O ESTA ANULADA',
                                    'ultimo_intento' => $fecha,
                                ]);
                                echo $this->colorize($index." de ".  $total. " | RESP: " . 'YA FUE ENVIADO O ESTA ANULADA' . "\n",'red');
                                \Log::error($index." de ".  $total. " YA FUE ENVIADA");
                            }
                           
                        }else{
                            $venta = $dbConnection->query("SELECT * FROM venta WHERE id = ".$venta_id." AND estado != -1 AND (id_transaccion IS NULL OR resp_electronic_invoice = 'ERROR')" )->fetch(\PDO::FETCH_OBJ);
                      

                            echo $this->colorize($index." de ".  $total. "| FECHA VENTA: ".@$venta->fecha,'green'); echo "\n";
                            echo $this->colorize($index." de ".  $total. "| Resp VENTA: ".@$venta->resp_electronic_invoice,'green'); echo "\n";
                            echo $this->colorize($index." de ".  $total. "| ALIADO VENTA: ".@$venta->aliado,'green'); echo "\n";
                            if (isset($venta->id) && $venta->id_transaccion == null || isset($venta->id) && $venta->resp_electronic_invoice == 'ERROR' || $pendiente->tipo_facturacion == 'guatemala') {
                                
                                if ($pendiente->tipo_facturacion == 'siigo') {
                                    $response = $this->getCurlSiigo('reemite/' . $venta_id, $token);
                                    $this->handleSiigoResponse($response, $pendiente, $venta_id, $fecha);
                                    \Log::info($index." de ".  $total. " Enviada Siigo");
                                } elseif ($pendiente->tipo_facturacion == 'fpropia') {
                                    $response = $this->postCurl('emite-propia-directo/' . $venta_id,[], $token);
                                 

                                    if( isset($response->error)&&$response->error==true ){
                                 
                                        if(is_array($response->validation_errors)){
                                            $mnsg='';
                                            foreach($response->validation_errors as $error){
                                                if($error->field == "invoice_lines"){
                                                    $mnsg.=" El campo de productos es obligatorio,";
                                                }else{
                                                    $mnsg.=" $error->error,";
                                                }
                                            
                                            }
                                            $pendiente->update([
                                                'respuesta' => $mnsg,
                                                'estado' => 12,
                                                'comentario' => 'error envio',
                                                'ultimo_intento' => $fecha,
                                            ]);
                                            \Log::info($index." de ".  $total. " ".$mnsg. " Estado 12");
                                        }else{
                                            $pendiente->update([
                                                'respuesta' => $response->msg,
                                                'estado' => 0,
                                                'comentario' => 'error envio el servicio esta caido',
                                                'ultimo_intento' => $fecha,
                                            ]);
                                            \Log::info($index." de ".  $total. " ".$response->msg. " en cola todavia");
                                        }
                                       
                                      
                                    }else{
                                     
                                        if(isset($response->response->success)&& $response->response->success == false){
                                            if($response->response->message){
                                                $pendiente->update([
                                                    'respuesta' => $response->response->message,
                                                    'estado' => 0,
                                                    'comentario' => 'SE ENVIO',
                                                    'ultimo_intento' => $fecha,
                                                ]);
                                              //  echo $this->colorize($index." de ".  $total. " | RESP: PASO! " . json_encode($response) . "\n","green");
                                              \Log::info($index." de ".  $total. " ".$response->response->message);
                                            }
                                           
                                        }else{
                                            $pendiente->update([
                                                'respuesta' => 'SE ENVIO',
                                                'estado' => 1,
                                                'comentario' => 'SE ENVIO',
                                                'ultimo_intento' => $fecha,
                                            ]);
                                            echo $this->colorize($index." de ".  $total. " | RESP: PASO! " . json_encode($response) . "\n","green");
                                            \Log::info($index." de ".  $total. " SE ENVIO");
                                        }
                                     
                                    }


                                 
                                    echo $this->colorize($index." de ".  $total. " Se envo la factura PROPIA","green");
                                    \Log::info($index." de ".  $total. " Enviada Factura Propia");
                                } elseif ($pendiente->tipo_facturacion == 'guatemala') {
                                     $response = $this->getCurlGuatemala('emite/' . $venta_id, $token);
                                     $pendiente->update([
                                        'respuesta' => 'SE ENVIO',
                                        'estado' => 1,
                                        'comentario' => 'SE ENVIO',
                                        'ultimo_intento' => $fecha,
                                    ]);
                                    \Log::info($index." de ".  $total. " Enviada Guatemala");
                                } else {
                                    $response = $this->getCurl('generate/' . $venta_id, $token);
                                    $pendiente->update([
                                        'respuesta' => 'SE ENVIO',
                                        'estado' => 1,
                                        'comentario' => 'SE ENVIO',
                                        'ultimo_intento' => $fecha,
                                    ]);
                                    echo $this->colorize($index." de ".  $total. "Se envo la factura B2B","green");
                                    \Log::info($index." Enviada Factura Facturaxion");
                                }
                            } else {
                                $pendiente->update([
                                    'respuesta' => 'YA FUE ENVIADO O ESTA ANULADA',
                                    'estado' => 10,
                                    'comentario' => 'YA FUE ENVIADO O ESTA ANULADA 2',
                                    'ultimo_intento' => $fecha,
                                ]);
                                echo $this->colorize($index." de ".  $total. " | RESP: " . 'YA FUE ENVIADO O ESTA ANULADA' . "\n",'red');
                                \Log::error($index." de ".  $total. " YA FUE ENVIADA");
                            }
                            
                        }
                        sleep(2);
                    } else {
                        $pendiente->update([
                            'respuesta' => "esta en demo",
                            'estado' => 22,
                            'ultimo_intento' => $fecha,
                        ]);
                    }
    
                } else {
                    $response = $this->postCurl('generateVendty', $pendiente->data_to_send, $token);
                    if ($response->codigoRespuesta == '01') {
                        echo $this->colorize($index." de ".  $total. " | RESP: " . 'Se envio la factura Vendty, se esperan 25 segundos para la respuesta' . "\n",'green');
                        sleep(25);
                        $response2 = $this->postCurl('requestVendty', json_encode($response));
                        if ($response2->RWS->codigoRespuesta == '01') {
                            $documento = $response2->RWS->Documento;
                            $this->storeInvoice($documento, $pendiente->id);
                        }
                    } else {
                        $pendiente->update([
                            'respuesta' => json_encode($response),
                            'estado' => 0,
                            'ultimo_intento' => $fecha,
                        ]);
                    }
                }
            }
        } else {
            echo $this->colorize("NO HAY FACTURAS POR ENVIAR","green"); echo "\n";
        }

        ElectronicInvoiceSender::query()
        ->whereIn('id', $porEnviar->pluck('id'))
        ->update(['enviando' => 0,'comentario' => "Update 0"]);

        // Eliminar los 10,000 registros más antiguos de hace más de 2 meses
        $fechaLimite = date('Y-m-d', strtotime('-2 months'));

        // Ejecutar la eliminación
        DB::connection('VendtyServices')->statement("
            DELETE FROM electronic_invoice_sender
            WHERE creacion < ? 
            ORDER BY creacion ASC
            LIMIT 10000
        ", [$fechaLimite]);
    
        // Reenviar con la emision alternativa las que fallaron
        // Reparar Facturas con precios decimales que no pasan
        \Log::info("Finalizado el envio correctamente");
        \Log::info("---------------------------------------------------------");
        echo $this->colorize("Finalizado el envio correctamente","green"); echo "\n";
        echo "\n";
        // Intentar pasar las facturas
        // $this->reenviarFacturasDecimalesMalosReparaas();
        
      
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

     private function getCurlGuatemala($method, $token)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://apifegt.vendty.com/api/v1/" . $method);
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
    private function postBetaCurl($method, $data, $token)
    {
        // Aquí va tu implementación para la llamada cURL con POST
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://beta-apipos.vendty.com/api/v1/" . $method);
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

    public function LicenciasxVencer0Dias() {
       $blackListedMails = Blacklist::all()->toArray();
       
       //busqueda licencias en app/Helpers.php
        $licencias = json_decode(ConsultaLicencias(0),TRUE); 
        //dd($licencias);

       if (!empty($licencias)) {
           
            foreach ($licencias as $key) {
                $email          = $key['email'];
                $usersnombres   = $key['first_name'] . ' ' . $key['last_name'];
                $servidor       = $key['servidor'];
                $base_dato      = $key['base_dato'];
                $usuario        = $key['usuario'];
                $clave          = $key['clave'];
                $idAlmacen      = $key['id_almacen']; 

                //dd($email);

                $almacen = (string)ConsultaAlmacen($base_dato, $servidor, $idAlmacen, $usuario, $clave);
                //dd($almacenes);
                $puede  = checkCanSend($blackListedMails, $email);

                //dd($puede);

                if (isset($puede)) {
                    $this->EnviarCorreo($licencias, strtoupper($usersnombres), $email, $almacen);
                }
 
            }
         
       }
   }

   public function LicenciasxVencer1Dias() {

    $blackListedMails = Blacklist::all()->toArray();
       
    //busqueda licencias en app/Helpers.php
     $licencias = json_decode(ConsultaLicencias(1),TRUE); 
     //dd($licencias);

        if (!empty($licencias)) {
            
            foreach ($licencias as $key) {
                $email          = $key['email'];
                $usersnombres   = $key['first_name'] . ' ' . $key['last_name'];
                $servidor       = $key['servidor'];
                $base_dato      = $key['base_dato'];
                $usuario        = $key['usuario'];
                $clave          = $key['clave'];
                $idAlmacen      = $key['id_almacen']; 

                //dd($email);

                $almacen = (string)ConsultaAlmacen($base_dato, $servidor, $idAlmacen, $usuario, $clave);
                //dd($almacenes);
                $puede  = checkCanSend($blackListedMails, $email);

                //dd($puede);

                if (isset($puede)) {
                    $this->EnviarCorreo($licencias, strtoupper($usersnombres), $email, $almacen);
                }

            }
        
        }
            
    }

   public function LicenciasxVencer3Dias() {

    $blackListedMails = Blacklist::all()->toArray();

    //busqueda licencias en app/Helpers.php
        $licencias = json_decode(ConsultaLicencias(3),TRUE); 
        //dd($licencias);

        if (!empty($licencias)) {
            
            foreach ($licencias as $key) {
                $email          = $key['email'];
                $usersnombres   = $key['first_name'] . ' ' . $key['last_name'];
                $servidor       = $key['servidor'];
                $base_dato      = $key['base_dato'];
                $usuario        = $key['usuario'];
                $clave          = $key['clave'];
                $idAlmacen      = $key['id_almacen']; 

                //dd($email);

                $almacen = (string)ConsultaAlmacen($base_dato, $servidor, $idAlmacen, $usuario, $clave);
                //dd($almacenes);
                $puede  = checkCanSend($blackListedMails, $email);

                //dd($puede);

                if (isset($puede)) {
                    $this->EnviarCorreo($licencias, strtoupper($usersnombres), $email, $almacen);
                }

            }
        
        }    

   }

   public function LicenciasxVencer7Dias() {

    $blackListedMails = Blacklist::all()->toArray();

    //busqueda licencias en app/Helpers.php
        $licencias = json_decode(ConsultaLicencias(7),TRUE); 
        //dd($licencias);

        if (!empty($licencias)) {
            
            foreach ($licencias as $key) {
                $email          = $key['email'];
                $usersnombres   = $key['first_name'] . ' ' . $key['last_name'];
                $servidor       = $key['servidor'];
                $base_dato      = $key['base_dato'];
                $usuario        = $key['usuario'];
                $clave          = $key['clave'];
                $idAlmacen      = $key['id_almacen']; 

                //dd($email);

                $almacen = (string)ConsultaAlmacen($base_dato, $servidor, $idAlmacen, $usuario, $clave);
                //dd($almacenes);
                $puede  = checkCanSend($blackListedMails, $email);

                //dd($puede);

                if (isset($puede)) {
                    $this->EnviarCorreo($licencias, strtoupper($usersnombres), $email, $almacen);
                }

            }
        
        }    

   }

   public function LicenciasxVencer15Dias() {

    $blackListedMails = Blacklist::all()->toArray();

    //busqueda licencias en app/Helpers.php
        $licencias = json_decode(ConsultaLicencias(15),TRUE); 
        //dd($licencias);

        if (!empty($licencias)) {
            
            foreach ($licencias as $key) {
                $email          = $key['email'];
                $usersnombres   = $key['first_name'] . ' ' . $key['last_name'];
                $servidor       = $key['servidor'];
                $base_dato      = $key['base_dato'];
                $usuario        = $key['usuario'];
                $clave          = $key['clave'];
                $idAlmacen      = $key['id_almacen']; 

                //dd($email);

                $almacen = (string)ConsultaAlmacen($base_dato, $servidor, $idAlmacen, $usuario, $clave);
                //dd($almacenes);
                $puede  = checkCanSend($blackListedMails, $email);

                //dd($puede);

                if (isset($puede)) {
                    $this->EnviarCorreo($licencias, strtoupper($usersnombres), $email, $almacen);
                }

            }
        
        }    

   }

   public function LicenciasxVencer30Dias() {

    $blackListedMails = Blacklist::all()->toArray();

    //busqueda licencias en app/Helpers.php
        $licencias = json_decode(ConsultaLicencias(30),TRUE); 
        //dd($licencias);

        if (!empty($licencias)) {
            
            foreach ($licencias as $key) {
                $email          = $key['email'];
                $usersnombres   = $key['first_name'] . ' ' . $key['last_name'];
                $servidor       = $key['servidor'];
                $base_dato      = $key['base_dato'];
                $usuario        = $key['usuario'];
                $clave          = $key['clave'];
                $idAlmacen      = $key['id_almacen']; 

                //dd($email);

                $almacen = (string)ConsultaAlmacen($base_dato, $servidor, $idAlmacen, $usuario, $clave);
                //dd($almacenes);
                $puede  = checkCanSend($blackListedMails, $email);

                //dd($puede);

                if (isset($puede)) {
                    $this->EnviarCorreo($licencias, strtoupper($usersnombres), $email, $almacen);
                }

            }
        
        }    

   }

   public function sendMailRegisterFirst()
    {
        $count  = 0;
        $emails = "";

        $blackListedMails = Blacklist::all()->toArray();
        
        $start_date     = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')));
        $end_date       = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - 1, date('Y')));

        $registros =  json_decode(Registros($start_date, $end_date), TRUE);
        
        try {
            foreach ($registros as $key) {
                $email = $key['correo'];
            
                $puede  = checkCanSend($blackListedMails, $email);

                if ($puede) {
                    $this->EnviarCorreoFirst($email);
                }

                $count++;
                $emails .= $key['nombre'] . ' ' . $key['apellidos'] . ' - ' . $key['correo'] . '<br>';
            }
        } catch (Exception $e) {
            $emails .= 'Excepción capturada: ' . $e->getMessage() . '<br>';
        }


        try {
            $this->EnviarCorreoFirstControl($count, $emails);
        }catch(Exception $e) {
            return $e;
        }
    }

    public function sendMailRegisterSecond()
    {
        $count  = 0;
        $emails = "";

        $blackListedMails = Blacklist::all()->toArray();
        
        $start_date     = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')));
        $end_date       = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - 2, date('Y')));

        $registros =  json_decode(Registros($start_date, $end_date), TRUE);
        
        try {
            foreach ($registros as $key) {
                $email = $key['correo'];
            
                $puede  = checkCanSend($blackListedMails, $email);

                if ($puede) {
                    $this->EnviarCorreoSecond($email);
                    $this->EnviarCorreoSecond2($email, $key['nombre'], $key['apellidos']);
                }

                $count++;
                $emails .= $key['nombre'] . ' ' . $key['apellidos'] . ' - ' . $key['correo'] . '<br>';
            }
        } catch (Exception $e) {
            $emails .= 'Excepción capturada: ' . $e->getMessage() . '<br>';
        }


        try {
            $this->EnviarCorreoSecondControl($count, $emails);
        }catch(Exception $e) {
            return $e;
        }
    }    

    public function sendMailRegisterThird()
    {
        $count  = 0;
        $emails = "";

        $blackListedMails = Blacklist::all()->toArray();
        
        $start_date     = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 3, date('Y')));
        $end_date       = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - 3, date('Y')));

        $registros =  json_decode(Registros($start_date, $end_date), TRUE);
        
        try {
            foreach ($registros as $key) {
                $email = $key['correo'];
            
                $puede  = checkCanSend($blackListedMails, $email);

                if ($puede) {
                    $this->EnviarCorreoThird($email, $key['nombre'], $key['apellidos']);
                }

                $count++;
                $emails .= $key['nombre'] . ' ' . $key['apellidos'] . ' - ' . $key['correo'] . '<br>';
            }
        } catch (Exception $e) {
            $emails .= 'Excepción capturada: ' . $e->getMessage() . '<br>';
        }


        try {
            $this->EnviarCorreoThirdControl($count, $emails);
        }catch(Exception $e) {
            return $e;
        }
    }    

    public function sendMailRegisterFourth()
    {
        $count  = 0;
        $emails = "";

        $blackListedMails = Blacklist::all()->toArray();
        
        $start_date     = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 4, date('Y')));
        $end_date       = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - 4, date('Y')));

        $registros =  json_decode(Registros($start_date, $end_date), TRUE);
        
        try {
            foreach ($registros as $key) {
                $email = $key['correo'];
            
                $puede  = checkCanSend($blackListedMails, $email);

                if ($puede) {
                    $this->EnviarCorreoFourth($email, $key['nombre'], $key['apellidos']);
                }

                $count++;
                $emails .= $key['nombre'] . ' ' . $key['apellidos'] . ' - ' . $key['correo'] . '<br>';
            }
        } catch (Exception $e) {
            $emails .= 'Excepción capturada: ' . $e->getMessage() . '<br>';
        }


        try {
            $this->EnviarCorreoFourthControl($count, $emails);
        }catch(Exception $e) {
            return $e;
        }
    }
    
    public function sendMailRegisterFifth()
    {
        $count  = 0;
        $emails = "";

        $blackListedMails = Blacklist::all()->toArray();
        
        $start_date     = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 5, date('Y')));
        $end_date       = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - 5, date('Y')));

        $registros =  json_decode(Registros($start_date, $end_date), TRUE);
        
        try {
            foreach ($registros as $key) {
                $email = $key['correo'];
            
                $puede  = checkCanSend($blackListedMails, $email);

                if ($puede) {
                    $this->EnviarCorreoFifth($email);
                    $this->EnviarCorreoFifth2($email);
                }

                $count++;
                $emails .= $key['nombre'] . ' ' . $key['apellidos'] . ' - ' . $key['correo'] . '<br>';
            }
        } catch (Exception $e) {
            $emails .= 'Excepción capturada: ' . $e->getMessage() . '<br>';
        }


        try {
            $this->EnviarCorreoFifthControl($count, $emails);
        }catch(Exception $e) {
            return $e;
        }
    }

    public function sendMailRegisterSixth()
    {
        $count  = 0;
        $emails = "";

        $blackListedMails = Blacklist::all()->toArray();
        
        $start_date     = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 6, date('Y')));
        $end_date       = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - 6, date('Y')));

        $registros =  json_decode(Registros($start_date, $end_date), TRUE);
        
        try {
            foreach ($registros as $key) {
                $email = $key['correo'];
            
                $puede  = checkCanSend($blackListedMails, $email);

                if ($puede) {
                    $this->EnviarCorreoSixth($email, $key['nombre'], $key['apellidos']);
                }

                $count++;
                $emails .= $key['nombre'] . ' ' . $key['apellidos'] . ' - ' . $key['correo'] . '<br>';
            }
        } catch (Exception $e) {
            $emails .= 'Excepción capturada: ' . $e->getMessage() . '<br>';
        }


        try {
            $this->EnviarCorreoSixthControl($count, $emails);
        }catch(Exception $e) {
            return $e;
        }
    }

    public function actualizarLicenciasVencidas()
    {
        $blackListedMails = Blacklist::all()->toArray();
        
        $fecha = date('Y-m-d');
        $fecha = Carbon::parse(date("Y-m-d", strtotime($fecha . "- 1 days")));

        $sqlupdate = LicenciaEmpresa::where('fecha_vencimiento', $fecha)
                                    ->where('estado_licencia', '!=', 15)
                                    ->update(['estado_licencia' => 15]);

        if ($sqlupdate > 0) {

            $licencias = Licencias::select('v_crm_licencias.id_licencia as licencia_id', 'users.email as user_email', 'users.username as user_name')
                                ->join('users', 'v_crm_licencias.id_db_config', '=', 'users.db_config_id')
                                ->where('v_crm_licencias.fecha_vencimiento', $fecha)
                                ->where('v_crm_licencias.id_plan', '!=', 1)
                                ->where('users.is_admin', 't')
                                ->groupBy('v_crm_licencias.id_licencia', 'users.email', 'users.username')
                                ->get();
            
            
            try {
                foreach ($licencias as $key) {
                    $email = $key['user_email'];

                    $puede  = checkCanSend($blackListedMails, $email);

                    /*post_curl('baremetrics/cancel_subscription', json_encode([ // indagar sobre su uso
                        'license_id' => $key['licencia_id'], 
                    ]));*/

                    if ($puede) {
                        $this->EnviarCorreoLicenciasVencidas($email, $key['user_name']);
                    }

                }
            } catch (Exception $e) {
                $emails .= 'Excepción capturada: ' . $e->getMessage() . '<br>';
            }
        }

    }

    public function SendMailWizardIncomplete()
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

        $databases = DB::connection('vendty2')
            ->table('users as u') 
            ->join('db_config as d', 'u.db_config_id', '=', 'd.id')
            ->join('crm_licencias_empresa as l', 'd.id', '=', 'l.id_db_config')
            ->select('u.username', 'u.email', 'd.base_dato', 'd.servidor', 'd.usuario', 'd.clave', 'l.id_almacen')
            ->where(function ($query) {
                $query->where('d.estado', 1)
                      ->orWhere('d.estado', 2);
            })
            ->where('l.fecha_vencimiento', '>=', $fecha_vencimiento)
            ->whereNotIn('l.planes_id', [15, 16, 17])
            ->where('u.is_admin', 't')
            ->whereNotIn('d.servidor', ['0.0.0.0', '10.0.0.7'])
            ->groupBy('u.db_config_id', 'username','email','base_dato','d.servidor','d.usuario','d.clave','l.id_almacen','l.id_almacen')
            //->take(10)
            ->get();

        if (!empty($databases)) {
            foreach ($databases as $database) {
                try {
                    
                    $puede  = checkCanSend($blackListedMails, $database->email);
                    
                   
                    if ($puede) {
                        $username_admin = $database->username;
                        $user_admin     = $database->email;
                        $db             = $database->base_dato;
                        $id_almacen     = $database->id_almacen;
                        $usuario        = $database->usuario;
                        $clave          = $database->clave;
                        $servidor       = $database->servidor;
                       
                    //if($user_admin == 'pimentonmetropolis@hotmail.com') {
                        //print_r("servidor" . $servidor);
                        //print_r("db" . $db);
                        //print_r("user" . $user_admin);

                    
                            //echo "<pre>"; print_r($db);
                            
                           
                           

                        /*if (!in_array($servidor, $this->servers)) {
                            continue;
                        }*/
                        $existeBeta = "";
                        $conection = dynamicDatabaseConnection($db, $servidor, $usuario, $clave);
                        $existeBeta = $conection->select("SHOW DATABASES LIKE '$db'");

                        if (!empty($existeBeta)) {
                            echo "<pre>"; print_r('---- base de datos' . $db . ' si existe' . 'en el host' . $servidor );
                            //$conection = dynamicDatabaseConnection($db, $servidor, $usuario, $clave);
                        }else{
                            echo "<pre>"; print_r('base de datos' . $db . 'no existe' . 'en el host' . $servidor );
                            continue;

                        }

                       
                      /*  try {
                            $existeBeta = $conection->table('almacen')->where('id', 1)->value('nombre');
                        } catch (\Throwable $th) {
                            continue;
                        }*/
                        
                        //dd($databaseList);
                        //dd($existeBeta);

                        
                        

                    /*  $base_datos = "vendty2";
                        $dns = "mysql://$usuario:$clave@$servidor/$base_datos";
                        $this->dbConnection = $this->load->database($dns, true);
                        $existeBeta = $this->dbConnection->query("SHOW DATABASES WHERE `database` = '" . $db . "'")->result();*/

                        //dd(!empty($existeBeta));
                       /* if (empty($existeBeta)) {
                            continue;
                        } else {
                           $dns = "mysql://$usuario:$clave@$servidor/$db";
                            $this->connection = $this->load->database($dns, true);
                            $this->connection->db_debug = false;
                        }*/

                        try {
                            $tableExists = $conection->select("SHOW TABLES FROM $db LIKE 'opciones'");

                            if(!empty($tableExists)) {
                                
                                //$query = $this->connection->get_where($db . '.opciones', array('nombre_opcion' => 'simbolo'));
                                $query = $conection->table($db.'.opciones')
                                ->where(array('nombre_opcion' => 'simbolo'))->get();
                            }else{
                                continue;
                            }
                        } catch (Exception $e) {
                            
                        }

                        $simbolo = ($query == null || $query->count() == 0 || empty($query[0]->valor_opcion)) ? "$" : $query[0]->valor_opcion;

                        //$sql = "SELECT * FROM  $db.almacen WHERE activo = 1 AND bodega = 0 AND id=$id_almacen";
                        //dd($simbolo);

                        try {
                            //$almacenes = $this->connection->query($sql);
                            $almacenes = $conection->table('almacen')
                                        ->where('activo', 1)
                                        ->where('bodega', 0)
                                        ->where('id', $id_almacen)
                                        ->get();
                                        //dd($almacenes);
                        } catch (Exception $e) {
                        }

                        $almacenes = $almacenes == null ? [] : $almacenes;

                        

                        if (count($almacenes) > 0) {
                           // dd($almacenes); 

                            foreach ($almacenes as $almacen) {
                                //Realizamos los calculos por almacen
                                //Ventas diarias
                                 /*   $sql = "SELECT  SUM((dv.unidades * IFNULL(dv.descuento,0))) AS total_descuento
                                ,SUM(((dv.precio_venta - IFNULL(dv.descuento,0)) * IFNULL(dv.impuesto,0) / 100 * dv.unidades)) AS impuesto
                                ,SUM((dv.precio_venta * dv.unidades)) AS total_precio_venta
                                ,SUM(((dv.precio_venta - IFNULL(dv.descuento,0)) * IFNULL(dv.impuesto,0) / 100 * dv.unidades)) + SUM((dv.precio_venta * dv.unidades)) AS total
                                FROM $db.venta v
                                INNER JOIN $db.detalle_venta dv ON v.id=dv.venta_id
                                WHERE v.fecha BETWEEN '" . $fecha_inicial . "' AND '" . $fecha_final . "' AND estado = 0 AND almacen_id =" . $almacen->id;*/

                               // dd($almacen->id);

                                $sql = $conection->table($db.'.venta as v')
                                    ->join($db.'.detalle_venta as dv', 'v.id', '=', 'dv.venta_id')
                                    ->selectRaw('
                                        SUM(dv.unidades * IFNULL(dv.descuento, 0)) AS total_descuento,
                                        SUM((dv.precio_venta - IFNULL(dv.descuento, 0)) * IFNULL(dv.impuesto, 0) / 100 * dv.unidades) AS impuesto,
                                        SUM(dv.precio_venta * dv.unidades) AS total_precio_venta,
                                        SUM((dv.precio_venta - IFNULL(dv.descuento, 0)) * IFNULL(dv.impuesto, 0) / 100 * dv.unidades) + SUM(dv.precio_venta * dv.unidades) AS total
                                    ')
                                    ->whereBetween('v.fecha', [$fecha_inicial, $fecha_final])
                                    ->where('v.estado', 0)
                                    ->where('v.almacen_id', $almacen->id)
                                    ->get();


                                //dd($sql);
                                
                                    // Devoluciones (NC)
                                    $subtotal_devoluciones = 0;
                                    ####todos
                                    $totaldevoluciones = 0;
                                   /* $total_devoluciones = "SELECT v.id, v.factura, v.fecha, SUM(d.valor) AS total_devolucion
                                                            FROM $db.devoluciones d
                                                            INNER JOIN $db.venta v ON d.factura=v.factura
                                                            WHERE v.fecha BETWEEN '" . $fecha_inicial . "' AND '" . $fecha_final . "' AND estado = '0' AND almacen_id = " . $almacen->id . " GROUP BY v.factura";*/
                                    $total_devoluciones = $conection->table($db.'.devoluciones as d')
                                                            ->join($db.'.venta as v', 'd.factura', '=', 'v.factura')
                                                            ->select('v.id', 'v.factura', 'v.fecha', DB::raw('SUM(d.valor) as total_devolucion'))
                                                            ->whereBetween('v.fecha', [$fecha_inicial, $fecha_final ])
                                                            ->where('v.estado', '0')
                                                            ->where('v.almacen_id', $almacen->id)
                                                            ->groupBy('v.id', 'v.factura', 'v.fecha')
                                                            ->get();                                                            

                                        //dd($total_devoluciones);
                                //$total_devoluciones = $this->connection->query($total_devoluciones)->result();
                                $idFactura = "";

                                foreach ($total_devoluciones as $key1 => $value1) {
                                    $totaldevoluciones += $value1->total_devolucion;
                                    $idFactura .= $value1->id . ",";
                                }
                                //dd($idFactura);

                                //$result = $sql->toArray();
                                $result_array = $sql->toArray(); //$result->row_array();

                                //dd($result_array[0]->total);

                                $ventas_diarias = $result_array[0]->total - $result_array[0]->total_descuento - $totaldevoluciones;
                                //$ventas_diarias = $simbolo . ' ' . $this->formatoMonedaMostrar($db, $ventas_diarias);
                                $ventas_diarias = $simbolo . ' ' . number_format((float)$ventas_diarias, 2, '.', '');

                                //dd($ventas_diarias);

                                //Gastos
                                //$sql = "SELECT SUM(valor) as total_gastos FROM $db.proformas WHERE fecha = '" . $fecha_ayer . "'  AND id_almacen = " . $almacen->id;
                                $sql = $conection->table($db.'.proformas')
                                                ->where('fecha', $fecha_ayer)
                                                ->where('id_almacen', $almacen->id)
                                                ->sum('valor');

                                //$result = $this->connection->query($sql);
                                $total_gastos = (float)$sql;
                                
                                //$total_gastos = $simbolo . ' ' . $this->formatoMonedaMostrar($db, $total_gastos["total_gastos"]);
                                $total_gastos = $simbolo . ' ' . number_format((float)$total_gastos, 2, '.', '');
                                    //dd($total_gastos);


                                //Utilidad
                                /*$sql = "SELECT  SUM( dv.margen_utilidad) AS total_margen_utilidad
                                FROM $db.venta AS v INNER JOIN $db.detalle_venta AS dv ON v.id = dv.venta_id
                                WHERE DATE(v.fecha) BETWEEN '" . $fecha_inicial . "'  AND  '" . $fecha_final . "'  AND  almacen_id =" . $almacen->id . "  AND estado = 0";*/
                                //dd($sql);
                                $sql = $conection->table($db.'.venta')
                                                ->join('detalle_venta', 'venta.id', '=', 'detalle_venta.venta_id')
                                                ->whereBetween('venta.fecha', [$fecha_inicial, $fecha_final])
                                                ->where('venta.almacen_id', $almacen->id)
                                                ->where('venta.estado', 0)
                                                ->sum('detalle_venta.margen_utilidad');
                                //dd($sql);

                                //$result = $this->connection->query($sql);
                                $total_utilidad = (float)$sql; //$result->row_array();
                                
                                $total_utilidad = $simbolo . ' ' . number_format((float)$total_utilidad, 2, '.', '');
                               // dd($total_utilidad);

                                //Ventas por formas de pago
                               /* $sql = "select v.id as id_venta, sum(vp.valor_entregado) - sum(vp.cambio)  as total_venta, count(vp.forma_pago) as cantidad, vp.forma_pago
                                from $db.ventas_pago  AS vp
                                inner join $db.venta AS v on vp.id_venta = v.id
                                where DATE(v.fecha) BETWEEN '" . $fecha_ayer . "'  AND  '" . $fecha_ayer . "'  AND  almacen_id = " . $almacen->id . " AND estado = 0  group by forma_pago  ORDER BY cantidad";*/
                                $sql = $conection->table($db.'.ventas_pago AS vp')
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
                                //dd($sql);

                                //$result = $this->connection->query($sql);
                                $total_formas_pago = $sql->toArray();
                                //dd($total_formas_pago);

                                //Productos mas vendidos
                                /*$sql = " SELECT producto.imagen, producto.nombre, SUM(unidades) AS count_productos, SUM(margen_utilidad) AS utilidad, dv.precio_venta
                                FROM $db.detalle_venta AS dv
                                INNER JOIN $db.venta AS v ON dv.venta_id = v.id
                                INNER JOIN $db.producto ON dv.producto_id = producto.id
                                where DATE(v.fecha) BETWEEN '" . $fecha_ayer . "'  AND  '" . $fecha_ayer . "' AND  v.almacen_id =" . $almacen->id . "  AND estado = 0
                                GROUP BY nombre_producto
                                ORDER BY count_productos
                                DESC LIMIT 3";*/
                                $sql = $conection->table($db.'.detalle_venta AS dv')
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

                               // dd($sql); 

                                //$result = $this->connection->query($sql);
                                $productos_mas_vendidos = $sql->toArray();
                                
                                //dd($productos_mas_vendidos);

                                $data = array(
                                    "fecha" => $fecha_ayer,
                                    "user" => $username_admin ? strtoupper($username_admin) : "",
                                    "almacen" => $almacen->nombre ? strtoupper($almacen->nombre) : "",
                                    "ventas_diarias" => $ventas_diarias,
                                    "total_utilidad" => $total_utilidad,
                                    "devoluciones" => $simbolo . ' ' . number_format((float)$totaldevoluciones, 2, '.', ''),
                                    "total_gastos" => $total_gastos,
                                    "total_formas_pago" => $total_formas_pago,
                                    "productos_mas_vendidos" => $productos_mas_vendidos,
                                );

                                //dd($data);
                                

                                //$message = $this->load->view("email/daily_sales", $data, true);
                                $this->EnviarCorreoEmailVentasDiarias($user_admin, $data, $fecha_ayer, (string)$almacen->nombre);

                                /*$this->email->from('no-responder@vendty.net', 'Vendty - POS y Tienda Virtual - Resumen de ventas del dia: ' . $fecha_ayer . '(' . $almacen->nombre . ')');
                                //$this->email->to("integraciones@vendty.com");
                                $this->email->to($user_admin);
                                //$this->email->cc('desarrollo@vendty.com');
                                $this->email->subject('Informe de ventas');
                                $this->email->message($message);
                                $this->email->send();*/
                                $count++;
                                $emails .= (string)$almacen->nombre . ' - ' . $user_admin . '<br>';
                            }
                        }

                        //echo $this->cli->cout_color('El mail ' . $database["email"] . ' SE ENVIO', 'green') . "\n";
                    //}
                    } else {
                        //echo $this->cli->cout_color('El mail ' . $database["email"] . ' se encuentra en la lista negra', 'red') . "\n";
                    }

                } catch (Exception $e) {
                    echo 'Excepción capturada: ', $database, ' - ', $e->getMessage(), "\n";
                }

            }
        }


        $this->EnviarCorreoVentasDiariasControl($count, $emails, $fecha_ayer);

        /*$this->email->from('no-responder@vendty.net', 'Vendty - POS y Tienda Virtual - Correos de Resumen de ventas del dia: ' . $fecha_ayer);
        $this->email->to('integraciones@vendty.com');
        $this->email->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com'));
        $this->email->subject('Informe de ventas');
        $this->email->message('<p><b>Total de correo enviados:</b> ' . $count . ',</p><p><b>Emails:</b><br>' . $emails . '</p>');
        $this->email->send();*/
    }

    /*----------------------------------------------------------*/
    /* FUNCTIONES ADICIONALES                                   */
    /*----------------------------------------------------------*/

    public function EnviarCorreoFirst($email) {
        Mail::to($email)
        ->send(new MailRegisterFirst($email));
    }

    public function EnviarCorreoSecond($email) {
        Mail::to($email)
        ->send(new MailRegisterSecond($email));
    }

    public function EnviarCorreoSecond2($email, $nombre, $apellidos) {
        Mail::to($email)
        ->send(new MailRegisterSecond2($email, $nombre, $apellidos));
    }

    public function EnviarCorreoThird($email, $nombre, $apellidos) {
        Mail::to($email)
        ->send(new MailRegisterThird($email, $nombre, $apellidos));
    }

    public function EnviarCorreoFourth($email, $nombre, $apellidos) {
        Mail::to($email)
        ->send(new MailRegisterFourth($email, $nombre, $apellidos));
    }

    public function EnviarCorreoFifth($email) {
        Mail::to($email)
        ->send(new MailRegisterFifth($email));
    }

    public function EnviarCorreoFifth2($email) {
        Mail::to($email)
        ->send(new MailRegisterFifth2($email));
    }

    public function EnviarCorreoSixth($email, $nombre, $apellidos) {
        Mail::to($email)
        ->send(new MailRegisterSixth($email, $nombre, $apellidos));
    }

    public function EnviarCorreoWizardIncomplete($email, $data) {
       // dd($email);
        Mail::to($email)
        ->send(new MailWizardIncomplete($data));
    }

    public function EnviarCorreoEmailVentasDiarias($email, $data, $fecha_ayer, $almacenNombre) {
        // dd($email);
         Mail::to($email)
         ->send(new MailVentasDiarias($data, $fecha_ayer, $almacenNombre));
     }
    

    public function EnviarCorreoLicenciasVencidas($email, $user) {
        Mail::to($email)
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'roxanna.vergara@gmail.com', 'desarrollo@vendty.com'))
        ->send(new MailActualizarLicenciasVencidas($email, $user));
    }

    public function EnviarCorreoFirstControl($count, $emails) {
        Mail::to('desarrollo@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'desarrollo@vendty.com'))
        ->send(new MailEnviarCorreoFirstControl($count, $emails));
    }

    public function EnviarCorreoSecondControl($count, $emails) {
        Mail::to('desarrollo@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'desarrollo@vendty.com'))
        ->send(new MailEnviarCorreoSecondControl($count, $emails));
    }

    public function EnviarCorreoThirdControl($count, $emails) {
        Mail::to('desarrollo@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'desarrollo@vendty.com'))
        ->send(new MailEnviarCorreoThirdControl($count, $emails));
    }

    public function EnviarCorreoFourthControl($count, $emails) {
        Mail::to('desarrollo@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'desarrollo@vendty.com'))
        ->send(new MailEnviarCorreoFourthControl($count, $emails));
    }

    public function EnviarCorreoFifthControl($count, $emails) {
        Mail::to('desarrollo@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'desarrollo@vendty.com'))
        ->send(new MailEnviarCorreoFifthControl($count, $emails));
    }

    public function EnviarCorreoSixthControl($count, $emails) {
        Mail::to('desarrollo@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'desarrollo@vendty.com'))
        ->send(new MailEnviarCorreoSixthControl($count, $emails));
    }

    public function EnviarCorreoVentasDiariasControl($count, $emails, $fecha_ayer) {
        Mail::to('integraciones@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'nelson@vendty.com'))
        ->send(new MailEnviarCorreoVentasDiariasControl($count, $emails, $fecha_ayer));
    }

}
