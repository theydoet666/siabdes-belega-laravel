<?php

namespace App\Models;

use App\Enums\SumberUndangan;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Undangan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'undangan';

    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'rapat_id',
        'nama',
        'jabatan',
        'instansi',
        'hp',
        'sumber',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'sumber' => SumberUndangan::class,
            'urutan' => 'integer',
            'dibuat_pada' => 'datetime',
        ];
    }

    public function rapat(): BelongsTo
    {
        return $this->belongsTo(Rapat::class, 'rapat_id');
    }

    public function kehadiran(): HasMany
    {
        return $this->hasMany(Kehadiran::class, 'undangan_id');
    }
}
