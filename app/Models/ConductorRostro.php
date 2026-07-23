<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConductorRostro extends Model
{
    protected $table = 'conductor_rostros';

    protected $fillable = [
        'conductor_id',
        'embedding',
        'foto_path',
        'activo',
    ];

    protected $casts = [
        'embedding' => 'array',
        'activo'    => 'boolean',
    ];

    // ── Relaciones ──
    public function conductor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Conductor::class);
    }

    // ── Accessors ──
    public function getFotoUrlAttribute(): string
    {
        return asset('storage/' . $this->foto_path);
    }
}
