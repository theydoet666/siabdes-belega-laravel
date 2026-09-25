/**
 * Algoritma pencarian token & fuzzy matching nama undangan rapat.
 * Porting murni dari src/lib/pencarian.js
 */

/**
 * Normalisasi string pencarian (huruf kecil, hapus karakter khusus, pangkas spasi).
 * @param {string} str
 * @returns {string}
 */
export function normalisasiTeks(str) {
    if (!str) return '';
    return str
        .toLowerCase()
        .replace(/[^a-z0-9 ]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

/**
 * Memeriksa apakah suatu target cocok dengan kata kunci pencarian berbasis token.
 * Semua token dalam kata kunci harus ditemukan dalam salah satu kolom target.
 *
 * @param {Object} item - Objek data undangan { nama, jabatan, instansi }
 * @param {string} kueri - Kata kunci yang diketik pengguna
 * @returns {boolean}
 */
export function cocokKueriToken(item, kueri) {
    const kueriNorm = normalisasiTeks(kueri);
    if (!kueriNorm) return true;

    const tokens = kueriNorm.split(' ').filter(t => t.length > 0);
    if (tokens.length === 0) return true;

    const targetTeks = normalisasiTeks(`${item.nama || ''} ${item.jabatan || ''} ${item.instansi || ''} ${item.hp || ''}`);

    return tokens.every(token => targetTeks.includes(token));
}

/**
 * Menghitung skor relevansi pencarian untuk pengurutan hasil terbaik.
 * Skor lebih tinggi menunjukkan kecocokan lebih dekat (misal diawali dengan token vs mengandung).
 *
 * @param {Object} item
 * @param {string} kueri
 * @returns {number}
 */
export function hitungSkorRelevansi(item, kueri) {
    const kueriNorm = normalisasiTeks(kueri);
    if (!kueriNorm) return 0;

    const namaNorm = normalisasiTeks(item.nama || '');
    const instansiNorm = normalisasiTeks(item.instansi || '');
    const jabatanNorm = normalisasiTeks(item.jabatan || '');

    let skor = 0;

    // Kecocokan persis di awal nama (bobot tertinggi)
    if (namaNorm === kueriNorm) skor += 100;
    else if (namaNorm.startsWith(kueriNorm)) skor += 50;
    else if (namaNorm.includes(kueriNorm)) skor += 25;

    if (instansiNorm.includes(kueriNorm)) skor += 10;
    if (jabatanNorm.includes(kueriNorm)) skor += 5;

    return skor;
}
