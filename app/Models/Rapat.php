<?php

namespace App\Models;

use App\Enums\StatusRapat;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rapat extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rapat';

    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id',
        'kode',
        'judul',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'tempat',
        'penyelenggara',
        'catatan',
        'status',
        'pin_kiosk',
        'retensi_hari',
        'foto_dihapus_pada',
        'verifikator_tampil',
        'verifikator_label',
        'verifikator_jabatan',
        'verifikator_nama',
        'verifikator_nip',
        'pelaksana_tampil',
        'pelaksana_jabatan',
        'pelaksana_nama',
        'pelaksana_nip',
        'mengetahui_tampil',
        'mengetahui_label',
        'penandatangan_nama',
        'penandatangan_jabatan',
        'penandatangan_nip',
        'penandatangan_lokasi',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusRapat::class,
            'tanggal' => 'date',
            'retensi_hari' => 'integer',
            'verifikator_tampil' => 'boolean',
            'pelaksana_tampil' => 'boolean',
            'mengetahui_tampil' => 'boolean',
            'foto_dihapus_pada' => 'datetime',
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(Profil::class, 'dibuat_oleh');
    }

    public function undangan(): HasMany
    {
        return $this->hasMany(Undangan::class, 'rapat_id');
    }

    public function kehadiran(): HasMany
    {
        return $this->hasMany(Kehadiran::class, 'rapat_id');
    }
}
