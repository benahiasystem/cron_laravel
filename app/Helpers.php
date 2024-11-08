<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
    if (!function_exists('dynamicDatabaseConnection')) {
        function dynamicDatabaseConnection($databaseName, $host, $usuario, $clave)
        {
            // Configurar la conexión dinámica
            config([
                'database.connections.dynamic' => [
                    'driver'    => 'mysql', // Cambiar esto según el tipo de base de datos que estés utilizando
                    'host'      =>  $host,
                    'port'      => env('DB_PORT_DOS', '3306'), // Puedes cambiar el puerto si es necesario
                    'database'  => $databaseName,
                    'username'  => $usuario,
                    'password'  => $clave,
                    'charset'   => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix'    => '',
                    'strict'    => true,
                    'engine'    => null,
                ]
            ]);
            // Establecer la conexión dinámica
            return DB::connection('dynamic');
        }
    }

if (!function_exists('checkCanSend')) {
    function checkCanSend($blackListedMails, $email) {

        $filtro = array_filter($blackListedMails, function($ListedMails) use ($email) {
            if ($email == $ListedMails) {
                return false;
            } 
        });

        if (isset($filtro)) {
            return true;
        }else{
            return false;
        }
    }
}

if (!function_exists('ConsultaActivos')) {

    function ConsultaActivos() {

        return  DB::connection('vendty2')
                    ->table('crm_empresas_clientes as e')
                    ->select(
                        'u.first_name', 
                        'u.last_name', 
                        'u.email', 
                        'u.phone',
                        'e.nombre_empresa', 
                        'e.id_db_config', 
                        'bd.base_dato',
                        'bd.servidor',
                        'bd.usuario',
                        'bd.clave',
                        'l.idlicencias_empresa', 
                        'l.estado_licencia', 
                        'l.id_almacen',
                        'l.fecha_inicio_licencia', 
                        'l.fecha_vencimiento', 
                        'l.planes_id',
                        'p.nombre_plan', 
                        'p.valor_plan', 
                        'p.dias_vigencia',
                        DB::raw('DATEDIFF(l.fecha_vencimiento, CURDATE()) AS dias')
                    )
                    ->join('crm_licencias_empresa as l', 'e.idempresas_clientes', '=', 'l.idempresas_clientes')
                    ->join('users as u', 'e.idusuario_creacion', '=', 'u.id')
                    ->join('crm_planes as p', 'l.planes_id', '=', 'p.id')
                    ->join('db_config as bd', 'bd.id', '=', 'l.id_db_config')                
                    ->whereIn('bd.servidor', ['pos5v8.cnvvsgytawik.us-east-2.rds.amazonaws.com','pos3v8.cnvvsgytawik.us-east-2.rds.amazonaws.com','demo24.cnvvsgytawik.us-east-2.rds.amazonaws.com'])
                    ->orderBy('l.id_db_config')
                    ->orderBy(DB::raw('DATEDIFF(l.fecha_vencimiento, CURDATE())'))
                   // ->limit(200)
                    ->get();

    }
}


if (!function_exists('ConsultaLicencias')) {

    function ConsultaLicencias($dias) {

        return  DB::connection('vendty2')
                    ->table('crm_empresas_clientes as e')
                    ->select(
                        'u.first_name', 
                        'u.last_name', 
                        'u.email', 
                        'u.phone',
                        'e.nombre_empresa', 
                        'e.id_db_config', 
                        'bd.base_dato',
                        'bd.servidor',
                        'bd.usuario',
                        'bd.clave',
                        'l.idlicencias_empresa', 
                        'l.estado_licencia', 
                        'l.id_almacen',
                        'l.fecha_inicio_licencia', 
                        'l.fecha_vencimiento', 
                        'l.planes_id',
                        'p.nombre_plan', 
                        'p.valor_plan', 
                        'p.dias_vigencia',
                        DB::raw('DATEDIFF(l.fecha_vencimiento, CURDATE()) AS dias')
                    )
                    ->join('crm_licencias_empresa as l', 'e.idempresas_clientes', '=', 'l.idempresas_clientes')
                    ->join('users as u', 'e.idusuario_creacion', '=', 'u.id')
                    ->join('crm_planes as p', 'l.planes_id', '=', 'p.id')
                    ->join('db_config as bd', 'bd.id', '=', 'l.id_db_config')
                    
                    ->where('l.planes_id', '>', 1)
                    ->havingRaw('dias IN(' . $dias . ') AND dias_vigencia IN(360,90,30)')
                    ->whereIn('p.dias_vigencia', [360, 90, 30])
                    ->whereNotIn('bd.servidor', ['pos5.cnvvsgytawik.us-east-2.rds.amazonaws.com','pos3.cnvvsgytawik.us-east-2.rds.amazonaws.com'])
                    ->groupBy('bd.id')  // Agrupar por bd.id
                    ->orderBy('l.id_db_config')
                    ->orderBy(DB::raw('DATEDIFF(l.fecha_vencimiento, CURDATE())'))
                    ->get();

    }
}

