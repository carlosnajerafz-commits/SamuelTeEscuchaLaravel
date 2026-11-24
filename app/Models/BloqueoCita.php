<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BloqueoCita extends Model
{
    protected $table = 'bloqueos_cita';

    public const UPDATED_AT = null;

    protected $guarded = ['id'];
}
