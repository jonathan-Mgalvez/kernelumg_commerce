<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_INVENTARIO = 'inventario';
    public const ROLE_CLIENTE = 'cliente';

    public const ROLES = [
        self::ROLE_ADMIN => 'Administrador',
        self::ROLE_INVENTARIO => 'Gestor de inventario',
        self::ROLE_CLIENTE => 'Cliente',
    ];

    protected $attributes = ['active' => true];

    /**
     * The attributes that are mass assignable.
     * El rol NO es asignable masivamente para que nadie se registre como admin.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isInventario(): bool
    {
        return $this->role === self::ROLE_INVENTARIO;
    }

    /** Admin o gestor de inventario: pueden entrar al panel (productos, ofertas, pedidos...). */
    public function canManageInventory(): bool
    {
        return $this->isAdmin() || $this->isInventario();
    }

    /** El gestor de inventario no cotiza: solo administra productos, ofertas y pedidos. */
    public function puedeCotizar(): bool
    {
        return ! $this->isInventario();
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst((string) $this->role);
    }
}
