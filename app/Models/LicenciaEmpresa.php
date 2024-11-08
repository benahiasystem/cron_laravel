<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenciaEmpresa extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $connection = 'vendty2';
    protected $table = 'crm_licencias_empresa';
}
