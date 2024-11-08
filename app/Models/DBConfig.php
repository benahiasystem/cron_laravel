<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DBConfig extends Model
{
    use HasFactory;
    protected $connection = 'vendty2';
    protected $table = 'db_config';
}
