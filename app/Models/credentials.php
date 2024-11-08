<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class credentials extends Model
{
    use HasFactory;
    
    protected $connection = 'VendtyServices';
    protected $table = 'configuracion_general';

    protected $fillable = [
        'smtp_user', 'smtp_pass'
   ];
}
