<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaturanSistem extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_sistem';

    public $incrementing = false;
    protected $primaryKey = 'id';

    const CREATED_AT = null;
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id',
        'nama_sistem',
        'subjudul_sistem',
        'nama_desa',
        'alamat_desa',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'logo_url',
        'favicon_url',
        'diperbarui_oleh',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'diperbarui_pada' => 'datetime',
        ];
    }

    public function getLogoUrlAttribute($value): ?string
    {
        if (!$value) return null;
        if (str_contains($value, '/storage/')) {
            $path = substr($value, strpos($value, '/storage/'));
            return asset(ltrim($path, '/'));
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }
        return asset('storage/' . ltrim($value, '/'));
    }

    public function getFaviconUrlAttribute($value): ?string
    {
        if (!$value) return null;
        if (str_contains($value, '/storage/')) {
            $path = substr($value, strpos($value, '/storage/'));
            return asset(ltrim($path, '/'));
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }
        return asset('storage/' . ltrim($value, '/'));
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(Profil::class, 'diperbarui_oleh');
    }
}
