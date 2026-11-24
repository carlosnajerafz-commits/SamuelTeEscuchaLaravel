# API REST y Chatbot — Guía de instalación

Para el proyecto **SamuelTeEscuchaLaravel**. El proyecto PHP original no se toca.

---

## Dónde va cada archivo

| Archivo entregado | Ruta dentro del proyecto Laravel |
|---|---|
| `routes/api.php` | `routes/api.php` |
| `app/Http/Controllers/Api/V1/*.php` | `app/Http/Controllers/Api/V1/` |
| `app/Http/Resources/*.php` | `app/Http/Resources/` |
| `app/Http/Controllers/ChatbotController.php` | `app/Http/Controllers/` |
| `app/Services/ChatbotService.php` | `app/Services/` |
| `resources/views/components/chatbot.blade.php` | `resources/views/components/` |
| `public/assets/css/chatbot.css` | `public/assets/css/` |
| `public/assets/js/chatbot.js` | `public/assets/js/` |
| `tests/Feature/ApiTest.php` | `tests/Feature/` |

Las carpetas `Api/V1`, `Resources`, `Services` y `components` probablemente no existen
todavía: créalas.

---

## Paso 1 — Instalar Sanctum y habilitar las rutas de API

Laravel 11 en adelante ya no trae `routes/api.php` por defecto. Este comando lo
registra y de paso instala Sanctum:

```bash
php artisan install:api
```

Responde **yes** cuando pregunte si quieres ejecutar las migraciones: crea la tabla
`personal_access_tokens`, que es donde viven los tokens.

Después **sustituye** el `routes/api.php` que generó Laravel por el que te entregué.

---

## Paso 2 — Preparar el modelo Empleado

Abre `app/Models/Empleado.php` y añade el trait de Sanctum:

```php
use Laravel\Sanctum\HasApiTokens;

class Empleado extends Authenticatable
{
    use HasApiTokens;   // <-- agregar esta línea

    // ... el resto del modelo se queda igual
}
```

Si `Empleado` extiende `Model` en lugar de `Authenticatable`, cámbialo:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;

class Empleado extends Authenticatable
```

Y confirma que oculta la contraseña:

```php
protected $hidden = ['password', 'remember_token'];
```

---

## Paso 3 — Apuntar el guard de Sanctum a los empleados

En `config/auth.php`, dentro de `providers`, agrega (o ajusta):

```php
'providers' => [
    'empleados' => [
        'driver' => 'eloquent',
        'model'  => App\Models\Empleado::class,
    ],
],
```

Y en `guards`:

```php
'guards' => [
    'sanctum' => [
        'driver'   => 'sanctum',
        'provider' => 'empleados',
    ],
],
```

---

## Paso 4 — Relación que necesita el comedor

El endpoint de eventos calcula el cupo disponible con `withSum`, así que
`EventoComedor` necesita conocer sus registros. En `app/Models/EventoComedor.php`:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function registros(): HasMany
{
    return $this->hasMany(RegistroComedor::class, 'evento_id');
}
```

---

## Paso 5 — Rutas del chatbot

En `routes/web.php`, agrega:

```php
use App\Http\Controllers\ChatbotController;

Route::post('/chatbot/mensaje', [ChatbotController::class, 'mensaje'])
    ->middleware('throttle:30,1')
    ->name('chatbot.mensaje');

Route::post('/chatbot/reiniciar', [ChatbotController::class, 'reiniciar'])
    ->name('chatbot.reiniciar');
```

El límite de 30 mensajes por minuto evita que alguien sature el servidor
mandando mensajes en bucle.

---

## Paso 6 — Mostrar el widget

En `resources/views/layouts/public.blade.php`, justo antes de `</body>`:

```blade
    <x-chatbot />
</body>
```

Eso es todo: el componente carga su propio CSS y su propio JS.

---

## Paso 7 — Fechas en español

El chatbot escribe fechas como *"martes 2 de diciembre"*. Para que salgan en
español, en `config/app.php`:

```php
'locale' => 'es',
'timezone' => 'America/Mexico_City',
```

---

## Paso 8 — Probar

```bash
php artisan route:clear
php artisan config:clear
php artisan serve
```

**La API:**

```bash
# Catálogo público de apoyos
curl http://localhost:8000/api/v1/apoyos

# Obtener un token
curl -X POST http://localhost:8000/api/v1/token \
  -H "Content-Type: application/json" \
  -d '{"usuario":"tu_usuario","password":"tu_contrasena"}'

# Usar el token
curl http://localhost:8000/api/v1/citas \
  -H "Authorization: Bearer EL_TOKEN_QUE_TE_DIO"
```

