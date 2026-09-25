import db from './db';

/**
 * Service pengelola antrean kehadiran lokal & sinkronisasi otomatis ke server Laravel.
 * Porting murni dari src/lib/antrean.js
 */
class PengelolaAntrean {
    constructor() {
        this.isSyncing = false;
        this.syncIntervalId = null;
        this.initEventListeners();
    }

    initEventListeners() {
        if (typeof window !== 'undefined') {
            window.addEventListener('online', () => {
                console.log('[SIABDES Kiosk] Koneksi pulih. Memulai sinkronisasi antrean...');
                this.sinkronkanSemua();
            });
        }
    }

    /**
     * Memulai timer polling periodik sinkronisasi antrean (setiap 5 detik).
     */
    mulaiAutoSync(intervalMs = 5000) {
        if (this.syncIntervalId) return;
        this.sinkronkanSemua();
        this.syncIntervalId = setInterval(() => {
            this.sinkronkanSemua();
        }, intervalMs);
    }

    hentikanAutoSync() {
        if (this.syncIntervalId) {
            clearInterval(this.syncIntervalId);
            this.syncIntervalId = null;
        }
    }

    /**
     * Menambahkan rekaman check-in baru ke antrean IndexedDB lokal.
     *
     * @param {Object} itemKehadiran
     * @returns {Promise<string>} ID kehadiran (UUID)
     */
    async simpanKehadiranLokal(itemKehadiran) {
        const id = itemKehadiran.id || crypto.randomUUID();
        const rekaman = {
            id: id,
            rapat_id: itemKehadiran.rapat_id,
            undangan_id: itemKehadiran.undangan_id,
            ttd: itemKehadiran.ttd, // base64 PNG
            foto: itemKehadiran.foto || null, // base64 JPEG atau null
            jalur: itemKehadiran.jalur || 'kiosk',
            perangkat_id: itemKehadiran.perangkat_id || this.getPerangkatId(),
            waktu_perangkat: itemKehadiran.waktu_perangkat || new Date().toISOString(),
            undangan_baru: itemKehadiran.undangan_baru || null, // jika tamu tambahan baru
            status_sinkron: 'pending', // 'pending' | 'sukses' | 'gagal'
            status_coba: 0,
            error_terakhir: null,
            dibuat_pada: new Date().toISOString(),
        };

        await db.antrean_kehadiran.put(rekaman);
        this.beriTahuStatus();

        // Segera coba sinkronisasi jika saat ini online
        if (navigator.onLine) {
            this.sinkronkanSemua();
        }

        return id;
    }

    /**
     * Mengambil seluruh data pending dari antrean lokal.
     */
    async getAntreanPending() {
        return await db.antrean_kehadiran
            .where('status_sinkron')
            .equals('pending')
            .toArray();
    }

    /**
     * Mengambil jumlah item antrean yang belum tersinkron.
     */
    async getJumlahPending() {
        return await db.antrean_kehadiran
            .where('status_sinkron')
            .equals('pending')
            .count();
    }

    /**
     * Mengirim seluruh antrean pending ke endpoint backend Laravel: /api/sinkron-kehadiran
     */
    async sinkronkanSemua() {
        if (this.isSyncing || !navigator.onLine) return;

        const pendingItems = await this.getAntreanPending();
        if (pendingItems.length === 0) return;

        this.isSyncing = true;
        console.log(`[SIABDES Kiosk] Mengirim ${pendingItems.length} data absensi ke server...`);

        try {
            const tokenCsrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const response = await fetch('/api/sinkron-kehadiran', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': tokenCsrf || '',
                },
                body: JSON.stringify({
                    items: pendingItems.map(item => ({
                        id: item.id,
                        rapat_id: item.rapat_id,
                        undangan_id: item.undangan_id,
                        ttd: item.ttd,
                        foto: item.foto,
                        jalur: item.jalur,
                        perangkat_id: item.perangkat_id,
                        waktu_perangkat: item.waktu_perangkat,
                        undangan_baru: item.undangan_baru,
                    }))
                }),
            });

            if (response.ok) {
                const hasil = await response.json();
                const suksesIds = hasil.sukses_ids || pendingItems.map(i => i.id);

                // Tandai seluruh id yang sukses
                for (const id of suksesIds) {
                    await db.antrean_kehadiran.update(id, {
                        status_sinkron: 'sukses',
                        disinkron_pada: new Date().toISOString(),
                    });
                }
                console.log(`[SIABDES Kiosk] ${suksesIds.length} data absensi berhasil tersinkronkan ke server.`);
            } else {
                console.warn('[SIABDES Kiosk] Server mengembalikan status:', response.status);
            }
        } catch (error) {
            console.error('[SIABDES Kiosk] Kesalahan jaringan saat sinkronisasi:', error);
        } finally {
            this.isSyncing = false;
            this.beriTahuStatus();
        }
    }

    /**
     * Memperbarui event custom pada window agar komponen UI StatusJaringan reaktif.
     */
    async beriTahuStatus() {
        if (typeof window !== 'undefined') {
            const jumlahPending = await this.getJumlahPending();
            window.dispatchEvent(new CustomEvent('status-antrean-diperbarui', {
                detail: {
                    online: navigator.onLine,
                    jumlahPending: jumlahPending,
                }
            }));
        }
    }

    getPerangkatId() {
        if (typeof window === 'undefined') return 'unknown';
        let id = localStorage.getItem('siabdes_perangkat_id');
        if (!id) {
            id = 'kiosk_' + Math.random().toString(36).substring(2, 9);
            localStorage.setItem('siabdes_perangkat_id', id);
        }
        return id;
    }
}

export const antrean = new PengelolaAntrean();
export default antrean;
