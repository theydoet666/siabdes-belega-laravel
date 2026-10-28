<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatusRapat;
use App\Http\Controllers\Controller;
use App\Http\Requests\SinkronKehadiranRequest;
use App\Models\Rapat;
use App\Services\CheckinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SinkronKehadiranController extends Controller
{
    public function __construct(
        protected CheckinService $checkinService
    ) {}

    /**
     * Endpoint penerima sinkronisasi antrean kehadiran offline dari tablet kiosk / client.
     */
    public function __invoke(SinkronKehadiranRequest $request): JsonResponse
    {
        $items = $request->validated('items');

        $suksesIds = [];
        $errors = [];

        foreach ($items as $item) {
            $itemId = $item['id'] ?? null;
            $jalur = $item['jalur'] ?? 'kiosk';
            $rapatId = $item['rapat_id'];

            // 1. Otorisasi Jalur Operator: Mewajibkan sesi login operator/admin aktif
            if ($jalur === 'operator') {
                $isOperator = Auth::check() && Auth::user()->profil?->aktif && in_array(Auth::user()->profil?->peran?->value ?? '', ['admin', 'operator'], true);
                if (! $isOperator) {
                    $errors[] = [
                        'id' => $itemId,
                        'error' => 'Akses ditolak: jalur operator mewajibkan sesi operator atau admin yang aktif.',
                    ];

                    continue;
                }
            }

            // 2. Otorisasi Jalur Kiosk: Mewajibkan sesi kiosk aktif atau header X-Kiosk-Token yang valid
            if ($jalur === 'kiosk') {
                $sessionUnlocked = $request->hasSession() && $request->session()->get('kiosk_unlocked_'.$rapatId) === true;
                $sessionToken = $request->hasSession() ? (string) $request->session()->get('kiosk_token_'.$rapatId, '') : '';
                $cachedToken = (string) Cache::get('kiosk_token_'.$rapatId, '');
                $headerToken = (string) $request->header('X-Kiosk-Token', '');
                $validToken = (! empty($headerToken) && ! empty($cachedToken) && hash_equals($cachedToken, $headerToken))
                    || (! empty($headerToken) && ! empty($sessionToken) && hash_equals($sessionToken, $headerToken));

                if (! $sessionUnlocked && ! $validToken && ! Auth::check()) {
                    $errors[] = [
                        'id' => $itemId,
                        'error' => 'Akses ditolak: sesi meja kiosk belum dibuka atau token tidak valid.',
                    ];

                    continue;
                }
            }

            // 3. Otorisasi Jalur Mandiri: Pastikan rapat ada dan belum ditutup
            if ($jalur === 'mandiri') {
                $rapat = Rapat::find($rapatId);
                if (! $rapat || $rapat->status === StatusRapat::DITUTUP) {
                    $errors[] = [
                        'id' => $itemId,
                        'error' => 'Akses ditolak: registrasi mandiri ditutup untuk rapat ini.',
                    ];

                    continue;
                }
            }

            try {
                $kehadiran = $this->checkinService->prosesCheckin($item);
                $suksesIds[] = $itemId ?: $kehadiran->id;
            } catch (\Exception $e) {
                Log::error('[SIABDES Sinkron API] Gagal memproses item absensi:', [
                    'item_id' => $itemId,
                    'error' => $e->getMessage(),
                ]);
                $errors[] = [
                    'id' => $itemId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $gagalIds = array_values(array_filter(array_column($errors, 'id')));

        // Jika seluruh item gagal diproses, kembalikan status error (403 jika otorisasi, 422 jika data)
        if (empty($suksesIds) && ! empty($errors)) {
            $isAksesDitolak = collect($errors)->every(fn ($e) => str_contains($e['error'], 'Akses ditolak'));

            return response()->json([
                'sukses' => false,
                'pesan' => 'Seluruh data absensi gagal disinkronkan.',
                'gagal_ids' => $gagalIds,
                'errors' => $errors,
            ], $isAksesDitolak ? 403 : 422);
        }

        return response()->json([
            'sukses' => true,
            'jumlah_sukses' => count($suksesIds),
            'sukses_ids' => $suksesIds,
            'gagal_ids' => $gagalIds,
            'errors' => $errors,
        ]);
    }
}
