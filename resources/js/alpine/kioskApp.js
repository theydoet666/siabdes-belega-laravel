import db from '../offline/db';
import antrean from '../offline/antrean';
import { normalisasiTeks, cocokKueriToken, hitungSkorRelevansi } from '../offline/pencarian';
import { generateUUID } from '../lib/uuid';

/**
 * Helper notifikasi konsisten SweetAlert2 bertema Desa Belega
 */
function notifikasiSiabdes({ icon = 'info', title = '', text = '', html = '', confirmButtonText = 'Tutup', timer = null }) {
    if (window.SiabdesAlert) {
        return window.SiabdesAlert.fire({
            icon,
            title,
            text,
            html,
            confirmButtonText,
            timer,
            timerProgressBar: Boolean(timer),
            showConfirmButton: !timer
        });
    }
    if (window.Swal) {
        return window.Swal.fire({
            icon,
            title,
            text,
            html,
            confirmButtonText,
            timer,
            timerProgressBar: Boolean(timer)
        });
    }
    alert(text || title);
}

/**
 * Komponen utama Kiosk Registrasi Mandiri Offline-First SIABDES Belega.
 * Mengelola alur state machine: cari -> (modal/form) -> ttd -> foto -> bukti
 */
export default function kioskApp(konfigurasi) {
    const jalurTerdeteksi = konfigurasi.jalur || (window.location.pathname.includes('/r/') ? 'mandiri' : 'kiosk');

    return {
        rapat: konfigurasi.rapat,
        rapatId: konfigurasi.rapat.id,
        kodeRapat: konfigurasi.rapat.kode,
        jalur: jalurTerdeteksi,

        // State Machine Layar: 'cari' | 'modal_daftar' | 'form_tambahan' | 'ttd' | 'foto' | 'bukti'
        layar: 'cari',

        // Pencarian Nama
        kueriCari: '',
        hasilCari: [],
        undanganDipilih: null,

        // Modal Daftar Undangan
        daftarLengkap: [],
        kueriModal: '',
        halamanModal: 1,
        perHalamanModal: 12,

        // Form Undangan Tambahan
        namaTambahan: '',
        jabatanTambahan: '',
        instansiTambahan: '',
        hpTambahan: '',
        errorFormTambahan: null,

        // Data Hasil Check-in
        ttdBase64: null,
        fotoBase64: null,
        dataBuktiHadir: null,
        timerSelesai: 8,
        timerSelesaiInterval: null,

        // Modal QR Code Absensi Mandiri
        tampilModalQr: false,

        // State Refresh Data
        sedangRefresh: false,

        // Status Jaringan
        isOnline: navigator.onLine,
        jumlahAntreanPending: 0,

        async init() {
            // Preload data undangan awal yang di-inject server ke Dexie
            if (konfigurasi.undanganAwal && Array.isArray(konfigurasi.undanganAwal)) {
                await this.preloadUndangan(konfigurasi.undanganAwal);
            }

            // Jalankan auto sync antrean offline
            antrean.mulaiAutoSync(5000);

            // Listener perubahan status jaringan & antrean
            window.addEventListener('status-antrean-diperbarui', (e) => {
                this.isOnline = e.detail.online;
                this.jumlahAntreanPending = e.detail.jumlahPending;
            });

            window.addEventListener('online', () => { this.isOnline = true; });
            window.addEventListener('offline', () => { this.isOnline = false; });

            this.jumlahAntreanPending = await antrean.getJumlahPending();
        },

        async preloadUndangan(daftarUndangan) {
            try {
                // Ambil daftar undangan_id yang sudah ada di antrean lokal
                const antreanList = await db.antrean_kehadiran.where('rapat_id').equals(this.rapatId).toArray();
                const antreanMap = new Map(antreanList.map(a => [a.undangan_id, a.id]));

                // Simpan/perbarui ke Dexie
                await db.undangan.bulkPut(daftarUndangan.map(u => ({
                    id: u.id,
                    rapat_id: u.rapat_id || this.rapatId,
                    nama: u.nama,
                    jabatan: u.jabatan || '',
                    instansi: u.instansi || '',
                    hp: u.hp || '',
                    sumber: u.sumber || 'import',
                    urutan: u.urutan || 0,
                    nama_cari: normalisasiTeks(u.nama),
                    sudah_hadir: Boolean(u.sudah_hadir) || antreanMap.has(u.id),
                    kehadiran_id: u.kehadiran_id || antreanMap.get(u.id) || null,
                })));
            } catch (err) {
                console.warn('[SIABDES Dexie] Gagal preload data undangan:', err);
            }
        },

        async cariNama() {
            const kueri = this.kueriCari.trim();
            if (kueri.length < 2) {
                this.hasilCari = [];
                return;
            }

            // Ambil daftar antrean lokal untuk sinkronisasi status
            const antreanList = await db.antrean_kehadiran.where('rapat_id').equals(this.rapatId).toArray();
            const antreanMap = new Map(antreanList.map(a => [a.undangan_id, a.id]));

            // Cari dari IndexedDB lokal
            const semuaUndangan = await db.undangan
                .where('rapat_id')
                .equals(this.rapatId)
                .toArray();

            this.hasilCari = semuaUndangan
                .map(item => ({
                    ...item,
                    sudah_hadir: Boolean(item.sudah_hadir) || antreanMap.has(item.id),
                    kehadiran_id: item.kehadiran_id || antreanMap.get(item.id) || null
                }))
                .filter(item => cocokKueriToken(item, kueri))
                .sort((a, b) => {
                    if (a.sudah_hadir !== b.sudah_hadir) {
                        return a.sudah_hadir ? 1 : -1;
                    }
                    return hitungSkorRelevansi(b, kueri) - hitungSkorRelevansi(a, kueri);
                })
                .slice(0, 6);
        },

        pilihUndangan(item) {
            if (item.sudah_hadir) {
                if (item.kehadiran_id) {
                    window.open('/r/' + this.rapat.kode + '/bukti/' + item.kehadiran_id, '_blank');
                    return;
                }

                notifikasiSiabdes({
                    icon: 'info',
                    title: 'Sudah Tercatat Hadir',
                    text: `Peserta "${item.nama}" sudah menyelesaikan absensi pada rapat ini.`,
                    confirmButtonText: 'Tutup'
                });
                return;
            }
            this.undanganDipilih = item;
            this.kueriCari = '';
            this.hasilCari = [];
            this.ttdBase64 = null;
            this.fotoBase64 = null;
            window.dispatchEvent(new CustomEvent('siapkan-kanvas-ttd'));
            this.pindahLayar('ttd');
        },

        async refreshData() {
            if (this.sedangRefresh) return;
            this.sedangRefresh = true;

            if (!navigator.onLine) {
                this.sedangRefresh = false;
                notifikasiSiabdes({
                    icon: 'warning',
                    title: 'Perangkat Offline',
                    text: 'Tablet sedang tidak terhubung ke jaringan internet/lokal. Menampilkan data yang tersimpan di memori lokal.'
                });
                return;
            }

            try {
                // 1. Dorong antrean offline lokal terlebih dahulu jika ada
                await antrean.prosesSinkronisasi();

                // 2. Ambil data undangan dan status kehadiran terbaru dari server
                const response = await fetch(`/kiosk/${encodeURIComponent(this.kodeRapat)}/data`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error ${response.status}`);
                }

                const json = await response.json();
                if (json.status === 'success' && Array.isArray(json.undangan)) {
                    // Update cache IndexedDB lokal
                    await this.preloadUndangan(json.undangan);

                    // Refresh pencarian nama jika sedang mengetik
                    if (this.kueriCari.trim()) {
                        await this.cariNama();
                    }

                    // Refresh modal daftar jika sedang terbuka
                    if (this.layar === 'modal_daftar') {
                        await this.bukaModalDaftar();
                    }

                    notifikasiSiabdes({
                        icon: 'success',
                        title: 'Data Berhasil Diperbarui',
                        html: `Data kehadiran dan undangan berhasil disinkronkan dengan server.<br><div class="mt-2 text-xs font-semibold text-emerald-800 bg-emerald-50 rounded-xl p-2.5 border border-emerald-200">Total: <b>${json.total}</b> Undangan · <b>${json.total_hadir}</b> Sudah Hadir</div>`,
                        timer: 2800
                    });
                } else {
                    throw new Error('Format data tidak valid');
                }
            } catch (err) {
                console.error('[SIABDES Kiosk] Gagal refresh data:', err);
                notifikasiSiabdes({
                    icon: 'error',
                    title: 'Gagal Memperbarui Data',
                    text: 'Terjadi kendala saat menghubungi server. Silakan pastikan server aktif dan coba beberapa saat lagi.'
                });
            } finally {
                this.sedangRefresh = false;
            }
        },

        async bukaModalDaftar() {
            const antreanList = await db.antrean_kehadiran.where('rapat_id').equals(this.rapatId).toArray();
            const antreanMap = new Map(antreanList.map(a => [a.undangan_id, a.id]));

            const rawList = await db.undangan
                .where('rapat_id')
                .equals(this.rapatId)
                .sortBy('nama');

            this.daftarLengkap = rawList.map(item => ({
                ...item,
                sudah_hadir: Boolean(item.sudah_hadir) || antreanMap.has(item.id),
                kehadiran_id: item.kehadiran_id || antreanMap.get(item.id) || null
            }));

            this.kueriModal = '';
            this.halamanModal = 1;
            this.pindahLayar('modal_daftar');
        },

        get daftarLengkapTerfilter() {
            if (!this.kueriModal.trim()) {
                return this.daftarLengkap;
            }
            return this.daftarLengkap.filter(item => cocokKueriToken(item, this.kueriModal));
        },

        get daftarLengkapPaginasi() {
            const list = this.daftarLengkapTerfilter;
            const start = (this.halamanModal - 1) * this.perHalamanModal;
            return list.slice(start, start + this.perHalamanModal);
        },

        get totalHalamanModal() {
            return Math.ceil(this.daftarLengkapTerfilter.length / this.perHalamanModal) || 1;
        },

        bukaModalQr() {
            this.tampilModalQr = true;
        },

        tutupModalQr() {
            this.tampilModalQr = false;
        },

        bukaFormTambahan() {
            this.namaTambahan = this.kueriCari;
            this.jabatanTambahan = '';
            this.instansiTambahan = '';
            this.hpTambahan = '';
            this.errorFormTambahan = null;
            this.pindahLayar('form_tambahan');
        },

        simpanFormTambahan() {
            if (!this.namaTambahan.trim()) {
                this.errorFormTambahan = 'Nama lengkap wajib diisi.';
                return;
            }

            const idSementara = generateUUID();
            this.undanganDipilih = {
                id: idSementara,
                rapat_id: this.rapatId,
                nama: this.namaTambahan.trim(),
                jabatan: this.jabatanTambahan.trim(),
                instansi: this.instansiTambahan.trim(),
                hp: this.hpTambahan.trim(),
                sumber: 'tambahan',
                is_baru: true,
            };

            this.ttdBase64 = null;
            this.fotoBase64 = null;
            window.dispatchEvent(new CustomEvent('siapkan-kanvas-ttd'));
            this.pindahLayar('ttd');
        },

        pindahLayar(namaLayar) {
            this.layar = namaLayar;
            window.scrollTo({ top: 0, behavior: 'smooth' });

            if (namaLayar === 'cari') {
                window.dispatchEvent(new CustomEvent('bersihkan-tanda-tangan'));
            } else if (namaLayar === 'ttd') {
                window.dispatchEvent(new CustomEvent('siapkan-kanvas-ttd'));
            }
        },

        lanjutDariTtd(dataPng, lewatiFoto = false) {
            if (!dataPng) {
                notifikasiSiabdes({
                    icon: 'warning',
                    title: 'Tanda Tangan Diperlukan',
                    text: 'Silakan goreskan tanda tangan Anda pada kotak kanvas terlebih dahulu sebelum melanjutkan.',
                    confirmButtonText: 'Mengerti'
                });
                return;
            }
            this.ttdBase64 = dataPng;
            if (lewatiFoto) {
                this.selesaikanCheckin(null);
            } else {
                this.pindahLayar('foto');
            }
        },

        async selesaikanCheckin(fotoData = null) {
            this.fotoBase64 = fotoData;

            const waktuSekarang = new Date();
            const waktuWita = waktuSekarang.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'Asia/Makassar',
            }) + ' WITA';

            const checkinId = generateUUID();

            // Simpan ke antrean lokal (IndexedDB)
            await antrean.simpanKehadiranLokal({
                id: checkinId,
                rapat_id: this.rapatId,
                undangan_id: this.undanganDipilih.id,
                ttd: this.ttdBase64,
                foto: this.fotoBase64,
                jalur: this.jalur || 'kiosk',
                waktu_perangkat: waktuSekarang.toISOString(),
                undangan_baru: this.undanganDipilih.is_baru ? {
                    nama: this.undanganDipilih.nama,
                    jabatan: this.undanganDipilih.jabatan,
                    instansi: this.undanganDipilih.instansi,
                    hp: this.undanganDipilih.hp,
                } : null,
            });

            // Tandai sudah_hadir di Dexie secara instan
            try {
                if (this.undanganDipilih.is_baru) {
                    await db.undangan.put({
                        id: this.undanganDipilih.id,
                        rapat_id: this.rapatId,
                        nama: this.undanganDipilih.nama,
                        jabatan: this.undanganDipilih.jabatan || '',
                        instansi: this.undanganDipilih.instansi || '',
                        hp: this.undanganDipilih.hp || '',
                        sumber: 'tambahan',
                        urutan: 9999,
                        nama_cari: normalisasiTeks(this.undanganDipilih.nama),
                        sudah_hadir: true,
                        kehadiran_id: checkinId,
                    });
                } else {
                    await db.undangan.update(this.undanganDipilih.id, { 
                        sudah_hadir: true,
                        kehadiran_id: checkinId,
                    });
                }
            } catch (e) {
                console.warn('[SIABDES Dexie] Gagal memperbarui status sudah_hadir:', e);
            }

            // Siapkan data kartu bukti hadir
            this.dataBuktiHadir = {
                id: checkinId,
                nama: this.undanganDipilih.nama,
                jabatan: this.undanganDipilih.jabatan,
                instansi: this.undanganDipilih.instansi,
                sumber: this.undanganDipilih.sumber,
                waktu_wita: waktuWita,
                foto: this.fotoBase64,
                ttd: this.ttdBase64,
                rapat_judul: this.rapat.judul,
                rapat_kode: this.kodeRapat,
            };

            this.pindahLayar('bukti');
            this.mulaiTimerResetOtomatis();
        },

        mulaiTimerResetOtomatis() {
            this.timerSelesai = 8;
            if (this.timerSelesaiInterval) clearInterval(this.timerSelesaiInterval);

            this.timerSelesaiInterval = setInterval(() => {
                this.timerSelesai--;
                if (this.timerSelesai <= 0) {
                    this.resetKeLayarAwal();
                }
            }, 1000);
        },

        resetKeLayarAwal() {
            if (this.timerSelesaiInterval) {
                clearInterval(this.timerSelesaiInterval);
                this.timerSelesaiInterval = null;
            }

            this.kueriCari = '';
            this.hasilCari = [];
            this.undanganDipilih = null;
            this.ttdBase64 = null;
            this.fotoBase64 = null;
            this.dataBuktiHadir = null;
            this.pindahLayar('cari');
        }
    };
}
