import db from '../offline/db';
import antrean from '../offline/antrean';
import { normalisasiTeks, cocokKueriToken, hitungSkorRelevansi } from '../offline/pencarian';

/**
 * Komponen utama Kiosk Registrasi Mandiri Offline-First SIABDES Belega.
 * Mengelola alur state machine: cari -> (modal/form) -> ttd -> foto -> bukti
 */
export default function kioskApp(konfigurasi) {
    return {
        rapat: konfigurasi.rapat,
        rapatId: konfigurasi.rapat.id,
        kodeRapat: konfigurasi.rapat.kode,

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
                // Simpan/perbarui ke Dexie
                await db.undangan.bulkPut(daftarUndangan.map(u => ({
                    id: u.id,
                    rapat_id: u.rapat_id,
                    nama: u.nama,
                    jabatan: u.jabatan || '',
                    instansi: u.instansi || '',
                    hp: u.hp || '',
                    sumber: u.sumber || 'import',
                    urutan: u.urutan || 0,
                    nama_cari: normalisasiTeks(u.nama),
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

            // Cari dari IndexedDB lokal
            const semuaUndangan = await db.undangan
                .where('rapat_id')
                .equals(this.rapatId)
                .toArray();

            this.hasilCari = semuaUndangan
                .filter(item => cocokKueriToken(item, kueri))
                .sort((a, b) => hitungSkorRelevansi(b, kueri) - hitungSkorRelevansi(a, kueri))
                .slice(0, 6);
        },

        pilihUndangan(item) {
            this.undanganDipilih = item;
            this.kueriCari = '';
            this.hasilCari = [];
            this.pindahLayar('ttd');
        },

        async bukaModalDaftar() {
            this.daftarLengkap = await db.undangan
                .where('rapat_id')
                .equals(this.rapatId)
                .sortBy('nama');
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

            const idSementara = crypto.randomUUID();
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

            this.pindahLayar('ttd');
        },

        pindahLayar(namaLayar) {
            this.layar = namaLayar;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        lanjutDariTtd(dataPng) {
            if (!dataPng) {
                alert('Silakan tanda tangani di area kanvas terlebih dahulu.');
                return;
            }
            this.ttdBase64 = dataPng;
            this.pindahLayar('foto');
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

            const checkinId = crypto.randomUUID();

            // Simpan ke antrean lokal (IndexedDB)
            await antrean.simpanKehadiranLokal({
                id: checkinId,
                rapat_id: this.rapatId,
                undangan_id: this.undanganDipilih.id,
                ttd: this.ttdBase64,
                foto: this.fotoBase64,
                jalur: 'kiosk',
                waktu_perangkat: waktuSekarang.toISOString(),
                undangan_baru: this.undanganDipilih.is_baru ? {
                    nama: this.undanganDipilih.nama,
                    jabatan: this.undanganDipilih.jabatan,
                    instansi: this.undanganDipilih.instansi,
                    hp: this.undanganDipilih.hp,
                } : null,
            });

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
