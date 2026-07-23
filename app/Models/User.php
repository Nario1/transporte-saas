<?php
 
// ══════════════════════════════════════════════════════════════════
// app/Models/User.php  — actualizado con conductor_id
// ══════════════════════════════════════════════════════════════════
 
namespace App\Models;
 
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
 
use App\Traits\AuditableWithEmpresa;
use OwenIt\Auditing\Contracts\Auditable;
 
class User extends Authenticatable implements Auditable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, AuditableWithEmpresa;
 
    protected $fillable = [
        'empresa_id',
        'conductor_id',   // ← nuevo
        'name',
        'email',
        'password',
        'activo',
    ];
 
    protected $hidden = ['password', 'remember_token'];
 
    protected $casts = [
        'activo'             => 'boolean',
        'email_verified_at'  => 'datetime',
    ];
 
    // ── Relaciones ──
    public function empresa()   { return $this->belongsTo(Empresa::class); }
    public function conductor() { return $this->belongsTo(Conductor::class); }  // ← nuevo
 
    // ── Helpers ──
    public function getInicialesAttribute(): string
    {
        $parts = explode(' ', $this->name);
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[1] ?? '', 0, 1));
    }
 
    public function getRolLabelAttribute(): string
    {
        return $this->roles->first()?->name ?? 'Sin rol';
    }
 
    /** e1_ADMIN, Supervisor -> ADMIN, Supervisor */
    public function getRolesLimpiosAttribute(): string
    {
        return $this->roles->map(function ($role) {
            return preg_replace('/^e\d+_/', '', $role->name);
        })->join(', ');
    }
 
    /** Si es un usuario con el rol ADMINISTRADOR de la empresa que no se puede eliminar */
    public function getIsAdminProtectedAttribute(): bool
    {
        return $this->roles->contains(function ($role) {
            return preg_replace('/^e\d+_/', '', $role->name) === 'ADMIN';
        }) || $this->hasRole('SUPER_ADMIN');
    }
 
    public function esConductor(): bool
    {
        return $this->hasRole('conductor');
    }
 
    public function esAdmin(): bool
    {
        return $this->hasRole('administrador') || $this->hasRole('superadmin');
    }
}