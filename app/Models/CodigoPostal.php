<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoPostal extends Model
{
    protected $table = 'codigos_postales';

    public $timestamps = false;

    protected $fillable = ['codigo_postal', 'colonia', 'municipio'];
}
