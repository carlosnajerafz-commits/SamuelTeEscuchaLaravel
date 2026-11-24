<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Empleado extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'empleados';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $hidden = ['password_hash'];

    protected $rememberTokenName = '';

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn () => $this->nombre_completo);
    }
}