if (!function_exists('ConsultaAlmacen')) {
    function ConsultaAlmacen($databaseName, $host, $idAlmacen, $usuario, $clave) {
        
        DB::purge('dynamic');
        $conection = dynamicDatabaseConnection($databaseName, $host, $usuario, $clave);

        return  $conection->table('almacen')
                ->where('id', $idAlmacen)
                ->value('nombre');

        
        DB::reconnect('dynamic');

    }
}

if (!function_exists('ConsultaAlmacenData')) {
    function ConsultaAlmacenData($databaseName, $host, $idAlmacen, $usuario, $clave) {
        
        DB::purge('dynamic');
        $conection = dynamicDatabaseConnection($databaseName, $host, $usuario, $clave);

        return  $conection->table('almacen')
                ->where('id', $idAlmacen)
                ->first();

        
        DB::reconnect('dynamic');

    }
}
if (!function_exists('ConsultaVentaOffline')) {
    function ConsultaVentaOffline($databaseName, $host, $idAlmacen, $usuario, $clave) {
        
        DB::purge('dynamic');
        $conection = @dynamicDatabaseConnection($databaseName, $host, $usuario, $clave);
        if($conection){
            if (!Schema::connection('dynamic')->hasColumn('venta', 'uiid')) {
                return null; // Retornar null si no existe la columna 'uuid'
            }
    
            $venta = @$conection->table('venta')
                    ->whereNotNull('uiid')
                    ->orderBy('id', 'desc') // Suponiendo que tienes una columna 'created_at'
                    ->first();
    
            if($venta){
                return $venta;
            }
            return null;
        }
      
        return null;
        DB::reconnect('dynamic');

    }
}

if (!function_exists('Registros')) {
    function Registros ($start_date, $end_date) {
        //dd($start_date . $end_date);
        return DB::connection('vendty2')
                    ->table('registros')
                    ->select('correo', 'nombre', 'apellidos')
                    ->where('created_at', '>=', $start_date)
                    ->where('created_at', '<=', $end_date)
                    ->where('suscripcion', true)
                    ->get();

    }
}

if (!function_exists('CuentaVentas')) {
    function CuentaVentas($databaseName, $host, $idAlmacen, $usuario, $clave, $tipo = 'cuenta_facturas_electronicas') {
        
        DB::purge('dynamic');
        $conection = @dynamicDatabaseConnection($databaseName, $host, $usuario, $clave);
        if($conection){
            if (!Schema::connection('dynamic')->hasColumn('venta', 'uiid')) {
                return null; // Retornar null si no existe la columna 'uuid'
            }
            if ($tipo == 'cuenta_facturas_electronicas') {
                $venta = @$conection->table('venta')
                    ->where('factura_electronica', 1)
                    ->where('almacen_id', $idAlmacen)
                    ->count(); // Cambiar a count() para contar los registros
            }
            if($tipo == 'ultima_factura_electronica'){
                $venta = @$conection->table('venta')
                    ->where('factura_electronica',1)
                    ->where('almacen_id',$idAlmacen)
                    ->orderBy('id', 'desc') // Suponiendo que tienes una columna 'id'
                    ->first();
            }
    
            if($venta){
                return $venta;
            }
            return null;
        }
      
        return null;
        DB::reconnect('dynamic');

    }
}