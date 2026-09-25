<?php

namespace App\Models;

use App\Enums\JalurCheckin;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kehadiran extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kehadiran';

    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'rapat_id',
        'undangan_id',
        'ttd_path',
        'foto_path',
        'jalur',
        'perangkat_id',
        'waktu_perangkat',
        'diwakili_oleh',
        'dibatalkan',
        'alasan_batal',
    ];

    protected function casts(): array
    {
        return [
            'jalur' => JalurCheckin::class,
            'dibatalkan' => 'boolean',
            'waktu_perangkat' => 'datetime',
            'dibuat_pada' => 'datetime',
        ];
    }

    public function rapat(): BelongsTo
    {
        return $this->belongsTo(Rapat::class, 'rapat_id');
    }

    public function undangan(): BelongsTo
    {
        return $this->belongsTo(Undangan::class, 'undangan_id');
    }
}
