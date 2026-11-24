<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroComedor extends Model
{
    protected $table = 'registros_comedor';

    public const UPDATED_AT = null;

    protected $guarded = ['id'];
}
