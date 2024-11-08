<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class BlackList extends Model
{
    use HasFactory;
    protected $connection = 'VendtyServices';
    protected $table = 'email_blacklist';

    protected $fillable = [
        'email', 'motivo', 'creado'
   ];
   
}
