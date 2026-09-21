<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * ============================================================================
 * CLASE: User (Modelo de Autenticación y Usuarios)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Representa a los usuarios con acceso administrativo o técnico al sistema del 
 * taller (tabla 'users'). Proporciona las bases de autenticación, control de 
 * sesiones, emisión de tokens API (Sanctum) y notificaciones del sistema.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Hashing Nativo Automático de Contraseñas (Laravel 11):
 *   El casteo 'password' => 'hashed' asegura que cualquier contraseña asignada 
 *   al modelo sea encriptada automáticamente con Bcrypt/Argon2id, previniendo 
 *   que se guarden claves en texto plano por error humano.
 * - Ocultación Estricta de Credenciales en JSON:
 *   El arreglo '$hidden' garantiza que el hash de la contraseña y el token 
 *   de recordatorio de sesión jamás se expongan en las respuestas del API REST.
 *
 * PROPIEDADES DE LA TABLA 'users':
 * @property int                 $id                 Identificador único de usuario
 * @property string              $name               Nombre completo del operador o mecánico
 * @property string              $email              Correo electrónico único para inicio de sesión
 * @property string              $password           Hash criptográfico de la contraseña
 * @property \Carbon\Carbon|null $email_verified_at  Fecha de verificación de correo
 * @property string|null         $remember_token     Token de sesión persistente
 * @property \Carbon\Carbon      $created_at         Fecha de registro
 * @property \Carbon\Carbon      $updated_at         Fecha de última actualización
 * ============================================================================
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // =========================================================================
    // SECCIÓN 1: CAMPOS HABILITADOS PARA ASIGNACIÓN MASIVA
    // =========================================================================

    /**
     * Atributos asignables de forma masiva durante el registro o edición de perfil.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // =========================================================================
    // SECCIÓN 2: PROTECCIÓN DE CREDENCIALES Y SERIALIZACIÓN
    // =========================================================================

    /**
     * Atributos ocultos en la serialización JSON del API para evitar fugas de seguridad.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // =========================================================================
    // SECCIÓN 3: TRANSFORMACIÓN DE TIPOS Y HASHING AUTOMÁTICO
    // =========================================================================

    /**
     * Reglas de casteo de atributos (Laravel 11 método casts()).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
