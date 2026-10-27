import Dexie from 'dexie';

/**
 * Inisialisasi basis data IndexedDB lokal untuk kiosk offline-first SIABDES Belega.
 * Skema disesuaikan dari skema Dexie.js asli di src/lib/db.js
 */
export const db = new Dexie('siabdes_kiosk_db');

db.version(2).stores({
    undangan: 'id, rapat_id, nama, jabatan, instansi, hp, sumber, urutan, nama_cari, sudah_hadir',
    antrean_kehadiran: 'id, rapat_id, undangan_id, jalur, waktu_perangkat, status_sinkron, status_coba, dibuat_pada'
});

export default db;
