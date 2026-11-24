<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queja extends Model
{
    protected $table = 'quejas';

    public const UPDATED_AT = null;

    protected $guarded = ['id'];
}
