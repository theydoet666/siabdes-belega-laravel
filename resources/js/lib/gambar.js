/**
 * Utilitas pengolahan citra kanvas tanda tangan & kompresi foto kamera wajah.
 * Porting murni dari src/lib/gambar.js
 */

/**
 * Memotong (auto-crop) area kanvas tanda tangan ke batas bounding box coretan tinta
 * dan mengekspor hasilnya sebagai PNG transparan.
 *
 * @param {HTMLCanvasElement} canvas
 * @param {number} padding - Jarak bantalan di sekitar tanda tangan dalam pixel
 * @returns {string|null} Data URL base64 image/png atau null jika kanvas kosong
 */
export function cropCanvasKeBoundingBox(canvas, padding = 12) {
    const ctx = canvas.getContext('2d');
    const w = canvas.width;
    const h = canvas.height;
    const imgData = ctx.getImageData(0, 0, w, h);
    const data = imgData.data;

    let minX = w;
    let minY = h;
    let maxX = 0;
    let maxY = 0;
    let adaCoretan = false;

    // Pindai piksel yang memiliki nilai alpha > 10
    for (let y = 0; y < h; y++) {
        for (let x = 0; x < w; x++) {
            const alphaIndex = (y * w + x) * 4 + 3;
            if (data[alphaIndex] > 10) {
                adaCoretan = true;
                if (x < minX) minX = x;
                if (x > maxX) maxX = x;
                if (y < minY) minY = y;
                if (y > maxY) maxY = y;
            }
        }
    }

    if (!adaCoretan) {
        return null;
    }

    // Terapkan bantalan (padding)
    minX = Math.max(0, minX - padding);
    minY = Math.max(0, minY - padding);
    maxX = Math.min(w, maxX + padding);
    maxY = Math.min(h, maxY + padding);

    const cropW = Math.max(1, maxX - minX);
    const cropH = Math.max(1, maxY - minY);

    const cropCanvas = document.createElement('canvas');
    cropCanvas.width = cropW;
    cropCanvas.height = cropH;

    const cropCtx = cropCanvas.getContext('2d');
    cropCtx.drawImage(canvas, minX, minY, cropW, cropH, 0, 0, cropW, cropH);

    return cropCanvas.toDataURL('image/png');
}

/**
 * Mengambil tangkapan frame dari elemen video kamera depan,
 * membalik secara horizontal (mirror) dan mengompres ke JPEG.
 *
 * @param {HTMLVideoElement} videoEl
 * @param {number} maxWidth
 * @param {number} quality
 * @returns {string} Data URL base64 image/jpeg
 */
export function kompresiFotoVideo(videoEl, maxWidth = 800, quality = 0.85) {
    const canvas = document.createElement('canvas');
    let w = videoEl.videoWidth || 640;
    let h = videoEl.videoHeight || 480;

    if (w > maxWidth) {
        h = Math.round((h * maxWidth) / w);
        w = maxWidth;
    }

    canvas.width = w;
    canvas.height = h;
    const ctx = canvas.getContext('2d');

    // Efek cermin (mirror) horizontal untuk kamera depan
    ctx.translate(w, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(videoEl, 0, 0, w, h);

    return canvas.toDataURL('image/jpeg', quality);
}
