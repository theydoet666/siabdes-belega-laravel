<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CheckinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SinkronKehadiranController extends Controller
{
    public function __construct(
        protected CheckinService $checkinService
    ) {}

    /**
     * Endpoint penerima sinkronisasi antrean kehadiran offline dari tablet kiosk / client.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $items = $request->input('items');

        // Jika request mengirim single object
        if (empty($items) && $request->has('rapat_id')) {
            $items = [$request->all()];
        }

        if (empty($items) || !is_array($items)) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Tidak ada item antrean absensi yang dikirim.',
            ], 422);
        }

        $suksesIds = [];
        $errors = [];

        foreach ($items as $item) {
            $itemId = $item['id'] ?? null;
            try {
                if (empty($item['rapat_id']) || (empty($item['undangan_id']) && empty($item['undangan_baru'])) || empty($item['ttd'])) {
                    $errors[] = [
                        'id' => $itemId,
                        'error' => 'Data tidak lengkap (rapat_id, undangan_id/undangan_baru, atau ttd kosong).',
                    ];
                    continue;
                }

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

        return response()->json([
            'sukses' => true,
            'jumlah_sukses' => count($suksesIds),
            'sukses_ids' => $suksesIds,
            'gagal_ids' => $gagalIds,
            'errors' => $errors,
        ]);
    }
}
