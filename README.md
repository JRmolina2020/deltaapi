# API Laravel 6 JWT

### Crear proyecto
```
composer global require laravel/installer
laravel new laravel-6-api-jwt
```

### Crear BD
```
php artisan make:model Models\\Product -mf
php artisan make:seed ProductSeed
```
### Configurar sistema de Storage
Cambiar
```
'default' => env('FILESYSTEM_DRIVER', 'local'),
```
por 
```
'default' => env('FILESYSTEM_DRIVER', 'public'),
```
Crear enlace simbólico a directorio storage
```
php artisan storage:link
```
### Cambiar namespace modelo User a Models

Mover modelo User a directorio Models

Actualizar namespace modelo User

Actualizar en config/auth.php
```
'providers' => [
    'users' => [
        // ...
        'model' => App\Models\User::class,
    ],
```
### Insertar datos iniciales
```
php artisan migrate:fresh --seed
```
### Instalar y configurar JWT
```
composer require tymon/jwt-auth ^1.0.0
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```
# Actualizar modelo User para utilizar JWT
```
<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
```
# Actualizar configuración AUTH en config/auth.php
Cambiar 
```
'guard' => 'web',
```
por 
```
'guard' => 'auth',
```

Cambiar
```
'guards' => [
        // ...
        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
    ],
```
por 
```
'guards' => [
        // ...
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
            'hash' => false,
        ],
    ],
```
### Rutas API
Actualizar el archivo routes/api.php
### Instalar Laravel CORS    
```
composer require barryvdh/laravel-cors
php artisan vendor:publish --provider="Barryvdh\Cors\ServiceProvider"
```
Actualizar archivo configuración CORS añadiendo Header Authorization.

Actualizar Kernel.php para utilizar Middleware CORS en API
```
protected $middlewareGroups = [
    'api' => [
        // ...
        \Barryvdh\Cors\HandleCors::class,
    ],
];
```
### Crear Middleware JwtMiddleware
Es necesario para controlar las peticiones HTTP
```
php artisan make:middleware JwtMiddleware
```
Añadir a Kernel
```
'jwt.verify' => \App\Http\Middleware\JwtMiddleware::class
```
### Limpiar configuración
```
php artisan config:clear
```
### Crear controlador Api\\AuthController
```
php artisan make:controller Api\\AuthController
```
Definir métodos y probar con Postman que todo funciona bien.

### Crear controlador Api\\ProductController
```
php artisan make:controller Api\\ProductController
```
Añadir todas las operaciones resource y probar con Postman.