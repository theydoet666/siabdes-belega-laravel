<?php

namespace App\Models;

use App\Enums\PeranPengguna;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profil extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'profil';

    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'user_id',
        'nama',
        'email',
        'peran',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'peran' => PeranPengguna::class,
            'aktif' => 'boolean',
            'dibuat_pada' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rapat(): HasMany
    {
        return $this->hasMany(Rapat::class, 'dibuat_oleh');
    }

    public function auditLog(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'aktor');
    }
}
