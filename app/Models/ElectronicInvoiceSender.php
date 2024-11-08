<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ElectronicInvoiceSender extends Model
{
    use HasFactory;
    protected $connection = 'VendtyServices';
    protected $table = 'electronic_invoice_sender';

    protected $fillable = [
        'tipo_facturacion', 'base_dato', 'venta_id', 'token', 'data_to_send', 'respuesta', 'estado', 'creacion', 'comentario', 'ultimo_intento', 'updated_at', 'created_at'
   ];
   
}
