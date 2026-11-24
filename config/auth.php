<?php

use App\Models\Empleado;

return [
    'defaults' => ['guard' => 'web', 'passwords' => 'empleados'],
    'guards' => ['web' => ['driver' => 'session', 'provider' => 'empleados']],
    'providers' => ['empleados' => ['driver' => 'eloquent', 'model' => Empleado::class]],
    'passwords' => [],
    'password_timeout' => 10800,
];
