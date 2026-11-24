<?php

use App\Models\Cita;
use App\Models\Queja;
use App\Models\RegistroApoyo;
use App\Models\RegistroComedor;

return [
    'modules' => [
        'quejas' => ['title' => 'Gestión y quejas', 'model' => Queja::class, 'statuses' => ['pendiente', 'atendida', 'cerrada', 'completada']],
        'citas' => ['title' => 'Martes ciudadano', 'model' => Cita::class, 'statuses' => ['solicitada', 'aceptada', 'rechazada', 'realizada', 'cancelada', 'vencida']],
        'apoyos' => ['title' => 'Apoyos', 'model' => RegistroApoyo::class, 'statuses' => ['pendiente', 'en proceso', 'entregado', 'cancelado']],
        'comedor' => ['title' => 'Comedor solidario', 'model' => RegistroComedor::class, 'statuses' => ['pendiente', 'confirmado', 'cancelado', 'asistio']],
    ],
    'slots' => ['09:00', '09:20', '09:40', '10:00', '10:20', '10:40', '11:00', '11:20', '11:40', '12:00', '12:20', '12:40', '13:00', '13:20', '13:40', '14:00', '14:20', '14:40'],
    'categories' => ['Adultos mayores', 'Personas con discapacidad', 'Madres solteras y jefas de familia', 'Niñas, niños y adolescentes', 'Personas en situación de pobreza', 'Personas desempleadas', 'Personas en situación de calle', 'Personas migrantes', 'Personas cuidadoras sin ingresos suficientes', 'Familias en situación de vulnerabilidad económica', 'Personas con enfermedades que les impidan trabajar', 'Víctimas de violencia o desplazamiento', 'Comunidades indígenas en situación de marginación'],
    'legacy_uploads' => env('LEGACY_UPLOADS_PATH'),
];
