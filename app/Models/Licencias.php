<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licencias extends Model
{
    use HasFactory;
    protected $connection = 'vendty2';
    protected $table = 'v_crm_licencias';
}
