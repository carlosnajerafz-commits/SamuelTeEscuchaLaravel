<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventoComedor extends Model
{
    protected $table = 'eventos_comedor';

    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    /** @return HasMany<RegistroComedor, $this> */
    public function registros(): HasMany
    {
        return $this->hasMany(RegistroComedor::class, 'evento_id');
    }
}
