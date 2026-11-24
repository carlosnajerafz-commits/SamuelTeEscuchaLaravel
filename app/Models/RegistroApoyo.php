<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroApoyo extends Model
{
    protected $table = 'registros_apoyos';

    public const UPDATED_AT = null;

    protected $guarded = ['id'];
}