**El chatbot:** abre el sitio y da clic en el botón flotante de abajo a la derecha.

**Las pruebas automatizadas:**

```bash
php artisan test --filter=ApiTest
```

---

## Endpoints disponibles

### Públicos

| Método | Ruta | Devuelve |
|---|---|---|
| POST | `/api/v1/token` | Token Bearer a cambio de usuario y contraseña |
| GET | `/api/v1/apoyos` | Catálogo de apoyos activos |
| GET | `/api/v1/comedor/eventos` | Próximos eventos con cupo disponible |
| GET | `/api/v1/codigos-postales/{cp}` | Colonias y municipio de un CP |
| GET | `/api/v1/citas/disponibilidad/{fecha}` | Horarios libres de ese martes |
| POST | `/api/v1/seguimiento` | Estatus de una solicitud (folio + celular) |

### Autenticados (requieren `Authorization: Bearer <token>`)

| Método | Ruta | Devuelve |
|---|---|---|
| GET | `/api/v1/me` | Empleado dueño del token |
| DELETE | `/api/v1/token` | Revoca el token actual |
| GET | `/api/v1/citas` | Listado paginado de citas |
| GET | `/api/v1/citas/{id}` | Detalle de una cita |
| PATCH | `/api/v1/citas/{id}/estatus` | Cambia el estatus |
| GET | `/api/v1/quejas` | Listado paginado de quejas |
| GET | `/api/v1/quejas/{id}` | Detalle de una queja |
| PATCH | `/api/v1/quejas/{id}/estatus` | Cambia el estatus |
| GET | `/api/v1/registros-apoyo` | Solicitudes de apoyo |
| PATCH | `/api/v1/registros-apoyo/{id}/estatus` | Cambia el estatus |
| GET | `/api/v1/comedor/registros` | Personas inscritas al comedor |
| GET | `/api/v1/estadisticas` | Conteos agregados, sin datos personales |

---

## Decisiones de seguridad que conviene poder defender

Estas son las que un profesor puede preguntarte, con su razón.

**El seguimiento exige folio *y* celular.** Los folios son correlativos:
`CITA-000014` es el registro 14. Si bastara el folio, cualquiera podría recorrerlos
uno por uno y leer solicitudes ajenas. Pedir también el celular convierte el folio
en un identificador y el celular en la prueba de identidad.

**El seguimiento responde igual ante folio inexistente y celular equivocado.**
Si dijera *"ese folio no existe"* en un caso y *"el celular no coincide"* en el otro,
un atacante sabría qué folios están en uso. El mensaje idéntico cierra esa fuga.

**El seguimiento devuelve solo el estatus.** Nunca nombre, domicilio, correo ni el
contenido de la solicitud. Lo mínimo para que el ciudadano sepa cómo va su trámite.

**Los documentos no se exponen.** Los *Resources* devuelven
`tiene_identificacion: true|false` en lugar de la ruta del archivo. Saber que existe
un documento no es lo mismo que poder descargarlo.

**Cada grupo de rutas tiene su propio límite de peticiones.** Cinco por minuto para
pedir token (freno a la fuerza bruta), diez para seguimiento (freno a la enumeración
de folios), sesenta para los catálogos públicos y ciento veinte para las rutas
autenticadas.

**El chatbot no manda nada fuera del servidor.** Al ser por reglas, el texto que
escribe el ciudadano nunca sale de la infraestructura de la oficina. Un chatbot con
IA de terceros implicaría transferir datos personales a un proveedor externo, lo que
exigiría consentimiento expreso conforme a la LFPDPPP y habría que declararlo en el
aviso de privacidad.

**El chatbot escapa el HTML antes de pintarlo.** `aHtmlSeguro()` escapa todo el texto
y después reconvierte únicamente `**negritas**`. Lo que escribe la persona se inserta
con `textContent`, nunca con `innerHTML`, de modo que no hay forma de inyectar script.

---

## Lo que el chatbot sabe responder

Por reglas de palabras clave, con tolerancia a acentos y mayúsculas:

- Consulta de folio (conversación de dos turnos: folio, luego celular)
- Cómo agendar cita, con los horarios libres del próximo martes **leídos de la base**
- Cómo reportar un problema de servicios públicos
- Qué apoyos están abiertos **leídos de la base**
- Próximas fechas del comedor con su cupo **leídas de la base**
- Teléfono, WhatsApp y ubicación
- Horarios de atención
- Aviso de privacidad y derechos ARCO
- Saludos, despedidas y agradecimientos

Cuando no entiende, lo dice y deriva al teléfono en vez de inventar una respuesta.

Para agregar temas, edita el arreglo `INTENCIONES` en `ChatbotService.php` y añade
su método `responderX()`.
